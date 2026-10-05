document.addEventListener('DOMContentLoaded', () => {
  document.addEventListener('click', (e) => {
    const link = e.target.closest('a[data-scroll]');
    if (!link) return;
    if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

    const url = new URL(link.href, window.location.href);
    const isSamePage = url.origin === window.location.origin && url.pathname.replace(/\/$/, '') === window.location.pathname.replace(/\/$/, '');
    const hash = url.hash || '';
    if (!isSamePage || !hash) return;

    e.preventDefault();
    const targetId = hash.replace('#', '');
    const target = document.getElementById(targetId);
    if (target) {
      window.scrollTo({ top: target.offsetTop - 80, behavior: 'smooth' });
    }
    const floatingMenu = document.querySelector('.floating-menu');
    const floatingToggle = document.getElementById('floatingMenuToggle');
    if (floatingMenu && floatingMenu.classList.contains('open')) {
      floatingMenu.classList.remove('open');
      if (floatingToggle) {
        floatingToggle.setAttribute('aria-expanded', 'false');
        floatingToggle.textContent = '+';
      }
      document.body.classList.remove('overlay-open');
      document.body.style.overflow = '';
    }
  });

  const floatingMenu = document.querySelector('.floating-menu');
  const floatingToggle = document.getElementById('floatingMenuToggle');
  const floatingLageplanToggle = document.getElementById('floatingLageplanToggle');
  if (floatingMenu && floatingToggle) {
    const panel = floatingMenu.querySelector('.floating-menu-panel');
    floatingToggle.addEventListener('click', (e) => {
      const isOpen = floatingMenu.classList.toggle('open');
      floatingToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      floatingToggle.textContent = isOpen ? '-' : '+';
      document.body.classList.toggle('overlay-open', isOpen);

      // Disable body scroll when menu is open
      if (isOpen) {
        document.body.style.overflow = 'hidden';
      } else {
        document.body.style.overflow = '';
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && floatingMenu.classList.contains('open')) {
        floatingMenu.classList.remove('open');
        floatingToggle.setAttribute('aria-expanded', 'false');
        floatingToggle.textContent = '+';
        document.body.classList.remove('overlay-open');
        document.body.style.overflow = '';
      }
    });

    floatingMenu.querySelectorAll('.floating-menu-link[data-scroll]').forEach(link => {
      link.addEventListener('click', function (e) {
        e.preventDefault();
        const targetId = this.getAttribute('href').replace('#', '');
        const target = document.getElementById(targetId);
        if (target) {
          window.scrollTo({ top: target.offsetTop - 80, behavior: 'smooth' });
        }
        floatingMenu.classList.remove('open');
        floatingToggle.setAttribute('aria-expanded', 'false');
        floatingToggle.textContent = '+';
        document.body.classList.remove('overlay-open');
        document.body.style.overflow = '';
      });
    });
  }

  if (floatingLageplanToggle) {
    floatingLageplanToggle.addEventListener('click', () => {
      const target = document.getElementById('lageplan-section');
      if (target) {
        window.scrollTo({ top: target.offsetTop - 80, behavior: 'smooth' });
      }
    });
  }

  const footerEl = document.querySelector('footer.footer');
  if (footerEl) {
    const io = new IntersectionObserver((entries) => {
      const entry = entries[0];
      document.body.classList.toggle('footer-in-view', !!entry && entry.isIntersecting);
    }, { root: null, threshold: 0.1 });
    io.observe(footerEl);
  }
});