<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Responden extends Model
{
    use HasFactory;

    protected $table = 'responden';

    protected $fillable = [
        'aplikasi_id',
        'nama',
        // 'tanggal_lahir',  
        'usia',
        'no_hp',
        'jenis_kelamin',
    ];

    public function jawaban()
    {
        return $this->hasMany(Jawaban::class);
    }
}