/**
 * Make email required if the user requests a reply.
 */
(function() {
    const contactForms = AppGlobals.elements.contactForms;

    contactForms.forEach(form => {
        setupForm(form);
    });

    function setupForm(form) {
        const request_reply_checkbox = form.querySelector(".request-reply input");
        const email_field = form.querySelector('input[type="email"]');
        if (!request_reply_checkbox || !email_field) return;

        request_reply_checkbox.addEventListener("change", () => {
            email_field.required = request_reply_checkbox.checked;
        });
    }
})();
