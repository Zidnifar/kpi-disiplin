@extends('layouts.app')

@php
    $ubah = $rencana !== null;

    // baris karyawan awal (dari input lama bila validasi gagal, dari data bila ubah, kosong bila baru)
    $barisAwal = old('karyawan');
    if ($barisAwal === null) {
        $barisAwal = $ubah
            ? $rencana->detail->map(fn ($d) => ['id_karyawan' => $d->id_karyawan, 'pekerjaan' => $d->pekerjaan])->values()->all()
            : [];
    }
    $barisAwal = collect($barisAwal)->filter(fn ($b) => ! empty($b['id_karyawan']))->values()->all();

    $namaKaryawan = collect($karyawanPilihan)->mapWithKeys(fn ($k) => [(string) $k->id_karyawan => $k->nama_karyawan])->all();

    $nomorTampil = $ubah
        ? ($nomor ?? ($rencana->no_pengajuan ?? '—'))
        : (($nomorOtomatis ?? 'LB-2026-015') . ' (otomatis)');

    $namaDepartemen = $rencana?->departemen?->nama_dept ?? ($departemen->nama_dept ?? '—');

    $jamOpsi = [
        '06:00', '07:00', '08:00', '12:00', '13:00', '14:00',
        '15:00', '16:00', '17:00', '18:00', '19:00', '20:00',
        '21:00', '22:00', '23:00', '00:00',
    ];
    $jamMulaiNilai = old('jam_mulai', $rencana ? \App\Support\Format::jam($rencana->jam_mulai) : '');
    $jamSelesaiNilai = old('jam_selesai', $rencana ? \App\Support\Format::jam($rencana->jam_selesai) : '');
    $tanggalNilai = old('tanggal_lembur', $rencana?->tanggal_lembur?->toDateString());
    $shiftNilai = old('id_shift', $rencana?->id_shift);
@endphp

@section('title', $ubah ? 'Ubah Rencana Lembur' : 'Ajukan Rencana Lembur')
@section('judul', $ubah ? 'Ubah Rencana Lembur' : 'Ajukan Rencana Lembur')

