{{-- format-ignore-start --}}
@props([
    'totalRecords' => null,
    'pageSize' => 25,
    'style' => 'arrows',
    'showTotal' => true,
    'showPageNumber' => true,
    'showTotalPages' => false,
    'defaultPage' => 1,
    'table' => null,
    'label' => __("bladewind::bladewind.pagination_label"),

    // a Laravel paginator. when present the component renders server-side links
    // instead of the DOM show/hide mode, which cannot work against a paginator.
    'paginator' => null,

    // page sizes to offer in a per-page selector. empty hides the selector.
    'perPageOptions' => config('bladewind.pagination.per_page_options', []),

    // query string parameter the per-page selector writes to
    'perPageName' => config('bladewind.pagination.per_page_name', 'per_page'),

    // how many page numbers to show either side of the current one
    'onEachSide' => config('bladewind.pagination.on_each_side', 1),

    'nonce' => config('bladewind.script.nonce', null),
])

@php
    $showTotal = parseBladewindVariable($showTotal);
    $onEachSide = parseBladewindVariable($onEachSide, 'int') ?: 0;

    // ---- server mode -----------------------------------------------------
    // a paginator switches the whole component over. everything below this block
    // is the original client-side mode and is untouched.
    $is_length_aware = $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
    $is_paginator = $is_length_aware || $paginator instanceof \Illuminate\Contracts\Pagination\Paginator;

    if ($is_paginator) {
        $current_page = $paginator->currentPage();
        $per_page = $paginator->perPage();
        $from = $paginator->firstItem() ?? 0;
        $to = $paginator->lastItem() ?? 0;
        $total = $is_length_aware ? $paginator->total() : null;
        $last_page = $is_length_aware ? max(1, (int) $paginator->lastPage()) : null;

        // first and last are always reachable, with an ellipsis standing in for
        // whatever the window leaves out. prev/next alone gives the reader no
        // sense of position, which is why the audited app replaced this outright.
        $pages = [];
        if ($is_length_aware) {
            $window_start = max(1, $current_page - $onEachSide);
            $window_end = min($last_page, $current_page + $onEachSide);

            foreach (range($window_start, $window_end) as $p) {
                $pages[] = $p;
            }
            if ($window_start > 1) {
                array_unshift($pages, ...($window_start > 2 ? [1, '...'] : [1]));
            }
            if ($window_end < $last_page) {
                array_push($pages, ...($window_end < $last_page - 1 ? ['...', $last_page] : [$last_page]));
            }
        }

        $server_label = str_replace(
            [':a', ':b', ':c'],
            [
                '<span class="font-semibold from">'.$from.'</span>',
                '<span class="font-semibold to">'.$to.'</span>',
                '<strong>'.($total ?? '').'</strong>',
            ],
            $label
        );

        $per_page_options = is_array($perPageOptions) ? $perPageOptions : array_filter(explode(',', (string) $perPageOptions));
    }

    $showPageNumber = parseBladewindVariable($showPageNumber);
    $showTotalPages = parseBladewindVariable($showTotalPages);
    $defaultPage = parseBladewindVariable($defaultPage, 'int');
    $style = (!in_array($style, ['arrows', 'dropdown', 'numbers'])) ? 'arrows' : $style;
    $total_pages = ceil($totalRecords/$pageSize);
    $defaultPage = (is_numeric($defaultPage) && $defaultPage > 0 && $defaultPage <= $total_pages) ? $defaultPage : 1;
    $inactive_css = "opacity-30 hover:opacity-30 !hover:border-gray-500/50";
    $active_css = "!border-primary-500 dark:!border-dark-400";
    $default_button_css = "!border-gray-200 dark:!border-dark-600 hover:!border-gray-400 dark:hover:!border-dark-500";
    $next_button_status_css = ($defaultPage == $total_pages) ? $inactive_css : $default_button_css;
    $prev_button_status_css = ($defaultPage == 1) ? $inactive_css : $default_button_css;
    $to = $pageSize*$defaultPage;
    $to = ($to > $totalRecords) ? $totalRecords : $to;
    $from = $to - ($pageSize-1);
    $from = ($to == $totalRecords) ? 1 : $from;
    $prev_page = $defaultPage-1;
    $next_page = $defaultPage+1;
    $prev_page = ($prev_page <= 0) ? 0 : $defaultPage-1;
@endphp
{{-- format-ignore-end --}}

@if($is_paginator)
    @include('bladewind::components.pagination-server')
