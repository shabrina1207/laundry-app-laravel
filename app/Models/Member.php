<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model

{

    use HasFactory;

    protected $table = 'member';

    protected $fillable = [

        'nama',
        'alamat',
        'jenis_kelamin',
        'tlp'

    ];


    public $timestamps = false;


    public function transaksi() {

        return $this->hasMany(Transaksi::class, 'id_member');

    }


    public function getTotalTransaksiAttribute() {

        return $this->transaksi()->count();

    }


    public function scopeSearch($query, $search) {

        return $query->where('nama', 'like', "%{$search}%")
            ->orWhere('tlp', 'like', "%{$search}%");

    }


    public function getJenisKelaminFormattedAttribute() {

        return $this->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
        
    }

}
