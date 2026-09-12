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
        toggle.setAttribute('aria-expanded', String(collapsed));
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

(() => {
    const modal = document.querySelector('[data-location-modal]');
    const form = document.querySelector('[data-location-form]');

    if (!(modal instanceof HTMLElement) || !(form instanceof HTMLFormElement)) {
        return;
    }

    const title = modal.querySelector('[data-location-modal-title]');
    const operation = form.querySelector('[data-location-operation]');
    const id = form.querySelector('[data-location-id]');
    const name = form.querySelector('[data-location-name]');
    const description = form.querySelector('[data-location-description]');
    const active = form.querySelector('[data-location-active]');
    const activeField = form.querySelector('[data-location-active-field]');

    if (
        !(title instanceof HTMLElement)
        || !(operation instanceof HTMLInputElement)
        || !(id instanceof HTMLInputElement)
        || !(name instanceof HTMLInputElement)
        || !(description instanceof HTMLInputElement)
        || !(active instanceof HTMLInputElement)
        || !(activeField instanceof HTMLElement)
    ) {
        return;
    }

    const open = () => {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('has-open-modal');
        window.requestAnimationFrame(() => name.focus());
    };

    const close = () => {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('has-open-modal');
    };

    const prepareCreate = () => {
        form.reset();
        operation.value = 'create';
        id.value = '';
        active.checked = true;
        activeField.hidden = true;
        title.textContent = 'Cadastrar local';
        open();
    };

    const prepareEdit = (button) => {
        form.reset();
        operation.value = 'update';
        id.value = button.dataset.locationId ?? '';
        name.value = button.dataset.locationName ?? '';
        description.value = button.dataset.locationDescription ?? '';
        active.checked = button.dataset.locationActive === '1';
        activeField.hidden = false;
        title.textContent = 'Editar local';
        open();
    };

    for (const button of document.querySelectorAll('[data-location-modal-create]')) {
        if (button instanceof HTMLButtonElement) {
            button.addEventListener('click', prepareCreate);
        }
    }

    for (const button of document.querySelectorAll('[data-location-modal-edit]')) {
        if (button instanceof HTMLButtonElement) {
            button.addEventListener('click', () => prepareEdit(button));
        }
    }

    for (const button of modal.querySelectorAll('[data-location-modal-close]')) {
        if (button instanceof HTMLButtonElement) {
            button.addEventListener('click', close);
        }
    }

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            close();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            close();
        }
    });
})();
