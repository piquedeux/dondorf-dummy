// Newsletter slide-up: show once user reached bottom then scrolls up
(function(){
  const key = 'newsletterDismissed_v1';
  if (localStorage.getItem(key)) return; // already dismissed

  let atBottom = false;
  let shown = false;

  function show() {
    if (shown) return;
    const el = document.querySelector('.newsletter-popup');
    if (!el) return;
    el.classList.add('visible');
    shown = true;
  }

  function hide() {
    const el = document.querySelector('.newsletter-popup');
    if (!el) return;
    el.classList.remove('visible');
    localStorage.setItem(key, '1');
  }

  window.addEventListener('scroll', () => {
    const scrollTop = window.scrollY || document.documentElement.scrollTop;
    const scrollHeight = document.documentElement.scrollHeight;
    const clientHeight = document.documentElement.clientHeight;

    const isBottom = (scrollTop + clientHeight) >= (scrollHeight - 5);
    if (isBottom) {
      atBottom = true;
      return;
    }

    // user scrolled up after reaching bottom -> show newsletter
    if (atBottom && !shown && scrollTop < (scrollHeight - clientHeight - 100)) {
      show();
    }
  }, { passive: true });

  document.addEventListener('click', (e) => {
    if (e.target.matches('.newsletter-popup .close') || e.target.closest('.newsletter-popup .close')) {
      hide();
    }
  });

  // simple ajax submit
  document.addEventListener('submit', (e) => {
    if (e.target.matches('.newsletter-popup form')) {
      e.preventDefault();
      const form = e.target;
      const fd = new FormData(form);
      // add basic client-side validation
      const email = fd.get('email') || '';
      if (!email.match(/@/)) { form.querySelector('.result').textContent = 'Bitte gültige E‑Mail'; return; }

      fetch('/', { method: 'POST', body: new URLSearchParams(fd) })
        .then(r => r.json())
        .then(json => {
          form.querySelector('.result').textContent = json.message || 'Danke!';
          if (json.status === 'ok') localStorage.setItem(key, '1');
        }).catch(err => { form.querySelector('.result').textContent = 'Fehler'; });
    }
  });
})();
