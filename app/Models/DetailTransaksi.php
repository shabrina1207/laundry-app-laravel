<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model {

    use HasFactory;

    protected $table = 'detail_transaksi';

    protected $fillable = [

        'id_transaksi',
        'id_paket',
        'qty',
        'berat',
        'keterangan'

    ];

    public $timestamps = false;

    protected $casts = [

        'qty' => 'double',
        'berat' => 'double',

    ];

    public function transaksi() {

        return $this->belongsTo(Transaksi::class, 'id_transaksi');

    }

    public function paket() {

        return $this->belongsTo(Paket::class, 'id_paket');

    }


    public function getQtyDigunakanAttribute() {

        if ($this->paket && $this->paket->jenis === 'kiloan' && $this->berat) {

            return $this->berat;

        }

        return $this->qty;

    }

    public function getSubtotalAttribute() {

    if ($this->paket && $this->paket->jenis === 'kiloan' && $this->berat) {

        return $this->paket->harga * $this->berat;

    }

    return $this->paket ? $this->paket->harga * $this->qty : 0;

    }

    public function getNamaPaketAttribute() {

        return $this->paket ? $this->paket->nama_paket : 'Paket tidak ditemukan';

    }

    public function getHargaPaketAttribute() {

        return $this->paket ? $this->paket->harga : 0;

    }

    public function getHargaPaketFormattedAttribute() {

        return 'Rp ' . number_format($this->harga_paket, 0, ',', '.');

    }

    public function getSubtotalFormattedAttribute() {

        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');

    }


    public function getDisplayQtyAttribute() {

        if ($this->paket && $this->paket->jenis === 'kiloan') {

            return $this->berat ? number_format($this->berat, 1) . ' kg' : '0 kg';

        }

        return number_format($this->qty, 1) . ' pcs';

    }


    public function getJenisPaketAttribute() {

        return $this->paket ? $this->paket->jenis : null;
        
    }

}
