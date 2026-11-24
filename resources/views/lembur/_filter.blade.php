@php
    $daftarUnit = collect($unit ?? []);
    $daftarOpsiStatus = $opsiStatus ?? [];
    $actionForm = $actionUrl ?? url()->current();
@endphp

<form action="{{ $actionForm }}" method="GET" class="pl-filter-form">
    @if (request('bulan'))
        <input type="hidden" name="bulan" value="{{ request('bulan') }}">
    @endif

    <div class="pl-search-box">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m16 16 4 4"/></svg>
        <input
            type="search"
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari no. pengajuan..."
        >
    </div>

    <div class="pl-select-box">
        <select name="unit" onchange="this.form.submit()">
            <option value="">Semua Unit</option>
            @foreach ($daftarUnit as $u)
                @php
                    $nilaiUnit = is_object($u) ? ($u->id_dept ?? $u->id) : ($u['id_dept'] ?? $u['id'] ?? '');
                    $teksUnit  = is_object($u) ? ($u->nama_dept ?? $u->nama) : ($u['nama_dept'] ?? $u['nama'] ?? '');
                @endphp
                <option value="{{ $nilaiUnit }}" @selected((string) request('unit') === (string) $nilaiUnit)>
                    {{ $teksUnit }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="pl-select-box">
        <select name="status" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            @foreach ($daftarOpsiStatus as $kodeStatus => $labelStatus)
                <option value="{{ $kodeStatus }}" @selected((string) request('status') === (string) $kodeStatus)>
                    {{ $labelStatus }}
                </option>
            @endforeach
        </select>
    </div>
</form>