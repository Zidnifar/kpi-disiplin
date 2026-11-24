<div class="kartu stat">
    <div>
        <div class="stat-angka">{{ $angka }}</div>
        <div class="stat-label">{{ $label }}</div>
    </div>
    <div class="stat-ikon"><x-icon :nama="$ikon ?? 'chart'" :ukuran="24" /></div>
</div>
