{{-- format-ignore-start --}}
{{--
 | Server-side pagination markup, rendered by pagination.blade.php when it is
 | handed a Laravel paginator. Expects the variables that component prepares.
 |
 | Three controls, because prev/next alone gives the reader no sense of position:
 | a "showing x to y of z" label, an optional per-page selector, and windowed page
 | numbers with first and last always reachable.
--}}
@php
    $link_base = 'inline-flex items-center justify-center min-w-8 h-8 px-2 text-xs rounded-md border transition';
    $link_idle = 'border-gray-200 dark:border-dark-600 hover:border-gray-400 dark:hover:border-dark-500 text-gray-600 dark:text-dark-300';
    $link_active = 'border-primary-500 dark:border-primary-500 text-primary-600 dark:text-primary-400 font-semibold';
    $link_off = 'border-gray-200 dark:border-dark-600 opacity-30 cursor-not-allowed';
@endphp
{{-- format-ignore-end --}}

<div {{ $attributes->exceptPropAliases(get_defined_vars())->merge([
        'class' => 'bw-pagination bw-pagination-server flex flex-wrap gap-3 px-5 py-2 justify-between items-center text-sm opacity-90',
    ]) }}>

    @if($showTotal && ! is_null($total))
        <div class="pagination-summary">{!! $server_label !!}</div>
    @endif

    @if(! empty($per_page_options))
        <div class="per-page flex items-center gap-2">
            <label for="{{ $perPageName }}" class="text-gray-500 dark:text-dark-400">
                {{ __('bladewind::bladewind.pagination_per_page') }}
            </label>
            <select id="{{ $perPageName }}"
                    class="bw-raw-select !py-1 !w-auto text-xs"
                    data-bw-per-page>
                @foreach($per_page_options as $option)
                    <option value="{{ $option }}"
                            @selected((int) $option === (int) $per_page)
                            data-url="{{ request()->fullUrlWithQuery([$perPageName => $option, $paginator->getPageName() => 1]) }}">
                        {{ $option }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif

    <div class="pagination-links flex items-center gap-1">
        @if($paginator->onFirstPage())
            <span class="{{ $link_base }} {{ $link_off }} prev-btn" aria-disabled="true">
                <x-bladewind::icon name="arrow-left" size="tiny" class="stroke-2"/>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
               rel="prev"
               aria-label="{{ __('bladewind::bladewind.pagination_previous') }}"
               class="{{ $link_base }} {{ $link_idle }} prev-btn">
                <x-bladewind::icon name="arrow-left" size="tiny" class="stroke-2"/>
            </a>
        @endif

        @foreach($pages as $page)
            @if($page === '...')
                <span class="dots px-1 text-gray-400 dark:text-dark-500">&hellip;</span>
            @elseif($page == $current_page)
                <span class="{{ $link_base }} {{ $link_active }} page current" aria-current="page">{{ $page }}</span>
            @else
                <a href="{{ $paginator->url($page) }}" class="{{ $link_base }} {{ $link_idle }} page">{{ $page }}</a>
            @endif
        @endforeach

        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
               rel="next"
               aria-label="{{ __('bladewind::bladewind.pagination_next') }}"
               class="{{ $link_base }} {{ $link_idle }} next-btn">
                <x-bladewind::icon name="arrow-right" size="tiny" class="stroke-2"/>
            </a>
        @else
            <span class="{{ $link_base }} {{ $link_off }} next-btn" aria-disabled="true">
                <x-bladewind::icon name="arrow-right" size="tiny" class="stroke-2"/>
            </span>
        @endif
    </div>
</div>

@once
    <x-bladewind::script :nonce="$nonce">
        {{-- delegated rather than an inline onchange, so a strict CSP does not
             disable the per-page selector. see #608 --}}
        bwOn('change', '[data-bw-per-page]', (select) => {
        const url = select.options[select.selectedIndex]?.dataset.url;
        if (url) window.location.href = url;
        });
    </x-bladewind::script>
@endonce
