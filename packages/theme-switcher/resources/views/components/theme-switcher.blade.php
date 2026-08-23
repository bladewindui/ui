{{-- format-ignore-start --}}
@props([
    'lightIcon' => 'sun',
    'lightText' => 'Light',
    'darkIcon' => 'moon',
    'darkText' => 'Dark',
    'systemIcon' => 'computer-desktop',
    'systemText' => 'System',
    'iconRight' => true,
    'iconType' => 'outline',
    'iconDir' => '',
    'modular' => false,
    'class' => '',
    'nonce' => config('bladewind.script.nonce', null),
])
@php
    $iconRight = parseBladewindVariable($iconRight);
    $modular = parseBladewindVariable($modular);
@endphp
{{-- format-ignore-end --}}

@once
    <x-bladewind::dropmenu :modular="$modular" icon_right="{{$iconRight}}">
        <x-slot:trigger>
            <x-bladewind::icon
                    name="{{$lightIcon}}"
                    type="{{$iconType}}"
                    dir="{{$iconDir}}"
                    class="text-primary-600 hover:text-primary-500 dark:text-dark-500! dark:hover:text-dark-300 stroke-2 theme-light hidden {{$class}}"/>
            <x-bladewind::icon
                    name="{{$darkIcon}}"
                    type="{{$iconType}}"
                    dir="{{$iconDir}}"
                    class="text-primary-400 hover:text-primary-500 dark:text-dark-500! dark:hover:text-dark-400! stroke-2 theme-dark hidden {{$class}}"/>
            <x-bladewind::icon
                    name="{{$systemIcon}}"
                    type="{{$iconType}}"
                    dir="{{$iconDir}}"
                    class="text-primary-400 hover:text-primary-500 dark:text-dark-500! dark:hover:text-dark-400! stroke-2 theme-system hidden {{$class}}"/>
        </x-slot:trigger>
        <x-bladewind::dropmenu.item data-bw-theme="light" icon="{{$lightIcon}}" icon_css="stroke-2">
            {{$lightText}}
        </x-bladewind::dropmenu.item>
        <x-bladewind::dropmenu.item data-bw-theme="dark" icon="{{$darkIcon}}" icon_css="stroke-2">
            {{$darkText}}
        </x-bladewind::dropmenu.item>
        <x-bladewind::dropmenu.item data-bw-theme="system" icon="{{$systemIcon}}" icon_css="stroke-2">
            {{$systemText}}
        </x-bladewind::dropmenu.item>
    </x-bladewind::dropmenu>
    <x-bladewind::script :nonce="$nonce">
        const chooseTheme = (theme) => {
        theme = (theme !== 'null' && theme !== undefined && theme !== null) ? theme : 'system';
        addToStorage('theme', theme);

        if (theme === 'dark') {
        document.documentElement.classList.add('dark');
        } else if (theme === 'light') {
        document.documentElement.classList.remove('dark');
        } else if (theme === 'system') {
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
        document.documentElement.classList.add('dark');
        } else {
        document.documentElement.classList.remove('dark');
        }
        }

        hide('.theme-dark');
        hide('.theme-light');
        hide('.theme-system');
        changeCss('.theme-dark','inline-block','remove');
        changeCss('.theme-light','inline-block','remove');
        changeCss('.theme-system','inline-block','remove');
        unhide(`.theme-${theme}`);
        };

        /* delegated rather than three inline onclicks, so a strict CSP does not
           disable theme switching. see #608 */
        bwOn('click', '[data-bw-theme]', (el) => chooseTheme(el.getAttribute('data-bw-theme')));

        chooseTheme(getFromStorage('theme'));

        // Listen for changes in the system theme
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if (getFromStorage('theme') === 'system') {
        chooseTheme('system');
        }
        });
    </x-bladewind::script>
@endonce