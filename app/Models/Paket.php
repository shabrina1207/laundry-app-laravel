<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paket extends Model {
    use HasFactory;

    protected $table = 'paket';

    protected $fillable = [

        'id_outlet',
        'jenis',
        'nama_paket',
        'harga'

    ];


    protected $casts = [

        'harga' => 'integer',

    ];


    public function outlet() {

        return $this->belongsTo(Outlet::class, 'id_outlet');

    }


    public function detailTransaksi() {

        return $this->hasMany(DetailTransaksi::class, 'id_paket');

    }


    public function getJenisFormattedAttribute() {

        $jenis = [

            'kiloan' => 'Kiloan',
            'selimut' => 'Selimut',
            'bed_cover' => 'Bed Cover',
            'kaos' => 'Kaos',
            'lain' => 'Lainnya'

        ];

        return $jenis[$this->jenis] ?? $this->jenis;

    }


    public function getHargaFormattedAttribute() {

        return 'Rp ' . number_format($this->harga, 0, ',', '.');

    }


    public function scopeByOutlet($query, $outletId) {

        return $query->where('id_outlet', $outletId);

    }


    public function scopeByJenis($query, $jenis) {

        return $query->where('jenis', $jenis);

    }


    public static function getCountByOutlet($outletId) {

        return self::where('id_outlet', $outletId)->count();

    }


    public static function getByJenisForOutlet($outletId, $jenis) {
        return self::where('id_outlet', $outletId)
            ->where('jenis', $jenis)
            ->get();

    }

}
