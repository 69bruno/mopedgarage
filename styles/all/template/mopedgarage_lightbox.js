document.addEventListener('DOMContentLoaded', function () {
    var lightbox = document.getElementById('mopedgarage-lightbox');
    if (!lightbox) {
        return;
    }

    var image = document.getElementById('mopedgarage-lightbox-image');
    var caption = document.getElementById('mopedgarage-lightbox-caption');
    var closeButtons = lightbox.querySelectorAll('.js-mopedgarage-lightbox-close');
    var prevButton = lightbox.querySelector('.js-mopedgarage-lightbox-prev');
    var nextButton = lightbox.querySelector('.js-mopedgarage-lightbox-next');
    var lastTrigger = null;
    var currentGroup = [];
    var currentIndex = 0;

    function getTriggersForGroup(groupName) {
        if (!groupName) {
            return [];
        }
        return Array.prototype.slice.call(
            document.querySelectorAll('.js-mopedgarage-lightbox-trigger[data-lightbox-group="' + groupName.replace(/"/g, '\\"') + '"]')
        );
    }

    function renderCurrent() {
        if (!currentGroup.length) {
            return;
        }

        var trigger = currentGroup[currentIndex];
        if (!trigger) {
            return;
        }

        var url = trigger.getAttribute('data-lightbox-image') || '';
        var title = trigger.getAttribute('data-lightbox-title') || '';

        image.src = url;
        image.alt = title;
        caption.textContent = title;

        if (prevButton) {
            prevButton.style.display = currentGroup.length > 1 ? 'flex' : 'none';
        }
        if (nextButton) {
            nextButton.style.display = currentGroup.length > 1 ? 'flex' : 'none';
        }
    }

    function openLightbox(trigger) {
        if (!trigger) {
            return;
        }

        var groupName = trigger.getAttribute('data-lightbox-group') || '';
        currentGroup = getTriggersForGroup(groupName);

        if (!currentGroup.length) {
            currentGroup = [trigger];
        }

        currentIndex = currentGroup.indexOf(trigger);
        if (currentIndex < 0) {
            currentIndex = 0;
        }

        lastTrigger = trigger;
        renderCurrent();

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

    function showPrev() {
        if (!currentGroup.length) {
            return;
        }
        currentIndex = (currentIndex - 1 + currentGroup.length) % currentGroup.length;
        renderCurrent();
    }

    function showNext() {
        if (!currentGroup.length) {
            return;
        }
        currentIndex = (currentIndex + 1) % currentGroup.length;
        renderCurrent();
    }

    document.addEventListener('click', function (event) {
        var trigger = event.target.closest('.js-mopedgarage-lightbox-trigger');
        if (trigger) {
            event.preventDefault();
            openLightbox(trigger);
            return;
        }

        if (event.target.closest('.js-mopedgarage-lightbox-close')) {
            event.preventDefault();
            closeLightbox();
            return;
        }

        if (event.target.closest('.js-mopedgarage-lightbox-prev')) {
            event.preventDefault();
            showPrev();
            return;
        }

        if (event.target.closest('.js-mopedgarage-lightbox-next')) {
            event.preventDefault();
            showNext();
        }
    });

    closeButtons.forEach(function (button) {
        button.addEventListener('click', closeLightbox);
    });

    document.addEventListener('keydown', function (event) {
        if (lightbox.hidden) {
            return;
        }

        if (event.key === 'Escape') {
            closeLightbox();
            return;
        }

        if (event.key === 'ArrowLeft') {
            showPrev();
            return;
        }

        if (event.key === 'ArrowRight') {
            showNext();
        }
    });
});
