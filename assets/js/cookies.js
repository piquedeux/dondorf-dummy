document.addEventListener('DOMContentLoaded', () => {
  // Cookie Banner
  const cookieBanner = document.getElementById('cookieBanner');
  const acceptCookies = document.getElementById('acceptCookies');
  const declineCookies = document.getElementById('declineCookies');
  const cookieSettingsTrigger = document.getElementById('cookieSettingsTrigger');

  // Exit if cookie banner elements don't exist
  if (!cookieBanner || !acceptCookies || !declineCookies) {
    return;
  }

  const cookieConsent = localStorage.getItem('cookieConsent');

  const openCookieBanner = () => {
    cookieBanner.classList.add('show');
  };

  if (!cookieConsent) {
    openCookieBanner();
  }

  acceptCookies.addEventListener('click', () => {
    localStorage.setItem('cookieConsent', 'accepted');
    cookieBanner.classList.remove('show');
  });

  declineCookies.addEventListener('click', () => {
    localStorage.setItem('cookieConsent', 'declined');
    cookieBanner.classList.remove('show');
  });

  if (cookieSettingsTrigger) {
    cookieSettingsTrigger.addEventListener('click', (event) => {
      event.preventDefault();
      localStorage.removeItem('cookieConsent');
      openCookieBanner();
    });
  }
});
