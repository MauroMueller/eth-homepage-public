/**
 * Toggle between light and dark mode.
 * This just controls the toggles, the actual theme switching is handled by
 * script.js.
 */
(function() {
    const toggles = AppGlobals.elements.themeToggles;

    function setAll(darkTheme) {
        toggles.forEach(otherToggle => {
            otherToggle.checked = !darkTheme;
        });
    }

    // On toggle: Set global variable
    toggles.forEach(toggle => {
        toggle.addEventListener('change', () => {
            AppGlobals.state.darkTheme = !toggle.checked;
        });
    });

    // On change of global value: Set all toggles correctly
    AppGlobals.onStateChange((prop, value) => {
        if (prop === 'darkTheme') setAll(value);
    });
})();
