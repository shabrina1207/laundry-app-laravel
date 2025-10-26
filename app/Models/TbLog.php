<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tblog extends Model {

    use HasFactory;

    protected $table = 'tb_log';

    protected $fillable = [

        'id_user',
        'aktivitas',
        'tanggal',
        'data_terkait'

    ];


    public $timestamps = false;

    protected $casts = [

        'data_terkait' => 'array',
        'tanggal' => 'datetime'

    ];


    public function user() {

        return $this->belongsTo(User::class, 'id_user');

    }


    public function scopeLatest($query) {

        return $query->orderBy('tanggal', 'desc');

    }


    public function scopeOrderById($query) {

        return $query->orderBy('id_log', 'desc');

    }
    
}
