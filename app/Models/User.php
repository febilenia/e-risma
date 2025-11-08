<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'password',
        'password_changed_at',
        'role',
        'opd_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password_changed_at' => 'datetime',
        ];
    }

    // ✅ Relasi ke OPD
    public function opd()
    {
        return $this->belongsTo(OPD::class, 'opd_id');
    }

    // ✅ Check apakah superadmin
    public function isSuperAdmin()
    {
        return $this->role === 'superadmin';
    }

    // ✅ Check apakah admin OPD
    public function isAdminOPD()
    {
        return $this->role === 'admin_opd';
    }

    /**
     * Check if password needs to be changed (3 months)
     */
    public function needsPasswordChange()
    {
        if (!$this->password_changed_at) {
            return true;
        }

        return $this->password_changed_at->diffInMonths(now()) >= 3;
    }

    /**
     * ✅ FIX: Get days until password expiry
     */
    public function daysUntilPasswordExpiry()
    {
        if (!$this->password_changed_at) {
            return 0;
        }

        // ✅ FIX: Gunakan copy() untuk tidak mengubah object asli
        $expiryDate = $this->password_changed_at->copy()->addMonths(3);
        $daysLeft = now()->diffInDays($expiryDate, false);

        // Jika sudah expired, return 0
        return max(0, (int)$daysLeft);
    }

    /**
     * Update password and set changed timestamp
     */
    public function updatePassword($newPassword)
    {
        $this->update([
            'password' => bcrypt($newPassword),
            'password_changed_at' => now(),
        ]);
    }
}
