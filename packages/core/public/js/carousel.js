(function () {
    // Always (re)define so published updates replace a stale in-memory class.
    window.BladewindCarousel = class BladewindCarousel {
        id;
        root;
        index = 0;
        timer = null;

        constructor(id, options = {}) {
            this.id = id;
            this.root = document.querySelector(`[data-bw-carousel="${id}"]`);
            if (!this.root) {
                return;
            }

            this.options = {
                loop: true,
                swipe: true,
                autoplay: false,
                interval: 5000,
                indicators: true,
                ...options,
            };

            this.track = this.root.querySelector('[data-track]');
            this.slides = [...this.root.querySelectorAll(':scope > [data-track] > .bw-carousel-slide')];
            this.indicatorsEl = this.root.querySelector('[data-indicators]');
            this.prevBtn = this.root.querySelector('[data-prev]');
            this.nextBtn = this.root.querySelector('[data-next]');

            this.reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            if (!this.slides.length) {
                return;
            }

            if (!this.reducedMotion) {
                this.track.style.transition = 'transform 300ms ease-in-out';
            }

            this.buildIndicators();
            this.goTo(0, false);

            this.prevBtn?.addEventListener('click', () => this.prev());
            this.nextBtn?.addEventListener('click', () => this.next());

            this.root.setAttribute('tabindex', '0');
            this.root.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowLeft') this.prev();
                if (e.key === 'ArrowRight') this.next();
            });

            if (this.options.swipe) {
                this.bindSwipe();
            }

            if (this.options.autoplay && !this.reducedMotion) {
                this.root.addEventListener('mouseenter', () => this.stopAutoplay());
                this.root.addEventListener('mouseleave', () => this.startAutoplay());
                this.startAutoplay();
            }
        }

        buildIndicators = () => {
            if (!this.options.indicators || !this.indicatorsEl) {
                return;
            }

            this.slides.forEach((_, i) => {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.setAttribute('aria-label', `Go to slide ${i + 1}`);
                dot.dataset.index = String(i);
                dot.className = 'size-2 rounded-full bg-white/50 hover:bg-white/80';
                dot.addEventListener('click', () => this.goTo(i));
                this.indicatorsEl.appendChild(dot);
            });
        };

        updateIndicators = () => {
            if (!this.indicatorsEl) {
                return;
            }

            [...this.indicatorsEl.children].forEach((dot, i) => {
                dot.classList.toggle('bg-white', i === this.index);
                dot.classList.toggle('bg-white/50', i !== this.index);
            });
        };

        updateArrows = () => {
            if (this.options.loop) {
                return;
            }

            if (this.prevBtn) this.prevBtn.disabled = this.index === 0;
            if (this.nextBtn) this.nextBtn.disabled = this.index === this.slides.length - 1;
        };

        goTo = (index, animate = true) => {
            const count = this.slides.length;

            if (this.options.loop) {
                this.index = (index + count) % count;
            } else {
                this.index = Math.max(0, Math.min(index, count - 1));
            }

            const previousTransition = this.track.style.transition;
            if (!animate) this.track.style.transition = 'none';

            this.track.style.transform = `translateX(-${this.index * 100}%)`;

            if (!animate) {
                // force layout before restoring the transition, so the jump
                // to the initial slide is never itself animated
                this.track.offsetHeight;
                this.track.style.transition = previousTransition;
            }

            this.updateIndicators();
            this.updateArrows();
        };

        next = () => this.goTo(this.index + 1);

        prev = () => this.goTo(this.index - 1);

        startAutoplay = () => {
            this.stopAutoplay();
            this.timer = setInterval(() => this.next(), this.options.interval);
        };

        stopAutoplay = () => {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
        };

        bindSwipe = () => {
            let startX = 0;
            let currentX = 0;
            let dragging = false;

            const onDown = (e) => {
                dragging = true;
                startX = currentX = e.clientX ?? e.touches?.[0]?.clientX ?? 0;
                this.stopAutoplay();
            };

            const onMove = (e) => {
                if (!dragging) return;
                currentX = e.clientX ?? e.touches?.[0]?.clientX ?? 0;
            };

            const onUp = () => {
                if (!dragging) return;
                dragging = false;

                const delta = currentX - startX;
                const threshold = 50;

                if (delta > threshold) {
                    this.prev();
                } else if (delta < -threshold) {
                    this.next();
                }

                if (this.options.autoplay && !this.reducedMotion) {
                    this.startAutoplay();
                }
            };

            this.track.addEventListener('pointerdown', onDown);
            this.track.addEventListener('pointermove', onMove);
            this.track.addEventListener('pointerup', onUp);
            this.track.addEventListener('pointercancel', onUp);
            this.track.addEventListener('pointerleave', onUp);
        };
    };
})();
