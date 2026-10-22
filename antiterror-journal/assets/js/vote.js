(function () {
  if (typeof atjVote === 'undefined') return;

  function label(count) {
    var n = Number(count) || 0;
    var mod10 = n % 10;
    var mod100 = n % 100;
    if (mod10 === 1 && mod100 !== 11) return n + ' голос';
    if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) return n + ' голоса';
    return n + ' голосов';
  }

  document.addEventListener('click', function (event) {
    var button = event.target.closest('[data-vote]');
    if (!button || button.disabled) return;

    var root = button.closest('[data-vote-root]');
    var id = button.getAttribute('data-vote');
    button.disabled = true;

    fetch(atjVote.endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': atjVote.nonce
      },
      credentials: 'same-origin',
      body: JSON.stringify({ id: Number(id) })
    }).then(function (response) {
      return response.json().then(function (data) {
        return { ok: response.ok, data: data };
      });
    }).then(function (result) {
      if (!result.ok) {
        var code = result.data && result.data.code;
        var message = atjVote.i18n.error;
        if (code === 'atj_duplicate') message = atjVote.i18n.duplicate;
        if (code === 'atj_closed') message = atjVote.i18n.closed;
        throw new Error(message);
      }

      if (root) {
        root.setAttribute('data-voted', 'true');
        var count = root.querySelector('[data-vote-count]');
        if (count) {
          count.innerHTML = '<strong>' + result.data.votes + '</strong> <span>' + label(result.data.votes) + '</span>';
        }
        var text = root.querySelector('.atj-vote__text');
        var mark = root.querySelector('.atj-vote__mark');
        if (text) text.textContent = atjVote.i18n.done;
        if (mark) mark.textContent = '✓';
      }
    }).catch(function (error) {
      button.disabled = false;
      window.alert(error.message || atjVote.i18n.error);
    });
  });
}());
