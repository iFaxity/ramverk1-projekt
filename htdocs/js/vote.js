/**
 * Script for voting functionality
 */
(function () {
  'use strict';

  const POST_TYPES = {
    q: 'question',
    a: 'answer',
    c: 'comment',
  };

  async function votePost($vote, $parent) {
    let voteType;

    if ($vote.classList.contains('upvote')) {
      voteType = 'upvote';
    } else if ($vote.classList.contains('downvote')) {
      voteType = 'downvote';
    } else {
      throw new Error("Element not valid.");
    }

    if (!$vote.classList.contains('active')) {
      try {
        const postId = $vote.dataset.id;
        const postType = POST_TYPES[postId[0]];
        const id = postId.substr(1);
        const root = location.href.substr(0, location.href.indexOf('/htdocs'));
        const res = await fetch(`${root}/htdocs/${postType}/${voteType}/${id}`, { method: 'post' });

        if (res.ok) {
          const data = await res.json();

          // Remove active from both votes to prevent both being active
          // Activate on the clicked element
          for (let $el of $parent.children) {
            $el.classList.remove('active');
          }

          $vote.classList.add('active');

          // Update the vote counter
          const $votes = $parent.previousElementSibling;
          $votes.textContent = data.votes;
        }
      } catch (ex) {
        // Ignore error
        console.error('There was an error voting, make sure you\'re logged in and try again soon.');
        console.error(ex);
      }
    }
  }

  const $votes = document.querySelectorAll('.vote');

  for (let $parent of $votes) {
    for (let $vote of $parent.children) {
      $vote.addEventListener('click', () => votePost($vote, $parent), false);
    }
  }
})();
