/**
 * Handle page-overview reverse sorting.
 */
(function() {
    const pageOverviews = AppGlobals.elements.pageOverviews;

    pageOverviews.forEach(pageOverview => {
        setup(pageOverview);
    });

    function setup(pageOverview) {
        const reverseButton = pageOverview.querySelector(':scope > .header > .reverse');
        const inner = pageOverview.querySelector(':scope > .inner');
        reverseButton.addEventListener('click', () => {
            reverseButton.classList.toggle('reversed');
            reverseTree(inner);
        });
    }

    function reverseChildren(container) {
        [...container.children].reverse().forEach(child => {
            container.appendChild(child);
        });
    }

    function reverseTree(tree) {
        reverseChildren(tree);
        tree.querySelectorAll('.children').forEach(subElement => {
            reverseChildren(subElement);
        });
    }
})();
