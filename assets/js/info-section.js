// Info Section - Animate on Viewport Entry
document.addEventListener('DOMContentLoaded', () => {
  const infoContent = document.getElementById('info-content');

  if (infoContent) {
    // Function to activate the content
    const activateContent = () => {
      infoContent.classList.add('active');
      const items = infoContent.querySelectorAll('.info-item');
      items.forEach((item, index) => {
        item.style.setProperty('--item-index', index);
      });
    };

    // Create intersection observer to detect when section comes into viewport
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting && !infoContent.classList.contains('active')) {
          activateContent();
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });

    observer.observe(infoContent);

    // Check if element is already in viewport on load
    setTimeout(() => {
      const rect = infoContent.getBoundingClientRect();
      if (rect.top < window.innerHeight && rect.bottom > 0) {
        activateContent();
        observer.unobserve(infoContent);
      }
    }, 100);
  }
});
