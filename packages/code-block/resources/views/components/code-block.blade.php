{{-- format-ignore-start --}}
@props([
    // the code to display. Pass it as a string here to avoid HTML-escaping
    // your source by hand — leave it blank and use the default slot instead
    // when writing the snippet directly in the blade file (escape any
    // &lt; &gt; &amp; in it yourself, same as any other <pre><code> block)
    'code' => null,
    // any Prism-supported language: markup, css, javascript, php, bash, json, sql, python, yaml
    'language' => config('bladewind.code_block.language', 'markup'),
    // an optional filename or label shown in the header, e.g. "routes/web.php"
    'title' => '',
    'lineNumbers' => config('bladewind.code_block.line_numbers', false),
    // lines to highlight, Prism line-highlight syntax, e.g. "2" or "3-5,8"
    'highlightLines' => '',
    // wrap long lines instead of scrolling horizontally
    'wrap' => config('bladewind.code_block.wrap', false),
    'showCopy' => config('bladewind.code_block.show_copy', true),
    'showLanguageLabel' => config('bladewind.code_block.show_language_label', true),
    'class' => '',
    'id' => uniqid('bw-code-block-'),
    'nonce' => config('bladewind.script.nonce', null),
])
@php
    $lineNumbers = parseBladewindVariable($lineNumbers);
    $wrap = parseBladewindVariable($wrap);
    $showCopy = parseBladewindVariable($showCopy);
    $showLanguageLabel = parseBladewindVariable($showLanguageLabel);

    // strips the common leading whitespace every line shares, so a snippet
    // indented to match its surrounding blade markup still renders flush left.
    // blade already strips the opening tag's own indentation off the first
    // line of a slot, so that line is left alone here and the shared indent
    // is measured from the remaining lines only
    $dedent = function (string $code): string {
        $lines = explode("\n", str_replace("\t", '    ', $code));

        while (count($lines) && trim($lines[0]) === '') {
            array_shift($lines);
        }
        while (count($lines) && trim(end($lines)) === '') {
            array_pop($lines);
        }

        if (! count($lines)) {
            return '';
        }

        $indent = null;
        foreach (array_slice($lines, 1) as $line) {
            if (trim($line) === '') {
                continue;
            }
            $leading = strlen($line) - strlen(ltrim($line, ' '));
            $indent = $indent === null ? $leading : min($indent, $leading);
        }

        return implode("\n", array_map(
            fn ($line, $i) => $i === 0 ? $line : substr($line, $indent ?? 0),
            $lines,
            array_keys($lines)
        ));
    };

    $code = $dedent($code !== null ? $code : (string) $slot);
@endphp
{{-- format-ignore-end --}}

<div data-bw-code-block="{{ $id }}" @class(['bw-code-block relative rounded-lg overflow-hidden bg-slate-900', $class])>
    @if($showLanguageLabel || $title !== '' || $showCopy)
        <div class="flex items-center justify-between gap-3 px-4 py-2 bg-slate-800/70 text-xs text-slate-300">
            <div class="flex items-center gap-2 min-w-0">
                @if($title !== '')
                    <span class="truncate font-medium">{{ $title }}</span>
                @endif
                @if($showLanguageLabel)
                    <span class="uppercase tracking-wide text-slate-400 shrink-0">{{ $language }}</span>
                @endif
            </div>
            @if($showCopy)
                <button type="button" data-bw-code-block-copy aria-label="Copy code"
                        class="shrink-0 flex items-center gap-1 text-slate-400 hover:text-white">
                    <span data-icon-default><x-bladewind::icon name="clipboard" class="size-4"/></span>
                    <span data-icon-success class="hidden"><x-bladewind::icon name="check" class="size-4 text-green-400"/></span>
                </button>
            @endif
        </div>
    @endif

    <pre @if($highlightLines !== '') data-line="{{ $highlightLines }}" @endif
         @class([
            "language-$language !m-0 !rounded-none",
            'line-numbers' => $lineNumbers,
            '!whitespace-pre-wrap break-words' => $wrap,
         ])><code data-bw-code-block-source class="language-{{ $language }}">{{ $code }}</code></pre>
</div>

@once
    <link rel="stylesheet" href="{{ asset('vendor/bladewind/css/prism-plugins.min.css') }}">
    {{-- a host page may already ship Prism (its own docs, a blog, ...). Only
         load our copy of Prism's core when there isn't one already, so we
         extend whatever is there instead of loading a clashing duplicate --}}
    <x-bladewind::script :nonce="$nonce">
        if (typeof window.Prism === 'undefined') {
            document.write('<scr' + 'ipt src="{{ asset('vendor/bladewind/js/prism-core.min.js') }}"><\/scr' + 'ipt>');
        }
    </x-bladewind::script>
    <x-bladewind::script :nonce="$nonce" src="{{ asset('vendor/bladewind/js/prism-extra-languages.min.js') }}"></x-bladewind::script>
    <x-bladewind::script :nonce="$nonce" src="{{ asset('vendor/bladewind/js/code-block.js') }}"></x-bladewind::script>
@endonce
<x-bladewind::script :nonce="$nonce">
    (() => {
        const root = document.querySelector('[data-bw-code-block="{{ $id }}"]');
        if (root && root.dataset.bwInitialised === 'true') return;
        if (root) root.dataset.bwInitialised = 'true';

        new BladewindCodeBlock('{{ $id }}');
    })();
</x-bladewind::script>
