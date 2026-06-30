<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\DetailPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PeriksaPasienController extends Controller
{
    public function index()
    {
        $dokterId = Auth::id();

        $daftarPasien = DaftarPoli::with(['pasien', 'jadwalPeriksa', 'periksas'])
            ->whereHas('jadwalPeriksa', function ($query) use ($dokterId) {
                $query->where('id_dokter', $dokterId);
            })
            ->orderBy('no_antrian')
            ->get();

        return view('dokter.periksa-pasien.index', compact('daftarPasien'));
    }

    public function create($id)
    {
        $obats = Obat::all();

        return view('dokter.periksa-pasien.create', compact('obats', 'id'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'obat_json' => 'required',
            'catatan' => 'nullable|string',
            'biaya_periksa' => 'required|integer',
        ]);

        $obatData = json_decode($request->obat_json, true);

        // ============================
        // VALIDASI STOK
        // ============================

        foreach ($obatData as $item) {

            $obat = Obat::find($item['id']);

            if (!$obat) {
                return back()->with('error', 'Obat tidak ditemukan.');
            }

            if ($obat->stok < $item['jumlah']) {
                return back()->with(
                    'error',
                    'Stok obat '.$obat->nama_obat.' tidak mencukupi.'
                );
            }
        }

        // ============================
        // SIMPAN DATA PEMERIKSAAN
        // ============================

        $periksa = Periksa::create([
            'id_daftar_poli' => $request->id_daftar_poli,
            'tgl_periksa' => now(),
            'catatan' => $request->catatan,
            'biaya_periksa' => $request->biaya_periksa + 150000,
        ]);

        // ============================
        // SIMPAN DETAIL + KURANGI STOK
        // ============================

        foreach ($obatData as $item) {

            DetailPeriksa::create([
                'id_periksa' => $periksa->id,
                'id_obat' => $item['id'],
                'jumlah' => $item['jumlah'],
            ]);

            $obat = Obat::find($item['id']);

            $obat->stok -= $item['jumlah'];

            $obat->save();
        }

        return redirect()
            ->route('periksa-pasien.index')
            ->with('success', 'Data pemeriksaan berhasil disimpan.');
    }
}