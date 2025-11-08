<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aplikasi extends Model
{
    protected $table = 'aplikasi';

    protected $fillable = [
        'nama_aplikasi',
        'opd_id',
        'id_encrypt',
        'link_survey',
        'status', // 'open'|'closed'
    ];

    public function opd()
    {
        return $this->belongsTo(\App\Models\OPD::class, 'opd_id');
    }

    public function jawaban()
    {
        return $this->hasMany(\App\Models\Jawaban::class, 'aplikasi_id');
    }

    public function responden()
    {
        return $this->hasMany(\App\Models\Responden::class, 'aplikasi_id');
    }
}