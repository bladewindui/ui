(function () {
    // Always (re)define so published updates replace a stale in-memory class.
    window.BladewindTransferList = class BladewindTransferList {
        name;
        root;
        lists = {};

        constructor(name) {
            this.name = name;
            this.root = domEl(`.${name}`);
            if (! this.root) {
                return;
            }
            this.lists = {
                available: this.root.querySelector('[data-list="available"]'),
                selected: this.root.querySelector('[data-list="selected"]'),
            };
            this.activate();
            this.refresh('available');
            this.refresh('selected');
        }

        otherKey = (listKey) => (listKey === 'available' ? 'selected' : 'available');

        items = (listKey) => Array.from(this.lists[listKey].querySelectorAll(':scope > li'));

        visibleItems = (listKey) => this.items(listKey).filter((li) => ! li.hidden);

        checkedItems = (listKey) => this.items(listKey).filter((li) => li.querySelector('[data-item-checkbox]')?.checked);

        activate = () => {
            this.root.querySelectorAll('[data-search]').forEach((input) => {
                input.addEventListener('input', () => this.filter(input.dataset.search, input.value));
            });

            this.root.querySelectorAll('[data-select-all]').forEach((checkbox) => {
                checkbox.addEventListener('change', () => {
                    const listKey = checkbox.dataset.selectAll;
                    this.visibleItems(listKey).forEach((li) => {
                        const box = li.querySelector('[data-item-checkbox]');
                        if (box) box.checked = checkbox.checked;
                    });
                });
            });

            this.root.querySelectorAll('[data-list] [data-item-checkbox]').forEach((box) => this.bindItemCheckbox(box));

            this.root.querySelector('[data-action="move-right"]')?.addEventListener('click', () => this.moveChecked('available', 'selected'));
            this.root.querySelector('[data-action="move-left"]')?.addEventListener('click', () => this.moveChecked('selected', 'available'));
            this.root.querySelector('[data-action="move-all-right"]')?.addEventListener('click', () => this.moveAll('available', 'selected'));
            this.root.querySelector('[data-action="move-all-left"]')?.addEventListener('click', () => this.moveAll('selected', 'available'));

            Object.entries(this.lists).forEach(([listKey, list]) => {
                list.addEventListener('dblclick', (event) => {
                    const li = event.target.closest('li');
                    if (li) this.moveItems(listKey, this.otherKey(listKey), [li]);
                });
            });
        };

        bindItemCheckbox = (box) => {
            box.addEventListener('change', () => {
                const listKey = box.closest('[data-list]')?.dataset.list;
                if (listKey) this.syncSelectAll(listKey);
            });
        };

        syncSelectAll = (listKey) => {
            const selectAll = this.root.querySelector(`[data-select-all="${listKey}"]`);
            if (! selectAll) {
                return;
            }
            const visible = this.visibleItems(listKey);
            const checkedCount = visible.filter((li) => li.querySelector('[data-item-checkbox]')?.checked).length;
            selectAll.checked = visible.length > 0 && checkedCount === visible.length;
            selectAll.indeterminate = checkedCount > 0 && checkedCount < visible.length;
        };

        moveChecked = (fromKey, toKey) => this.moveItems(fromKey, toKey, this.checkedItems(fromKey));

        moveAll = (fromKey, toKey) => this.moveItems(fromKey, toKey, this.visibleItems(fromKey));

        moveItems = (fromKey, toKey, items) => {
            if (items.length === 0) {
                return;
            }

            const toList = this.lists[toKey];

            items.forEach((li) => {
                const checkbox = li.querySelector('[data-item-checkbox]');
                if (checkbox) checkbox.checked = false;

                const hidden = this.root.querySelector(`[data-hidden-value="${CSS.escape(li.dataset.value)}"]`);
                if (hidden) hidden.disabled = toKey !== 'selected';

                toList.appendChild(li);
            });

            this.filter(fromKey, this.root.querySelector(`[data-search="${fromKey}"]`)?.value || '');
            this.filter(toKey, this.root.querySelector(`[data-search="${toKey}"]`)?.value || '');
            this.syncSelectAll(fromKey);
            this.syncSelectAll(toKey);
        };

        filter = (listKey, query) => {
            const needle = query.trim().toLowerCase();
            this.items(listKey).forEach((li) => {
                li.hidden = needle !== '' && ! li.dataset.label.includes(needle);
            });
            this.syncSelectAll(listKey);
            this.refresh(listKey);
        };

        refresh = (listKey) => {
            const total = this.items(listKey).length;
            const visible = this.visibleItems(listKey).length;

            const count = this.root.querySelector(`[data-count="${listKey}"]`);
            if (count) count.textContent = total === visible ? `${total}` : `${visible} / ${total}`;

            const empty = this.root.querySelector(`[data-empty="${listKey}"]`);
            if (empty) empty.classList.toggle('hidden', visible > 0);
        };
    };
})();
