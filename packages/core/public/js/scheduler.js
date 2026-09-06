(function () {
    // Always (re)define so published updates replace a stale in-memory class.
    window.BladewindScheduler = class BladewindScheduler {
        id;
        root;

        constructor(id, options = {}) {
            this.id = id;
            this.root = document.querySelector(`[data-bw-scheduler="${id}"]`);
            if (!this.root) {
                return;
            }

            this.options = {
                startHour: 8,
                endHour: 18,
                slotMinutes: 30,
                bodyHeightPx: 0,
                onSlotClick: null,
                onEventClick: null,
                ...options,
            };

            if (typeof this.options.onEventClick === 'function') {
                this.root.querySelectorAll('[data-event]').forEach((el) => {
                    el.addEventListener('click', (e) => {
                        if (el.tagName === 'A' && el.getAttribute('href')) {
                            return;
                        }
                        e.stopPropagation();
                        this.options.onEventClick(el.dataset.eventId);
                    });
                });
            }

            if (typeof this.options.onSlotClick === 'function') {
                this.root.querySelectorAll('[data-column]').forEach((column) => {
                    column.addEventListener('click', (e) => {
                        if (e.target.closest('[data-event]')) {
                            return;
                        }

                        const rect = column.getBoundingClientRect();
                        const offsetY = Math.max(0, Math.min(this.options.bodyHeightPx, e.clientY - rect.top));
                        const totalMinutes = (this.options.endHour - this.options.startHour) * 60;
                        const minutesFromStart = (offsetY / this.options.bodyHeightPx) * totalMinutes;
                        const snapped = Math.floor(minutesFromStart / this.options.slotMinutes) * this.options.slotMinutes;
                        const hour = this.options.startHour + Math.floor(snapped / 60);
                        const minute = snapped % 60;
                        const time = `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`;

                        this.options.onSlotClick(column.dataset.columnId, time);
                    });
                });
            }
        }
    };
})();
