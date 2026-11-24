<div class="paginasi">
    <span class="paginasi-info">
        @if ($paginator->total() > 0)
            Menampilkan {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
        @else
            Tidak ada data
        @endif
    </span>

    @if ($paginator->hasPages())
        <nav class="paginasi-tombol" role="navigation" aria-label="Halaman">
            @if ($paginator->onFirstPage())
                <span class="nonaktif">‹</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev">‹</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="nonaktif">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $halaman => $url)
                        @if ($halaman == $paginator->currentPage())
                            <span class="aktif">{{ $halaman }}</span>
                        @else
                            <a href="{{ $url }}">{{ $halaman }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next">›</a>
            @else
                <span class="nonaktif">›</span>
            @endif
        </nav>
    @endif
</div>
