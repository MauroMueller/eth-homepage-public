/**
 * Set up global variables
 */
window.AppGlobals = (() => {
    const config = {
        breakpoints: {
            large: window.matchMedia('(min-width: 750px)'),
        },
        interactiveSelector: [
            'button',
            'a',
            'nav',
            'input',
            'select',
            'textarea',
            '.theme-toggle',
            '.menu-toggle',
        ].join(', '),
    };
    const elements = {
        content: document.querySelector('.content'),
        topbar: document.querySelector('.topbar'),
        sidebar: document.querySelector('.sidebar'),
        menuToggles: document.querySelectorAll('.menu-toggle > input'),
        themeToggles: document.querySelectorAll('.theme-toggle > input'),
        topbarUser: document.querySelector('.topbar-user'),
        topbarUserButton: document.querySelector('.topbar-user-button'),
        breadcrumbs: document.querySelector('.breadcrumbs'),
        fileCollections: document.querySelectorAll('.file-collection'),
        preferenceForms: document.querySelectorAll(
            '.account-form.preferences > form'
        ),
    };
    const state = {
        sidebarOpen: false,
        darkTheme: true,
        topbarUserMenuOpen: false,
    };

    // Listeners for reactive state
    const listeners = [];

    // Proxy handles state changes
    const reactiveState = new Proxy(state, {
        set(target, prop, value) {
            // Set the actual value
            target[prop] = value;
            // Notify listeners
            listeners.forEach(callback => callback(prop, value));
            return true;
        }
    });

    return {
        // Expose some sections
        config,
        elements,
        state: reactiveState,

        // Allow to register listeners for state changes
        onStateChange(callback) {
            if (typeof callback === 'function') listeners.push(callback);
        }
    };
})();



/**
 * When the page is fully loaded, start enabling transitions
 */
(function() {
    window.addEventListener('load', enableTransitions);

    async function enableTransitions() {
        // Any timeout should do, even just 1ms seems to work
        await new Promise(r => setTimeout(r, 1));
        document.documentElement.classList.remove('no-transition');
    }
})();



/**
 * Handle theme changing (including localStorage stuff)
 */
(function() {
    // On change of theme global variable: Update theme
    AppGlobals.onStateChange((prop, value) => {
        if (prop === 'darkTheme') {
            document.body.classList.toggle('lightmode', !value);
            localStorage.setItem('theme', value ? 'dark' : 'light');
        }
    });

    // On load: Set global variable to value from localStorage
    document.addEventListener('DOMContentLoaded', () => {
        const saved = localStorage.getItem('theme');
        AppGlobals.state.darkTheme = (saved !== 'light');
    });
})();
