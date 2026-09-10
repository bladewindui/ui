[![License](https://img.shields.io/github/license/mkocansey/bladewind)](https://github.com/mkocansey/bladewind/blob/main/LICENSE) [![Packagist Version](https://img.shields.io/packagist/v/bladewindui/sortable)](https://packagist.org/packages/bladewindui/sortable)

<img src="https://bladewindui.com/assets/images/bw-logo.png" height="30" alt="BladewindUI" />

# Sortable

BladewindUI — Drag and drop sortable list component, powered by [SortableJS](https://sortablejs.github.io/Sortable/).

## Installation

```bash
composer require bladewindui/sortable
```

Or install the full library:

```bash
composer require bladewindui/ui
```

## Usage

```blade
<x-bladewind::sortable>
    <x-bladewind::sortable.item>Tomatoes</x-bladewind::sortable.item>
    <x-bladewind::sortable.item>Onions</x-bladewind::sortable.item>
    <x-bladewind::sortable.item>Garlic</x-bladewind::sortable.item>
</x-bladewind::sortable>
```

### Attributes

| Attribute    | Type           | Default    | Description                                                                 |
|--------------|----------------|------------|-----------------------------------------------------------------------------|
| `type`       | `simple\|shared` | `simple`   | `shared` lists can exchange items with other lists in the same `group`.     |
| `group`      | string         | `null`     | Group name used by `shared` lists to know which lists can swap items.       |
| `clone`      | bool           | `false`    | Leave a copy behind when dragging an item into another (shared) list.       |
| `sortable`   | bool           | `true`     | Enable or disable sorting of items within the list.                         |
| `hasHandle`  | bool           | `false`    | Drag items by a dedicated handle instead of the whole item surface.         |
| `handleIcon` | string         | `bars-3`   | Heroicon name used for the drag handle when `hasHandle` is `true`.          |
| `filter`     | string         | `null`     | Space/comma separated class names whose items cannot be dragged.            |
| `multidrag`  | bool           | `false`    | Select (ctrl/cmd + click) and drag multiple items at once.                  |
| `swap`       | bool           | `false`    | Swap items on drop instead of shifting them. Not combinable with `multidrag`. |
| `animation`  | int            | `150`      | Reorder animation duration in milliseconds.                                 |
| `inputName`  | string         | `null`     | Renders a hidden `<input>` with this name, kept in sync with the order as a JSON array. Submit it with your form. |
| `onSorted`   | string         | `null`     | Name of a JS function called after each reorder as `(order, event)`. Useful for saving via AJAX. |

The `sortable.item` component accepts a **`value`** attribute (rendered as `data-id`) — this is the identifier reported in the order array, e.g. your model id.

### Saving the order

**Submitting with a form.** Give the list an `inputName` and each item a `value`. A hidden input is
kept in sync with the order and submitted like any other field:

```blade
<form method="post" action="/tasks/reorder">
    @csrf
    <x-bladewind::sortable input-name="task_order" has-handle="true">
        @foreach($tasks as $task)
            <x-bladewind::sortable.item :value="$task->id">{{ $task->title }}</x-bladewind::sortable.item>
        @endforeach
    </x-bladewind::sortable>
    <x-bladewind::button can_submit="true" label="Save order"/>
</form>
```

On the server the field arrives as a JSON array of ids in their new order:

```php
$order = json_decode($request->input('task_order')); // ["30", "10", "20"]

foreach ($order as $position => $id) {
    Task::where('id', $id)->update(['position' => $position]);
}
```

**Saving with AJAX (no form submit).** Point `onSorted` at a JS function that receives the order array:

```blade
<x-bladewind::sortable :on-sorted="'saveOrder'">
    @foreach($tasks as $task)
        <x-bladewind::sortable.item :value="$task->id">{{ $task->title }}</x-bladewind::sortable.item>
    @endforeach
</x-bladewind::sortable>

<script>
    function saveOrder(order, event) {
        fetch('/tasks/reorder', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ task_order: order }),
        });
    }
</script>
```

**Reading the order yourself.** Each list is exposed as a JS variable named after its `name`, so you can
call SortableJS methods directly — e.g. `task_list.toArray()` returns the current order at any time.

### Sharing items between two lists

```blade
<x-bladewind::sortable type="shared" group="fruits">
    <x-bladewind::sortable.item>Apple</x-bladewind::sortable.item>
    <x-bladewind::sortable.item>Mango</x-bladewind::sortable.item>
</x-bladewind::sortable>

<x-bladewind::sortable type="shared" group="fruits">
    <x-bladewind::sortable.item>Banana</x-bladewind::sortable.item>
</x-bladewind::sortable>
```

## Documentation

Full documentation, live demos, and all available attributes are at **[bladewindui.com](https://bladewindui.com)**.

## Livewire

The component guards against a Livewire re-render creating a duplicate `Sortable` instance. When `input-name` is set, the hidden order input now dispatches a native `change` event on every reorder, so `wire:model` observes it. The drag order itself is runtime state kept in the DOM, so wrap the list in `wire:ignore` if it sits inside a component that re-renders for reasons unrelated to the list — otherwise an in-progress reorder can be reset.

## License

MIT — see the [LICENSE](https://github.com/mkocansey/bladewind/blob/main/LICENSE) file.
