<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;

class User extends Authenticatable implements MustVerifyEmail {

    use HasFactory, Notifiable;

    protected $fillable = [

        'name',
        'username',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'id_outlet'

    ];

    protected $hidden = [

        'password',
        'remember_token',

    ];

    protected $casts = [

        'email_verified_at' => 'datetime',
        'password' => 'hashed',

    ];

    protected $enumCasts = [

        'role' => 'admin,kasir,owner'

    ];

    protected static function boot() {

        parent::boot();

        static::creating(function ($user) {

            if (empty($user->username)) {

                $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $user->name));
                $username = $baseUsername;
                $counter = 1;

                while (User::where('username', $username)->exists()) {

                    $username = $baseUsername . $counter;
                    $counter++;

                }

                $user->username = $username;
            }

        });

    }

    public function outlet() {

        return $this->belongsTo(Outlet::class, 'id_outlet');

    }

    public function transaksi() {

        return $this->hasMany(Transaksi::class, 'id_user');

    }

    public function logs() {

        return $this->hasMany(Tblog::class, 'id_user');

    }



    public function getNamaOutletAttribute() {

        return $this->outlet ? $this->outlet->nama : 'Tidak ada outlet';

    }

    public function getRoleFormattedAttribute() {

        $roles = [

            'admin' => 'Admin',
            'kasir' => 'Kasir',
            'owner' => 'Owner'

        ];

        return $roles[$this->role] ?? ucfirst($this->role);

    }

    public function scopeByRole($query, $role) {

        return $query->where('role', $role);

    }

    public function scopeByOutlet($query, $outletId) {

        return $query->where('id_outlet', $outletId);

    }

    public function getIsAdminAttribute() {

        return $this->role === 'admin';

    }

    public function getIsKasirAttribute() {

        return $this->role === 'kasir';

    }

    public function getIsOwnerAttribute() {

        return $this->role === 'owner';

    }

    public static function getCountByOutlet($outletId) {

        return self::where('id_outlet', $outletId)->count();

    }

    public static function getRecentUsers($limit = 5) {

        return self::with('outlet')
            ->latest()
            ->take($limit)
            ->get();

    }

    public static function getByRoleForOutlet($outletId, $role) {

        return self::where('id_outlet', $outletId)
            ->where('role', $role)
            ->get();

    }
    
}
