(function () {
    // Always (re)define so published updates replace a stale in-memory class.
    window.BladewindKanban = class BladewindKanban {
        id;
        root;

        constructor(id, options = {}) {
            this.id = id;
            this.root = document.querySelector(`[data-bw-kanban="${id}"]`);
            if (!this.root || typeof Sortable === 'undefined') {
                return;
            }

            this.options = { animation: 150, onMove: null, ...options };
            this.groupName = `bw-kanban-${id}`;
            this.columns = [...this.root.querySelectorAll(':scope > [data-column]')];
            this.sortables = [];

            this.columns.forEach((column) => this.bindColumn(column));
            this.columns.forEach((column) => this.refreshColumn(column));

            this.root.addEventListener('keydown', (e) => this.onKeydown(e));
        }

        bindColumn = (column) => {
            const list = column.querySelector(':scope > [data-card-list]');
            if (!list) {
                return;
            }

            const sortable = new Sortable(list, {
                group: this.groupName,
                animation: this.options.animation,
                ghostClass: 'bw-kanban-ghost',
                chosenClass: 'bw-kanban-chosen',
                dragClass: 'bw-kanban-drag',
                onEnd: (evt) => this.onDrop(evt),
            });

            this.sortables.push(sortable);
        };

        onDrop = (evt) => {
            const card = evt.item;
            const fromColumn = evt.from.closest('[data-column]');
            const toColumn = evt.to.closest('[data-column]');

            this.refreshColumn(fromColumn);
            if (toColumn !== fromColumn) {
                this.refreshColumn(toColumn);
            }

            this.notifyMove(card, fromColumn, toColumn, evt.newIndex);
        };

        notifyMove = (card, fromColumn, toColumn, newIndex) => {
            if (typeof this.options.onMove !== 'function') {
                return;
            }

            this.options.onMove(
                card.dataset.id ?? null,
                fromColumn?.dataset.columnId ?? null,
                toColumn?.dataset.columnId ?? null,
                newIndex
            );
        };

        refreshColumn = (column) => {
            if (!column) {
                return;
            }

            const list = column.querySelector(':scope > [data-card-list]');
            const countEl = column.querySelector('[data-count]');
            const emptyEl = column.querySelector(':scope > [data-empty]');
            if (!list) {
                return;
            }

            const count = list.children.length;
            if (countEl) countEl.textContent = String(count);
            if (emptyEl) emptyEl.hidden = count > 0;
        };

        onKeydown = (e) => {
            if (!['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                return;
            }

            const card = e.target.closest('[data-card]');
            if (!card) {
                return;
            }

            e.preventDefault();

            if (e.key === 'ArrowUp') this.moveWithinColumn(card, -1);
            if (e.key === 'ArrowDown') this.moveWithinColumn(card, 1);
            if (e.key === 'ArrowLeft') this.moveToAdjacentColumn(card, -1);
            if (e.key === 'ArrowRight') this.moveToAdjacentColumn(card, 1);
        };

        moveWithinColumn = (card, direction) => {
            const list = card.parentElement;
            const sibling = direction < 0 ? card.previousElementSibling : card.nextElementSibling;
            if (!sibling) {
                return;
            }

            if (direction < 0) {
                list.insertBefore(card, sibling);
            } else {
                list.insertBefore(card, sibling.nextElementSibling);
            }

            card.focus();

            const column = list.closest('[data-column]');
            const newIndex = [...list.children].indexOf(card);
            this.refreshColumn(column);
            this.notifyMove(card, column, column, newIndex);
        };

        moveToAdjacentColumn = (card, direction) => {
            const fromColumn = card.closest('[data-column]');
            const columnIndex = this.columns.indexOf(fromColumn);
            const toColumn = this.columns[columnIndex + direction];
            if (!toColumn) {
                return;
            }

            const toList = toColumn.querySelector(':scope > [data-card-list]');
            if (!toList) {
                return;
            }

            const fromIndex = [...fromColumn.querySelector(':scope > [data-card-list]').children].indexOf(card);
            const targetChildren = [...toList.children];
            const newIndex = Math.min(fromIndex, targetChildren.length);
            const reference = targetChildren[newIndex] ?? null;

            toList.insertBefore(card, reference);
            card.focus();

            this.refreshColumn(fromColumn);
            this.refreshColumn(toColumn);
            this.notifyMove(card, fromColumn, toColumn, newIndex);
        };
    };
})();
