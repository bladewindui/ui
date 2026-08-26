(function () {
    if (window.BladewindTooltip) return;

    /**
     * Tooltips that are not clipped by whatever the trigger happens to sit in.
     *
     * The bubble used to be a ::after on the trigger itself. A pseudo-element
     * cannot leave its own element, so any ancestor establishing a scroll
     * container cut it off — most often the overflow-x-auto wrapper a wide table
     * needs, which is precisely where the table's action-icon tooltips live.
     * (overflow-x: auto silently computes overflow-y to auto, so a horizontal
     * scroll container clips vertically too.) See #591.
     *
     * One bubble is appended to <body> and reused. It is positioned fixed against
     * the trigger's rect, so no ancestor can contain it.
     *
     * The contract is unchanged: any element carrying data-tooltip gets one, with
     * data-position, data-size and data-inverted read from the trigger exactly as
     * the old stylesheet read them.
     */
    class BladewindTooltip {
        bubble = null;
        trigger = null;
        gap = 8;

        constructor() {
            // tells the stylesheet to stop drawing the pseudo-element version
            document.documentElement.classList.add('bw-tooltip-js');

            document.addEventListener('mouseover', this.onEnter);
            document.addEventListener('mouseout', this.onLeave);
            document.addEventListener('focusin', this.onEnter);
            document.addEventListener('focusout', this.onLeave);
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') this.hide();
            });

            // capture phase, so scrolling any ancestor is caught, not just the window
            window.addEventListener('scroll', this.reposition, true);
            window.addEventListener('resize', this.reposition);
        }

        ensureBubble = () => {
            if (this.bubble && this.bubble.isConnected) return this.bubble;

            this.bubble = document.createElement('div');
            this.bubble.className = 'bw-tooltip-bubble';
            this.bubble.setAttribute('role', 'tooltip');
            this.bubble.setAttribute('data-open', '0');
            document.body.appendChild(this.bubble);

            return this.bubble;
        };

        triggerFrom = (target) => {
            if (! target || typeof target.closest !== 'function') return null;

            return target.closest('[data-tooltip]');
        };

        onEnter = (e) => {
            const trigger = this.triggerFrom(e.target);
            if (! trigger || trigger === this.trigger) return;

            const text = trigger.getAttribute('data-tooltip');
            if (! text) return;

            this.trigger = trigger;
            this.show(trigger, text);
        };

        onLeave = (e) => {
            const trigger = this.triggerFrom(e.target);
            if (! trigger || trigger !== this.trigger) return;

            // moving onto a child of the same trigger is not leaving it
            if (e.relatedTarget && trigger.contains(e.relatedTarget)) return;

            this.hide();
        };

        show = (trigger, text) => {
            const bubble = this.ensureBubble();

            bubble.textContent = text;
            bubble.setAttribute('data-size', trigger.getAttribute('data-size') || 'small');
            bubble.setAttribute('data-inverted', trigger.hasAttribute('data-inverted') ? '1' : '0');

            // the old attribute is "top center", "left center" and so on
            const position = (trigger.getAttribute('data-position') || 'top center').split(' ')[0];
            bubble.setAttribute('data-side', ['top', 'bottom', 'left', 'right'].includes(position) ? position : 'top');

            bubble.setAttribute('data-open', '1');
            this.reposition();
        };

        hide = () => {
            this.trigger = null;
            if (this.bubble) this.bubble.setAttribute('data-open', '0');
        };

        reposition = () => {
            if (! this.trigger || ! this.bubble) return;
            if (this.bubble.getAttribute('data-open') !== '1') return;

            const rect = this.trigger.getBoundingClientRect();

            // a trigger scrolled out of sight should take its tooltip with it
            if (rect.bottom < 0 || rect.top > window.innerHeight) {
                this.hide();
                return;
            }

            const width = this.bubble.offsetWidth;
            const height = this.bubble.offsetHeight;
            let side = this.bubble.getAttribute('data-side');
            let top, left;

            // flip rather than run off the top or bottom of the viewport
            if (side === 'top' && rect.top - height - this.gap < 0) side = 'bottom';
            else if (side === 'bottom' && rect.bottom + height + this.gap > window.innerHeight) side = 'top';

            if (side === 'top') {
                top = rect.top - height - this.gap;
                left = rect.left + (rect.width / 2) - (width / 2);
            } else if (side === 'bottom') {
                top = rect.bottom + this.gap;
                left = rect.left + (rect.width / 2) - (width / 2);
            } else if (side === 'left') {
                top = rect.top + (rect.height / 2) - (height / 2);
                left = rect.left - width - this.gap;
            } else {
                top = rect.top + (rect.height / 2) - (height / 2);
                left = rect.right + this.gap;
            }

            this.bubble.setAttribute('data-side', side);
            this.bubble.style.top = `${Math.round(top)}px`;
            this.bubble.style.left = `${Math.round(Math.max(4, Math.min(left, window.innerWidth - width - 4)))}px`;
        };
    }

    window.BladewindTooltip = BladewindTooltip;

    const start = () => { window.bladewindTooltip = new BladewindTooltip(); };

    document.readyState === 'loading'
        ? document.addEventListener('DOMContentLoaded', start)
        : start();
})();
