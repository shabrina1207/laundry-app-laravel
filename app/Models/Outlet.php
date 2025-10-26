<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Outlet extends Model {

    use HasFactory;

    protected $table = 'outlet';

    protected $fillable = [

        'nama',
        'alamat',
        'tlp'

    ];


    protected $casts = [

        'created_at' => 'datetime',
        'updated_at' => 'datetime',

    ];


    public function users(): HasMany {

        return $this->hasMany(User::class, 'id_outlet');

    }


    public function transaksi(): HasMany {

        return $this->hasMany(Transaksi::class, 'id_outlet');

    }


    public function scopeSearch($query, $search) {

        return $query->where('nama', 'like', "%{$search}%")
            ->orWhere('alamat', 'like', "%{$search}%")
            ->orWhere('tlp', 'like', "%{$search}%");

    }


    public function getTlpFormattedAttribute() {

        return $this->tlp ? '+62 ' . substr($this->tlp, 1) : '-';

    }


    public function getAlamatSingkatAttribute() {

        return $this->alamat ? Str::limit($this->alamat, 50) : '-';

    }


    public function getCanDeleteAttribute() {

        return $this->users()->count() === 0 && $this->transaksi()->count() === 0;

    }


    public function getTotalUsersAttribute() {

        return $this->users()->count();

    }


    public function getTotalTransactionsAttribute() {

        return $this->transaksi()->count();

    }


    public function getCreatedAtFormattedAttribute() {

        return $this->created_at ? $this->created_at->format('d M Y H:i') : '-';

    }


    public function getUpdatedAtFormattedAttribute() {
        
        return $this->updated_at ? $this->updated_at->format('d M Y H:i') : '-';

    }
}

