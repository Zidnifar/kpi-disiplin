// Dropdown profil (Profil / Keluar): klik tombol untuk buka-tutup, klik di luar untuk menutup.
document.addEventListener('click', function (e) {
    var tombol = e.target.closest('[data-dropdown-tombol]');

    document.querySelectorAll('.dropdown.terbuka').forEach(function (d) {
        if (!tombol || !d.contains(tombol)) {
            d.classList.remove('terbuka');
        }
    });

    if (tombol) {
        tombol.closest('.dropdown').classList.toggle('terbuka');
    }
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.dropdown.terbuka').forEach(function (d) { d.classList.remove('terbuka'); });
    }
});


/* ---------- tahap 3-5 ---------- */

// Konfirmasi sebelum kirim form berbahaya: <form data-konfirmasi="Yakin?">
document.addEventListener('submit', function (e) {
    var pesan = e.target.getAttribute && e.target.getAttribute('data-konfirmasi');
    if (pesan && !window.confirm(pesan)) {
        e.preventDefault();
    }
});

// Tombol cetak: <button data-cetak>
document.addEventListener('click', function (e) {
    if (e.target.closest('[data-cetak]')) {
        window.print();
    }
});

// Baris dinamis (karyawan lembur): wadah [data-baris-wadah], tombol tambah [data-baris-tambah],
// templat <template data-baris-templat> berisi "__INDEKS__" pada atribut name, tombol hapus [data-baris-hapus].
document.addEventListener('click', function (e) {
    var tambah = e.target.closest('[data-baris-tambah]');
    if (tambah) {
        var wadah = document.querySelector('[data-baris-wadah]');
        var templat = document.querySelector('template[data-baris-templat]');
        var indeks = parseInt(wadah.getAttribute('data-indeks') || wadah.children.length, 10);
        wadah.insertAdjacentHTML('beforeend', templat.innerHTML.replace(/__INDEKS__/g, indeks));
        wadah.setAttribute('data-indeks', indeks + 1);
        return;
    }
    var hapus = e.target.closest('[data-baris-hapus]');
    if (hapus) {
        var wadahHapus = document.querySelector('[data-baris-wadah]');
        if (wadahHapus.children.length > 1) {
            hapus.closest('.baris-karyawan').remove();
        }
    }
});

// Pilih shift -> isi jam mulai/selesai: <select data-shift> dengan option data-mulai / data-selesai.
document.addEventListener('change', function (e) {
    var sel = e.target.closest && e.target.closest('select[data-shift]');
    if (!sel) { return; }
    var opsi = sel.options[sel.selectedIndex];
    if (opsi && opsi.getAttribute('data-mulai')) {
        document.querySelector('[name=jam_mulai]').value = opsi.getAttribute('data-mulai');
        document.querySelector('[name=jam_selesai]').value = opsi.getAttribute('data-selesai');
    }
});

// Form periode: tahun + triwulan -> tanggal mulai/selesai dan semester.
(function () {
    var tahun = document.querySelector('[data-periode-tahun]');
    var tw = document.querySelector('[data-periode-tw]');
    if (!tahun || !tw) { return; }
    var mulai = document.querySelector('[data-periode-mulai]');
    var selesai = document.querySelector('[data-periode-selesai]');
    var semester = document.querySelector('[data-periode-semester]');
    function pad(n) { return (n < 10 ? '0' : '') + n; }
    function isi() {
        var t = parseInt(tahun.value, 10), q = parseInt(tw.value, 10);
        if (!t || !q) { return; }
        var bulanAwal = (q - 1) * 3 + 1, bulanAkhir = bulanAwal + 2;
        var hariAkhir = new Date(t, bulanAkhir, 0).getDate();
        mulai.value = t + '-' + pad(bulanAwal) + '-01';
        selesai.value = t + '-' + pad(bulanAkhir) + '-' + pad(hariAkhir);
        semester.value = 'Semester ' + Math.ceil(q / 2);
    }
    tahun.addEventListener('change', isi);
    tw.addEventListener('change', isi);
})();

// Realisasi lembur: hitung total jam per baris <tr data-realisasi-baris> (input jam mulai/selesai + .total).
(function () {
    function hitung(tr) {
        var m = tr.querySelector('[data-mulai]'), s = tr.querySelector('[data-selesai]'), out = tr.querySelector('[data-total]');
        if (!m || !s || !out || !m.value || !s.value) { return; }
        var a = m.value.split(':'), b = s.value.split(':');
        var menit = (parseInt(b[0], 10) * 60 + parseInt(b[1], 10)) - (parseInt(a[0], 10) * 60 + parseInt(a[1], 10));
        if (menit <= 0) { menit += 24 * 60; }
        out.textContent = (Math.round(menit / 6) / 10).toString().replace('.', ',') + ' jam';
    }
    document.addEventListener('input', function (e) {
        var tr = e.target.closest && e.target.closest('[data-realisasi-baris]');
        if (tr) { hitung(tr); }
    });
})();

// Konfirmasi kehadiran karyawan: tampilkan kolom koreksi hanya bila memilih "perlu koreksi".
(function () {
    var radios = document.querySelectorAll('input[name=jawaban]');
    var kotak = document.querySelector('[data-koreksi]');
    if (!radios.length || !kotak) { return; }
    function atur() {
        var pilih = document.querySelector('input[name=jawaban]:checked');
        kotak.style.display = pilih && pilih.value === 'Koreksi' ? '' : 'none';
    }
    radios.forEach(function (r) { r.addEventListener('change', atur); });
    atur();
})();
