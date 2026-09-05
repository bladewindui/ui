{{-- format-ignore-start --}}
@props([
    // filename to display
    'name' => '',
    // file size in bytes. formatted to B/KB/MB/GB. blank hides the size line
    'size' => null,
    // link used for the download action and, if given, the whole preview
    'url' => null,
    // an image URL to show as the thumbnail. omit to show a generic icon
    // derived from the filename's extension instead
    'thumbnail' => null,
    // overrides the extension-derived icon
    'icon' => null,
    'removable' => config('bladewind.file_preview.removable', true),
    'downloadable' => config('bladewind.file_preview.downloadable', true),
    // raw JS run when remove is clicked. blank removes the preview from the
    // DOM directly, the same default-close pattern as the Tag component
    'onRemove' => '',
    'class' => '',
])
@php
    $removable = parseBladewindVariable($removable);
    $downloadable = parseBladewindVariable($downloadable);

    $formatSize = function ($bytes) {
        if ($bytes === null || $bytes === '' || ! is_numeric($bytes)) {
            return null;
        }
        $bytes = (float) $bytes;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return ($i === 0 ? (string) (int) $bytes : number_format($bytes, 1)).' '.$units[$i];
    };

    $extensionIcons = [
        'pdf' => 'document-text',
        'doc' => 'document-text', 'docx' => 'document-text',
        'txt' => 'document-text', 'rtf' => 'document-text',
        'xls' => 'table-cells', 'xlsx' => 'table-cells', 'csv' => 'table-cells',
        'ppt' => 'presentation-chart-bar', 'pptx' => 'presentation-chart-bar',
        'jpg' => 'photo', 'jpeg' => 'photo', 'png' => 'photo', 'gif' => 'photo',
        'webp' => 'photo', 'svg' => 'photo', 'bmp' => 'photo',
        'mp4' => 'film', 'mov' => 'film', 'avi' => 'film', 'webm' => 'film',
        'mp3' => 'musical-note', 'wav' => 'musical-note', 'ogg' => 'musical-note',
        'zip' => 'archive-box', 'rar' => 'archive-box', '7z' => 'archive-box', 'tar' => 'archive-box', 'gz' => 'archive-box',
        'json' => 'code-bracket', 'xml' => 'code-bracket', 'html' => 'code-bracket',
        'js' => 'code-bracket', 'php' => 'code-bracket', 'css' => 'code-bracket',
    ];

    $extension = strtolower((string) pathinfo($name, PATHINFO_EXTENSION));
    $resolvedIcon = $icon ?: ($extensionIcons[$extension] ?? 'document');
    $formattedSize = $formatSize($size);
@endphp
{{-- format-ignore-end --}}
<div @class([
        'bw-file-preview flex items-center gap-3 rounded-lg border border-gray-200 dark:border-dark-600 p-3',
        "$class",
    ])>
    <div class="shrink-0 size-10 rounded-md overflow-hidden bg-gray-100 dark:bg-dark-800 flex items-center justify-center">
        @if(! empty($thumbnail))
            <img src="{{ $thumbnail }}" alt="" class="size-full object-cover"/>
        @else
            <x-bladewind::icon name="{{ $resolvedIcon }}" class="size-6 text-gray-400 dark:text-dark-400"/>
        @endif
    </div>
    <div class="min-w-0 grow">
        <div class="text-sm text-gray-700 dark:text-dark-200 truncate" title="{{ $name }}">{{ $name }}</div>
        @if($formattedSize !== null)
            <div class="text-xs text-gray-400 dark:text-dark-500">{{ $formattedSize }}</div>
        @endif
    </div>
    <div class="flex items-center gap-1 shrink-0">
        @if($downloadable && ! empty($url))
            <a href="{{ $url }}" download target="_blank" rel="noopener"
               aria-label="{{ __('bladewind::bladewind.file_preview_download') }} {{ $name }}"
               class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-dark-800">
                <x-bladewind::icon name="arrow-down-tray" class="size-4 text-gray-400 hover:text-gray-600 dark:hover:text-dark-200"/>
            </a>
        @endif
        @if($removable)
            <a href="javascript:void(0)"
               @if($onRemove === '') data-bw-file-preview-remove @else onclick="{!! $onRemove !!}" @endif
               aria-label="{{ __('bladewind::bladewind.file_preview_remove') }} {{ $name }}"
               class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-dark-800">
                <x-bladewind::icon name="x-mark" class="size-4 text-gray-400 hover:text-red-600"/>
            </a>
        @endif
    </div>
</div>
