// Collective Cards functionality
class CollectiveCards {
    constructor() {
        this.currentLang = document.documentElement.lang || 'de';
        this.activeStudios = new Set();
        this.activeTags = new Set();
        this.init();
    }

    init() {
        this.attachEventListeners();

        const observer = new MutationObserver(() => {
            this.currentLang = document.documentElement.lang || 'de';
            this.activeTags.clear();
            this.renderFilters();
        });
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['lang'] });
    }

    attachEventListeners() {
        document.querySelectorAll('.collective-header').forEach((header) => {
            header.addEventListener('click', () => {
                const card = header.closest('.collective-card');
                this.toggleCard(card);
            });
        });

        document.querySelectorAll('.collective-studio-link').forEach((link) => {
            link.addEventListener('click', (event) => {
                event.stopPropagation();
                const studioId = link.dataset.studio;
                if (studioId && typeof window.highlightFloorplanRoom === 'function') {
                    window.highlightFloorplanRoom(studioId);
                }
            });
        });

        this.renderFilters();
    }

    toggleCard(card) {
        if (!card) return;

        const wasExpanded = card.classList.contains('expanded');
        document.querySelectorAll('.collective-card').forEach((currentCard) => {
            currentCard.classList.remove('expanded');
        });

        if (!wasExpanded) {
            card.classList.add('expanded');
        }
    }

    collectData() {
        const cards = Array.from(document.querySelectorAll('.collective-card'));
        const studios = new Set();
        const tags = new Set();
        const roomNames = {};

        cards.forEach((card) => {
            const studioList = (card.dataset.studio || '')
                .split(',')
                .map((studio) => studio.trim())
                .filter((studio) => studio && studio !== 'none');
            const roomDeList = (card.dataset.roomDe || '').split(',').map((name) => name.trim());
            const roomEnList = (card.dataset.roomEn || '').split(',').map((name) => name.trim());

            studioList.forEach((studio, index) => {
                studios.add(studio);
                const fallback = studio.toUpperCase().replace(/-/g, ' ');
                roomNames[studio] = {
                    de: roomDeList[index] || fallback,
                    en: roomEnList[index] || roomDeList[index] || fallback
                };
            });

            const tagAttr = this.currentLang === 'en'
                ? (card.dataset.tagsEn || card.dataset.tagsDe || '')
                : (card.dataset.tagsDe || card.dataset.tagsEn || '');

            tagAttr
                .split(',')
                .map((tag) => tag.trim())
                .filter(Boolean)
                .forEach((tag) => tags.add(tag));
        });

        return {
            cards,
            studios: Array.from(studios).sort(),
            tags: Array.from(tags).sort(),
            roomNames
        };
    }

    renderFilters() {
        const container = document.getElementById('collectives-filters');
        if (!container) return;

        const { studios, tags, roomNames } = this.collectData();
        if (studios.length === 0 && tags.length === 0) {
            container.innerHTML = '';
            return;
        }

        let html = '';
        html += `<div class="filter-bar">
      <button class="filter-toggle" id="collectives-filter-toggle">
        <span class="lang-de">Filter</span>
        <span class="lang-en" style="display: none">Filter</span>
      </button>
      <div class="filter-panel" id="collectives-filter-panel" data-open="false">
        <div class="filter-section">
          <div class="filter-section-header">
            <span>${this.currentLang === 'de' ? 'Räume' : 'Rooms'}</span>
          </div>
          <ul class="filter-tag-list">`;

        studios.forEach((studio) => {
            const checked = this.activeStudios.has(studio) ? 'checked' : '';
            const displayName = roomNames[studio]
                ? roomNames[studio][this.currentLang]
                : studio.toUpperCase().replace(/-/g, ' ');
            html += `<li><label class="filter-tag-item"><input type="checkbox" data-studio-filter="${studio}" ${checked}> <span>${displayName}</span></label></li>`;
        });

        html += `</ul>
        </div>
        <div class="filter-section">
          <div class="filter-section-header">
            <span>${this.currentLang === 'de' ? 'Kategorien' : 'Categories'}</span>
          </div>
          <ul class="filter-tag-list">`;

        tags.forEach((tag) => {
            const checked = this.activeTags.has(tag) ? 'checked' : '';
            html += `<li><label class="filter-tag-item"><input type="checkbox" data-tag-filter="${tag}" ${checked}> <span>${tag}</span></label></li>`;
        });

        html += `</ul>
        </div>
      </div>
    </div>`;

        if (this.activeStudios.size > 0 || this.activeTags.size > 0) {
            const pills = [];
            this.activeStudios.forEach((studio) => {
                const label = roomNames[studio]
                    ? roomNames[studio][this.currentLang]
                    : studio.toUpperCase().replace(/-/g, ' ');
                pills.push(`<span class="tag-pill" data-remove-studio="${studio}">${label} ×</span>`);
            });
            this.activeTags.forEach((tag) => {
                pills.push(`<span class="tag-pill" data-remove-tag="${tag}">${tag} ×</span>`);
            });
            html += `<div class="active-tag-pills">${pills.join('')}</div>`;
        }

        container.innerHTML = html;

        const toggleButton = container.querySelector('#collectives-filter-toggle');
        const panel = container.querySelector('#collectives-filter-panel');

        if (toggleButton && panel) {
            toggleButton.addEventListener('click', () => {
                const isOpen = panel.getAttribute('data-open') === 'true';
                panel.setAttribute('data-open', isOpen ? 'false' : 'true');
            });

            panel.querySelectorAll('input[data-studio-filter]').forEach((checkbox) => {
                checkbox.addEventListener('change', (event) => {
                    const value = event.target.dataset.studioFilter;
                    if (event.target.checked) {
                        this.activeStudios.add(value);
                    } else {
                        this.activeStudios.delete(value);
                    }
                    this.applyFilters();
                });
            });

            panel.querySelectorAll('input[data-tag-filter]').forEach((checkbox) => {
                checkbox.addEventListener('change', (event) => {
                    const value = event.target.dataset.tagFilter;
                    if (event.target.checked) {
                        this.activeTags.add(value);
                    } else {
                        this.activeTags.delete(value);
                    }
                    this.applyFilters();
                });
            });
        }

        container.querySelectorAll('.tag-pill').forEach((pill) => {
            pill.addEventListener('click', () => {
                const studio = pill.dataset.removeStudio;
                const tag = pill.dataset.removeTag;

                if (studio) this.activeStudios.delete(studio);
                if (tag) this.activeTags.delete(tag);

                this.applyFilters();
            });
        });
    }

    applyFilters() {
        const { cards } = this.collectData();

        cards.forEach((card) => {
            const studioList = (card.dataset.studio || '')
                .split(',')
                .map((studio) => studio.trim())
                .filter((studio) => studio && studio !== 'none');
            const tagAttr = this.currentLang === 'en'
                ? (card.dataset.tagsEn || card.dataset.tagsDe || '')
                : (card.dataset.tagsDe || card.dataset.tagsEn || '');
            const tags = tagAttr.split(',').map((tag) => tag.trim()).filter(Boolean);

            let show = true;
            if (this.activeStudios.size > 0 && !studioList.some((studio) => this.activeStudios.has(studio))) show = false;
            if (show && this.activeTags.size > 0 && !tags.some((tag) => this.activeTags.has(tag))) show = false;

            card.style.display = show ? '' : 'none';
        });

        this.renderFilters();
    }

    focusCardByRoom(roomId) {
        if (!roomId || roomId === 'none') return false;

        if (this.activeStudios.size > 0 || this.activeTags.size > 0) {
            this.activeStudios.clear();
            this.activeTags.clear();
            this.applyFilters();
        }

        const card = Array.from(document.querySelectorAll('.collective-card')).find((currentCard) => {
            const studioList = (currentCard.dataset.studio || '')
                .split(',')
                .map((studio) => studio.trim());
            return studioList.includes(roomId);
        });
        if (!card) return false;

        document.querySelectorAll('.collective-card').forEach((currentCard) => {
            currentCard.classList.remove('expanded');
        });
        card.classList.add('expanded');

        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        const originalShadow = card.style.boxShadow;
        card.style.boxShadow = '0 0 0 3px rgba(0,0,0,0.25)';
        setTimeout(() => {
            card.style.boxShadow = originalShadow;
        }, 2000);

        return true;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('.collective-card')) {
        window.collectiveCardsInstance = new CollectiveCards();
    }
});

window.focusCollectiveCard = function (slug) {
    const card = document.querySelector(`.collective-card[data-collective-slug="${slug}"]`);
    if (!card) return;

    document.querySelectorAll('.collective-card').forEach((currentCard) => {
        currentCard.classList.remove('expanded');
    });
    card.classList.add('expanded');

    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
    const originalShadow = card.style.boxShadow;
    card.style.boxShadow = '0 0 0 3px rgba(0,0,0,0.25)';
    setTimeout(() => {
        card.style.boxShadow = originalShadow;
    }, 2000);
};

window.focusCollectiveByRoom = function (roomId) {
    if (window.collectiveCardsInstance && typeof window.collectiveCardsInstance.focusCardByRoom === 'function') {
        return window.collectiveCardsInstance.focusCardByRoom(roomId);
    }

    const card = Array.from(document.querySelectorAll('.collective-card')).find((currentCard) => {
        const studioList = (currentCard.dataset.studio || '')
            .split(',')
            .map((studio) => studio.trim());
        return studioList.includes(roomId);
    });
    if (!card) return false;

    document.querySelectorAll('.collective-card').forEach((currentCard) => {
        currentCard.classList.remove('expanded');
    });
    card.classList.add('expanded');
    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
    return true;
};
