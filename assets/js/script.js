document.addEventListener('DOMContentLoaded', () => {

  // Logo switch depending on scroll and theme
  const mainLogo = document.getElementById('mainLogo');
  const mainLogoColor = document.getElementById('mainLogoColor');
  const wordmarkLogo = document.getElementById('logoWordmark');
  const wordmarkLogoColored = document.getElementById('logoWordmarkColored');

  function updateHeaderLogo() {
    const theme = document.body.dataset.theme || 'monochrome';
    const scrolled = window.scrollY > 100;

    // Wordmark-Logo Handling
    if (scrolled) {
      if (theme === 'colorful' && wordmarkLogoColored) {
        wordmarkLogoColored.style.display = 'block';
        if (wordmarkLogo) wordmarkLogo.style.display = 'none';
      } else {
        if (wordmarkLogo) wordmarkLogo.style.display = 'block';
        if (wordmarkLogoColored) wordmarkLogoColored.style.display = 'none';
      }
      if (mainLogo) mainLogo.style.display = 'none';
      if (mainLogoColor) mainLogoColor.style.display = 'none';
    } else {
      if (wordmarkLogo) wordmarkLogo.style.display = 'none';
      if (wordmarkLogoColored) wordmarkLogoColored.style.display = 'none';
      // show themed logomark
      if (theme === 'colorful' && mainLogoColor) {
        if (mainLogo) mainLogo.style.display = 'none';
        mainLogoColor.style.display = 'block';
      } else if (mainLogo) {
        mainLogo.style.display = 'block';
        if (mainLogoColor) mainLogoColor.style.display = 'none';
      }
    }
  }

  if (mainLogo || mainLogoColor || wordmarkLogo) {
    updateHeaderLogo();
    let logoUpdateScheduled = false;
    window.addEventListener('scroll', () => {
      if (logoUpdateScheduled) return;
      logoUpdateScheduled = true;
      requestAnimationFrame(() => {
        updateHeaderLogo();
        logoUpdateScheduled = false;
      });
    }, { passive: true });
  }

  // Language switch
  const langSwitch = document.getElementById('langSwitch');
  const browserLangRaw = (navigator.languages && navigator.languages.length > 0
    ? navigator.languages[0]
    : navigator.language || 'en').toLowerCase();
  const detectedLang = browserLangRaw.startsWith('de') ? 'de' : 'en';
  let currentLang = localStorage.getItem('siteLanguage') || detectedLang;

  if (currentLang !== 'de' && currentLang !== 'en') {
    currentLang = detectedLang;
  }

  document.documentElement.setAttribute('lang', currentLang);

  if (langSwitch) {
    // Initialize button state
    langSwitch.textContent = currentLang === 'en' ? 'EN' : 'DE';
    langSwitch.addEventListener('click', () => {
      // Always allow toggling language; sections with missing translations will gracefully fallback
      currentLang = currentLang === 'en' ? 'de' : 'en';
      langSwitch.textContent = currentLang === 'en' ? 'EN' : 'DE';
      document.documentElement.setAttribute('lang', currentLang);
      localStorage.setItem('siteLanguage', currentLang);
    });
  }

  // Theme switch: cycle between 'monochrome' and 'colorful' and persist choice
  const themeSwitch = document.getElementById('themeSwitch');
  if (themeSwitch) {
    const availableThemes = ['monochrome', 'colorful'];
    let currentTheme = localStorage.getItem('siteTheme') || 'monochrome';
    // apply initial theme
    document.body.dataset.theme = currentTheme;
    updateThemeSwitchUI();

    themeSwitch.addEventListener('click', () => {
      const idx = availableThemes.indexOf(currentTheme);
      currentTheme = availableThemes[(idx + 1) % availableThemes.length];
      document.body.dataset.theme = currentTheme;
      localStorage.setItem('siteTheme', currentTheme);
      updateThemeSwitchUI();
    });

    function updateThemeSwitchUI() {
      themeSwitch.classList.remove('monochrome', 'colorful');
      themeSwitch.classList.add(currentTheme === 'colorful' ? 'colorful' : 'monochrome');
      themeSwitch.setAttribute('aria-pressed', currentTheme === 'colorful' ? 'true' : 'false');
      themeSwitch.setAttribute('title', currentTheme === 'colorful' ? 'Farbiges Thema' : 'Monochromes Thema');
      // update header logo when theme changes
      updateHeaderLogo();
    }
  }

  // Smooth scroll when clicking logo link
  const headerLogoLink = document.getElementById('headerLogoLink');
  if (headerLogoLink) {
    headerLogoLink.addEventListener('click', (e) => {
      e.preventDefault();

      // Custom smooth scroll with slower speed
      const startPosition = window.pageYOffset;
      const duration = 600;
      const startTime = performance.now();

      function easeInOutCubic(t) {
        return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
      }

      function scrollAnimation(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const ease = easeInOutCubic(progress);

        window.scrollTo(0, startPosition * (1 - ease));

        if (progress < 1) {
          requestAnimationFrame(scrollAnimation);
        }
      }

      requestAnimationFrame(scrollAnimation);
    });
  }

  // Smooth-scroll helper for deep links (collectives, rooms)
  function smoothScrollToTarget(targetId) {
    const el = document.getElementById(targetId);
    if (!el) return;
    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    // Update hash so backend-selected anchor is reflected
    history.replaceState(null, '', `#${targetId}`);
    // Optional visual emphasis
    el.classList.add('scroll-target-highlight');
    setTimeout(() => el.classList.remove('scroll-target-highlight'), 1200);
  }

  // Delegate clicks on event meta links to scroll and close modal
  document.body.addEventListener('click', (e) => {
    const link = e.target.closest('a.event-meta-link');
    if (!link) return;

    const target = link.dataset.scrollTarget;
    if (!target) return;

    e.preventDefault();

    // Close modal if open
    if (window.eventModalInstance && window.eventModalInstance.modal && window.eventModalInstance.modal.classList.contains('active')) {
      window.eventModalInstance.close();
    }

    smoothScrollToTarget(target);
  });
});

