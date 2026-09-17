/**
 * Toggle visibility of some fields & buttons of the preferences form.
 */
(function() {
    const preferenceForms = AppGlobals.elements.preferenceForms;

    preferenceForms.forEach(form => {
        setupForm(form);
    });

    function setupForm(form) {
        const editButton = form.querySelector('button.edit');
        if (editButton == null) return;
        const cancelButton = form.querySelector('button.cancel');

        editButton.addEventListener('click', () => {
            preferenceForms.forEach(otherForm => {
                disableForm(otherForm);
            });
            enableForm(form);
        });

        cancelButton.addEventListener('click', () => {
            disableForm(form);
        });
    }

    function enableForm(form) {
        const inputs = form.querySelectorAll('input');
        form.classList.add('editing');
        inputs.forEach(input => {
            input.disabled = false;
        });
    }

    function disableForm(form) {
        const inputs = form.querySelectorAll('input');
        form.classList.remove('editing');
        inputs.forEach(input => {
            input.disabled = true;
            input.value = input.defaultValue;
        });
    }
})();
