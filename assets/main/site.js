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

(() => {
    const shell = document.querySelector('[data-hecate-discovery]');

    if (!(shell instanceof HTMLElement)) {
        return;
    }

    const hotspots = Array.from(shell.querySelectorAll('[data-hecate-hotspot]'));
    const closeButton = shell.querySelector('[data-hecate-close]');

    if (hotspots.length === 0 || !(closeButton instanceof HTMLButtonElement)) {
        return;
    }

    const setState = (state) => {
        shell.dataset.hecateDiscovery = state;

        for (const hotspot of hotspots) {
            if (hotspot instanceof HTMLButtonElement) {
                hotspot.setAttribute('aria-expanded', String(hotspot.dataset.hecateHotspot === state));
            }
        }
    };

    for (const hotspot of hotspots) {
        if (!(hotspot instanceof HTMLButtonElement)) {
            continue;
        }

        hotspot.addEventListener('click', () => {
            const state = hotspot.dataset.hecateHotspot;

            if (state === 'identity' || state === 'purpose') {
                setState(shell.dataset.hecateDiscovery === state ? 'idle' : state);
            }
        });
    }

    closeButton.addEventListener('click', () => setState('idle'));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setState('idle');
        }
    });

    let frameRequested = false;

    document.addEventListener('pointermove', (event) => {
        if (frameRequested || event.pointerType === 'touch') {
            return;
        }

        frameRequested = true;
        window.requestAnimationFrame(() => {
            frameRequested = false;

            for (const hotspot of hotspots) {
                if (!(hotspot instanceof HTMLButtonElement)) {
                    continue;
                }

                const rect = hotspot.getBoundingClientRect();
                const centerX = rect.left + rect.width / 2;
                const centerY = rect.top + rect.height / 2;
                const distance = Math.hypot(event.clientX - centerX, event.clientY - centerY);

                hotspot.classList.toggle('is-near', distance < 150);
            }
        });
    }, {passive: true});
})();
