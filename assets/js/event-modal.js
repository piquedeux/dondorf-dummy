// Event Modal functionality
class EventModal {
  constructor() {
    this.modal = null;
    this.currentLang = document.documentElement.lang || "de";

    this.init();
  }

  init() {
    this.createModal();
    this.attachEventListeners();

    // Listen for language changes
    const observer = new MutationObserver(() => {
      this.currentLang = document.documentElement.lang || "de";
    });
    observer.observe(document.documentElement, {
      attributes: true,
      attributeFilter: ["lang"],
    });
  }

  createModal() {
    // Create modal element
    this.modal = document.createElement("div");
    this.modal.className = "event-modal";
    this.modal.id = "event-modal";
    this.modal.innerHTML = `
      <div class="event-modal-content">
        <button class="event-modal-close" aria-label="Close">&times;</button>
        <div class="event-modal-header">
          <h2 class="event-modal-title"></h2>
          <div class="event-modal-meta"></div>
        </div>
        <div class="event-modal-description"></div>
        <div class="event-modal-gallery"></div>
      </div>
    `;
    document.body.appendChild(this.modal);
  }

  attachEventListeners() {
    // Close on background click
    this.modal.addEventListener("click", (e) => {
      if (e.target === this.modal) {
        this.close();
      }
    });

    // Close button
    const closeBtn = this.modal.querySelector(".event-modal-close");
    closeBtn.addEventListener("click", () => this.close());

    // Close on escape key
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && this.modal.classList.contains("active")) {
        this.close();
      }
    });
  }

  escape(value) {
    return String(value ?? "").replace(/[&<>"']/g, (char) => ({"&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;"}[char]));
  }

  open(eventData) {
    const title =
      this.currentLang === "de" ? eventData.title_de : eventData.title_en;
    const description =
      this.currentLang === "de"
        ? eventData.description_de
        : eventData.description_en;
    const descriptionHtml =
      this.currentLang === "de"
        ? eventData.description_de_html
        : eventData.description_en_html;
    const initiativeName =
      eventData.initiative_name || eventData.initiative || "";
    const initiativeSlug = eventData.initiative_slug || "";
    const roomLabel = eventData.room_label || "";
    const roomId = eventData.room_id || "";
    const eventTime =
      this.currentLang === "de"
        ? eventData.time || ""
        : eventData.time_en || eventData.time || "";

    // Disable body scroll
    document.body.style.overflow = "hidden";

    // Set title
    this.modal.querySelector(".event-modal-title").textContent =
      title || eventData.title || "";

    // Set meta information
    const metaHtml = `
      ${eventData.cancelled ? `<p class="event-cancelled">${this.currentLang === "de" ? "Fällt aus" : "Cancelled"}</p>` : ""}
      ${
        eventData.date
          ? `
        <div class="event-meta-item">
          <span class="event-meta-label">${this.currentLang === "de" ? "Datum:" : "Date:"}</span>
          <span>${this.formatDate(new Date(eventData.date))}</span>
        </div>
      `
          : ""
      }
      ${
        eventTime
          ? `
        <div class="event-meta-item">
          <span class="event-meta-label">${this.currentLang === "de" ? "Uhrzeit:" : "Time:"}</span>
          <span>${this.escape(eventTime)}</span>
        </div>
      `
          : ""
      }
      ${
        roomLabel || eventData.location
          ? `
        <div class="event-meta-item">
          <span class="event-meta-label">${this.currentLang === "de" ? "Ort:" : "Location:"}</span>
          ${roomLabel ? `<a href="#floorplan-section" class="event-link" data-room-id="${roomId}">${roomLabel}</a>` : `<span>${this.escape(eventData.location)}</span>`}
        </div>
      `
          : ""
      }
      ${
        initiativeName
          ? `
        <div class="event-meta-item">
          <span class="event-meta-label">${this.currentLang === "de" ? "Initiative:" : "Initiative:"}</span>
          <a href="#collective-${initiativeSlug || "section"}" class="event-link" data-collective-slug="${initiativeSlug}">${initiativeName}</a>
        </div>
      `
          : ""
      }
      ${
        eventData.social_links && eventData.social_links.length > 0
          ? `
        <div class="event-meta-item event-socials">
          <span class="event-meta-label">${this.currentLang === "de" ? "Links:" : "Links:"}</span>
          <span>
            ${eventData.social_links.map((link) => `<a href="${link.url}" target="_blank" rel="noopener">${link.label}</a>`).join(" &nbsp; ")}
          </span>
        </div>
      `
          : ""
      }
    `;

    const metaContainer = this.modal.querySelector(".event-modal-meta");
    metaContainer.innerHTML = metaHtml;

    // Wire up deep links to collective cards
    metaContainer
      .querySelectorAll("a[data-collective-slug]")
      .forEach((link) => {
        link.addEventListener("click", (e) => {
          e.preventDefault();
          const slug = link.dataset.collectiveSlug;
          this.close();
          if (slug && typeof window.focusCollectiveCard === "function") {
            window.focusCollectiveCard(slug);
          } else {
            const target = document.getElementById("initiatives-section");
            if (target)
              target.scrollIntoView({ behavior: "smooth", block: "start" });
          }
        });
      });

    // Wire up deep links to floorplan rooms
    metaContainer.querySelectorAll("a[data-room-id]").forEach((link) => {
      link.addEventListener("click", (e) => {
        e.preventDefault();
        const id = link.dataset.roomId;
        this.close();
        if (id && typeof window.highlightFloorplanRoom === "function") {
          window.highlightFloorplanRoom(id);
        } else {
          const target = document.getElementById("floorplan-section");
          if (target)
            target.scrollIntoView({ behavior: "smooth", block: "start" });
        }
      });
    });

    // Set description - hide container if empty
    const descriptionEl = this.modal.querySelector(".event-modal-description");
    const descText = description || eventData.description || "";
    if (descriptionHtml) {
      descriptionEl.innerHTML = descriptionHtml;
      descriptionEl.style.display = "block";
    } else if (descText) {
      descriptionEl.textContent = descText;
      descriptionEl.style.display = "block";
    } else {
      descriptionEl.style.display = "none";
    }

    // Set gallery
    if (eventData.images && eventData.images.length > 0) {
      const galleryHtml = `
        <h3 class="event-gallery-title">${this.currentLang === "de" ? "Bilder" : "Images"}</h3>
        <div class="event-gallery-scroll">
          ${eventData.images
            .map(
              (img) => `
            <img src="${img.url}" alt="${img.alt || title}" class="event-gallery-image">
          `,
            )
            .join("")}
        </div>
      `;
      this.modal.querySelector(".event-modal-gallery").innerHTML = galleryHtml;
    } else {
      this.modal.querySelector(".event-modal-gallery").innerHTML = "";
    }

    // Show modal
    this.modal.classList.add("active");
  }

  close() {
    if (
      this.modal.contains(document.activeElement) &&
      document.activeElement instanceof HTMLElement
    ) {
      document.activeElement.blur();
    }
    this.modal.classList.remove("active");
    // Re-enable body scroll
    document.body.style.overflow = "";
  }

  formatDate(date) {
    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const year = date.getFullYear();
    return `${day}.${month}.${year}`;
  }
}

// Make modal globally accessible
let eventModalInstance = null;

window.openEventModal = function (eventData) {
  if (!eventModalInstance) {
    eventModalInstance = new EventModal();
  }
  eventModalInstance.open(eventData);
};

// Initialize modal when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
  eventModalInstance = new EventModal();
});
