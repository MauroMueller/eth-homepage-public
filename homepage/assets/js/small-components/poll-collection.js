/**
 * Handle poll behavior.
 * In particular, make sure updates are directly posted to the server, the new
 * state is periodically fetched, and the bars get their required widths & text
 * positioning.
 */
(function() {
    const pollCollections = AppGlobals.elements.pollCollections;
    const polls = [];

    pollCollections.forEach(collection => {
        polls.push(...collection.querySelectorAll('.poll'));
    });

    polls.forEach(poll => {
        setupPoll(poll);
    });

    function setupPoll(poll) {
        const optionsForm = poll.querySelector('.options');
        if (!optionsForm) return;

        optionsForm.addEventListener('submit', e => {
            e.preventDefault();

            requestUpdate(optionsForm, e.submitter);
        });
    }

    async function requestUpdate(optionsForm, submitter = null) {
        const xhr = new XMLHttpRequest();
        const data = new FormData(optionsForm, submitter);

        xhr.addEventListener('load', () => {
            if (xhr.status < 200 || xhr.status >= 300) {
                return;
            }

            var result;
            try {
                result = JSON.parse(xhr.responseText);
            } catch (error) {
                return;
            }

            updateOptions(optionsForm, result);
        });

        xhr.open('POST', optionsForm.action);
        xhr.send(data);
    }

    async function requestUpdateAll() {
        polls.forEach(poll => {
            const optionsForm = poll.querySelector('.options');
            if (!optionsForm) return;
            requestUpdate(optionsForm);
        });

        setTimeout(requestUpdateAll, 2000);
    }

    function updateOptions(optionsForm, result) {
        const buttons = optionsForm.querySelectorAll('.option');

        const maxVotes = Math.max(...result.map(option => option.vote_count));
        optionsById = new Map(result.map(option => [option.id, option]));

        buttons.forEach(button => {
            const option = optionsById.get(Number(button.value));
            if (!option) return;

            const relativeVotes = maxVotes != 0
                                ? option.vote_count / maxVotes * 100
                                : 0;

            button.style.setProperty('--relative-votes', `${relativeVotes}%`);
            button.ariaPressed = String(option.selected);
            button.querySelector('.vote-count').textContent = option.vote_count;
        });

        updateLabelPosAll();
    }

    async function updateLabelPos(button) {
        if (AppGlobals.config.breakpoints.large.matches) {
            button.classList.remove('label-outside');
            return;
        }
        const bar = button.querySelector('.bar');
        const label = bar.querySelector('.label');
        const measurementBar = bar.cloneNode(true);
        const measurementLabel = measurementBar.querySelector('.label');
        measurementBar.style.transition = 'none';
        measurementBar.style.position = 'absolute';
        measurementBar.style.visibility = 'hidden';

        button.appendChild(measurementBar);
        const outsideBefore = button.classList.contains('label-outside');
        button.classList.remove('label-outside');

        const widthWith = measurementBar.getBoundingClientRect().width;
        measurementLabel.style.display = 'none';
        const widthWithout = measurementBar.getBoundingClientRect().width;
        measurementLabel.style.display = '';
        measurementBar.style.width = 'max-content';
        const requiredWidth = measurementBar.getBoundingClientRect().width;

        button.classList.toggle('label-outside', outsideBefore);
        measurementBar.remove();

        const outsideAfter = widthWithout < widthWith;

        let outsideNow = outsideBefore;
        while (outsideNow != outsideAfter) {
            label.style.display = 'none';
            const widthNow = bar.getBoundingClientRect().width;
            label.style.display = '';
            outsideNow = widthNow < requiredWidth;
            await new Promise(resolve => setTimeout(resolve, 1));
        }
        button.classList.toggle('label-outside', outsideAfter);
    }

    function updateLabelPosAll() {
        polls.forEach(poll => {
            const buttons = poll.querySelectorAll('.options > .option');
            buttons.forEach(button => updateLabelPos(button));
        });
    }

    window.addEventListener('load', requestUpdateAll);
    window.addEventListener('resize', updateLabelPosAll);
})();
