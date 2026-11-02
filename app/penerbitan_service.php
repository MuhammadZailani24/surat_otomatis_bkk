<?php
declare(strict_types=1);

function bkk_date_is_valid(string $value): bool {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) return false;
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
}
function bkk_date_id(string $value): string {
    if (!bkk_date_is_valid($value)) return $value;
    $d = new DateTimeImmutable($value);
    $months = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
    return $d->format('j') . ' ' . $months[(int)$d->format('n')] . ' ' . $d->format('Y');
}
function bkk_field(mixed $value): string {
    return is_string($value) ? trim($value) : '';
}
function bkk_required(array &$errors, string $value, string $label, int $max, bool $required = true): void {
    if ($required && $value === '') $errors[] = $label . ' wajib diisi.';
    if (text_length($value) > $max) $errors[] = $label . " maksimal {$max} karakter.";
}
/** Validation independent of database to enable CLI tests. */
function validate_penerbitan(array $post): array {
    $errors = [];
    $fields = [
        'nomor_st' => ['Nomor Surat Tugas', 120, true],
        'nomor_sppd' => ['Nomor SPPD', 120, true],
        'nama_kegiatan' => ['Nama kegiatan', 5000, true],
        'maksud_perjalanan' => ['Maksud perjalanan', 5000, true],
        'tempat_berangkat' => ['Tempat berangkat', 150, true],
        'tujuan' => ['Tempat tujuan', 200, true],
    ];
    // New UI supplies only number and year. Persist the complete legacy-compatible
    // values, so existing drafts and the unique MySQL indexes need no migration.
    foreach (['nomor_st'=>'SR.04.02/C.X.11', 'nomor_sppd'=>'KU.01.02/C.X.11'] as $field=>$prefix) {
        if (array_key_exists($field.'_urut',$post) || array_key_exists($field.'_tahun',$post)) {
            $sequence = bkk_field($post[$field.'_urut'] ?? '');
            $year = bkk_field($post[$field.'_tahun'] ?? '');
            if (!preg_match('/^[A-Za-z0-9.-]{1,50}$/D',$sequence)) {
                $errors[] = 'Nomor urut '.($field==='nomor_st'?'ST':'SPPD').' hanya boleh huruf, angka, titik, atau tanda hubung (1–50 karakter).';
            }
            if (!preg_match('/^[0-9]{4}$/D',$year)) $errors[] = 'Tahun '.($field==='nomor_st'?'ST':'SPPD').' harus empat angka.';
            $post[$field] = $prefix.'/'.$sequence.'/'.$year;
        }
        // Existing API and old saved forms remain accepted, but immutable prefix is checked.
        if (!preg_match('~^'.preg_quote($prefix,'~').'/[A-Za-z0-9.-]{1,50}/[0-9]{4}$~D',bkk_field($post[$field] ?? ''))) {
            $errors[] = 'Format nomor '.($field==='nomor_st'?'ST':'SPPD').' tidak sesuai kode tetap '.$prefix.'/NOMOR/TAHUN.';
        }
    }
    $data = [];
    foreach ($fields as $key => [$label, $max, $required]) {
        $data[$key] = bkk_field($post[$key] ?? '');
        bkk_required($errors, $data[$key], $label, $max, $required);
    }
    $dates = [
        'tanggal_surat'=>'Tanggal surat', 'tanggal_tugas'=>'Tanggal berlaku ST',
        'tanggal_ttd'=>'Tanggal tanda tangan ST', 'tanggal_pemeriksaan'=>'Tanggal pemeriksaan',
        'tanggal_berangkat'=>'Tanggal berangkat', 'tanggal_kembali'=>'Tanggal kembali',
        'tanggal_pernyataan'=>'Tanggal Surat Pernyataan',
    ];
    foreach ($dates as $key=>$label) {
        $data[$key] = bkk_field($post[$key] ?? '');
        if (!bkk_date_is_valid($data[$key])) $errors[] = $label . ' harus berupa tanggal valid.';
    }
    if (bkk_date_is_valid($data['tanggal_berangkat']) && bkk_date_is_valid($data['tanggal_kembali'])) {
        $start = new DateTimeImmutable($data['tanggal_berangkat']);
        $end = new DateTimeImmutable($data['tanggal_kembali']);
        if ($start > $end) $errors[] = 'Tanggal kembali tidak boleh sebelum tanggal berangkat.';
        $data['lama_hari'] = (int)$start->diff($end)->days + 1;
        if ($data['lama_hari'] > 365) $errors[] = 'Lama perjalanan tidak boleh lebih dari 365 hari.';
    } else {
        $data['lama_hari'] = 1;
    }
    $data['pejabat_st_id'] = valid_positive_id($post['pejabat_st_id'] ?? null);
    $data['pejabat_ppk_id'] = valid_positive_id($post['pejabat_ppk_id'] ?? null);
    $data['petugas'] = [];
    $inputPetugas = is_array($post['petugas'] ?? null) ? $post['petugas'] : [];
    foreach ([1,2] as $i) {
        $row = is_array($inputPetugas[$i] ?? null) ? $inputPetugas[$i] : [];
        $person = [];
        foreach (['nama'=>['Nama',150], 'nip'=>['NIP',30], 'pangkat_golongan'=>['Pangkat/golongan',120], 'jabatan'=>['Jabatan',200]] as $key=>[$label,$max]) {
            $person[$key] = bkk_field($row[$key] ?? '');
            bkk_required($errors, $person[$key], $label . " petugas {$i}", $max);
        }
        $data['petugas'][$i] = $person;
    }
    if ($data['petugas'][1]['nip'] !== '' && $data['petugas'][1]['nip'] === $data['petugas'][2]['nip']) {
        $errors[] = 'Petugas pertama dan kedua harus berbeda (NIP tidak boleh sama).';
    }
    $jumlahKapal = filter_var($post['jumlah_kapal'] ?? null, FILTER_VALIDATE_INT);
    if (!in_array($jumlahKapal, [1,2], true)) {
        $errors[] = 'Jumlah kapal harus 1 atau 2.';
        $jumlahKapal = 1;
    }
    $data['jumlah_kapal'] = $jumlahKapal;
    $data['kapal'] = [];
    $inputKapal = is_array($post['kapal'] ?? null) ? $post['kapal'] : [];
    foreach (range(1, $jumlahKapal) as $i) {
        $row = is_array($inputKapal[$i] ?? null) ? $inputKapal[$i] : [];
        $ship = [];
        $f = [
            'nama_kapal'=>['Nama kapal',180,true], 'nama_agen'=>['Nama agen',180,true],
            'posisi_labuh'=>['Posisi labuh',200,true], 'kebangsaan'=>['Kebangsaan',100,false],
            'grt'=>['GRT',40,false], 'nomor_imo'=>['Nomor IMO',30,false],
            'nama_nakhoda'=>['Nama nakhoda',150,true], 'jabatan_nakhoda'=>['Jabatan nakhoda',100,true],
            'rekomendasi'=>['Catatan rekomendasi',5000,false],
        ];
        foreach ($f as $key=>[$label,$max,$required]) {
            $ship[$key] = bkk_field($row[$key] ?? '');
            bkk_required($errors, $ship[$key], $label . " kapal {$i}", $max, $required);
        }
        $ship['kapal_id'] = valid_positive_id($row['kapal_id'] ?? null);
        $ship['keagenan_id'] = valid_positive_id($row['keagenan_id'] ?? null);
        $ship['status_pemeriksaan'] = bkk_field($row['status_pemeriksaan'] ?? 'belum_ditetapkan');
        if (!in_array($ship['status_pemeriksaan'], ['memenuhi','tidak_memenuhi','belum_ditetapkan'], true)) {
            $errors[] = "Status pemeriksaan kapal {$i} tidak dikenal.";
        }
        $data['kapal'][$i] = $ship;
    }
    return [$data, $errors];
}
function bkk_penerbitan_load(int $id): ?array {
    $stmt = db()->prepare('SELECT * FROM penerbitan WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) return null;
    $stmt = db()->prepare('SELECT * FROM penerbitan_petugas WHERE penerbitan_id = ? ORDER BY urutan');
    $stmt->execute([$id]);
    $row['petugas'] = [];
    foreach ($stmt->fetchAll() as $petugas) $row['petugas'][(int)$petugas['urutan']] = $petugas;
    $stmt = db()->prepare('SELECT * FROM penerbitan_kapal WHERE penerbitan_id = ? ORDER BY urutan');
    $stmt->execute([$id]);
    $row['kapal'] = [];
    foreach ($stmt->fetchAll() as $kapal) $row['kapal'][(int)$kapal['urutan']] = $kapal;
    $row['jumlah_kapal'] = count($row['kapal']);
    return $row;
}
function bkk_is_editable(array $record, array $user): bool {
    return $record['status'] === 'draft' && ($user['role'] === 'admin' || (int)$record['created_by'] === (int)$user['id']);
}
function bkk_pejabat_snapshot(?int $id): ?string {
    if (!$id) return null;
    $stmt = db()->prepare('SELECT id,nama,nip,jabatan FROM pejabat WHERE id = ? AND active = 1');
    $stmt->execute([$id]);
    $pejabat = $stmt->fetch();
    if (!$pejabat) throw new DomainException('Pejabat yang dipilih tidak tersedia atau nonaktif.');
    return json_encode($pejabat, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
}
function bkk_check_master_reference(string $table, ?int $id): void {
    if (!$id) return;
    if (!in_array($table, ['kapal','keagenan'], true)) throw new LogicException('Tabel master tidak valid.');
    $stmt = db()->prepare("SELECT id FROM {$table} WHERE id = ? AND active = 1");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) throw new DomainException('Referensi ' . $table . ' tidak ditemukan atau nonaktif.');
}
function bkk_save_penerbitan(array $data, ?int $id): int {
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $snapshotST = bkk_pejabat_snapshot($data['pejabat_st_id']);
        $snapshotPPK = bkk_pejabat_snapshot($data['pejabat_ppk_id']);
        foreach ($data['kapal'] as $ship) {
            bkk_check_master_reference('kapal', $ship['kapal_id']);
            bkk_check_master_reference('keagenan', $ship['keagenan_id']);
        }
        $keys = ['nomor_st','nomor_sppd','nama_kegiatan','maksud_perjalanan','tanggal_surat',
            'tanggal_tugas','tanggal_ttd','tanggal_pemeriksaan','tanggal_berangkat','tanggal_kembali',
            'tanggal_pernyataan','tempat_berangkat','tujuan','lama_hari','pejabat_st_id','pejabat_ppk_id'];
        $values = array_map(static fn(string $key): mixed => $data[$key], $keys);
        if ($id) {
            $assignment = implode(', ', array_map(static fn(string $key): string => "`{$key}` = ?", $keys));
            $stmt = $pdo->prepare("UPDATE penerbitan SET {$assignment}, snapshot_pejabat_st=?, snapshot_pejabat_ppk=? WHERE id=? AND status='draft'");
            $stmt->execute([...$values, $snapshotST, $snapshotPPK, $id]);
            if ($stmt->rowCount() !== 1) {
                // MySQL normally counts changed rows. Check status if values were identical.
                $check = $pdo->prepare("SELECT status FROM penerbitan WHERE id = ? FOR UPDATE");
                $check->execute([$id]);
                if ($check->fetchColumn() !== 'draft') throw new DomainException('Hanya draft yang bisa diedit.');
            }
            $pdo->prepare('DELETE FROM penerbitan_petugas WHERE penerbitan_id = ?')->execute([$id]);
            $pdo->prepare('DELETE FROM penerbitan_kapal WHERE penerbitan_id = ?')->execute([$id]);
        } else {
            $code = 'PKT-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(4)));
            $columns = implode(',', array_map(static fn(string $key): string => "`{$key}`", $keys));
            $marks = implode(',', array_fill(0, count($keys), '?'));
            $stmt = $pdo->prepare("INSERT INTO penerbitan (kode_paket,{$columns},snapshot_pejabat_st,snapshot_pejabat_ppk,status,created_by) VALUES (?,{$marks},?,?,'draft',?)");
            $stmt->execute([$code, ...$values, $snapshotST, $snapshotPPK, (int)$_SESSION['uid']]);
            $id = (int)$pdo->lastInsertId();
        }
        $personInsert = $pdo->prepare('INSERT INTO penerbitan_petugas (penerbitan_id,urutan,nama,nip,pangkat_golongan,jabatan) VALUES (?,?,?,?,?,?)');
        foreach ($data['petugas'] as $i=>$p) {
            $personInsert->execute([$id, $i, $p['nama'], $p['nip'], $p['pangkat_golongan'], $p['jabatan']]);
        }
        $shipInsert = $pdo->prepare('INSERT INTO penerbitan_kapal (penerbitan_id,urutan,kapal_id,keagenan_id,nama_kapal,nama_agen,posisi_labuh,kebangsaan,grt,nomor_imo,nama_nakhoda,jabatan_nakhoda,status_pemeriksaan,rekomendasi) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
        foreach ($data['kapal'] as $i=>$s) {
            $shipInsert->execute([$id, $i, $s['kapal_id'], $s['keagenan_id'], $s['nama_kapal'],
                $s['nama_agen'], $s['posisi_labuh'], $s['kebangsaan'], $s['grt'], $s['nomor_imo'],
                $s['nama_nakhoda'], $s['jabatan_nakhoda'], $s['status_pemeriksaan'], $s['rekomendasi']]);
        }
        audit_log($data['_is_edit'] ? 'edit_draft_penerbitan' : 'buat_draft_penerbitan', 'Draft surat ' . $data['nomor_st'], $id);
        $pdo->commit();
        if ($data['_is_edit']) bkk_doc_invalidate($id); // Edit draft membatalkan preview lama.
        return $id;
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $error;
    }
}
function handle_penerbitan_post(array $user): never {
    csrf_validate();
    if (($_POST['action'] ?? '') !== 'save_draft') {
        http_response_code(400); exit('Aksi tidak valid.');
    }
    $id = valid_positive_id($_POST['id'] ?? null);
    if (isset($_POST['id']) && trim((string)$_POST['id']) !== '' && !$id) {
        http_response_code(422); exit('ID draft tidak valid.');
    }
    if ($id) {
        $old = bkk_penerbitan_load($id);
        if (!$old) { http_response_code(404); exit('Draft tidak ditemukan.'); }
        if (!bkk_is_editable($old, $user)) {
            http_response_code(403); exit('Tidak dapat mengubah dokumen ini.');
        }
    }
    [$data, $errors] = validate_penerbitan($_POST);
    $data['_is_edit'] = (bool)$id;
    $goBack = $id ? ['edit'=>$id] : ['new'=>1];
    if ($errors) {
        $_SESSION['penerbitan_old'] = $data;
        $_SESSION['penerbitan_errors'] = $errors;
        go('penerbitan', $goBack);
    }
    try {
        $saved = bkk_save_penerbitan($data, $id);
    } catch (DomainException $error) {
        $_SESSION['penerbitan_old'] = $data;
        $_SESSION['penerbitan_errors'] = [$error->getMessage()];
        go('penerbitan', $goBack);
    } catch (PDOException $error) {
        error_log('Simpan draft: ' . $error->getMessage());
        $_SESSION['penerbitan_old'] = $data;
        $_SESSION['penerbitan_errors'] = [$error->getCode() === '23000'
            ? 'Nomor ST atau SPPD sudah digunakan. Gunakan nomor lain.'
            : 'Gagal menyimpan draft. Periksa koneksi database dan coba lagi.'];
        go('penerbitan', $goBack);
    }
    flash($id ? 'Draft berhasil diperbarui.' : 'Draft penerbitan berhasil disimpan.');
    go('penerbitan', ['view'=>$saved]);
}
