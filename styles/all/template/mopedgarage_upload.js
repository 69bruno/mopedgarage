document.addEventListener('DOMContentLoaded', function () {
    function initWidget(widget) {
        var dropzone = widget.querySelector('.mopedgarage-upload-dropzone');
        var input = widget.querySelector('.js-mopedgarage-file-input');
        var previewWrap = widget.querySelector('.mopedgarage-upload-preview-wrap');
        var preview = widget.querySelector('.mopedgarage-upload-preview');
        var replaceBtn = widget.querySelector('.js-mopedgarage-replace');
        var removeBtn = widget.querySelector('.js-mopedgarage-remove');
        var deleteFlag = widget.querySelector('.js-mopedgarage-delete-flag');
        var hasStoredPreview = widget.getAttribute('data-has-stored-preview') === '1';

        if (!dropzone || !input || !previewWrap || !preview) {
            return;
        }

        function openFileDialog() {
            input.click();
        }

        function showPreview(file) {
            if (!file || !file.type || file.type.indexOf('image/') !== 0) {
                return;
            }

            var reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                previewWrap.classList.remove('is-hidden');
                widget.classList.add('has-preview');
                widget.classList.remove('has-stored-preview');
                if (deleteFlag) {
                    deleteFlag.value = '0';
                }
            };
            reader.readAsDataURL(file);
        }

        function clearPreview() {
            preview.src = '';
            previewWrap.classList.add('is-hidden');
            widget.classList.remove('has-preview');
            widget.classList.remove('has-stored-preview');
        }

        function resetToStoredPreview() {
            if (hasStoredPreview && preview.getAttribute('src')) {
                previewWrap.classList.remove('is-hidden');
                widget.classList.add('has-preview');
                widget.classList.add('has-stored-preview');
                return;
            }
            clearPreview();
        }

        function assignFiles(files) {
            if (!files || !files.length) {
                return;
            }

            try {
                var dataTransfer = new DataTransfer();
                dataTransfer.items.add(files[0]);
                input.files = dataTransfer.files;
            } catch (error) {
                // Some browsers do not allow assigning FileList programmatically.
            }

            showPreview(files[0]);
        }

        input.addEventListener('change', function () {
            if (input.files && input.files[0]) {
                showPreview(input.files[0]);
                return;
            }
            resetToStoredPreview();
        });

        dropzone.addEventListener('click', function () {
            openFileDialog();
        });

        dropzone.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openFileDialog();
            }
        });

        ['dragenter', 'dragover'].forEach(function (eventName) {
            dropzone.addEventListener(eventName, function (event) {
                event.preventDefault();
                event.stopPropagation();
                widget.classList.add('is-dragover');
            });
        });

        ['dragleave', 'dragend', 'drop'].forEach(function (eventName) {
            dropzone.addEventListener(eventName, function (event) {
                event.preventDefault();
                event.stopPropagation();
                if (eventName !== 'drop') {
                    widget.classList.remove('is-dragover');
                }
            });
        });

        dropzone.addEventListener('drop', function (event) {
            widget.classList.remove('is-dragover');
            if (event.dataTransfer && event.dataTransfer.files && event.dataTransfer.files.length) {
                assignFiles(event.dataTransfer.files);
            }
        });

        if (replaceBtn) {
            replaceBtn.addEventListener('click', function () {
                if (deleteFlag) {
                    deleteFlag.value = '0';
                }
                openFileDialog();
            });
        }

        if (removeBtn) {
            removeBtn.addEventListener('click', function () {
                input.value = '';
                clearPreview();
                if (deleteFlag) {
                    deleteFlag.value = '1';
                }
            });
        }
    }

    document.querySelectorAll('.mopedgarage-upload-widget').forEach(initWidget);
});
