(function () {
    // Always (re)define so published updates replace a stale in-memory class.
    window.BladewindPasswordMeter = class BladewindPasswordMeter {
        name;
        root;
        field;
        bars;
        label;

        // bar colour and the dataset key of the label to show at each score,
        // 0 (empty) through 4 (strongest)
        levels = [
            {colour: null, labelKey: null},
            {colour: 'bg-red-500', labelKey: 'labelWeak'},
            {colour: 'bg-orange-500', labelKey: 'labelFair'},
            {colour: 'bg-yellow-500', labelKey: 'labelGood'},
            {colour: 'bg-green-600', labelKey: 'labelStrong'},
        ];

        constructor(name) {
            this.name = name;
            this.root = domEl(`.${name}`);
            if (! this.root) {
                return;
            }
            this.field = this.findField();
            this.bars = domEls(`.${name} [data-bar]`);
            this.label = domEl(`.${name} [data-label]`);
            this.activate();
        }

        findField = () => {
            const target = this.root.getAttribute('data-for');
            if (! target) {
                return null;
            }
            return document.querySelector(`[name="${target}"]`) || document.getElementById(target);
        };

        activate = () => {
            if (! this.field) {
                return;
            }
            this.field.addEventListener('input', () => this.update(this.field.value));
            this.update(this.field.value);
        };

        // A simple rules-based score from 0 (empty) to 4 (strongest): up to
        // two points for length, up to two for character variety.
        score = (value) => {
            if (! value) {
                return 0;
            }

            const minLength = parseInt(this.root.dataset.minLength, 10) || 8;
            const strongLength = parseInt(this.root.dataset.strongLength, 10) || 12;

            let score = 0;
            if (value.length >= minLength) score++;
            if (value.length >= strongLength) score++;

            let variety = 0;
            if (/[a-z]/.test(value)) variety++;
            if (/[A-Z]/.test(value)) variety++;
            if (/[0-9]/.test(value)) variety++;
            if (/[^a-zA-Z0-9]/.test(value)) variety++;
            score += Math.min(variety, 2);

            return Math.min(score, 4);
        };

        update = (value) => {
            const score = Math.min(this.score(value), 4);
            const level = this.levels[score];

            this.bars.forEach((bar, i) => {
                bar.classList.remove('bg-gray-200', 'dark:bg-dark-600', 'bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-600');
                if (i < score && level.colour) {
                    bar.classList.add(level.colour);
                } else {
                    bar.classList.add('bg-gray-200', 'dark:bg-dark-600');
                }
            });

            if (this.label) {
                this.label.textContent = (value === '' || ! level.labelKey) ? '' : (this.root.dataset[level.labelKey] || '');
            }
        };
    };
})();
