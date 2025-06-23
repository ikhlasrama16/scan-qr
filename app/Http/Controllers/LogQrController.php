<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LogQr;

class LogQrController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'lokasi_scan' => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();

        if (empty($user->kantor)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data kantor pengguna belum diisi.'
            ], 422); // pakai 422 Unprocessable Entity
        }

        LogQr::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'kantor' => $user->kantor,
            'lokasi_scan' => $request->lokasi_scan,
            'keterangan' => $request->keterangan,
            'waktu_scan' => now(),
        ]);

        return response()->json(['status' => 'success']);
    }
}
