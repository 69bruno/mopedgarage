document.addEventListener('DOMContentLoaded', function () {
    var sliders = document.querySelectorAll('[data-mopedgarage-slider]');

    sliders.forEach(function (slider) {
        var slides = slider.querySelectorAll('[data-mopedgarage-slide]');
        if (!slides.length) {
            return;
        }

        var current = 0;
        var currentLabel = slider.querySelector('[data-mopedgarage-current]');
        var prevButton = slider.querySelector('[data-mopedgarage-prev]');
        var nextButton = slider.querySelector('[data-mopedgarage-next]');

        function render() {
            slides.forEach(function (slide, index) {
                slide.classList.toggle('is-active', index === current);
            });

            if (currentLabel) {
                currentLabel.textContent = String(current + 1);
            }
        }

        if (prevButton) {
            prevButton.addEventListener('click', function () {
                current = (current - 1 + slides.length) % slides.length;
                render();
            });
        }

        if (nextButton) {
            nextButton.addEventListener('click', function () {
                current = (current + 1) % slides.length;
                render();
            });
        }

        render();
    });
});