@elseif(!empty($totalRecords) && !empty($table))
    <x-bladewind::script :nonce="$nonce">
        var previousPage_{{$table}} = {{$defaultPage}};
        var totalPages_{{$table}} = {{$total_pages}};
        var pageSize_{{$table}} = {{$pageSize}};
        var totalRecords_{{$table}} = {{$totalRecords}};
        var paginationStyle_{{$table}} = '{{$style}}';
    </x-bladewind::script>
    <div class="flex px-5 py-2 justify-between text-sm items-center opacity-80 bw-pagination-{{$table}}">
        <div>
            @if($showTotal)
                {!! str_replace(':c', '<strong>'.$totalRecords.'</strong>',
                    str_replace(':b', '<span class="font-semibold to">'.$to.'</span>',
                    str_replace(':a', '<span class="font-semibold from">'.$from.'</span>', $label)))  !!}
            @endif
        </div>
        <div>
            @if($style == 'arrows')
                <x-bladewind::button
                        type="primary"
                        :outline="true"
                        color="gray"
                        size="tiny"
                        icon="arrow-left" class="!pr-0 prev-btn {{$prev_button_status_css}}"
                        onclick="goToPage('{{$prev_page}}', '{{$table}}', '{{$defaultPage}}')"/>
                <span class="page-number font-semibold p-1 @if(!$showPageNumber)hidden @endif"><span
                            class="page">{{$defaultPage}}</span>@if($showTotalPages)
                        /{{$total_pages}}
                    @endif</span>
                <x-bladewind::button
                        type="primary"
                        :outline="true"
                        color="gray"
                        size="tiny"
                        icon="arrow-right" class="!pr-0 next-btn {{$next_button_status_css}}"
                        onclick="goToPage('{{$next_page}}', '{{$table}}')"/>
            @elseif($style == 'dropdown')
                <div class="!z-50">
                    <span class="table-name hidden" data-value="{{$table}}"></span>
                    <span class="current-page hidden" data-value="{{$defaultPage}}"></span>
                    <x-bladewind::select :selected_value="$defaultPage" :required="true" data="manual"
                                         meta="{table: '{{$table}}'}" size="small">
                        @for($p=1; $p <= $total_pages; $p++)
                            <x-bladewind::select.item :label="$p" :value="$p" onselect="routeToPage"/>
                        @endfor
                    </x-bladewind::select>
                </div>
            @else
                <span class="current-page hidden" data-value="{{$defaultPage}}"></span>
                <div class="flex">
                    <x-bladewind::button
                            type="primary"
                            color="gray"
                            :outline="true"
                            icon="arrow-left"
                            size="tiny"
                            class="!mr-0.5 !pl-5 !pr-1 prev-btn {{$default_button_css}}"
                            onclick="routeToPage('{{$prev_page}}', '', '', {table: '{{$table}}'}); shufflePageNumbers('{{$prev_page}}', '{{$table}}')"/>
                    <span class="mt-3 prev-dots"></span>
                    @for($p=1; $p <= $total_pages; $p++)
                        @php
                            $button_css = ($p==$defaultPage) ? $active_css : $default_button_css;
                        @endphp
                        <x-bladewind::button
                                type="primary"
                                color="gray"
                                :outline="true"
                                size="tiny"
                                data-page="{{$p}}"
                                class="btn {{ (strlen($p) == 1) ? '!px-3' : '!px-2' }} hidden !text-xs !mx-0.5 btn-{{$p}} {{$button_css}}"
                                onclick="routeToPage('{{$p}}', '', '', {table: '{{$table}}'}); shufflePageNumbers('{{$p}}', '{{$table}}')">{{$p}}</x-bladewind::button>
                        <span class="mt-3 dots-{{$p}}"></span>
                    @endfor
                    <span class="mt-3 next-dots"></span>
                    <x-bladewind::button
                            type="primary"
                            color="gray"
                            :outline="true"
                            icon="arrow-right"
                            size="tiny"
                            class="!ml-0.5 !pl-5 !pr-1 next-btn {{$default_button_css}}"
                            onclick="routeToPage('{{$next_page}}', '', '', {table: '{{$table}}'}); shufflePageNumbers('{{$next_page}}', '{{$table}}')"/>
                </div>
                <x-bladewind::script :nonce="$nonce">shufflePageNumbers('{{$defaultPage}}', '{{$table}}')
                </x-bladewind::script>
            @endif
        </div>
    </div>
