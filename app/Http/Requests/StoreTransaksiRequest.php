<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransaksiRequest extends FormRequest {
    
    public function authorize() {

        return auth()->check() && in_array(auth()->user()->role, ['admin', 'kasir']);

    }

    public function rules() {

        $rules = [

            'id_outlet' => 'required|exists:outlet,id',
            'tipe_pelanggan' => 'required|in:member,biasa',
            'id_paket' => 'required|array|min:1',
            'id_paket.*' => 'required|exists:paket,id',
            'qty' => 'required|array|min:1',
            'qty.*' => 'required|numeric|min:0.1',
            'keterangan' => 'nullable|array',
            'keterangan.*' => 'nullable|string|max:255',
            'biaya_tambahan' => 'nullable|numeric|min:0',
            'diskon' => 'nullable|numeric|min:0',
            'pajak' => 'nullable|numeric|min:0',
            'durasi' => 'nullable|integer|min:1|max:30'

        ];


        if ($this->tipe_pelanggan === 'member') {

            $rules['id_member'] = 'required|exists:member,id';

        } else {

            $rules['nama_pelanggan'] = 'required|string|max:255';
            $rules['tlp_pelanggan'] = 'nullable|string|max:20';

        }

        return $rules;

    }

    public function messages() {

        return [

            'id_paket.required' => 'Pilih minimal satu paket',
            'id_paket.min' => 'Pilih minimal satu paket',
            'qty.*.required' => 'Quantity harus diisi',
            'qty.*.numeric' => 'Quantity harus berupa angka',
            'qty.*.min' => 'Quantity minimal 0.1',
            'id_member.required' => 'Pilih member untuk transaksi member',
            'nama_pelanggan.required' => 'Nama pelanggan harus diisi untuk pelanggan biasa',
            'id_member.exists' => 'Member yang dipilih tidak valid',
            'id_paket.*.exists' => 'Paket yang dipilih tidak valid'
        ];

    }


    protected function prepareForValidation() {

        $this->merge([
            'biaya_tambahan' => $this->biaya_tambahan ?: 0,
            'diskon' => $this->diskon ?: 0,
            'pajak' => $this->pajak ?: 0,
            'durasi' => $this->durasi ?: 2,
            'tlp_pelanggan' => $this->tlp_pelanggan ?: null,

        ]);

    }

}
