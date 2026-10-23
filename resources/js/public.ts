// path: resources/js/public.ts

import '../css/public.css';

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
        }

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

document.documentElement.dataset.publicApp = 'ready';

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        initialiseInquiryForms,
        { once: true },
    );
} else {
    initialiseInquiryForms();
}