@section('isi')
<div class="lf-page">
    <div class="lf-breadcrumb">
        <a href="{{ route('unit.lembur.index') }}">Rencana Lembur</a>
        <span>/</span>
        <strong>{{ $ubah ? 'Ubah Rencana Lembur' : 'Ajukan Rencana Lembur' }}</strong>
    </div>

    @if ($ubah && method_exists($rencana, 'catatanPenolakan') && $rencana->catatanPenolakan())
        <div class="lf-alert">
            <strong>Catatan dari pemeriksa:</strong> {{ $rencana->catatanPenolakan() }}
        </div>
    @endif

    <div class="lf-card">
        <h2 class="lf-heading">Formulir Pengajuan Rencana Lembur</h2>
        <p class="lf-subheading">Lengkapi informasi rencana kerja lembur berikut.</p>

        <form method="POST" action="{{ $ubah ? route('unit.lembur.update', $rencana) : route('unit.lembur.store') }}" id="formRencanaLembur" novalidate>
            @csrf
            @if ($ubah) @method('PUT') @endif

            <!-- Baris 1: No. Pengajuan & Departemen -->
            <div class="lf-grid-2">
                <div class="lf-field">
                    <label class="lf-label" for="no_pengajuan_tampil">No. Pengajuan</label>
                    <input id="no_pengajuan_tampil" type="text" class="lf-control is-readonly" value="{{ $nomorTampil }}" readonly>
                </div>
                <div class="lf-field">
                    <label class="lf-label" for="departemen_tampil">Departemen</label>
                    <input id="departemen_tampil" type="text" class="lf-control is-readonly" value="{{ $namaDepartemen }}" readonly>
                </div>
            </div>

            <!-- Baris 2: Tanggal Lembur & Shift -->
            <div class="lf-grid-2">
                <div class="lf-field">
                    <label class="lf-label" for="tanggal_lembur">Tanggal Lembur</label>
                    <div class="lf-datewrap">
                        <input id="tanggal_lembur" name="tanggal_lembur" type="{{ $tanggalNilai ? 'date' : 'text' }}" class="lf-control" value="{{ $tanggalNilai }}" placeholder="Pilih tanggal" autocomplete="off" required>
                        <svg class="lf-dateicon" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    </div>
                    @error('tanggal_lembur') <div class="lf-error">{{ $message }}</div> @enderror
                </div>

                <div class="lf-field">
                    <label class="lf-label" for="id_shift">Shift</label>
                    <select id="id_shift" name="id_shift" class="lf-control" data-shift data-placeholder-select>
                        <option value="">Pilih shift</option>
                        @foreach ($shiftPilihan as $s)
                            <option
                                value="{{ $s->id_shift }}"
                                data-mulai="{{ \App\Support\Format::jam($s->jam_mulai) }}"
                                data-selesai="{{ \App\Support\Format::jam($s->jam_selesai) }}"
                                @selected($shiftNilai === $s->id_shift)
                            >{{ $s->nama_shift }} ({{ \App\Support\Format::rentangJam($s->jam_mulai, $s->jam_selesai) }})</option>
                        @endforeach
                    </select>
                    @error('id_shift') <div class="lf-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Baris 3: Jam Mulai & Jam Selesai -->
            <div class="lf-grid-2">
                <div class="lf-field">
                    <label class="lf-label" for="jam_mulai">Jam Mulai</label>
                    <select id="jam_mulai" name="jam_mulai" class="lf-control" data-placeholder-select required>
                        <option value="">Pilih jam mulai</option>
                        @if ($jamMulaiNilai !== '' && ! in_array($jamMulaiNilai, $jamOpsi, true))
                            <option value="{{ $jamMulaiNilai }}" selected>{{ $jamMulaiNilai }}</option>
                        @endif
                        @foreach ($jamOpsi as $jm)
                            <option value="{{ $jm }}" @selected($jamMulaiNilai === $jm)>{{ $jm }}</option>
                        @endforeach
                    </select>
                    @error('jam_mulai') <div class="lf-error">{{ $message }}</div> @enderror
                </div>

                <div class="lf-field">
                    <label class="lf-label" for="jam_selesai">Jam Selesai</label>
                    <select id="jam_selesai" name="jam_selesai" class="lf-control" data-placeholder-select required>
                        <option value="">Pilih jam selesai</option>
                        @if ($jamSelesaiNilai !== '' && ! in_array($jamSelesaiNilai, $jamOpsi, true))
                            <option value="{{ $jamSelesaiNilai }}" selected>{{ $jamSelesaiNilai }}</option>
                        @endif
                        @foreach ($jamOpsi as $js)
                            <option value="{{ $js }}" @selected($jamSelesaiNilai === $js)>{{ $js }}</option>
                        @endforeach
                    </select>
                    <span class="lf-hint">Jam selesai lebih kecil dari jam mulai dianggap melewati tengah malam.</span>
                    @error('jam_selesai') <div class="lf-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Baris 4: Karyawan yang Lembur -->
            <div class="lf-field" style="margin-bottom: 16px; position: relative;">
                <label class="lf-label">Karyawan yang Lembur</label>

                <div class="lf-karyawan-box" id="wadahChipKaryawan">
                    <div class="lf-chip-list" id="daftarChipKaryawan"></div>
                    <button type="button" class="lf-btn-tambah-chip" id="btnTambahChipKaryawan">+ Tambah karyawan</button>
                </div>

                <!-- Pemilih karyawan -->
                <div class="lf-picker" id="pickerKaryawan" hidden>
                    <select id="pickerSelect" class="lf-control" data-placeholder-select>
                        <option value="">Pilih karyawan</option>
                        @foreach ($karyawanPilihan as $k)
                            <option value="{{ $k->id_karyawan }}">{{ $k->nama_karyawan }}</option>
                        @endforeach
                    </select>
                    <input type="text" id="pickerPekerjaan" class="lf-control" placeholder="Pekerjaan (opsional)" maxlength="100">
                    <div class="lf-picker-aksi">
                        <button type="button" class="lf-btn lf-btn-batal lf-btn-kecil" id="pickerBatal">Batal</button>
                        <button type="button" class="lf-btn lf-btn-utama lf-btn-kecil" id="pickerTambah">Tambah</button>
                    </div>
                </div>

                <!-- Data yang dikirim ke server -->
                <div id="wadahInputKaryawan" data-indeks="{{ count($barisAwal) }}">
                    @foreach ($barisAwal as $i => $b)
                        <div data-item>
                            <input type="hidden" name="karyawan[{{ $i }}][id_karyawan]" value="{{ $b['id_karyawan'] }}">
                            <input type="hidden" name="karyawan[{{ $i }}][pekerjaan]" value="{{ $b['pekerjaan'] ?? '' }}">
                        </div>
                    @endforeach
                </div>

                @error('karyawan') <div class="lf-error">{{ $message }}</div> @enderror
                @foreach ($errors->get('karyawan.*') as $pesan)
                    <div class="lf-error">{{ $pesan[0] }}</div>
                @endforeach
            </div>

            <!-- Baris 5: Uraian Pekerjaan & Alasan Lembur -->
            <div class="lf-grid-2">
                <div class="lf-field">
                    <label class="lf-label" for="uraian_pekerjaan">Uraian Pekerjaan</label>
                    <textarea id="uraian_pekerjaan" name="uraian_pekerjaan" class="lf-textarea" placeholder="Jelaskan pekerjaan yang akan dilakukan...">{{ old('uraian_pekerjaan', $rencana?->uraian_pekerjaan) }}</textarea>
                    @error('uraian_pekerjaan') <div class="lf-error">{{ $message }}</div> @enderror
                </div>
                <div class="lf-field">
                    <label class="lf-label" for="alasan_lembur">Alasan Lembur</label>
                    <textarea id="alasan_lembur" name="alasan_lembur" class="lf-textarea" placeholder="Jelaskan alasan perlunya lembur...">{{ old('alasan_lembur', $rencana?->alasan_lembur) }}</textarea>
                    @error('alasan_lembur') <div class="lf-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Tombol Aksi Kanan Bawah -->
            <div class="lf-actions">
                <a href="{{ route('unit.lembur.index') }}" class="lf-btn lf-btn-batal">Batal</a>
                <button type="submit" name="aksi" value="draf" class="lf-btn lf-btn-draf">Simpan Draf</button>
                <button type="submit" name="aksi" value="ajukan" class="lf-btn lf-btn-utama">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m22 2-7 20-4-9-9-4Z"/>
                        <path d="M22 2 11 13"/>
                    </svg>
                    <span>Ajukan Rencana</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    // ---------------------------------------------------------------- Karyawan (chip + pemilih)
    const NAMA = @json($namaKaryawan);
    const wadah = document.getElementById('wadahInputKaryawan');
    const chipList = document.getElementById('daftarChipKaryawan');
    const picker = document.getElementById('pickerKaryawan');
    const pickerSelect = document.getElementById('pickerSelect');
    const pickerPekerjaan = document.getElementById('pickerPekerjaan');
    const btnTambah = document.getElementById('btnTambahChipKaryawan');
    let indeks = Number(wadah.dataset.indeks || 0);

    function idTerpilih() {
        return Array.from(wadah.querySelectorAll('[data-item] input[name$="[id_karyawan]"]')).map((i) => i.value);
    }

    function gambarChip() {
        chipList.innerHTML = '';
        wadah.querySelectorAll('[data-item]').forEach((item) => {
            const idInput = item.querySelector('input[name$="[id_karyawan]"]');
            const kerjaInput = item.querySelector('input[name$="[pekerjaan]"]');
            const nama = NAMA[idInput.value] || idInput.value;

            const chip = document.createElement('span');
            chip.className = 'lf-chip';
            if (kerjaInput && kerjaInput.value) chip.title = 'Pekerjaan: ' + kerjaInput.value;

            const label = document.createElement('span');
            label.textContent = nama;

            const x = document.createElement('button');
            x.type = 'button';
            x.className = 'lf-chip-hapus';
            x.setAttribute('aria-label', 'Hapus ' + nama);
            x.innerHTML = '&times;';
            x.addEventListener('click', () => { item.remove(); gambarChip(); });

            chip.appendChild(label);
            chip.appendChild(x);
            chipList.appendChild(chip);
        });
    }

    function tutupPicker() {
        picker.hidden = true;
        pickerSelect.value = '';
        pickerPekerjaan.value = '';
        tandaiPlaceholder(pickerSelect);
    }

    btnTambah.addEventListener('click', () => {
        const dipilih = idTerpilih();
        Array.from(pickerSelect.options).forEach((o) => { o.disabled = o.value !== '' && dipilih.includes(o.value); });
        picker.hidden = !picker.hidden;
        if (!picker.hidden) pickerSelect.focus();
    });

    document.getElementById('pickerBatal').addEventListener('click', tutupPicker);

    document.getElementById('pickerTambah').addEventListener('click', () => {
        const id = pickerSelect.value;
        if (!id) { pickerSelect.focus(); return; }

        const item = document.createElement('div');
        item.setAttribute('data-item', '');
        item.innerHTML =
            '<input type="hidden" name="karyawan[' + indeks + '][id_karyawan]">' +
            '<input type="hidden" name="karyawan[' + indeks + '][pekerjaan]">';
        item.querySelector('input[name$="[id_karyawan]"]').value = id;
        item.querySelector('input[name$="[pekerjaan]"]').value = pickerPekerjaan.value.trim();
        wadah.appendChild(item);
        indeks++;

        gambarChip();
        tutupPicker();
    });

    // ---------------------------------------------------------------- Tanggal (tampil "Pilih tanggal" sampai diklik)
    const tgl = document.getElementById('tanggal_lembur');
    tgl.addEventListener('focus', () => {
        if (tgl.type !== 'date') {
            tgl.type = 'date';
            try { tgl.showPicker(); } catch (e) {}
        }
    });
    tgl.addEventListener('blur', () => { if (!tgl.value) tgl.type = 'text'; });

    // ---------------------------------------------------------------- Warna placeholder pada dropdown
    function tandaiPlaceholder(sel) { sel.classList.toggle('is-placeholder', !sel.value); }
    document.querySelectorAll('select[data-placeholder-select]').forEach((sel) => {
        tandaiPlaceholder(sel);
        sel.addEventListener('change', () => tandaiPlaceholder(sel));
    });

    // ---------------------------------------------------------------- Shift mengisi jam mulai/selesai
    const shiftSelect = document.getElementById('id_shift');
    const jamMulai = document.getElementById('jam_mulai');
    const jamSelesai = document.getElementById('jam_selesai');

    function setJam(selectEl, nilai) {
        if (!selectEl || !nilai) return;
        if (!Array.from(selectEl.options).some((o) => o.value === nilai)) {
            const opt = document.createElement('option');
            opt.value = nilai;
            opt.textContent = nilai;
            selectEl.appendChild(opt);
        }
        selectEl.value = nilai;
        tandaiPlaceholder(selectEl);
    }

    shiftSelect?.addEventListener('change', () => {
        const opt = shiftSelect.options[shiftSelect.selectedIndex];
        if (!opt || !opt.value) return;
        setJam(jamMulai, opt.dataset.mulai);
        setJam(jamSelesai, opt.dataset.selesai);
    });

    gambarChip();
})();
</script>

