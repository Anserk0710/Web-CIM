// path: resources/js/public.ts

import '../css/public.css';
import { initialiseNavigation } from './public/navigation';

function initialiseInquiryForms(): void {
    const forms = document.querySelectorAll<HTMLFormElement>(
        '[data-inquiry-form]',
    );

    forms.forEach((form) => {
        const typeSelect =
            form.querySelector<HTMLSelectElement>(
                '[data-inquiry-type]',
            );

        const productField =
            form.querySelector<HTMLElement>(
                '[data-product-field]',
            );

        const catalogField =
            form.querySelector<HTMLElement>(
                '[data-catalog-field]',
            );

        const productSelect =
            form.querySelector<HTMLSelectElement>(
                '[data-product-select]',
            );

        const catalogSelect =
            form.querySelector<HTMLSelectElement>(
                '[data-ecatalog-select]',
            );

        const catalogProductId =
            form.querySelector<HTMLInputElement>(
                '[data-ecatalog-product-id]',
            );

        if (
            !typeSelect
            || !productField
            || !catalogField
            || !productSelect
            || !catalogSelect
            || !catalogProductId
        ) {
            return;
        }

        const updateFields = (): void => {
            const type = typeSelect.value;

            const isProduct = type === 'PRODUCT';
            const isCatalog = type === 'ECATALOG';

            productField.hidden = !isProduct;
            catalogField.hidden = !isCatalog;

            productSelect.disabled = !isProduct;
            productSelect.required = isProduct;

            catalogSelect.disabled = !isCatalog;
            catalogSelect.required = isCatalog;

            catalogProductId.disabled = !isCatalog;

            if (isCatalog) {
                const selectedOption =
                    catalogSelect.selectedOptions.item(0);

                catalogProductId.value =
                    selectedOption?.dataset.productId ?? '';
            } else {
                catalogProductId.value = '';
            }
        };

        typeSelect.addEventListener(
            'change',
            updateFields,
        );

        catalogSelect.addEventListener(
            'change',
            updateFields,
        );

        updateFields();
    });
}

function initialiseScrollProgress(): void {
    const bar = document.querySelector<HTMLElement>('[data-scroll-progress]');
    if (!bar) return;

    const onScroll = (): void => {
        const total = document.documentElement.scrollHeight - window.innerHeight;
        const progress = total > 0 ? Math.min(1, Math.max(0, window.scrollY / total)) : 0;
        bar.style.transform = `scaleX(${progress})`;
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
}

document.documentElement.dataset.publicApp = 'ready';

function boot(): void {
    initialiseNavigation();
    initialiseScrollProgress();
    initialiseInquiryForms();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot, { once: true });
} else {
    boot();
}