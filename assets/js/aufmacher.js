// Collapsible Aufmacher Text
document.addEventListener('DOMContentLoaded', () => {
  const aufmacherToggle = document.querySelector('.aufmacher-toggle');
  const aufmacherContent = document.querySelector('.aufmacher-text-content');

  if (aufmacherToggle && aufmacherContent) {
    const brandColors = ['#CA492C', '#4E6858', '#3D617D', '#718D7B'];
    let colorIndex = 0;
    let mobileColorTimer;

    const setCurrentColor = () => {
      aufmacherToggle.style.setProperty('--aufmacher-current-color', brandColors[colorIndex]);
    };

    const stepToNextColor = () => {
      colorIndex = (colorIndex + 1) % brandColors.length;
      setCurrentColor();
    };

    setCurrentColor();

    // Desktop: each hover event advances to the next brand color.
    aufmacherToggle.addEventListener('mouseenter', stepToNextColor);

    // Mobile: no hover available, so rotate every 0.5s.
    if (window.matchMedia('(hover: none), (pointer: coarse)').matches) {
      mobileColorTimer = window.setInterval(stepToNextColor, 1000);
      window.addEventListener('beforeunload', () => {
        if (mobileColorTimer) {
          window.clearInterval(mobileColorTimer);
        }
      });
    }

    const setExpanded = (expanded) => {
      aufmacherToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    };

    const expand = () => {
      aufmacherContent.classList.add('is-open');
      const targetHeight = aufmacherContent.scrollHeight;
      aufmacherContent.style.height = `${targetHeight}px`;
      setExpanded(true);
    };

    const collapse = () => {
      const currentHeight = aufmacherContent.scrollHeight;
      aufmacherContent.style.height = `${currentHeight}px`;

      requestAnimationFrame(() => {
        aufmacherContent.style.height = '0px';
        aufmacherContent.classList.remove('is-open');
      });

      setExpanded(false);
    };

    aufmacherContent.style.height = '0px';
    setExpanded(false);

    aufmacherContent.addEventListener('transitionend', (event) => {
      if (event.propertyName !== 'height') {
        return;
      }

      if (aufmacherToggle.getAttribute('aria-expanded') === 'true') {
        aufmacherContent.style.height = 'auto';
      }
    });

    aufmacherToggle.addEventListener('click', () => {
      const isExpanded = aufmacherToggle.getAttribute('aria-expanded') === 'true';
      if (isExpanded) {
        collapse();
      } else {
        if (aufmacherContent.style.height === 'auto') {
          aufmacherContent.style.height = `${aufmacherContent.scrollHeight}px`;
        }
        expand();
      }
    });
  }
});
