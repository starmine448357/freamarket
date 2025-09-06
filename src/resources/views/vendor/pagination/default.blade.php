@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="pager">
        {{-- Prev --}}
        @if ($paginator->onFirstPage())
            <span class="pager__btn is-disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">‹</span>
        @else
            <a class="pager__btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">‹</a>
        @endif

        {{-- Numbers --}}
        <ul class="pager__list" role="list">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="pager__sep" aria-hidden="true">{{ $element }}</li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li>
                                <span class="pager__item is-active" aria-current="page">{{ $page }}</span>
                            </li>
                        @else
                            <li>
                                <a class="pager__item" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </ul>

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a class="pager__btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">›</a>
        @else
            <span class="pager__btn is-disabled" aria-disabled="true" aria-label="@lang('pagination.next')">›</span>
        @endif
    </nav>
@endif
