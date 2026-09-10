{{-- format-ignore-start --}}
@props([
    // true right-aligns the bubble as a message sent by the current user,
    // false left-aligns it as a message received from someone else
    'outgoing' => config('bladewind.chat_message.outgoing', false),
    'sender' => '',
    'avatar' => null,
    'time' => '',
    // sending, sent, delivered, read, failed. only shown on outgoing messages
    'status' => '',
    // hides the avatar and sender name, and tightens the gap above, for a
    // message that follows another one from the same sender
    'grouped' => config('bladewind.chat_message.grouped', false),
    'showAvatar' => config('bladewind.chat_message.show_avatar', true),
    'class' => '',
])
@php
    $outgoing = parseBladewindVariable($outgoing);
    $grouped = parseBladewindVariable($grouped);
    $showAvatar = parseBladewindVariable($showAvatar);

    $statusIcons = [
        'sending' => 'clock',
        'sent' => 'check',
        'delivered' => 'check-circle',
        'read' => 'check-circle',
        'failed' => 'exclamation-circle',
    ];
@endphp
{{-- format-ignore-end --}}

<div @class([
        'bw-chat-message flex items-start gap-2',
        'flex-row-reverse' => $outgoing,
        'mt-3' => ! $grouped,
        'mt-0.5' => $grouped,
        $class,
     ])>
    @if($showAvatar && ! $outgoing)
        <div class="shrink-0 w-8">
            @unless($grouped)
                <x-bladewind::avatar :image="$avatar" :label="$sender" size="tiny" :show_ring="false"/>
            @endunless
        </div>
    @endif

    <div @class(['flex flex-col max-w-[75%]', 'items-end' => $outgoing])>
        @if($sender !== '' && ! $outgoing && ! $grouped)
            <span class="text-xs font-medium text-gray-500 dark:text-dark-400 mb-1 ml-1">{{ $sender }}</span>
        @endif

        <div @class([
                'bw-chat-bubble rounded-2xl px-4 py-2 text-sm break-words',
                'bg-primary-600 text-white rounded-br-sm' => $outgoing,
                'bg-gray-100 dark:bg-dark-800 text-gray-800 dark:text-dark-200 rounded-bl-sm' => ! $outgoing,
             ])>
            {{ $slot }}

            @isset($attachments)
                <div @class(['flex flex-col gap-2', 'mt-2' => trim((string) $slot) !== ''])>
                    {{ $attachments }}
                </div>
            @endisset
        </div>

        @if($time !== '' || ($outgoing && $status !== ''))
            <div class="flex items-center gap-1 mt-1 mx-1 text-[11px] text-gray-400 dark:text-dark-500">
                @if($time !== '')
                    <span>{{ $time }}</span>
                @endif
                @if($outgoing && $status !== '' && isset($statusIcons[$status]))
                    <x-bladewind::icon
                        name="{{ $statusIcons[$status] }}"
                        class="size-3 {{ $status === 'read' ? 'text-primary-500' : '' }} {{ $status === 'failed' ? 'text-red-500' : '' }}"/>
                @endif
            </div>
        @endif
    </div>
</div>
