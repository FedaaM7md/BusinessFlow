/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */
import Alpine from '@alpinejs/csp';

window.Alpine = Alpine;

/*
 * Progressive-enhancement handlers. Forms still submit correctly without
 * JavaScript; these only add confirmation prompts and print/back shortcuts.
 * Using delegated listeners keeps the Content-Security-Policy free of
 * 'unsafe-inline' for scripts.
 */
document.addEventListener('submit', (event) => {
    const message = event.target.getAttribute?.('data-confirm');
    if (message && !window.confirm(message)) {
        event.preventDefault();
    }
});

document.addEventListener('click', (event) => {
    const submitTrigger = event.target.closest('[data-submit-form]');
    if (submitTrigger) {
        const message = submitTrigger.getAttribute('data-confirm');
        if (!message || window.confirm(message)) {
            const form = document.getElementById(submitTrigger.getAttribute('data-submit-form'));
            if (form) {
                form.submit();
            }
        }

        return;
    }

    if (event.target.closest('[data-print]')) {
        window.print();

        return;
    }

    if (event.target.closest('[data-back]')) {
        history.back();
    }
});

/*
 * Application shell: the mobile navigation drawer. The closed state is enforced
 * by CSS before Alpine runs, so the drawer never flashes open. Opening moves
 * focus into the drawer, Tab is trapped inside it, and Esc, an overlay tap or a
 * link click closes it and returns focus to the trigger.
 */
Alpine.data('appShell', () => ({
    open: false,
    lastFocused: null,
    show() {
        this.lastFocused = document.activeElement;
        this.open = true;
    },
    scheduleDrawerFocus() {
        window.setTimeout(() => {
            if (!this.open) {
                return;
            }
            const drawer = document.getElementById('app-sidebar');
            const close = drawer ? drawer.querySelector('button') : null;
            if (close) {
                close.focus();
            }
        }, 80);
    },
    hide() {
        this.open = false;
        if (this.lastFocused && typeof this.lastFocused.focus === 'function') {
            this.lastFocused.focus();
        }
    },
    focusable() {
        const drawer = document.getElementById('app-sidebar');
        if (!drawer) {
            return [];
        }
        return Array.from(drawer.querySelectorAll('a[href], button:not([disabled])'))
            .filter((el) => el.offsetWidth > 0 && el.offsetHeight > 0);
    },
    focusFirst() {
        const items = this.focusable();
        if (items.length) {
            items[0].focus();
        }
    },
    trap(event) {
        if (!this.open) {
            return;
        }

        const items = this.focusable();
        if (!items.length) {
            return;
        }

        const first = items[0];
        const last = items[items.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    },
}));

/*
 * Line-item editor used by quotation and invoice forms. The component is
 * registered with Alpine.data so the CSP-friendly build can run it without
 * evaluating template literals or arbitrary globals. Row names are precomputed
 * and reindexed when rows are added or removed.
 */
Alpine.data('lineItems', () => ({
    lines: [],
    products: [],
    defaultTax: '0',
    init() {
        try {
            this.products = JSON.parse(this.$el.dataset.products || '[]');
        } catch {
            this.products = [];
        }

        let source = [];
        try {
            source = JSON.parse(this.$el.dataset.lines || '[]');
        } catch {
            source = [];
        }

        this.defaultTax = this.$el.dataset.tax || '0';

        if (!Array.isArray(source) || source.length === 0) {
            source = [{}];
        }

        this.lines = source.map((line) => this.normalise(line));
        this.reindex();
    },
    normalise(line) {
        return {
            key: `${Date.now()}-${Math.random()}`,
            field: '',
            product_id: line.product_id ?? '',
            description: line.description ?? '',
            quantity: line.quantity ?? '1',
            unit_price: line.unit_price ?? '0.00',
            tax_rate: line.tax_rate !== undefined && line.tax_rate !== null && line.tax_rate !== '' ? line.tax_rate : this.defaultTax,
            formatted_total: '0.00',
        };
    },
    reindex() {
        this.lines.forEach((line, index) => {
            line.field = `lines[${index}]`;
            this.recalc(line);
        });
    },
    recalc(line) {
        line.formatted_total = this.formatAmount(this.lineTotal(line));
    },
    addLine() {
        this.lines.push(this.normalise({}));
        this.reindex();
    },
    removeLine(line) {
        if (this.lines.length === 1) {
            this.lines[0] = this.normalise({});
        } else {
            const index = this.lines.indexOf(line);
            if (index !== -1) {
                this.lines.splice(index, 1);
            }
        }
        this.reindex();
    },
    pickProduct(line) {
        const product = this.products.find((item) => String(item.id) === String(line.product_id));
        if (!product) {
            return;
        }
        line.description = product.name;
        line.unit_price = product.price;
        line.tax_rate = product.tax_rate;
        this.recalc(line);
    },
    lineTotal(line) {
        return (parseFloat(line.quantity) || 0) * (parseFloat(line.unit_price) || 0);
    },
    formatAmount(value) {
        return new Intl.NumberFormat('en-EG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value || 0);
    },
}));

Alpine.start();
