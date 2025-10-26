<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\FormatHelper;

class Transaksi extends Model {
    
    use HasFactory;

    protected $table = 'transaksi';

    protected $fillable = [

        'id_outlet',
        'kode_invoice',
        'id_member',
        'tipe_pelanggan',
        'nama_pelanggan',
        'tlp_pelanggan',
        'tgl',
        'batas_waktu',
        'tgl_bayar',
        'biaya_tambahan',
        'diskon',
        'pajak',
        'status',
        'dibayar',
        'id_user'

    ];

    public $timestamps = false;

    protected $casts = [

        'tgl' => 'datetime',
        'batas_waktu' => 'datetime',
        'tgl_bayar' => 'datetime',
        'biaya_tambahan' => 'integer',
        'diskon' => 'double',
        'pajak' => 'integer',

    ];

    public function getDiskonPersenAttribute() {

        return $this->diskon * 100;

    }

    public function getBiayaTambahanRupiahAttribute() {

        return FormatHelper::rupiah($this->biaya_tambahan);

    }

    public function getPajakRupiahAttribute() {

        return FormatHelper::rupiah($this->pajak);

    }

    public function getDiskonDisplayAttribute() {

        return FormatHelper::persen($this->diskon);

    }

    public function getTotalAttribute() {

        $subtotal = $this->detailTransaksi->sum(function($detail) {

            return $detail->qty * $detail->paket->harga;

        });

        $total = $subtotal
            + $this->biaya_tambahan
            - ($subtotal * $this->diskon)
            + $this->pajak;

        return max(0, $total);

    }

    public function getTotalRupiahAttribute() {

        return FormatHelper::rupiah($this->total);

    }

    public function getSubtotalRupiahAttribute() {

        $subtotal = $this->detailTransaksi->sum(function($detail) {

            return $detail->qty * $detail->paket->harga;

        });

        return FormatHelper::rupiah($subtotal);

    }

    public function outlet() {

        return $this->belongsTo(Outlet::class, 'id_outlet');

    }

    public function member() {

        return $this->belongsTo(Member::class, 'id_member');

    }

    public function user() {

        return $this->belongsTo(User::class, 'id_user');

    }

    public function detailTransaksi() {

        return $this->hasMany(DetailTransaksi::class, 'id_transaksi');

    }

    public function getStatusFormattedAttribute() {

        $statuses = [

            'baru' => 'Baru',
            'proses' => 'Diproses',
            'selesai' => 'Selesai',
            'diambil' => 'Diambil'

        ];

        return $statuses[$this->status] ?? $this->status;

    }

    public function getDibayarFormattedAttribute() {

        return $this->dibayar === 'dibayar' ? 'Sudah Dibayar' : 'Belum Dibayar';

    }

    public function getIsTerlambatAttribute() {

        if (!$this->batas_waktu || $this->status === 'diambil') {

            return false;

        }

        return now()->greaterThan($this->batas_waktu);

    }

    public function getNamaPelangganDisplayAttribute() {

        if ($this->tipe_pelanggan === 'member' && $this->member) {

            return $this->member->nama;

        } elseif ($this->tipe_pelanggan === 'biasa') {

            return $this->nama_pelanggan ?? 'Pelanggan Biasa';

        }

        return 'Tidak Diketahui';

    }

    public function getTlpPelangganDisplayAttribute() {

        if ($this->tipe_pelanggan === 'member' && $this->member) {

            return $this->member->tlp;

        } elseif ($this->tipe_pelanggan === 'biasa') {

            return $this->tlp_pelanggan ?? '-';

        }

        return '-';

    }

    public function getTipePelangganFormattedAttribute() {

        return $this->tipe_pelanggan === 'member' ? 'Member' : 'Pelanggan Biasa';

    }

    public function scopeHariIni($query) {

        return $query->whereDate('tgl', today());

    }

    public function scopeByStatus($query, $status) {

        return $query->where('status', $status);

    }

    public function scopeByOutlet($query, $outletId) {

        return $query->where('id_outlet', $outletId);

    }

    public function scopeByTipePelanggan($query, $tipe) {

        return $query->where('tipe_pelanggan', $tipe);

    }

    public static function getCountByOutlet($outletId) {

        return self::where('id_outlet', $outletId)->count();

    }

    public static function getTodayCountByOutlet($outletId) {

        return self::where('id_outlet', $outletId)->whereDate('tgl', today())->count();

    }

}
