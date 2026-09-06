{{-- format-ignore-start --}}
@props([
    // used to open the dialog: showModal(name), and to derive the confirm/cancel
    // button classes the runtime helper below addresses them by
    'name' => defaultBladewindName('bw-confirm-'),
    'title' => __('bladewind::bladewind.are_you_sure'),
    // danger | warning | info | primary — picks the icon, its colour, and the
    // confirm button's colour. distinct from Modal's own "type", which this
    // maps onto.
    'tone' => config('bladewind.confirm_dialog.tone', 'danger'),
    'confirmLabel' => __('bladewind::bladewind.confirm'),
    'cancelLabel' => config('bladewind.modal.cancel_button_label', __('bladewind::bladewind.cancel')),
    // raw JS expression run when Confirm is clicked, e.g. onConfirm="deleteUser(1)".
    // wrap it in a function that may return a Promise: while that promise is
    // pending, both buttons disable and the confirm button shows a spinner.
    // a rejection re-enables the buttons and leaves the dialog open, so a
    // consumer can show its own error state before the user retries. leaving
    // this blank makes Confirm behave like a plain close.
    'onConfirm' => '',
    'closeAfterConfirm' => config('bladewind.confirm_dialog.close_after_confirm', true),
    'backdropCanClose' => config('bladewind.confirm_dialog.backdrop_can_close', false),
    'size' => config('bladewind.confirm_dialog.size', 'small'),
    'icon' => '',
    'nonce' => config('bladewind.script.nonce', null),
])
@php
    $name = parseBladewindName($name);
    $tone = in_array($tone, ['danger', 'warning', 'info', 'primary']) ? $tone : 'danger';
    $closeAfterConfirm = parseBladewindVariable($closeAfterConfirm);
    $backdropCanClose = parseBladewindVariable($backdropCanClose);

    $modalType = ['danger' => 'error', 'warning' => 'warning', 'info' => 'info', 'primary' => ''][$tone];
    $confirmColour = ['danger' => 'red', 'warning' => 'yellow', 'info' => 'blue', 'primary' => ''][$tone];

    $hasConfirmAction = trim((string) $onConfirm) !== '';
    $confirmClass = "bw-{$name}-confirm";
    $cancelClass = "bw-{$name}-cancel";

    $confirmAction = $hasConfirmAction
        ? "runBwConfirmDialogAction('{$name}', function(){ return ({$onConfirm}); }, ".($closeAfterConfirm ? 'true' : 'false').")"
        : "hideModal('{$name}')";
@endphp
{{-- format-ignore-end --}}
<x-bladewind::modal
    :name="$name"
    :title="$title"
    :type="$modalType"
    :icon="$icon"
    :size="$size"
    :backdrop-can-close="$backdropCanClose"
    :show-action-buttons="false"
    :nonce="$nonce"
>
    {{ $slot }}

    <div class="bw-confirm-dialog-actions flex justify-end space-x-2 pt-5">
        <x-bladewind::button
            type="secondary"
            outline="true"
            size="small"
            class="{{ $cancelClass }}"
            data-bw-modal-close="{{ $name }}"
        >{{ $cancelLabel }}</x-bladewind::button>

        <x-bladewind::button
            size="small"
            :color="$confirmColour ?: null"
            has-spinner="true"
            class="{{ $confirmClass }}"
            onclick="{!! $confirmAction !!}"
        >{{ $confirmLabel }}</x-bladewind::button>
    </div>
</x-bladewind::modal>
