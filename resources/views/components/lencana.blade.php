@props(['teks', 'kelas' => null])
<span class="lencana {{ $kelas ?? \App\Support\Format::kelasStatus((string) $teks) }}">{{ $teks }}</span>
