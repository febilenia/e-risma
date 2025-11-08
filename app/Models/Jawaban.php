<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jawaban extends Model
{
    use HasFactory;

    protected $table = 'jawaban';

    protected $fillable = [
        'responden_id',
        'aplikasi_id',
        'kuesioner_id',
        'skor',      // 1..5 untuk pertanyaan radio
        'isi_teks',  // untuk free_text
    ];

    protected $casts = [
        'skor' => 'integer',
    ];

    public function responden()
    {
        return $this->belongsTo(Responden::class);
    }

    public function aplikasi()
    {
        return $this->belongsTo(Aplikasi::class);
    }

    public function kuesioner()
    {
        return $this->belongsTo(Kuesioner::class, 'kuesioner_id');
    }
}