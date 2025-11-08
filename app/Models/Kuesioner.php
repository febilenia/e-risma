<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kuesioner extends Model
{
    protected $table = 'kuesioner';

    protected $fillable = [
        'pertanyaan',
        'tipe',
        'is_mandatory',
        'urutan',
        'persepsi',
        'kategori_id',
        'gambar',
        'skala_type',
        'skala_labels'
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'urutan'       => 'integer',
        'skala_labels' => 'array',
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriKuesioner::class, 'kategori_id');
    }

    public function jawaban()
    {
        return $this->hasMany(Jawaban::class, 'kuesioner_id');
    }

    // scopes opsional
    public function scopeByKategori($q, $kategoriId)
    {
        return $q->where('kategori_id', $kategoriId);
    }

    public function scopeByTipe($q, $tipe)
    {
        return $q->where('tipe', $tipe);
    }

    public function scopeByPersepsi($q, $persepsi)
    {
        return $q->where('persepsi', $persepsi);
    }

    public function scopeMandatory($q)
    {
        return $q->where('is_mandatory', true);
    }

    public function getMandatoryStatusAttribute()
    {
        return $this->is_mandatory ? 'Wajib' : 'Tidak Wajib';
    }

    public function getSkalaLabelsForDisplay(): array
    {
        if ($this->tipe === 'free_text') {
            return [];
        }

        if (is_null($this->skala_type)) {
            return [];
        }

        return $this->getSkalaLabelsArray();
    }

    public function getSkalaLabelsArray(): array
    {
        if ($this->tipe === 'free_text') {
            return [];
        }

        if (is_null($this->skala_type)) {
            return [];
        }

        // Jika ada custom labels, gunakan itu
        if (!empty($this->skala_labels) && is_array($this->skala_labels)) {
            return $this->skala_labels;
        }

        // Gunakan default labels berdasarkan type
        return $this->getDefaultSkalaLabels($this->skala_type);
    }

    public function getDefaultSkalaLabels(string $type): array
    {
        if (is_null($type)) {
            return [];
        }

        $labels = [
            'kemudahan' => [
                5 => 'Sangat Mudah',
                4 => 'Mudah',
                3 => 'Cukup Mudah',
                2 => 'Sulit',
                1 => 'Sangat Sulit'
            ],
            'membantu' => [
                5 => 'Sangat Membantu',
                4 => 'Membantu',
                3 => 'Cukup Membantu',
                2 => 'Kurang Membantu',
                1 => 'Tidak Membantu'
            ],
            'manfaat' => [
                5 => 'Sangat Bermanfaat',
                4 => 'Bermanfaat',
                3 => 'Cukup Bermanfaat',
                2 => 'Kurang Bermanfaat',
                1 => 'Tidak Bermanfaat'
            ],
            'kepercayaan' => [
                5 => 'Sangat Percaya',
                4 => 'Percaya',
                3 => 'Cukup Percaya',
                2 => 'Kurang Percaya',
                1 => 'Tidak Percaya'
            ],
            'khawatir' => [
                5 => 'Sangat Khawatir',
                4 => 'Khawatir',
                3 => 'Cukup Khawatir',
                2 => 'Tidak Khawatir',
                1 => 'Sangat Tidak Khawatir'
            ],
            'kualitas' => [
                5 => 'Sangat Baik',
                4 => 'Baik',
                3 => 'Cukup',
                2 => 'Buruk',
                1 => 'Sangat Buruk'
            ],
        ];

        return $labels[$type] ?? $labels['kualitas'];
    }

    public function getCalculatedScore(int $userScore): int
    {
        // Validasi input
        if ($userScore < 1 || $userScore > 5) {
            return $userScore;
        }

        if ($this->persepsi === 'risiko') {
            // User pilih 5 (label positif: tidak khawatir) â†’ hitung sebagai 1 (risiko rendah)
            // User pilih 1 (label negatif: sangat khawatir) â†’ hitung sebagai 5 (risiko tinggi)
            return 6 - $userScore;
        }

        // Untuk persepsi manfaat, tidak perlu inversi
        return $userScore;
    }
}
