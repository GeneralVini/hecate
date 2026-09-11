(() => {
    const shell = document.querySelector('[data-sidebar-state]');
    const toggle = document.querySelector('[data-sidebar-toggle]');

    if (!(shell instanceof HTMLElement) || !(toggle instanceof HTMLButtonElement)) {
        return;
    }

    toggle.addEventListener('click', () => {
        const collapsed = shell.dataset.sidebarState === 'collapsed';
        shell.dataset.sidebarState = collapsed ? 'expanded' : 'collapsed';
        toggle.setAttribute('aria-label', collapsed ? 'Recolher menu' : 'Expandir menu');
    });
})();
