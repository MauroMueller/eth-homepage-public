/**
 * Toggle visibility of sidebar menu.
 * This just controls the toggles, the actual sidebar is handled by sidebar.js.
 */
(function() {
    const toggles = AppGlobals.elements.menuToggles;

    function setAll(sidebarOpen) {
        toggles.forEach(otherToggle => {
            otherToggle.checked = sidebarOpen;
        });
    }

    // On toggle: Set global variable
    toggles.forEach(toggle => {
        toggle.addEventListener('change', () => {
            AppGlobals.state.sidebarOpen = toggle.checked;
        });
    });

    // On change of global value: Set all toggles correctly
    AppGlobals.onStateChange((prop, value) => {
        if (prop === 'sidebarOpen') setAll(value);
    });
})();
