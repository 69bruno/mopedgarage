(function () {
    'use strict';

    function normalizeText(value) {
        return (value || '').replace(/\s+/g, ' ').trim();
    }

    function getTextNodes(root) {
        var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
            acceptNode: function (node) {
                return normalizeText(node.nodeValue) ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
            }
        });
        var nodes = [];
        while (walker.nextNode()) {
            nodes.push(walker.currentNode);
        }
        return nodes;
    }

    function buildTitleWrappers(scope) {
        var textNodes = getTextNodes(scope);
        textNodes.forEach(function (node) {
            var text = normalizeText(node.nodeValue);
            if (!/^Motorrad\s+\d+$/i.test(text)) {
                return;
            }

            var parent = node.parentNode;
            if (!parent || parent.closest('.mopedgarage-bike-title')) {
                return;
            }

            var title = document.createElement('div');
            title.className = 'mopedgarage-bike-title';
            title.textContent = text;

            if (parent.childNodes.length === 1) {
                parent.parentNode.insertBefore(title, parent);
                parent.remove();
            } else {
                parent.insertBefore(title, node);
                parent.removeChild(node);
            }
        });
    }

    function markPhotoRows(scope) {
        var dts = scope.querySelectorAll('dt');
        dts.forEach(function (dt) {
            var labelText = normalizeText(dt.textContent);
            if (!/^Foto$/i.test(labelText)) {
                return;
            }
            var dl = dt.closest('dl');
            if (dl) {
                dl.classList.add('mopedgarage-photo-row');

                var dd = dl.querySelector('dd');
                if (dd) {
                    var html = dd.innerHTML;
                    if (/Bild hierher ziehen/i.test(dd.textContent) && !dd.querySelector('.mopedgarage-upload-hint')) {
                        dd.innerHTML = html.replace(/(Bild\s+hierher\s+ziehen)/i, '<span class="mopedgarage-upload-hint">$1</span>');
                    }
                }
            }
        });
    }

    function wrapBlocks(scope) {
        var titles = Array.prototype.slice.call(scope.querySelectorAll('.mopedgarage-bike-title'));
        if (!titles.length) {
            return;
        }

        titles.forEach(function (title) {
            if (title.parentNode && title.parentNode.classList && title.parentNode.classList.contains('mopedgarage-block')) {
                return;
            }

            var block = document.createElement('div');
            block.className = 'mopedgarage-block';
            title.parentNode.insertBefore(block, title);
            block.appendChild(title);

            var node = block.nextSibling;
            while (node) {
                var next = node.nextSibling;

                if (node.nodeType === 1 && node.classList && node.classList.contains('mopedgarage-bike-title')) {
                    break;
                }

                if (node.nodeType === 3 && /^\s*$/.test(node.nodeValue)) {
                    block.appendChild(node);
                    node = next;
                    continue;
                }

                if (node.nodeType === 1) {
                    var text = normalizeText(node.textContent);
                    if (/^Motorrad\s+\d+$/i.test(text) && !node.querySelector('input, select, textarea, img, button')) {
                        break;
                    }
                }

                block.appendChild(node);
                node = next;
            }
        });
    }

    function init() {
        var heading = Array.prototype.find.call(document.querySelectorAll('h2'), function (h) {
            return /Mopedgarage verwalten/i.test(normalizeText(h.textContent));
        });

        if (!heading) {
            return;
        }

        var panel = heading.closest('.panel') || document.querySelector('.panel') || document.body;
        document.body.classList.add('mopedgarage-ui-hotfix');

        buildTitleWrappers(panel);
        markPhotoRows(panel);
        wrapBlocks(panel);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
