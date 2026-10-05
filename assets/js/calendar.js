// Calendar functionality
class EventCalendar {
  constructor(calendarEvents, upcomingEvents, regularEvents, translations) {
    // keep original source events and normalize
    this.sourceEvents = this.normalizeEvents(calendarEvents);
    this.sourceUpcomingEvents = this.normalizeEvents(upcomingEvents);
    this.sourceRegularEvents = this.normalizeEvents(regularEvents);

    this.activeTags = new Set();
    this.activeInitiatives = new Set();
    this.translations = translations;
    this.currentDate = new Date();
    this.selectedDate = new Date();
    this.currentLang = document.documentElement.lang || "de";
    this.visibleEventsCount = 3; // Initial number of events to show

    this.init();
  }

  escape(value) {
    return String(value ?? "").replace(/[&<>"']/g, (char) => ({"&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;"}[char]));
  }

  normalizeEvents(events) {
    const source = Array.isArray(events) ? events : [];
    return source.map((e) => ({
      ...e,
      tags: Array.isArray(e.tags)
        ? e.tags.map((t) => t.trim()).filter(Boolean)
        : typeof e.tags === "string"
          ? e.tags
              .split(",")
              .map((t) => t.trim())
              .filter(Boolean)
          : [],
      social_links: Array.isArray(e.social_links) ? e.social_links : [],
    }));
  }

  init() {
    this.render();
    this.attachEventListeners();

    // Listen for language changes
    const observer = new MutationObserver(() => {
      this.currentLang = document.documentElement.lang || "de";
      this.render();
    });
    observer.observe(document.documentElement, {
      attributes: true,
      attributeFilter: ["lang"],
    });
  }

  attachEventListeners() {
    const prevBtn = document.getElementById("calendar-prev");
    const nextBtn = document.getElementById("calendar-next");
    const todayBtn = document.getElementById("calendar-today");

    if (prevBtn) {
      prevBtn.addEventListener("click", () => this.previousMonth());
    }
    if (nextBtn) {
      nextBtn.addEventListener("click", () => this.nextMonth());
    }
    if (todayBtn) {
      todayBtn.addEventListener("click", () => this.goToToday());
    }
  }

  previousMonth() {
    this.currentDate.setMonth(this.currentDate.getMonth() - 1);
    this.render();
  }

  nextMonth() {
    this.currentDate.setMonth(this.currentDate.getMonth() + 1);
    this.render();
  }

  goToToday() {
    this.currentDate = new Date();
    this.selectedDate = new Date();
    this.render();
  }

  getMonthName(date) {
    const months =
      this.currentLang === "de"
        ? [
            "Januar",
            "Februar",
            "März",
            "April",
            "Mai",
            "Juni",
            "Juli",
            "August",
            "September",
            "Oktober",
            "November",
            "Dezember",
          ]
        : [
            "January",
            "February",
            "March",
            "April",
            "May",
            "June",
            "July",
            "August",
            "September",
            "October",
            "November",
            "December",
          ];
    return months[date.getMonth()];
  }

  getWeekdayNames() {
    return this.currentLang === "de"
      ? ["Mo", "Di", "Mi", "Do", "Fr", "Sa", "So"]
      : ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
  }

  getDaysInMonth(date) {
    const year = date.getFullYear();
    const month = date.getMonth();
    return new Date(year, month + 1, 0).getDate();
  }

  getFirstDayOfMonth(date) {
    const year = date.getFullYear();
    const month = date.getMonth();
    const firstDay = new Date(year, month, 1).getDay();
    // Convert Sunday (0) to 7, then adjust so Monday is 1
    return firstDay === 0 ? 7 : firstDay;
  }

  isSameDay(date1, date2) {
    return (
      date1.getDate() === date2.getDate() &&
      date1.getMonth() === date2.getMonth() &&
      date1.getFullYear() === date2.getFullYear()
    );
  }

  // Return the events after applying active tag filters
  getFilteredEvents() {
    let events = this.getUpcomingEventsSource();
    if (this.activeTags && this.activeTags.size > 0) {
      events = events.filter(
        (ev) => ev.tags && ev.tags.some((t) => this.activeTags.has(t)),
      );
    }
    if (this.activeInitiatives && this.activeInitiatives.size > 0) {
      events = events.filter((ev) => {
        if (!ev.tags) return false;
        // Check if any tag matches the initiative slug directly
        return ev.tags.some((tag) => this.activeInitiatives.has(tag));
      });
    }
    return events;
  }

  getUpcomingEventsSource() {
    return this.sourceUpcomingEvents.length
      ? this.sourceUpcomingEvents
      : this.sourceEvents;
  }

  // Render the filter UI (dropdown panel)
  renderUpcomingFilters() {
    const container = document.getElementById("upcoming-filters");
    if (!container) return;
    const allTags = Array.from(
      new Set(this.getUpcomingEventsSource().flatMap((e) => e.tags || [])),
    );
    const rawInits = Array.isArray(window.calendarInitiatives)
      ? window.calendarInitiatives
      : [];
    const initiatives = rawInits.map((i) =>
      typeof i === "object" && i !== null
        ? i
        : { slug: String(i), title_de: String(i), title_en: String(i) },
    );
    if (allTags.length === 0 && initiatives.length === 0) {
      container.innerHTML = "";
      return;
    }

    // Toggle + panel
    let html = "";
    html += `<div class="filter-bar">
      <button class="filter-toggle" id="upcoming-filter-toggle">
        <span class="lang-de">Filter</span>
        <span class="lang-en" style="display: none">Filter</span>
      </button>
      <div class="filter-panel" id="upcoming-filter-panel" data-open="false">
          <div class="filter-section">
          <div class="filter-section-header">
            <span>${this.currentLang === "de" ? "Initiativen" : "Initiatives"}</span>
          </div>
          <ul class="filter-tag-list">`;
    initiatives.forEach((init) => {
      const label = this.currentLang === "de" ? init.title_de : init.title_en;
      const active = this.activeInitiatives.has(init.slug);
      html += `<li><label class="filter-tag-item"><input type="checkbox" data-initiative="${init.slug}" ${active ? "checked" : ""}> <span>${label}</span></label></li>`;
    });
    html += `</ul>
        </div>
        <div class="filter-section">
          <div class="filter-section-header">
            <span>${this.currentLang === "de" ? "Kategorien" : "Categories"}</span>
          </div>
          <ul class="filter-tag-list">`;
    allTags.forEach((tag) => {
      const active = this.activeTags.has(tag);
      html += `<li><label class="filter-tag-item"><input type="checkbox" data-tag="${tag}" ${active ? "checked" : ""}> <span>${tag}</span></label></li>`;
    });
    html += `</ul>
        </div>
      </div>
    </div>`;

    // Active pills
    const pills = [];
    if (this.activeInitiatives.size > 0) {
      this.activeInitiatives.forEach((slug) => {
        // find label
        const init = initiatives.find((i) => i.slug === slug);
        const label = init
          ? this.currentLang === "de"
            ? init.title_de
            : init.title_en
          : slug;
        pills.push(
          `<span class="tag-pill" data-pill-initiative="${slug}">${label} ×</span>`,
        );
      });
    }
    if (this.activeTags.size > 0) {
      this.activeTags.forEach((t) =>
        pills.push(`<span class="tag-pill" data-pill-tag="${t}">${t} ×</span>`),
      );
    }
    if (pills.length > 0) {
      html += `<div class="active-tag-pills">${pills.join("")}</div>`;
    }

    container.innerHTML = html;

    // Toggle behavior
    const toggleBtn = container.querySelector("#upcoming-filter-toggle");
    const panel = container.querySelector("#upcoming-filter-panel");
    if (toggleBtn && panel) {
      toggleBtn.addEventListener("click", () => {
        const open = panel.getAttribute("data-open") === "true";
        panel.setAttribute("data-open", open ? "false" : "true");
      });
    }

    // Checkbox listeners
    panel.querySelectorAll('input[type="checkbox"]').forEach((cb) => {
      cb.addEventListener("change", (e) => {
        const tag = e.target.dataset.tag;
        const init = e.target.dataset.initiative;
        if (tag) {
          if (e.target.checked) this.activeTags.add(tag);
          else this.activeTags.delete(tag);
        }
        if (init) {
          if (e.target.checked) this.activeInitiatives.add(init);
          else this.activeInitiatives.delete(init);
        }
        this.render();
      });
    });

    // Clear buttons
    const clearTags = container.querySelector("#clear-filters");
    if (clearTags)
      clearTags.addEventListener("click", () => {
        this.activeTags.clear();
        this.render();
      });
    const clearInits = container.querySelector("#clear-initiatives");
    if (clearInits)
      clearInits.addEventListener("click", () => {
        this.activeInitiatives.clear();
        this.render();
      });

    // Pills remove
    container.querySelectorAll(".tag-pill").forEach((pill) => {
      pill.addEventListener("click", () => {
        const tag = pill.dataset.pillTag;
        const init = pill.dataset.pillInitiative;
        if (tag) this.activeTags.delete(tag);
        if (init) this.activeInitiatives.delete(init);
        this.render();
      });
    });
  }

  getEventsForDate(date) {
    const events = this.sourceEvents;
    return events.filter((event) => {
      const eventDate = new Date(event.date);
      return this.isSameDay(eventDate, date);
    });
  }

  hasEventsOnDate(date) {
    return this.getEventsForDate(date).length > 0;
  }

  formatDate(date) {
    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const year = date.getFullYear();
    return `${day}.${month}.${year}`;
  }

  renderCalendarGrid() {
    const year = this.currentDate.getFullYear();
    const month = this.currentDate.getMonth();
    const daysInMonth = this.getDaysInMonth(this.currentDate);
    const firstDay = this.getFirstDayOfMonth(this.currentDate);

    let html = "";

    // Weekday headers
    const weekdays = this.getWeekdayNames();
    weekdays.forEach((day) => {
      html += `<div class="calendar-weekday">${day}</div>`;
    });

    // Empty cells before first day
    for (let i = 1; i < firstDay; i++) {
      const prevMonthDate = new Date(year, month, 1 - (firstDay - i));
      const dayNum = prevMonthDate.getDate();
      html += `<div class="calendar-day other-month" data-date="${prevMonthDate.toISOString()}">
        <span class="day-number">${dayNum}</span>
      </div>`;
    }

    // Days of current month
    for (let day = 1; day <= daysInMonth; day++) {
      const date = new Date(year, month, day);
      const isToday = this.isSameDay(date, new Date());
      const isSelected = this.isSameDay(date, this.selectedDate);
      const hasEvents = this.hasEventsOnDate(date);

      const classes = ["calendar-day"];
      if (isToday) classes.push("today");
      if (isSelected) classes.push("selected");
      if (hasEvents) classes.push("has-events");

      html += `<div class="${classes.join(" ")}" data-date="${date.toISOString()}">
        <span class="day-number">${day}</span>
      </div>`;
    }

    // Empty cells after last day
    const totalCells = firstDay - 1 + daysInMonth;
    const remainingCells = 7 - (totalCells % 7);
    if (remainingCells < 7) {
      for (let i = 1; i <= remainingCells; i++) {
        const nextMonthDate = new Date(year, month + 1, i);
        html += `<div class="calendar-day other-month" data-date="${nextMonthDate.toISOString()}">
          <span class="day-number">${i}</span>
        </div>`;
      }
    }

    return html;
  }

  renderSelectedDayEvents() {
    const events = this.getEventsForDate(this.selectedDate);
    const title =
      this.currentLang === "de"
        ? `Events am ${this.formatDate(this.selectedDate)}`
        : `Events on ${this.formatDate(this.selectedDate)}`;

    if (events.length === 0) {
      return `
        <h3 class="selected-day-title">${title}</h3>
        <p class="no-events">${this.currentLang === "de" ? "Keine Veranstaltungen" : "No events"}</p>
      `;
    }

    let html = `<h3 class="selected-day-title">${title}</h3>`;
    events.forEach((event) => {
      const eventTitle =
        this.currentLang === "de" ? event.title_de : event.title_en;
      const eventDesc =
        this.currentLang === "de" ? event.description_de : event.description_en;
      const eventJSON = encodeURIComponent(JSON.stringify(event));

      // Show tags on event card
      let tagsHtml = "";
      if (event.tags && event.tags.length > 0) {
        tagsHtml =
          '<div class="event-tags">' +
          event.tags
            .map((tag) => `<span class="event-tag">${tag}</span>`)
            .join("") +
          "</div>";
      }

      html += `
        <article class="event-card" data-event="${eventJSON}" style="cursor: pointer;">
          <h3>${this.escape(eventTitle || event.title)}</h3>
          ${event.cancelled ? `<p class="event-cancelled">${this.currentLang === "de" ? "Fällt aus" : "Cancelled"}</p>` : ""}
          <time>${this.formatDate(new Date(event.date))}${this.getEventTimeString(event) ? " · " + this.escape(this.getEventTimeString(event)) : ""}</time>
          ${tagsHtml}
          ${eventDesc || event.description ? `<p>${this.escape(eventDesc || event.description)}</p>` : ""}
        </article>
      `;
    });

    return html;
  }

  renderAllUpcomingEvents() {
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    // Use filtered events (respects active tags)
    // Only show events with valid dates
    const upcomingEvents = this.getFilteredEvents()
      .filter((event) => event.date && event.date.trim() !== "")
      .map((event) => ({ ...event, dateObj: new Date(event.date) }))
      .filter(
        (event) => !isNaN(event.dateObj.getTime()) && event.dateObj >= today,
      )
      .sort((a, b) => a.dateObj - b.dateObj);

    const title =
      this.currentLang === "de"
        ? "Alle kommenden Veranstaltungen"
        : "All Upcoming Events";

    if (upcomingEvents.length === 0) {
      const noEventsText =
        this.activeTags.size > 0 || this.activeInitiatives.size > 0
          ? this.currentLang === "de"
            ? "Keine Veranstaltungen für die ausgewählten Filter"
            : "No events for selected filters"
          : this.currentLang === "de"
            ? "Keine kommenden Veranstaltungen"
            : "No upcoming events";

      return `
        <h3 class="all-events-title">${title}</h3>
        <p class="no-events">${noEventsText}</p>
      `;
    }

    let html = `<h3 class="all-events-title">${title}</h3><div class="upcoming-events-list">`;

    // Show only visibleEventsCount events initially
    const eventsToShow = upcomingEvents.slice(0, this.visibleEventsCount);
    eventsToShow.forEach((event) => {
      const eventTitle =
        this.currentLang === "de" ? event.title_de : event.title_en;
      const eventDesc =
        this.currentLang === "de" ? event.description_de : event.description_en;
      const eventJSON = encodeURIComponent(JSON.stringify(event));

      // Show tags on event card
      let tagsHtml = "";
      if (event.tags && event.tags.length > 0) {
        tagsHtml =
          '<div class="event-tags">' +
          event.tags
            .map((tag) => `<span class="event-tag">${tag}</span>`)
            .join("") +
          "</div>";
      }

      html += `
        <article class="event-card" data-event="${eventJSON}" style="cursor: pointer;">
          <h3>${this.escape(eventTitle || event.title)}</h3>
          ${event.cancelled ? `<p class="event-cancelled">${this.currentLang === "de" ? "Fällt aus" : "Cancelled"}</p>` : ""}
          <time>${this.formatDate(event.dateObj)}${this.getEventTimeString(event) ? " · " + this.escape(this.getEventTimeString(event)) : ""}</time>
          ${tagsHtml}
          ${eventDesc || event.description ? `<p>${this.escape(eventDesc || event.description)}</p>` : ""}
        </article>
      `;
    });

    html += "</div>";

    // Add "Load More" button if there are more events
    if (upcomingEvents.length > this.visibleEventsCount) {
      const remainingCount = upcomingEvents.length - this.visibleEventsCount;
      html += `
        <button class="load-more-btn" id="load-more-events">
          <span class="lang-de">Mehr laden (${remainingCount} weitere)</span>
          <span class="lang-en" style="display: none">Load more (${remainingCount} more)</span>
        </button>
      `;
    }

    return html;
  }

  renderRegularEvents() {
    const titleData = window.regularEventsHeading || {};
    const title =
      this.currentLang === "de"
        ? titleData.de || "Regelmäßige Veranstaltungen"
        : titleData.en || "Regular Events";

    const regularEvents = this.sourceRegularEvents;

    if (regularEvents.length === 0) {
      return "";
    }

    let html = `<h3 class="regular-events-title">${title}</h3><div class="upcoming-events-list regular-events-list">`;

    regularEvents.forEach((event) => {
      const eventTitle =
        this.currentLang === "de" ? event.title_de : event.title_en;
      const eventDesc =
        this.currentLang === "de" ? event.description_de : event.description_en;
      const eventJSON = encodeURIComponent(JSON.stringify(event));

      let tagsHtml = "";
      if (event.tags && event.tags.length > 0) {
        tagsHtml =
          '<div class="event-tags">' +
          event.tags
            .map((tag) => `<span class="event-tag">${tag}</span>`)
            .join("") +
          "</div>";
      }

      const metaParts = [];
      const eventTime = this.getEventTimeString(event);
      if (eventTime) {
        metaParts.push(eventTime);
      }
      if (event.location) {
        metaParts.push(event.location);
      }

      html += `
        <article class="event-card regular-event-card" data-event="${eventJSON}" style="cursor: pointer;">
          <h3>${this.escape(eventTitle || event.title)}</h3>
          ${event.cancelled ? `<p class="event-cancelled">${this.currentLang === "de" ? "Fällt aus" : "Cancelled"}</p>` : ""}
          ${metaParts.length ? `<p class="event-card-meta">${this.escape(metaParts.join(" · "))}</p>` : ""}
          ${tagsHtml}
          ${eventDesc || event.description ? `<p>${this.escape(eventDesc || event.description)}</p>` : ""}
        </article>
      `;
    });

    html += "</div>";
    return html;
  }

  render() {
    // Update calendar header
    const monthYearEl = document.getElementById("calendar-month-year");
    if (monthYearEl) {
      monthYearEl.textContent = `${this.getMonthName(this.currentDate)} ${this.currentDate.getFullYear()}`;
    }

    // render tag dropdown filters for upcoming events only
    this.renderUpcomingFilters();

    // Update calendar grid
    const gridEl = document.getElementById("calendar-grid");
    if (gridEl) {
      gridEl.innerHTML = this.renderCalendarGrid();

      // Add click listeners to day cells
      gridEl.querySelectorAll(".calendar-day").forEach((dayEl) => {
        dayEl.addEventListener("click", () => {
          const dateStr = dayEl.dataset.date;
          this.selectedDate = new Date(dateStr);
          this.render();
        });
      });
    }

    // Update selected day events
    const selectedDayEl = document.getElementById("selected-day-events");
    if (selectedDayEl) {
      selectedDayEl.innerHTML = this.renderSelectedDayEvents();
      // Add click listeners to event cards
      selectedDayEl.querySelectorAll(".event-card").forEach((card) => {
        card.addEventListener("click", () => {
          const raw = card.dataset.event;
          let eventData;
          try {
            eventData = JSON.parse(decodeURIComponent(raw));
          } catch (err) {
            console.error(
              "Event modal parse error (selected day events):",
              err,
              raw,
            );
            eventData = {
              title_de: "Fehler",
              title_en: "Error",
              description_de:
                "Die Veranstaltungsdaten konnten nicht geladen werden.",
              description_en: "Could not load event data.",
            };
          }
          if (window.openEventModal) {
            window.openEventModal(eventData);
          }
        });
      });
    }

    // Update all upcoming events
    const allEventsEl = document.getElementById("all-upcoming-events");
    if (allEventsEl) {
      allEventsEl.innerHTML = this.renderAllUpcomingEvents();
      // Add click listeners to event cards
      allEventsEl.querySelectorAll(".event-card").forEach((card) => {
        card.addEventListener("click", () => {
          const raw = card.dataset.event;
          let eventData;
          try {
            eventData = JSON.parse(decodeURIComponent(raw));
          } catch (err) {
            console.error(
              "Event modal parse error (upcoming events):",
              err,
              raw,
            );
            eventData = {
              title_de: "Fehler",
              title_en: "Error",
              description_de:
                "Die Veranstaltungsdaten konnten nicht geladen werden.",
              description_en: "Could not load event data.",
            };
          }
          if (window.openEventModal) {
            window.openEventModal(eventData);
          }
        });
      });

      // Add listener for "Load More" button
      const loadMoreBtn = document.getElementById("load-more-events");
      if (loadMoreBtn) {
        loadMoreBtn.addEventListener("click", () => {
          this.visibleEventsCount += 3; // Show 3 more events
          this.render();
        });
      }
    }

    // Update regular recurring events section
    const regularEventsEl = document.getElementById("regular-events-list");
    if (regularEventsEl) {
      regularEventsEl.innerHTML = this.renderRegularEvents();
      regularEventsEl.querySelectorAll(".event-card").forEach((card) => {
        card.addEventListener("click", () => {
          const raw = card.dataset.event;
          let eventData;
          try {
            eventData = JSON.parse(decodeURIComponent(raw));
          } catch (err) {
            console.error(
              "Event modal parse error (regular events):",
              err,
              raw,
            );
            eventData = {
              title_de: "Fehler",
              title_en: "Error",
              description_de:
                "Die Veranstaltungsdaten konnten nicht geladen werden.",
              description_en: "Could not load event data.",
            };
          }
          if (window.openEventModal) {
            window.openEventModal(eventData);
          }
        });
      });
    }
  }

  getEventTimeString(event) {
    if (this.currentLang === "de") {
      return event.time || "";
    }

    return event.time_en || event.time || "";
  }
}

// Initialize calendar when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
  const calendarData = window.calendarEvents || [];
  const regularEventsData = window.regularEvents || [];
  const translations = window.calendarTranslations || {};

  if (document.getElementById("calendar-grid")) {
    new EventCalendar(
      calendarData,
      Array.isArray(window.upcomingEvents) ? window.upcomingEvents : [],
      regularEventsData,
      translations,
    );
  }
});
