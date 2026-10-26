export function initialiseNavigation(): void {
    const header = document.querySelector<HTMLElement>('[data-public-navigation]');
    const toggle = document.querySelector<HTMLButtonElement>('#public-menu-toggle');
    const navigation = document.querySelector<HTMLElement>('#public-mobile-navigation');

    if (!header || !toggle || !navigation) return;

    const desktop = window.matchMedia('(min-width: 1024px)');
    const hover = window.matchMedia('(hover: hover) and (pointer: fine)');
    const groups = [...header.querySelectorAll<HTMLDetailsElement>('details')];
    const background = [...document.querySelectorAll<HTMLElement>('main, .site-footer')];
    const initialInert = background.map((element) => element.inert);
    let menuOpen = false;

    const closeGroups = (except?: HTMLDetailsElement): void => {
        groups.forEach((group) => {
            if (group !== except) group.open = false;
        });
    };

    const setMenuOpen = (open: boolean, restoreFocus = false): void => {
        menuOpen = open && !desktop.matches;
        header.dataset.menuOpen = String(menuOpen);
        toggle.setAttribute('aria-expanded', String(menuOpen));
        toggle.setAttribute('aria-label', menuOpen ? 'Tutup menu navigasi' : 'Buka menu navigasi');
        document.body.classList.toggle('public-menu-open', menuOpen);
        navigation.inert = !desktop.matches && !menuOpen;
        background.forEach((element, index) => {
            element.inert = menuOpen || initialInert[index];
        });
        if (!menuOpen) closeGroups();
        if (restoreFocus) toggle.focus();
    };

    toggle.addEventListener('click', () => setMenuOpen(!menuOpen));

    groups.forEach((group) => {
        group.addEventListener('toggle', () => {
            if (group.open) closeGroups(group);
        });
        group.addEventListener('pointerenter', () => {
            if (desktop.matches && hover.matches) group.open = true;
        });
        group.addEventListener('pointerleave', () => {
            if (desktop.matches && !group.contains(document.activeElement)) group.open = false;
        });
        group.addEventListener('focusout', (event) => {
            if (desktop.matches && !group.contains(event.relatedTarget as Node | null)) group.open = false;
        });
    });

    document.addEventListener('click', (event) => {
        if (event.target instanceof Node && !header.contains(event.target)) closeGroups();
    });

    header.addEventListener('click', (event) => {
        if (event.target instanceof Element && event.target.closest('a')) {
            setMenuOpen(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            if (menuOpen) {
                setMenuOpen(false, true);
            } else {
                const activeGroup = groups.find((group) => group.open && group.contains(document.activeElement));
                closeGroups();
                activeGroup?.querySelector('summary')?.focus();
            }
        }

        if (event.key !== 'Tab' || !menuOpen) return;

        const focusable = [...header.querySelectorAll<HTMLElement>('a, button, summary')]
            .filter((element) => element.getClientRects().length > 0);
        const first = focusable[0];
        const last = focusable.at(-1);
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last?.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first?.focus();
        }
    });

    desktop.addEventListener('change', () => {
        const focusWasInNavigation = navigation.contains(document.activeElement);
        setMenuOpen(false, !desktop.matches && focusWasInNavigation);
    });

    // Enhance only after handlers exist; without JavaScript every route remains reachable.
    document.body.dataset.navigationReady = 'true';
    setMenuOpen(false);
}
