/**
 * Correctly handle side bar layout (wide vs. small screens, etc.).
 * This mostly just reacts to changes of the global state (the exceptions being
 * that it updates it on load and on window resize).
 */
(function() {
    const sidebar = AppGlobals.elements.sidebar;

    const desktopQuery = AppGlobals.config.breakpoints.large;

    function set(sidebarOpen) {
        sidebar.classList.toggle('hidden', !sidebarOpen);
    }

    function reset() {
        AppGlobals.state.sidebarOpen = desktopQuery.matches;
        // set() gets automatically triggered after that (if needed)
    }

    // On change of state, trigger set()
    AppGlobals.onStateChange((prop, value) => {
        if (prop === 'sidebarOpen') set(value);
    });

    // On change between desktop/mobile mode, trigger reset()
    desktopQuery.addEventListener('change', reset);

    // On click outside of sidebar on small device, hide it
    document.addEventListener('click', e => {
        if (sidebar.contains(e.target)) return;
        if (e.target.closest(AppGlobals.config.interactiveSelector)) return;
        if (desktopQuery.matches) return;
        AppGlobals.state.sidebarOpen = false;
    });

    // On load: trigger reset()
    document.addEventListener('DOMContentLoaded', reset);
    window.addEventListener('load', reset);
    reset();
})();
