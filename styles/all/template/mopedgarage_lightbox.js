document.addEventListener('DOMContentLoaded', function () {
    var lightbox = document.getElementById('mopedgarage-lightbox');
    if (!lightbox) {
        return;
    }

    var image = document.getElementById('mopedgarage-lightbox-image');
    var caption = document.getElementById('mopedgarage-lightbox-caption');
    var closeButtons = lightbox.querySelectorAll('.js-mopedgarage-lightbox-close');
    var lastTrigger = null;

    function openLightbox(url, title, trigger) {
        if (!url) {
            return;
        }

        lastTrigger = trigger || null;
        image.src = url;
        image.alt = title || '';
        caption.textContent = title || '';
        lightbox.hidden = false;
        lightbox.setAttribute('aria-hidden', 'false');
        document.documentElement.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';

        var closeButton = lightbox.querySelector('.mopedgarage-lightbox__close');
        if (closeButton) {
            closeButton.focus();
        }
    }

    function closeLightbox() {
        lightbox.hidden = true;
        lightbox.setAttribute('aria-hidden', 'true');
        image.src = '';
        image.alt = '';
        caption.textContent = '';
        document.documentElement.style.overflow = '';
        document.body.style.overflow = '';

        if (lastTrigger) {
            lastTrigger.focus();
        }
    }

    document.addEventListener('click', function (event) {
        var trigger = event.target.closest('.js-mopedgarage-lightbox-trigger');
        if (trigger) {
            event.preventDefault();
            openLightbox(trigger.getAttribute('data-lightbox-image'), trigger.getAttribute('data-lightbox-title') || '', trigger);
            return;
        }

        if (event.target.closest('.js-mopedgarage-lightbox-close')) {
            closeLightbox();
        }
    });

    closeButtons.forEach(function (button) {
        button.addEventListener('click', closeLightbox);
    });

    document.addEventListener('keydown', function (event) {
        if (!lightbox.hidden && event.key === 'Escape') {
            closeLightbox();
        }
    });
});
