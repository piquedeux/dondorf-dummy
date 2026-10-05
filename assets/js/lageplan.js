document.addEventListener("DOMContentLoaded", () => {
  const trigger = document.getElementById("lageplanImageTrigger");
  const lightbox = document.getElementById("lageplanLightbox");
  const closeButton = document.getElementById("lageplanLightboxClose");
  const stage = document.getElementById("lageplanLightboxStage");
  const image = document.getElementById("lageplanLightboxImage");
  const zoomInButton = document.getElementById("lageplanZoomIn");
  const zoomOutButton = document.getElementById("lageplanZoomOut");

  if (!trigger || !lightbox || !stage || !image) {
    return;
  }

  const minZoom = 1;
  const maxZoom = 4;
  const zoomStep = 0.5;
  let zoom = 1;
  let previouslyFocusedElement = null;

  const applyZoom = () => {
    image.style.transform = `scale(${zoom})`;
    stage.style.cursor = "zoom-in";
  };

  const setZoom = (value) => {
    zoom = Math.max(minZoom, Math.min(maxZoom, value));
    applyZoom();
  };

  const openLightbox = () => {
    previouslyFocusedElement = document.activeElement;
    lightbox.classList.add("active");
    lightbox.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
    setZoom(1);
    if (closeButton) {
      closeButton.focus({ preventScroll: true });
    }
  };

  const closeLightbox = () => {
    if (
      lightbox.contains(document.activeElement) &&
      document.activeElement instanceof HTMLElement
    ) {
      document.activeElement.blur();
    }
    lightbox.classList.remove("active");
    lightbox.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
    setZoom(1);
    if (previouslyFocusedElement instanceof HTMLElement) {
      previouslyFocusedElement.focus({ preventScroll: true });
    } else {
      trigger.focus({ preventScroll: true });
    }
  };

  trigger.addEventListener("click", openLightbox);

  if (closeButton) {
    closeButton.addEventListener("click", closeLightbox);
  }

  if (zoomInButton) {
    zoomInButton.addEventListener("click", (event) => {
      event.stopPropagation();
      setZoom(zoom + zoomStep);
    });
  }

  if (zoomOutButton) {
    zoomOutButton.addEventListener("click", (event) => {
      event.stopPropagation();
      setZoom(zoom - zoomStep);
    });
  }

  stage.addEventListener("click", () => {
    const nextZoom = zoom >= maxZoom ? 1 : zoom + zoomStep;
    setZoom(nextZoom);
  });

  lightbox.addEventListener("click", (event) => {
    if (event.target === lightbox) {
      closeLightbox();
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && lightbox.classList.contains("active")) {
      closeLightbox();
    }
  });
});
