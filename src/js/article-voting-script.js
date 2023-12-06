document.addEventListener("DOMContentLoaded", () => {
    // debug errors and warnings in browser console
    const enableDebug = true;

    // adding dom elements selector here
    const domElements = {
        voteButtonsWrapper: document.querySelector('[data-helpful-buttons-wrapper]'),
        voteButtons: document.querySelectorAll('[data-vp-helpful-button]'),
        voteStatusLabel: document.querySelector('[data-status-label]'),
        positivePercentage: document.querySelector('[data-positive-percentage]'),
        negativePercentage: document.querySelector('[data-negative-percentage]'),
    };

    // debug to browser console if debugging enabled
    function debug(message) {
        if (enableDebug) {
            console.log(message);
        }
    }

    function updateVote(message, positiveVotes, negativeVotes) {
        if (!message) {
            return;
        }

        // update label text with feedback message
        if( domElements.voteStatusLabel ) {
            domElements.voteStatusLabel.innerHTML = message;
        }

        // update percentage
        if(domElements.positivePercentage && domElements.negativePercentage) {
            domElements.positivePercentage.innerHTML = `${positiveVotes}%`;
            domElements.negativePercentage.innerHTML = `${negativeVotes}%`;
        }
    }

    function submitVote(vote_value) {
        // skip undefined or null value
        vote_value = vote_value || '';

        // trim and keep it lowercase
        vote_value = vote_value.toLowerCase().trim();

        // validate the vote
        if (vote_value !== 'yes' && vote_value !== 'no') {
            return debug('Empty or invalid vote submited!');
        }

        if (!article_voting_vars) {
            return debug('Missing localized article_voting_vars variable provided by the plugin.');
        }

        var post_id = article_voting_vars.post_id;
        var nonce = article_voting_vars.nonce;

        // Using fetch API to send the request and get feddback form the server
        fetch(article_voting_vars.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=submit_vote&post_id=' + post_id + '&vote_value=' + vote_value + '&nonce=' + nonce,
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data) {
                updateVote(data.message, data.positive_votes, data.negative_votes);
            }
        })
        .catch(function(error) {
            debug(error);
        });

    }

    // if buttons exist
    if (domElements.voteButtons.length) {
        domElements.voteButtons.forEach(function(btn){
            btn.onclick = function(ev) {
                // prevent default action on click
                ev.preventDefault();

                // set target button
                const targetBtn = this;

                // highlight selected button
                if (targetBtn && targetBtn.getAttribute('data-action')) {
                    // submit the vote via ajax
                    submitVote(targetBtn.getAttribute('data-action'));

                    // highlight the button
                    targetBtn.classList.add('active');

                    // add voted class to the buttons parent to ignore interaction if already voted
                    if (domElements.voteButtonsWrapper) {
                        domElements.voteButtonsWrapper.classList.add('voted');
                    }
                } else {
                    debug('Helpfull article button missing data-action attribute!');
                }
            };
        });
    }
});
