import {
    expect,
    test,
} from '@playwright/test';

test('login -> save draft -> publish -> public visibility', async ({
    page,
}) => {
    const email =
        process.env.E2E_USER_EMAIL
        ?? 'e2e@cim.test';

    const password =
        process.env.E2E_USER_PASSWORD
        ?? 'CimE2E-Local-Only-2026!';

    const slug = `e2e-product-${Date.now()}`;

    await page.goto('/admin/login');

    await page
        .getByLabel('Email')
        .fill(email);

    await page
        .getByLabel('Password')
        .fill(password);

    await page
        .getByRole('button', {
            name: 'Masuk',
        })
        .click();

    await expect(page).toHaveURL(
        /\/admin$/,
    );

    await page.goto(
        '/admin/products/create',
    );

    await page
        .getByLabel('Name')
        .fill('E2E Product Smoke');

    await page
        .getByLabel('Requested slug')
        .fill(slug);

    await page
        .getByLabel('Primary category')
        .selectOption({
            label: 'E2E Category',
        });

    await page
        .getByLabel('Brand')
        .selectOption({
            label: 'E2E Brand',
        });

    await page
        .getByLabel('Type')
        .selectOption({
            label: 'E2E Type',
        });

    await page
        .getByLabel('Summary')
        .fill(
            'Product created by Playwright smoke test.',
        );

    await page
        .getByRole('button', {
            name: 'Save draft',
        })
        .click();

    await expect(page).toHaveURL(
        /\/admin\/products\/\d+\/edit$/,
    );

    await expect(
        page.getByText(
            'Draft product berhasil dibuat.',
        ),
    ).toBeVisible();

    await page
        .getByRole('button', {
            name: 'Publish',
            exact: true,
        })
        .click();

    await expect(
        page.getByText(
            'Product berhasil dipublish.',
        ),
    ).toBeVisible();

    await page.goto(`/products/${slug}`);

    await expect(
        page.getByRole('heading', {
            name: 'E2E Product Smoke',
            level: 1,
        }),
    ).toBeVisible();

    await expect(
        page.getByText(
            'Product created by Playwright smoke test.',
        ),
    ).toBeVisible();
});