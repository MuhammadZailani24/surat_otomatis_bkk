<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/masters.php';
require __DIR__ . '/../app/penerbitan_service.php';

function assert_test(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException('GAGAL: ' . $message);
    echo "OK: {$message}\n";
}
assert_test(h(42) === '42', 'HTML escaping menerima ID integer (perbaikan Edit)');
assert_test(h('<script>') === '&lt;script&gt;', 'HTML escaping aman');
assert_test(bkk_date_is_valid('2026-10-31'), 'tanggal valid');
assert_test(!bkk_date_is_valid('2026-02-30'), 'tanggal mustahil ditolak');

$input = [
    'nomor_st' => 'SR.04.02/C.X.11/123/2026',
    'nomor_sppd' => 'KU.01.02/C.X.11/456/2026',
    'nama_kegiatan' => 'Pemeriksaan kapal',
    'maksud_perjalanan' => 'Melaksanakan tugas pemeriksaan kapal',
    'tempat_berangkat' => 'Banjarmasin',
    'tujuan' => 'Taboneo',
    'tanggal_surat' => '2026-10-31',
    'tanggal_tugas' => '2026-10-31',
    'tanggal_ttd' => '2026-10-31',
    'tanggal_pemeriksaan' => '2026-10-31',
    'tanggal_berangkat' => '2026-10-30',
    'tanggal_kembali' => '2026-10-31',
    'tanggal_pernyataan' => '2026-10-31',
    'jumlah_kapal' => '2',
    'petugas' => [
        1 => ['nama'=>'Petugas Satu','nip'=>'198901010000001','pangkat_golongan'=>'III/a','jabatan'=>'Perawat'],
        2 => ['nama'=>'Petugas Dua','nip'=>'198901010000002','pangkat_golongan'=>'III/b','jabatan'=>'Epidemiolog'],
    ],
    'kapal' => [
        1 => ['nama_kapal'=>'Kapal Satu','nama_agen'=>'Agen Satu','posisi_labuh'=>'Taboneo',
            'nama_nakhoda'=>'Nakhoda Satu','jabatan_nakhoda'=>'Nakhoda','status_pemeriksaan'=>'belum_ditetapkan'],
        2 => ['nama_kapal'=>'Kapal Dua','nama_agen'=>'Agen Dua','posisi_labuh'=>'Taboneo',
            'nama_nakhoda'=>'Nakhoda Dua','jabatan_nakhoda'=>'Nakhoda','status_pemeriksaan'=>'memenuhi'],
    ],
];
[$data,$errors] = validate_penerbitan($input);
assert_test($errors === [], 'draft dua petugas + dua kapal valid: ' . implode(', ', $errors));
$split = $input;
unset($split['nomor_st'], $split['nomor_sppd']);
$split['nomor_st_urut'] = '789';
$split['nomor_st_tahun'] = '2026';
$split['nomor_sppd_urut'] = '101';
$split['nomor_sppd_tahun'] = '2027';
[$splitData,$splitErrors] = validate_penerbitan($split);
assert_test($splitErrors === [] && $splitData['nomor_st'] === 'SR.04.02/C.X.11/789/2026'
    && $splitData['nomor_sppd'] === 'KU.01.02/C.X.11/101/2027',
    'form nomor urut/tahun otomatis menyusun awalan resmi');
$split['nomor_st_tahun'] = '20AA';
[, $badYear] = validate_penerbitan($split);
assert_test(count($badYear)>0, 'tahun surat bukan 4 angka ditolak');
$split['nomor_st_tahun'] = '2026';
$split['nomor_sppd_urut'] = 'KU.01.02/C.X.11/101';
[, $badDoublePrefix] = validate_penerbitan($split);
assert_test(count($badDoublePrefix)>0, 'awalan ganda pada input nomor urut ditolak');
assert_test($data['lama_hari'] === 2, 'lama perjalanan dihitung termasuk dua hari');
assert_test(count($data['kapal']) === 2, 'dua kapal tersimpan pada payload');
$input['petugas'][2]['nip'] = $input['petugas'][1]['nip'];
[, $errors] = validate_penerbitan($input);
assert_test(count($errors) >= 1, 'NIP petugas yang sama ditolak');
$input['petugas'][2]['nip'] = '198901010000002';
$input['tanggal_kembali'] = '2026-10-29';
[, $errors] = validate_penerbitan($input);
assert_test(count($errors) >= 1, 'tanggal kembali sebelum berangkat ditolak');
$input['tanggal_kembali'] = '2026-10-31';
$input['jumlah_kapal'] = '1';
[$data,$errors] = validate_penerbitan($input);
assert_test($errors === [] && count($data['kapal']) === 1, 'mode satu kapal mengabaikan kapal kedua');
echo "Semua unit test PASS (tidak termasuk MySQL).\n";
