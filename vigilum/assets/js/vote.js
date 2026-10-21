(function () {
  if (typeof vigilumVote === 'undefined') return;

  document.addEventListener('click', function (event) {
    var button = event.target.closest('[data-vote]');
    if (!button || button.disabled) return;

    var id = button.getAttribute('data-vote');
    var box = button.closest('[data-vote-box]');
    button.disabled = true;

    fetch(vigilumVote.rest, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': vigilumVote.nonce
      },
      credentials: 'same-origin',
      body: JSON.stringify({ id: Number(id) })
    })
      .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data }; }); })
      .then(function (result) {
        if (!result.ok) {
          var message = (result.data && result.data.message) || vigilumVote.i18n.error;
          if (box) {
            var note = document.createElement('p');
            note.className = 'aside-note';
            note.textContent = message;
            box.appendChild(note);
          }
          if (result.data && result.data.code === 'vigilum_double') {
            var label = button.querySelector('.vote-button__label');
            if (label) label.textContent = vigilumVote.i18n.done;
            return;
          }
          button.disabled = false;
          return;
        }
        var label = button.querySelector('.vote-button__label');
        if (label) label.textContent = vigilumVote.i18n.done;
        var count = box && box.querySelector('[data-vote-count]');
        if (count && result.data) {
          var labelText = result.data.label || '';
          var noun = labelText.replace(/^\s*\d+[\s\u00a0]*/, '');
          count.innerHTML = '<strong>' + result.data.votes + '</strong><span>' + noun + '</span>';
        }
      })
      .catch(function () {
        button.disabled = false;
      });
  });
}());
