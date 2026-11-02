<?php
declare(strict_types=1);

// Tabel dan nama kolom selalu berasal dari whitelist internal, bukan input pengguna.
function master_config(string $key): ?array {
    $definitions = [
        'kapal' => ['name' => 'Data Kapal', 'fields' => [
            'nama_kapal' => ['Nama kapal', true, 180],
            'nomor_imo' => ['Nomor IMO', false, 30],
            'kebangsaan' => ['Kebangsaan', false, 100],
            'grt' => ['GRT', false, 40],
        ]],
        'keagenan' => ['name' => 'Data Keagenan', 'fields' => [
            'nama_agen' => ['Nama agen', true, 180],
            'alamat' => ['Alamat', false, 2000],
            'kontak' => ['Kontak', false, 100],
        ]],
        'pejabat' => ['name' => 'Data Pejabat', 'fields' => [
            'nama' => ['Nama pejabat', true, 150],
            'nip' => ['NIP', false, 30],
            'jabatan' => ['Jabatan', true, 200],
        ]],
    ];
    return $definitions[$key] ?? null;
}
function valid_positive_id(mixed $input): ?int {
    $id = filter_var($input, FILTER_VALIDATE_INT);
    return $id !== false && $id > 0 ? (int)$id : null;
}
