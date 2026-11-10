/**
 * Toggle visibility of user menu.
 * Clicking the user icon (or username) in the top right corner shows/hides the
 * menu to access account pages.
 */
(function() {
    const userMenu = AppGlobals.elements.topbarUser;
    const button = AppGlobals.elements.topbarUserButton;


    button.addEventListener('click', e => {
        e.stopPropagation();
        userMenu.classList.toggle('open');
    });

    document.addEventListener('click', e => {
        if (e.target.closest(AppGlobals.config.interactiveSelector)) return;
        userMenu.classList.remove('open');
    });
})();
