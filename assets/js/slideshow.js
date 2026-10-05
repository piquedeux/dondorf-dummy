// Slideshow functionality
document.addEventListener('DOMContentLoaded', () => {
    const slideshows = document.querySelectorAll('.slideshow');

    slideshows.forEach((slideshow) => {
        const wrapper = slideshow.querySelector('.slideshow-wrapper');
        const slides = slideshow.querySelectorAll('.slide');
        const indicators = slideshow.querySelectorAll('.indicator');
        const slideImages = slideshow.querySelectorAll('.slide img');

        const loadImage = (img) => {
            if (!img || !img.dataset.src) {
                return;
            }

            img.src = img.dataset.src;
            img.removeAttribute('data-src');
        };

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries, io) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    const img = entry.target;
                    loadImage(img);
                    io.unobserve(img);
                });
            }, {
                root: wrapper,
                rootMargin: '100px 0px',
                threshold: 0.01
            });

            slideImages.forEach((img) => {
                if (img.dataset.src) {
                    observer.observe(img);
                }
            });
        } else {
            slideImages.forEach(loadImage);
        }

        const getCurrentIndex = () => {
            const slideWidth = wrapper.offsetWidth;
            if (!slideWidth) {
                return 0;
            }

            return Math.round(wrapper.scrollLeft / slideWidth);
        };

        const goToIndex = (index) => {
            const slideWidth = wrapper.offsetWidth;
            const maxIndex = Math.max(slides.length - 1, 0);
            const targetIndex = Math.max(0, Math.min(index, maxIndex));

            const currentImage = slides[targetIndex]?.querySelector('img');
            const nextImage = slides[targetIndex + 1]?.querySelector('img');
            const prevImage = slides[targetIndex - 1]?.querySelector('img');
            loadImage(currentImage);
            loadImage(nextImage);
            loadImage(prevImage);

            wrapper.scrollTo({
                left: targetIndex * slideWidth,
                behavior: 'smooth'
            });
        };

        // Update indicators on scroll
        const updateIndicators = () => {
            const scrollLeft = wrapper.scrollLeft;
            const slideWidth = wrapper.offsetWidth;
            const currentIndex = Math.round(scrollLeft / slideWidth);

            indicators.forEach((indicator, i) => {
                if (i === currentIndex) {
                    indicator.classList.add('active');
                    indicator.setAttribute('aria-current', 'true');
                } else {
                    indicator.classList.remove('active');
                    indicator.removeAttribute('aria-current');
                }
            });
        };

        // Listen to scroll events
        let ticking = false;
        wrapper.addEventListener('scroll', () => {
            if (ticking) {
                return;
            }

            ticking = true;
            requestAnimationFrame(() => {
                updateIndicators();
                ticking = false;
            });
        }, { passive: true });

        // Click left/right side to navigate slides
        wrapper.addEventListener('click', (event) => {
            if (slides.length < 2) {
                return;
            }

            const bounds = wrapper.getBoundingClientRect();
            const clickX = event.clientX - bounds.left;
            const isLeftSide = clickX < bounds.width / 2;
            const currentIndex = getCurrentIndex();

            goToIndex(isLeftSide ? currentIndex - 1 : currentIndex + 1);
        });

        // Initialize first indicator
        if (indicators.length > 0) {
            indicators[0].classList.add('active');
        }
    });
});
