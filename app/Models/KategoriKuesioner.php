<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriKuesioner extends Model
{
    protected $table = 'kategori_kuesioner';

    protected $fillable = [
        'nama_kategori',
        //'bobot_kategori'
    ];

    public function kuesioner()
    {
        return $this->hasMany(Kuesioner::class, 'kategori_id');
    }
}