<x-layouts.app title="Periksa Pasien">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('periksa-pasien.index') }}" class="inline-flex items-center justify-center w-9 h-9 
                  rounded-lg bg-slate-100 text-slate-500 
                  hover:bg-slate-200 transition">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <h2 class="text-2xl font-bold text-slate-800">
            Periksa Pasien
        </h2>
    </div>

    {{-- Card --}}
    <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
        <div class="card-body p-8">

            <form action="{{ route('periksa-pasien.store') }}" method="POST">
                @csrf
                <input type="hidden" name="id_daftar_poli" value="{{ $id }}">

                {{-- Pilih Obat --}}
                <div class="form-control mb-5">
                    <label class="label pb-1">
                        <span class="text-sm font-semibold text-gray-700">Pilih Obat <span class="text-red-500">*</span></span>
                    </label>
                    <select id="select-obat" class="select select-bordered w-full rounded-lg border-2 px-4">
                        <option value="">-- Pilih Obat --</option>
                      @foreach ($obats as $obat)

    <option
        value="{{ $obat->id }}"
        data-nama="{{ $obat->nama_obat }}"
        data-harga="{{ $obat->harga }}"
        data-stok="{{ $obat->stok }}"
        {{ $obat->stok == 0 ? 'disabled' : '' }}>

        {{ $obat->nama_obat }}

        @if($obat->stok == 0)
            - STOK HABIS
        @else
            | Stok: {{ $obat->stok }}
        @endif

        | Rp{{ number_format($obat->harga) }}

    </option>

@endforeach
                    </select>
                </div>

                {{-- Obat Terpilih --}}
                <div class="form-control mb-5">
                    <label class="label pb-1 ">
                        <span class="text-sm font-semibold text-gray-700">Obat Terpilih</span>
                    </label>

                    <ul id="obat-terpilih" class="flex flex-col gap-2 mb-2 min-h-[48px]"></ul>

                    <input type="hidden" name="biaya_periksa" id="biaya_periksa" value="0">
                    <input type="hidden" name="obat_json" id="obat_json">
                </div>

                {{-- Total Harga --}}
                <div class="form-control mb-5">
                    <label class="label pb-1">
                        <span class="text-sm font-semibold text-gray-700">Total Harga</span>
                    </label>
                    <div class="input input-bordered w-full rounded-lg flex items-center bg-slate-50 text-slate-700 font-bold" id="total-harga">
                        Rp 0
                    </div>
                </div>

                {{-- Catatan --}}
                <div class="form-control mb-8">
                    <label class="label pb-1">
                        <span class="text-sm font-semibold text-gray-700">Catatan <span class="text-slate-400 font-normal">(Opsional)</span></span>
                    </label>
                    <textarea name="catatan" id="catatan" rows="4"
                        placeholder="Masukkan catatan..."
                        class="textarea textarea-bordered w-full border-2 px-4 py-2 rounded-lg resize-none">{{ old('catatan') }}</textarea>
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3">
                    <button type="submit"
                        class="btn bg-[#2d4499] hover:bg-[#1e2d6b] text-white border-none rounded-lg px-6">
                        <i class="fas fa-save"></i>
                        Simpan
                    </button>
                    <a href="{{ route('periksa-pasien.index') }}"
                        class="btn btn-ghost bg-slate-100 hover:bg-slate-200 text-slate-500 rounded-lg px-6">
                        Batal
                    </a>
                </div>

            </form>
        </div>
    </div>

    <script>
        const selectObat = document.getElementById('select-obat');
        const listObat = document.getElementById('obat-terpilih');
        const inputBiaya = document.getElementById('biaya_periksa');
        const inputObatJson = document.getElementById('obat_json');
        const totalHargaEl = document.getElementById('total-harga');

        let daftarObat = [];

        selectObat.addEventListener('change', () => {
            const selectedOption = selectObat.options[selectObat.selectedIndex];
           const id = selectedOption.value;
const nama = selectedOption.dataset.nama;
const harga = parseInt(selectedOption.dataset.harga || 0);
const stok = parseInt(selectedOption.dataset.stok || 0);

            if (!id || daftarObat.some(o => o.id == id)) return;

daftarObat.push({
    id,
    nama,
    harga,
    stok,
    jumlah: 1
});            renderObat();
            selectObat.selectedIndex = 0;
        });

        function renderObat() {
            listObat.innerHTML = '';
            let total = 0;

            daftarObat.forEach((obat, index) => {
total += obat.harga * obat.jumlah;

                const item = document.createElement('li');
                item.className = 'flex items-center justify-between px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-700';
              item.innerHTML = `
<div class="border border-slate-200 rounded-xl p-5 bg-white shadow-sm">

    <div class="flex justify-between items-start">

        <div class="flex-1">

            <h3 class="font-bold text-lg text-slate-800 mb-4">
                ${obat.nama}
            </h3>

            <div class="grid grid-cols-2 gap-y-3 text-sm">

                <div class="text-slate-500">
                    Harga
                </div>

                <div class="font-semibold text-blue-600">
                    Rp ${obat.harga.toLocaleString()}
                </div>

                <div class="text-slate-500">
                    Stok
                </div>

                <div class="font-semibold">
                    ${obat.stok}
                </div>

                <div class="text-slate-500 flex items-center">
                    Jumlah
                </div>

                <div>

                    <div class="join">

                        <button
                            type="button"
                            class="btn btn-sm join-item"
                            onclick="kurangJumlah(${index})">

                            -

                        </button>

                       <input
    type="number"
    min="1"
    max="${obat.stok}"
    value="${obat.jumlah}"
    class="input input-bordered input-sm join-item w-16 text-center"
    onchange="ubahJumlah(${index}, this.value)">

                        <button
                            type="button"
                            class="btn btn-sm join-item"
                            onclick="tambahJumlah(${index})">

                            +

                        </button>

                    </div>

                </div>

                <div class="text-slate-500">
                    Subtotal
                </div>

                <div class="font-bold text-green-600 text-base">
                    Rp ${(obat.harga * obat.jumlah).toLocaleString()}
                </div>

            </div>

        </div>

        <button
            type="button"
            onclick="hapusObat(${index})"
            class="btn btn-sm btn-error ml-5">

            <i class="fas fa-trash"></i>

        </button>

    </div>

</div>
`;
                listObat.appendChild(item);
            });

            inputBiaya.value = total;
            totalHargaEl.textContent = `Rp ${total.toLocaleString()}`;
inputObatJson.value = JSON.stringify(daftarObat);
        }


        function ubahJumlah(index, jumlah) {

    jumlah = parseInt(jumlah);

    if (isNaN(jumlah) || jumlah < 1) {
        jumlah = 1;
    }

    if (jumlah > daftarObat[index].stok) {
        alert("Jumlah melebihi stok!");
        jumlah = daftarObat[index].stok;
    }

    daftarObat[index].jumlah = jumlah;

    renderObat();
}

function tambahJumlah(index){

    if(daftarObat[index].jumlah < daftarObat[index].stok){

        daftarObat[index].jumlah++;

        renderObat();

    }else{

        alert("Stok obat tidak mencukupi.");

    }

}

function kurangJumlah(index){

    if(daftarObat[index].jumlah > 1){

        daftarObat[index].jumlah--;

        renderObat();

    }

}
        function hapusObat(index) {
            daftarObat.splice(index, 1);
            renderObat();
        }
    </script>

</x-layouts.app>