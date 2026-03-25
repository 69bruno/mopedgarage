document.addEventListener('DOMContentLoaded', function () {
    var brand = document.getElementById('mopedgarage_search_brand');
    if (!brand) {
        return;
    }

    var form = brand.closest('form');
    if (!form) {
        return;
    }

    var fields = [
        brand,
        document.getElementById('mopedgarage_search_model'),
        document.getElementById('mopedgarage_search_capacity'),
        document.getElementById('mopedgarage_search_year'),
        document.getElementById('mopedgarage_search_with_image')
    ];

    var routeNode = document.getElementById('mopedgarage-search-route');
    var originalAction = form.getAttribute('action') || '';
    var motoAction = routeNode ? (routeNode.getAttribute('data-url') || originalAction) : originalAction;

    form.addEventListener('submit', function () {
        var hasMotoValue = false;

        fields.forEach(function (field) {
            if (!field) {
                return;
            }

            if ((field.type === 'checkbox' && field.checked) || ((field.value || '').trim() !== '')) {
                hasMotoValue = true;
            }
        });

        form.setAttribute('action', hasMotoValue ? motoAction : originalAction);
    });
});