<style>
.lf-page {
    width: 100%;
    max-width: none;
    margin: 0;
    padding: 0 8px 32px;
    box-sizing: border-box;
    color: #111827;
    font-family: 'Segoe UI',Inter,Arial,sans-serif;
}

.lf-page * {
    box-sizing: border-box;
}

.lf-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 14px;
}

.lf-breadcrumb a {
    color: #6b7280;
    text-decoration: none;
}

.lf-breadcrumb a:hover {
    color: #ff941c;
}

.lf-breadcrumb strong {
    color: #111827;
    font-weight: 600;
}

.lf-alert {
    padding: 12px 16px;
    border-radius: 12px;
    font-size: 13px;
    margin-bottom: 16px;
    background: #fffbeb;
    border: 1px solid #fcd34d;
    color: #92400e;
}

.lf-card {
    background: #fff;
    border: 1px solid #d0d5dd;
    border-radius: 22px;
    padding: 24px 26px;
}

.lf-heading {
    margin: 0 0 4px;
    font-size: 19px;
    font-weight: 700;
    color: #111827;
}

.lf-subheading {
    margin: 0 0 22px;
    font-size: 13.5px;
    color: #6b7280;
}

.lf-grid-2 {
    display: grid;
    grid-template-columns: repeat(2,minmax(0,1fr));
    gap: 16px 28px;
    margin-bottom: 16px;
}

