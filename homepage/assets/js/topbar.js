/**
 * Correctly handle top bar layout
 * (wide vs. small screens, hide on scroll, etc.)
 */
(function() {
    const topbar = AppGlobals.elements.topbar;
    const desktopQuery = AppGlobals.config.breakpoints.large;
    let topbarHeight = 0;
    let lastScroll = 0;
    let scrollUpDist = 0;

    function reset() {
        setPadding();
        placeBreadcrumbs();
        lastScroll = window.scrollY;
        scrollUpDist = 50;
        topbar.style.transform = 'translateY(0)';
    }

    function atPageBottom(currentScroll) {
        const docHeight = Math.max(
            document.body.scrollHeight,
            document.documentElement.scrollHeight
        );
        return window.innerHeight + currentScroll >= docHeight;
    }

    function setPadding() {
        topbarHeight = topbar.offsetHeight;
        document.body.style.paddingTop = topbarHeight + 'px';
    }

    function placeBreadcrumbs() {
        const breadcrumbs = AppGlobals.elements.breadcrumbs;
        if (desktopQuery.matches) {
            topbar.querySelector('.middle').prepend(breadcrumbs);
        } else {
            AppGlobals.elements.content.prepend(breadcrumbs);
        }
    }

    function handleScroll() {
        if (desktopQuery.matches) {
            // Desktop => show bar
            reset();
            return;
        }

        // Mobile => handle scroll
        let showTopbar = false;
        const currentScroll = window.scrollY;

        if (currentScroll > lastScroll) {
            // Scrolling down => showTopbar stays false either way
            scrollUpDist = 0;
        } else {
            // Scrolling up => showTopbar becomes true if scrolled up enough
            scrollUpDist += (lastScroll - currentScroll);
            if (scrollUpDist >= 50) showTopbar = true;
        }

        // At bottom of page
        // => showTopbar becomes false (unless also at top, checked next)
        if (atPageBottom(currentScroll)) showTopbar = false;
        // At top of page => showTopbar becomes true regardless of anything else
        if (currentScroll <= topbarHeight) showTopbar = true;

        topbar.style.transform = showTopbar ? 'translateY(0)'
                                            : 'translateY(-100%)';

        lastScroll = currentScroll;
    }

    // On change between desktop/mobile mode: Show bar in all cases
    desktopQuery.addEventListener('change', reset);

    window.addEventListener('scroll', handleScroll);

    // Padding might change if top bar layout changes
    window.addEventListener('resize', reset);

    // Correctly initialize padding when page is loaded
    document.addEventListener('DOMContentLoaded', reset);
    window.addEventListener('load', reset);
    reset();
})();