@endif
@if(! $is_paginator)
@once
    <span class="dots hidden"><x-bladewind::icon name="ellipsis-horizontal"/></span>
    <x-bladewind::script :nonce="$nonce">
        var selectPage = (page, previousPage, table) => {
        let pageButton = domEl(`.bw-pagination-${table} button[data-page="${page}"]`);
        let prevPageButton = domEl(`.bw-pagination-${table} button[data-page="${previousPage}"]`);
        if (pageButton && prevPageButton) {
        let cssToRemove = `border-gray-500/50, focus:ring-gray-500, hover:border-gray-600,
        dark:hover:border-gray-500,{{str_replace(' ', ',', $default_button_css)}}`;
        let cssToAdd = '{{str_replace(' ', ',', $active_css)}}';
        changeCss(pageButton, cssToRemove, 'remove', true);
        changeCss(pageButton, cssToAdd, 'add', true);
        changeCss(prevPageButton, cssToRemove, 'add', true);
        changeCss(prevPageButton, cssToAdd, 'remove', true);
        }
        }

        var goToPage = (page, table) => {
        let currentPage = parseInt(page);
        let previousPage = window[`previousPage_${table}`];
        let totalPages = window[`totalPages_${table}`];
        let totalRecords = window[`totalRecords_${table}`];
        let pageSize = window[`pageSize_${table}`];
        let paginationStyle = window[`paginationStyle_${table}`];

        if ((currentPage === previousPage)) return false;
        if (currentPage !== 0 && currentPage <= totalPages) {
        let nextPage = currentPage + 1;
        let prevPage = currentPage - 1;
        let nextButton = domEl(`.bw-pagination-${table} .next-btn`);
        let prevButton = domEl(`.bw-pagination-${table} .prev-btn`);
        let end = (currentPage * pageSize);
        let begin = end - (pageSize - 1);
        end = (end > totalRecords) ? totalRecords : end;

        // show rows on the current page
        domEls(`table.${table} tr[data-page="${currentPage}"]`).forEach((el) => {
        unhide(el, true);
        });
        // hide rows on previous pages
        if (domEls(`table.${table} tr[data-page="${previousPage}"]`)) {
        domEls(`table.${table} tr[data-page="${previousPage}"]`).forEach((el) => {
        hide(el, true);
        });
        }

        if (prevButton) {
        if (currentPage === 1) {
        prevButton.removeAttribute('onclick');
        changeCss(prevButton, 'opacity-30,hover:opacity-30,hover:!border-gray-500/50', 'add', true);
        } else {
        if (paginationStyle === 'numbers') {
        prevButton.setAttribute('onclick', `goToPage(${prevPage}, '${table}'); shufflePageNumbers('${prevPage}',
        '${table}')`);
        } else {
        prevButton.setAttribute('onclick', `goToPage(${prevPage}, '${table}')`);
        }
        changeCss(prevButton, 'opacity-30,hover:opacity-30,hover:!border-gray-500/50', 'remove', true);
        }
        }
        if (nextButton) {
        if (currentPage === totalPages) {
        nextButton.removeAttribute('onclick');
        changeCss(nextButton, 'opacity-30,hover:opacity-30,hover:!border-gray-500/50', 'add', true);
        } else {
        if (paginationStyle === 'numbers') {
        nextButton.setAttribute('onclick', `goToPage(${nextPage}, '${table}'); shufflePageNumbers('${nextPage}',
        '${table}')`);
        } else {
        nextButton.setAttribute('onclick', `goToPage(${nextPage}, '${table}')`);
        }
        changeCss(nextButton, 'opacity-30,hover:opacity-30,hover:!border-gray-500/50', 'remove', true);
        }
        }
        selectPage(currentPage, previousPage, table);
        domEl(`.bw-pagination-${table} .from`).innerText = begin;
        domEl(`.bw-pagination-${table} .to`).innerText = end;
        domEl(`.bw-pagination-${table} .page-number .page`).innerText = currentPage;
        domEl(`.${table}`).setAttribute('data-current-page', currentPage);
        window[`previousPage_${table}`] = currentPage;
        }
        }

        var routeToPage = (page, value, third, meta = {}) => {
        let table = meta.table;
        let currentPage = domEl(`.${table}`).getAttribute('data-current-page');

        if ((currentPage === page)) return false;
        if ((page !== currentPage) && domEls(`table.${table} tr[data-page="${currentPage}"]`)) {
        domEls(`table.${table} tr[data-page="${currentPage}"]`).forEach((el) => {
        hide(el, true);
        });
        }
        goToPage(page, table);
        }

        const shufflePageNumbers = (page, table) => {
        let totalPages = window[`totalPages_${table}`];
        let dots = domEl('.dots').innerHTML;
        let equalDivider = domEl(`.bw-pagination-${table} .dots-3`);
        let firstDots = domEl(`.bw-pagination-${table} .dots-1`);
        let lastDots = domEl(`.bw-pagination-${table} .dots-${totalPages - 1}`);
        page = parseInt(page);

        const hideAllButtons = () => {
        domEls(`.bw-pagination-${table} .btn`).forEach((el) => {
        hide(el, true);
        });
        }

        const defaultMode = () => {
        let defaultsArray = [1, 2, 3, totalPages - 2, totalPages - 1, totalPages];
        equalDivider.innerHTML = dots;
        firstDots.innerHTML = '';
        lastDots.innerHTML = '';
        hideAllButtons();
        for (x = 0; x <= defaultsArray.length; x++) {
        unhide(`.bw-pagination-${table} .btn-${defaultsArray[x]}`);
        }
        }

        // if total pages are more than 7
        if (totalPages > 7) {
        // -- if the page is 3 or more
        if (page >= 3) {
        if (page >= (totalPages - 1)) {
        defaultMode();
        } else {
        equalDivider.innerHTML = '';
        firstDots.innerHTML = dots;
        lastDots.innerHTML = dots;

        hideAllButtons();

        for (x = (page - 2); x <= (page + 2); x++) {
        unhide(`.bw-pagination-${table} .btn-${x}`);
        }
        unhide(`.bw-pagination-${table} .btn-1`);
        unhide(`.bw-pagination-${table} .btn-${totalPages}`);
        }
        } else {
        defaultMode();
        }
        }

        }
    </x-bladewind::script>
@endonce
@endif
