// Floorplan functionality
class Floorplan {
  constructor() {
    this.currentLang = document.documentElement.lang || 'de';
    this.init();
  }

  init() {
    this.attachEventListeners();

    // Listen for language changes
    const observer = new MutationObserver(() => {
      this.currentLang = document.documentElement.lang || 'de';
      this.updateRoomNames();
    });
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['lang'] });
  }

  attachEventListeners() {
    // Room hover and click handlers are handled by CSS and data attributes
    const rooms = document.querySelectorAll('.room');
    rooms.forEach(room => {
      room.addEventListener('click', () => {
        const roomId = room.dataset.roomId;
        const shouldOpenCollective = roomId && typeof window.focusCollectiveByRoom === 'function';

        this.highlightRoom(roomId, { scrollToFloorplan: !shouldOpenCollective });

        if (shouldOpenCollective) {
          window.focusCollectiveByRoom(roomId);
        }
      });
    });
  }

  highlightRoom(roomId, options = {}) {
    const { scrollToFloorplan = true } = options;

    // Remove previous highlights
    document.querySelectorAll('.room.highlighted').forEach(room => {
      room.classList.remove('highlighted');
    });

    // Highlight the selected room
    const room = document.querySelector(`.room[data-room-id="${roomId}"]`);
    if (room) {
      room.classList.add('highlighted');

      // Scroll to floorplan
      const floorplan = document.getElementById('floorplan-section');
      if (scrollToFloorplan && floorplan) {
        floorplan.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }

      // Remove highlight after 3 seconds
      setTimeout(() => {
        room.classList.remove('highlighted');
      }, 3000);
    }
  }

  updateRoomNames() {
    // Update room names based on current language if needed
    // This can be extended if you have language-specific room names
  }
}

// Make highlightRoom globally accessible for studio links
window.highlightFloorplanRoom = function (roomId) {
  if (window.floorplanInstance) {
    window.floorplanInstance.highlightRoom(roomId);
  }
};

// Initialize floorplan when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  if (document.getElementById('floorplan-section')) {
    window.floorplanInstance = new Floorplan();
  }
});