.lf-field {
    display: flex;
    flex-direction: column;
    gap: 7px;
    min-width: 0;
}

.lf-label {
    font-size: 13px;
    font-weight: 600;
    color: #6b7280;
}

.lf-control {
    width: 100%;
    height: 44px;
    padding: 0 14px;
    border: 1px solid #d0dbea;
    border-radius: 11px;
    background: #fff;
    color: #111827;
    font-size: 14px;
    font-family: inherit;
    outline: none;
    transition: border-color .15s ease;
}

.lf-control:focus {
    border-color: #ff941c;
}

.lf-control::placeholder {
    color: #9ca3af;
}

.lf-control.is-readonly {
    background: #f3f4f6;
    color: #111827;
    cursor: default;
}

select.lf-control {
    padding-right: 40px;
    cursor: pointer;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23101828' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
}

select.lf-control.is-placeholder {
    color: #9ca3af;
}

select.lf-control option {
    color: #111827;
}

/* tanggal dengan ikon kalender */

.lf-datewrap {
    position: relative;
}

.lf-dateicon {
    position: absolute;
    right: 14px;
    top: 50%;
    width: 18px;
    height: 18px;
    transform: translateY(-50%);
    stroke: #111827;
    stroke-width: 1.8;
    fill: none;
    stroke-linecap: round;
    stroke-linejoin: round;
    pointer-events: none;
}

