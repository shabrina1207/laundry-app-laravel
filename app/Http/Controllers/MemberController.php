<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Tblog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MemberController extends Controller {
    
    public function index() {

        $members = Member::all();
        return view('member.index', compact('members'));

    }

    public function create() {
        return view('member.create');

    }

    public function store(Request $request) {

        $request->validate([

            'nama' => 'required|string|max:100',
            'alamat' => 'nullable|string',
            'jenis_kelamin' => 'nullable|in:L,P',
            'tlp' => 'nullable|string|max:15|unique:member,tlp'

        ], [

            'nama.required' => 'Nama pelanggan wajib diisi',
            'nama.max' => 'Nama pelanggan maksimal 100 karakter',
            'jenis_kelamin.in' => 'Jenis kelamin harus L atau P',
            'tlp.max' => 'Nomor telepon maksimal 15 karakter',
            'tlp.unique' => 'Nomor telepon sudah digunakan oleh pelanggan lain'

        ]);

        try {

            $member = Member::create($request->all());

            $this->logActivity(

                'Registrasi Pelanggan Baru: ' . $request->nama,
                $member->toArray()

            );

            $user = Auth::user();

            if ($user->role === 'admin') {

                return redirect()->route('admin.members.index')
                    ->with('success', 'Registrasi pelanggan berhasil!');

            } else {

                return redirect()->route('kasir.members.index')
                    ->with('success', 'Registrasi pelanggan berhasil!');

            }

        } catch (\Exception $e) {

            \Log::error('Error creating member: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menambahkan pelanggan: ' . $e->getMessage())
                ->withInput();

        }

    }

    public function show($id) {

        $member = Member::findOrFail($id);
        return view('member.show', compact('member'));

    }

    public function edit($id) {

        $member = Member::findOrFail($id);
        return view('member.edit', compact('member'));

    }

    public function update(Request $request, $id) {

        $member = Member::findOrFail($id);

        $request->validate([

            'nama' => 'required|string|max:100',
            'alamat' => 'nullable|string',
            'jenis_kelamin' => 'nullable|in:L,P',
            'tlp' => 'nullable|string|max:15|unique:member,tlp,' . $id

        ], [

            'nama.required' => 'Nama pelanggan wajib diisi',
            'nama.max' => 'Nama pelanggan maksimal 100 karakter',
            'jenis_kelamin.in' => 'Jenis kelamin harus L atau P',
            'tlp.max' => 'Nomor telepon maksimal 15 karakter',
            'tlp.unique' => 'Nomor telepon sudah digunakan oleh pelanggan lain'

        ]);

        try {

            $oldData = $member->toArray();
            $member->update($request->all());

            $this->logActivity(

                'Update Data Pelanggan: ' . $member->nama,

                [

                    'before' => $oldData,
                    'after' => $member->toArray()

                ]

            );

            $user = Auth::user();

            if ($user->role === 'admin') {

                return redirect()->route('admin.members.index')
                    ->with('success', 'Data pelanggan berhasil diupdate!');

            } else {

                return redirect()->route('kasir.members.index')
                    ->with('success', 'Data pelanggan berhasil diupdate!');

            }

        } catch (\Exception $e) {

            \Log::error('Error updating member: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal mengupdate pelanggan: ' . $e->getMessage())
                ->withInput();

        }

    }

    public function destroy($id) {

        $member = Member::findOrFail($id);


        if ($member->transaksi()->count() > 0) {

            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus pelanggan karena memiliki transaksi terkait!');

        }

        try {

            $memberName = $member->nama;
            $memberData = $member->toArray();

            $member->delete();

            $this->logActivity(

                'Hapus Data Pelanggan: ' . $memberName,
                ['deleted_member' => $memberData]

            );

            $user = Auth::user();

            if ($user->role === 'admin') {

                return redirect()->route('admin.members.index')
                    ->with('success', 'Data pelanggan berhasil dihapus!');

            } else {

                return redirect()->route('kasir.members.index')
                    ->with('success', 'Data pelanggan berhasil dihapus!');

            }

        } catch (\Exception $e) {

            \Log::error('Error deleting member: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menghapus pelanggan: ' . $e->getMessage());

        }

    }


    public function checkDuplicatePhone(Request $request) {

        try {

            $request->validate([

                'tlp' => 'required|string|max:15',
                'member_id' => 'nullable|exists:member,id'

            ]);

            $query = Member::where('tlp', $request->tlp);

            if ($request->has('member_id') && $request->member_id) {

                $query->where('id', '!=', $request->member_id);

            }

            $exists = $query->exists();
            $existingMember = $query->first();

            return response()->json([

                'exists' => $exists,
                'message' => $exists ? "Nomor telepon sudah digunakan oleh pelanggan: {$existingMember->nama}" : 'Nomor telepon tersedia'

            ]);

        } catch (\Exception $e) {

            return response()->json([

                'error' => 'Terjadi kesalahan saat validasi',
                'exists' => false

            ], 500);

        }

    }

    private function logActivity($aktivitas, $dataTerkait = null) {

        Tblog::create([

            'id_user' => Auth::id(),
            'aktivitas' => $aktivitas,
            'data_terkait' => $dataTerkait ? json_encode($dataTerkait) : null

        ]);

    }

}
