<?php
declare(strict_types=1);

function handle_master_post(string $table, array $config): never {
    csrf_validate();
    $action = (string)($_POST['action'] ?? '');
    $id = valid_positive_id($_POST['id'] ?? null);
    $pdo = db();
    if ($action === 'enable' || $action === 'disable') {
        require_admin();
        if (!$id) { http_response_code(422); exit('ID tidak valid.'); }
        $check = $pdo->prepare("SELECT id FROM {$table} WHERE id = ?");
        $check->execute([$id]);
        if (!$check->fetch()) { http_response_code(404); exit('Data tidak ditemukan.'); }
        $stmt = $pdo->prepare("UPDATE {$table} SET active = ? WHERE id = ?");
        $stmt->execute([$action === 'enable' ? 1 : 0, $id]);
        audit_log('status_' . $table, "ID {$id}: {$action}");
        flash('Status berhasil diperbarui.');
        go($table);
    }
    if ($action !== 'save') { http_response_code(400); exit('Aksi tidak dikenal.'); }
    if (isset($_POST['id']) && trim((string)$_POST['id']) !== '' && !$id) {
        http_response_code(422); exit('ID edit tidak valid.');
    }
    $input = [];
    $errors = [];
    foreach ($config['fields'] as $field => $meta) {
        $raw = $_POST[$field] ?? '';
        $value = is_string($raw) ? trim($raw) : '';
        if ($meta[1] && $value === '') $errors[] = $meta[0] . ' wajib diisi.';
        if (text_length($value) > $meta[2]) $errors[] = $meta[0] . ' maksimal ' . $meta[2] . ' karakter.';
        $input[$field] = $value === '' ? null : $value;
    }
    if ($errors) {
        $_SESSION['master_old'] = $input;
        flash(implode(' ', $errors), 'error');
        go($table, $id ? ['edit' => $id] : []);
    }
    if ($id) {
        $stmt = $pdo->prepare("SELECT id FROM {$table} WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) { http_response_code(404); exit('Data yang akan diedit tidak ditemukan.'); }
    }
    $wasEdit = $id !== null;
    try {
        $fields = array_keys($input);
        if ($id) {
            $assignment = implode(',', array_map(static fn(string $name): string => "`{$name}` = ?", $fields));
            $sql = "UPDATE {$table} SET {$assignment} WHERE id = ?";
            $pdo->prepare($sql)->execute([...array_values($input), $id]);
            audit_log('edit_' . $table, "Edit ID {$id}");
        } else {
            $columns = implode(',', array_map(static fn(string $name): string => "`{$name}`", $fields));
            $marks = implode(',', array_fill(0, count($fields), '?'));
            $pdo->prepare("INSERT INTO {$table} ({$columns}) VALUES ({$marks})")->execute(array_values($input));
            $id = (int)$pdo->lastInsertId();
            audit_log('tambah_' . $table, "Tambah ID {$id}");
        }
    } catch (PDOException $exception) {
        error_log('Master save: ' . $exception->getMessage());
        $_SESSION['master_old'] = $input;
        flash('Data belum dapat disimpan. Periksa kembali isinya.', 'error');
        go($table, $id ? ['edit' => $id] : []);
    }
    flash($wasEdit ? 'Perubahan data berhasil disimpan.' : 'Data baru berhasil ditambahkan.');
    go($table, ['edit' => $id]);
}

function render_master_page(string $table, array $config, array $user): void {
    $pdo = db();
    $rows = $pdo->query("SELECT * FROM {$table} ORDER BY id DESC LIMIT 250")->fetchAll();
    $editId = valid_positive_id($_GET['edit'] ?? null);
    $edit = null;
    if ($editId) {
        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE id = ?");
        $stmt->execute([$editId]);
        $edit = $stmt->fetch() ?: null;
    }
    $old = $_SESSION['master_old'] ?? null;
    unset($_SESSION['master_old']);
    if (is_array($old)) $edit = array_merge($edit ?? [], $old);
    ?>
    <div class="page-heading"><div><span class="page-eyebrow">DATA REFERENSI</span><h1><?=h($config['name'])?></h1><p>Lengkapi data di formulir, lalu simpan. Gunakan Edit untuk memperbarui data yang sudah ada.</p></div>
    <?php if ($editId): ?><a class="btn btn-outline" href="<?=h(route_url($table))?>#form-edit"><?=bkk_ui_icon('plus',18)?> Tambah data baru</a><?php else: ?><a class="back-action" href="#form-edit"><?=bkk_ui_icon('edit',17)?> Isi formulir</a><?php endif; ?></div>
    <?php if ($editId && !$edit): ?><div class="alert error">Data dengan ID <?=h($editId)?> tidak ditemukan.</div><?php endif; ?>
    <div class="master-grid">
      <section class="card master-editor" id="form-edit">
        <div class="section-header"><h2><?= $editId ? 'Edit data' : 'Tambah data baru' ?></h2>
        <?php if ($editId): ?><span class="pill">ID #<?=h($editId)?></span><?php endif; ?></div>
        <p class="muted">Kolom bertanda * wajib diisi.</p><form method="post" action="<?=h(route_url($table, $editId ? ['edit'=>$editId] : []))?>">
          <input type="hidden" name="_csrf" value="<?=h(csrf())?>">
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="id" value="<?=h($editId ?? '')?>">
          <?php foreach ($config['fields'] as $field=>$meta): ?>
          <label><?=h($meta[0])?><?= $meta[1] ? ' *' : '' ?>
          <?php if ($field === 'alamat'): ?><textarea name="<?=h($field)?>" maxlength="<?=h($meta[2])?>" rows="3"><?=h($edit[$field] ?? '')?></textarea>
          <?php else: ?><input type="text" name="<?=h($field)?>" value="<?=h($edit[$field] ?? '')?>" maxlength="<?=h($meta[2])?>" <?= $meta[1] ? 'required' : '' ?>><?php endif; ?>
          </label>
          <?php endforeach; ?>
          <div class="form-actions"><button type="submit" class="btn btn-primary" data-loading-label="Menyimpan data..."><?=bkk_ui_icon('check',17)?> <?= $editId ? 'Simpan perubahan' : 'Simpan data' ?></button>
          <?php if ($editId): ?><a class="btn secondary" href="<?=h(route_url($table))?>">Batal Edit</a><?php endif; ?></div>
        </form>
      </section>
      <section class="card master-list"><div class="section-header"><h2>Data tersimpan</h2><span class="pill pill-gray"><?=count($rows)?> ditampilkan</span></div><p class="muted">Klik Edit pada baris yang ingin diperbarui. Status hanya bisa diubah oleh admin.</p>
      <div class="table-scroll"><table><thead><tr><th>ID</th>
        <?php foreach ($config['fields'] as $meta): ?><th><?=h($meta[0])?></th><?php endforeach; ?>
        <th>Status</th><th>Aksi</th></tr></thead><tbody>
        <?php foreach ($rows as $row): ?><tr class="<?= $editId === (int)$row['id'] ? 'row-selected' : '' ?>">
          <td>#<?=h($row['id'])?></td>
          <?php foreach ($config['fields'] as $field=>$meta): ?><td><?=h($row[$field] ?? '')?></td><?php endforeach; ?>
          <td><span class="pill <?= $row['active'] ? 'pill-green' : 'pill-gray' ?>"><?= $row['active'] ? 'Aktif' : 'Nonaktif' ?></span></td>
          <td class="action-cell"><a class="btn tiny secondary" href="<?=h(route_url($table, ['edit'=>(int)$row['id']]))?>#form-edit"><?=bkk_ui_icon('edit',14)?> Edit</a>
          <?php if ($user['role'] === 'admin'): ?>
            <form method="post" class="inline" action="<?=h(route_url($table))?>" onsubmit="return confirm('Yakin ingin mengubah status data ini?')">
            <input type="hidden" name="_csrf" value="<?=h(csrf())?>"><input type="hidden" name="id" value="<?=h($row['id'])?>">
            <input type="hidden" name="action" value="<?= $row['active'] ? 'disable' : 'enable' ?>">
            <button type="submit" class="linkbtn"><?= $row['active'] ? 'Nonaktifkan' : 'Aktifkan' ?></button></form>
          <?php endif; ?></td>
        </tr><?php endforeach; ?></tbody></table></div>
        <?php if (!$rows): ?><p>Belum ada data. Gunakan formulir di sebelah kiri untuk menambahkan.</p><?php endif; ?>
      </section>
    </div>
    <?php
}