.lf-datewrap input[type="date"]::-webkit-calendar-picker-indicator {
    position: absolute;
    right: 0;
    top: 0;
    width: 46px;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

/* kotak karyawan (chip) */

.lf-karyawan-box {
    min-height: 46px;
    padding: 8px 12px;
    border: 1px solid #d0dbea;
    border-radius: 11px;
    background: #fff;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

.lf-chip-list {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

.lf-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    height: 30px;
    padding: 0 10px 0 14px;
    border-radius: 15px;
    background: #eef2f6;
    color: #111827;
    font-size: 13px;
    font-weight: 500;
}

.lf-chip-hapus {
    border: none;
    background: transparent;
    color: #4b5563;
    font-size: 16px;
    line-height: 1;
    padding: 0 2px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.lf-chip-hapus:hover {
    color: #c62828;
}

.lf-btn-tambah-chip {
    border: none;
    background: transparent;
    color: #ff941c;
    font-size: 13px;
    font-weight: 600;
    font-family: inherit;
    padding: 4px 6px;
    cursor: pointer;
}

.lf-btn-tambah-chip:hover {
    text-decoration: underline;
}

/* pemilih karyawan */

.lf-picker {
    position: absolute;
    left: 0;
    top: calc(100% - 4px);
    z-index: 20;
    width: min(420px,100%);
    padding: 14px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    background: #fff;
    border: 1px solid #d0d5dd;
    border-radius: 14px;
    box-shadow: 0 12px 30px rgba(15,23,42,.14);
}

.lf-picker[hidden] {
    display: none;
}

.lf-picker-aksi {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}

.lf-textarea {
    width: 100%;
    min-height: 92px;
    padding: 12px 14px;
    border: 1px solid #d0dbea;
    border-radius: 12px;
    background: #fff;
    color: #111827;
    font-size: 14px;
    font-family: inherit;
    resize: vertical;
    outline: none;
    transition: border-color .15s ease;
}

.lf-textarea::placeholder {
    color: #9ca3af;
}

.lf-textarea:focus {
    border-color: #ff941c;
}

.lf-hint {
    font-size: 12px;
    color: #6b7280;
}

.lf-error {
    font-size: 12px;
    color: #c62828;
    font-weight: 500;
}

.lf-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 22px;
}

.lf-btn {
    height: 44px;
    padding: 0 22px;
    border-radius: 11px;
    font-size: 14px;
    font-weight: 600;
    font-family: inherit;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none;
    cursor: pointer;
    transition: all .15s ease;
}

.lf-btn-batal,
.lf-btn-draf {
    border: 1px solid #d0d5dd;
    background: #fff;
    color: #111827;
}

.lf-btn-batal:hover {
    background: #f9fafb;
    border-color: #9ca3af;
}

.lf-btn-draf:hover {
    border-color: #ff941c;
    color: #ff941c;
    background: #fffaf4;
}

.lf-btn-utama {
    border: 1px solid #ff941c;
    background: #ff941c;
    color: #fff;
}

.lf-btn-utama:hover {
    background: #ea820c;
    border-color: #ea820c;
}

.lf-btn-kecil {
    height: 36px;
    padding: 0 14px;
    font-size: 13px;
}

@media(max-width:760px) {
    .lf-grid-2 {
        grid-template-columns: 1fr;
    }

    .lf-actions {
        justify-content: stretch;
    }

    .lf-actions .lf-btn {
        flex: 1 1 auto;
    }
}
</style>
@endsection