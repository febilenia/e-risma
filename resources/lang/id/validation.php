<?php

return [
    'accepted'             => 'Kolom :attribute harus diterima.',
    'active_url'           => 'Kolom :attribute bukan URL yang valid.',
    'after'                => 'Kolom :attribute harus berisi tanggal setelah :date.',
    'alpha'                => 'Kolom :attribute hanya boleh berisi huruf.',
    'alpha_num'            => 'Kolom :attribute hanya boleh berisi huruf dan angka.',
    'array'                => 'Kolom :attribute harus berupa sebuah array.',
    'before'               => 'Kolom :attribute harus berisi tanggal sebelum :date.',
    'between'              => [
        'numeric' => 'Kolom :attribute harus bernilai antara :min dan :max.',
        'string'  => 'Kolom :attribute harus terdiri dari :min sampai :max karakter.',
    ],
    'boolean'              => 'Kolom :attribute harus bernilai benar atau salah.',
    'confirmed'            => 'Konfirmasi :attribute tidak cocok.',
    'date'                 => 'Kolom :attribute bukan tanggal yang valid.',
    'date_format'          => 'Kolom :attribute tidak cocok dengan format :format.',
    'different'            => 'Kolom :attribute dan :other harus berbeda.',
    'digits'               => 'Kolom :attribute harus terdiri dari :digits digit.',
    'email'                => 'Kolom :attribute harus berupa alamat surel yang valid.',
    'exists'               => 'Kolom :attribute yang dipilih tidak valid.',
    'integer'              => 'Kolom :attribute harus berupa bilangan bulat.',
    'max'                  => [
        'string'  => 'Kolom :attribute tidak boleh lebih dari :max karakter.',
    ],
    'min'                  => [
        'string'  => 'Kolom :attribute minimal :min karakter.',
    ],
    'not_in'               => 'Kolom :attribute yang dipilih tidak valid.',
    'numeric'              => 'Kolom :attribute harus berupa angka.',
    'required'             => 'Kolom :attribute wajib diisi.',
    'same'                 => 'Kolom :attribute dan :other harus sama.',
    'size'                 => [
        'string'  => 'Kolom :attribute harus terdiri dari :size karakter.',
    ],
    'string'               => 'Kolom :attribute harus berupa teks.',
    'unique'               => 'Kolom :attribute sudah digunakan.',
    'url'                  => 'Format kolom :attribute tidak valid.',

    // Custom attribute
    'attributes' => [
        'pertanyaan' => 'Pertanyaan',
        'tipe' => 'Tipe Kuesioner',
        'kategori_id' => 'Kategori',
        'is_mandatory' => 'Wajib Diisi',
        'urutan' => 'Nomor Urut',
    ],
];
