{{-- format-ignore-start --}}
{{--
 | Column-model rendering for the table component (#592).
 |
 | Alignment lives here rather than in the consumer's class attribute, which is
 | where the 371 !text-right / !text-center overrides in the audited app came
 | from: there was no column model to hang it on.
--}}
@php
    $rows_to_render = is_array($data) ? $data : [];
    $column_count = count($columns) + ($showRowNumbers ? 1 : 0) + (!empty($actionIcons) ? 1 : 0);
@endphp
{{-- format-ignore-end --}}

<thead>
<tr>
    @if($showRowNumbers)
        <th>#</th>
    @endif
    @foreach($columns as $index => $column)
        <th @class([$column_alignment[$column['align']], $column['class'], 'cursor-pointer' => $column['sortable']])
            @if($column['width']) style="width: {{ $column['width'] }}" @endif
            @if($column['sortable'])
                data-sort-dir="no-sort"
                data-can-sort="true"
                data-column-index="{{ $checkable ? $index + 1 : $index }}"
            @endif>
            <span @class(['peer', 'cursor-pointer' => $column['sortable']])>{{ $column['label'] }}</span>
            @if($column['sortable'])
                <x-bladewind::icon name="funnel" class="size-3 opacity-40 peer-hover:opacity-80 no-sort"/>
                <x-bladewind::icon name="arrow-long-up" class="size-3 opacity-60 peer-hover:opacity-90 sort-asc hidden"/>
                <x-bladewind::icon name="arrow-long-down" class="size-3 opacity-60 peer-hover:opacity-90 sort-desc hidden"/>
            @endif
        </th>
    @endforeach
    @if(!empty($actionIcons))
        <th class="text-right">{{ $actionsTitle }}</th>
    @endif
</tr>
</thead>
<tbody>
@forelse($rows_to_render as $row)
    @php
        $row = (array) $row;
        $row_id = $row['id'] ?? uniqid();
    @endphp
    {{-- paginationRow() is emitted whether or not the table is paginated, the same
         as the legacy data path. sortTableByColumn filters rows by data-page, so
         without it a non-paginated table sorts nothing at all. --}}
    <tr {!! paginationRow($loop->iteration, $pageSize, $defaultPage) !!} data-id="{{ $row_id }}">
        @if($showRowNumbers)
            <td>{{ $loop->iteration }}</td>
        @endif
        @foreach($columns as $column)
            @php
                $value = $row[$column['key']] ?? null;
                $formatter = $column['format'];
                if (is_callable($formatter)) {
                    $value = $formatter($value, $row);
                }
            @endphp
            <td @class([$column_alignment[$column['align']], $column['class']])
                data-row-id="{{ $row_id }}"
                data-column="{{ $column['key'] }}"
                @if(!empty($onclick)) onclick="{!! build_click($onclick, $row) !!}" @endif>{!! $value !!}</td>
        @endforeach
        <x-bladewind::table-icons :icons_array="$actionIcons" :row="$row"/>
    </tr>
@empty
    <tr class="no-hover">
        <td colspan="{{ $column_count ?: 1 }}" class="text-center">
            @if($empty)
                {{ $empty }}
            @elseif($messageAsEmptyState)
                <x-bladewind::empty-state
                        :message="$noDataMessage"
                        :buttonLabel="$buttonLabel"
                        :onclick="$onclick"
                        :image="$image"
                        :showImage="$showImage"
                        :heading="$heading"/>
            @else
                {{ $noDataMessage }}
            @endif
        </td>
    </tr>
@endforelse
</tbody>
