(() => {
    const modal = document.querySelector('[data-printer-modal]');
    const form = document.querySelector('[data-printer-form]');

    if (!(modal instanceof HTMLElement) || !(form instanceof HTMLFormElement)) {
        return;
    }

    const title = modal.querySelector('[data-printer-modal-title]');
    const operation = form.querySelector('[data-printer-operation]');
    const id = form.querySelector('[data-printer-id]');
    const name = form.querySelector('[data-printer-name]');
    const host = form.querySelector('[data-printer-host]');
    const location = form.querySelector('[data-printer-location]');
    const active = form.querySelector('[data-printer-active]');
    const activeField = form.querySelector('[data-printer-active-field]');

    if (!(title instanceof HTMLElement)
        || !(operation instanceof HTMLInputElement)
        || !(id instanceof HTMLInputElement)
        || !(name instanceof HTMLInputElement)
        || !(host instanceof HTMLInputElement)
        || !(location instanceof HTMLSelectElement)
        || !(active instanceof HTMLInputElement)
        || !(activeField instanceof HTMLElement)) {
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
        title.textContent = 'Cadastrar impressora';
        open();
    };

    const prepareEdit = (button) => {
        form.reset();
        operation.value = 'update';
        id.value = button.dataset.printerId ?? '';
        name.value = button.dataset.printerName ?? '';
        host.value = button.dataset.printerHost ?? '';
        location.value = button.dataset.printerLocationId ?? '';
        active.checked = button.dataset.printerActive === '1';
        activeField.hidden = false;
        title.textContent = 'Editar impressora';
        open();
    };

    for (const button of document.querySelectorAll('[data-printer-modal-create]')) {
        if (button instanceof HTMLButtonElement) {
            button.addEventListener('click', prepareCreate);
        }
    }

    for (const button of document.querySelectorAll('[data-printer-modal-edit]')) {
        if (button instanceof HTMLButtonElement) {
            button.addEventListener('click', () => prepareEdit(button));
        }
    }

    for (const button of modal.querySelectorAll('[data-printer-modal-close]')) {
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
