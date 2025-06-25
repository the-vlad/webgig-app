jQuery(document).ready(function ($) {
    const $input = $('#ta-product-search');
    const $results = $('#ta-product-results');
    const $button = $('#ta-search-button');

    let currentTab = 'products';
    let results = { products: [], posts: [] };
    let debounceTimeout;

    function renderResults() {
        $results.empty();

        // Tabs
        $results.append(`
            <li style="display: flex; justify-content: flex-end; padding: 8px; border-bottom: 0px solid #eee;">
                <button class="switch-tab" data-tab="products" ${currentTab === 'products' ? 'disabled' : ''}>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M14.625 5.3999L16.3 7.0749L11.35 12.0249L16.3 16.9749L14.625 18.6499L7.99999 12.0249L14.625 5.3999Z" fill="#0F172A"/></svg>
                </button>
                <button class="switch-tab" data-tab="posts" ${currentTab === 'posts' ? 'disabled' : ''}>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M9.37501 18.6001L7.70001 16.9251L12.65 11.9751L7.70001 7.0251L9.37501 5.3501L16 11.9751L9.37501 18.6001Z" fill="#0F172A"/></svg>
                </button>
            </li>
        `);

        const items = results[currentTab];

        // Count
        $results.append(`
            <li class="count-results" style="padding: 8px 12px; font-weight: bold; border-bottom: 0px solid #eee;">
                ${items.length} result${items.length === 1 ? '' : 's'}
            </li>
        `);

        if (items.length) {
            items.forEach(item => {
                $results.append(`
                    <li style="padding: 10px;">
                        <a href="${item.url}" style="display: flex; align-items: center; gap: 10px;">
                            <img src="${item.image}" alt="${item.title}" style="width: 50px;">
                            <div>
                                <div>${item.title}</div>
                                ${item.price ? `<div style="color: #888;">${item.price}</div>` : ''}
                            </div>
                        </a>
                    </li>
                `);
            });

            $results.append(`
                <li style="text-align: center; padding: 10px; border-top: 0px solid #eee;">
                    <button id="see-all-results" style="background: #1d4ed8; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer;">
                        See all results
                    </button>
                </li>
            `);
        } else {
            $results.append('<li style="padding: 10px;">No results found</li>');
        }
    }

    $results.on('click', '.switch-tab', function () {
        currentTab = $(this).data('tab');
        renderResults();
    });

    $results.on('click', '#see-all-results', function () {
        const query = $input.val().trim();
        if (query.length) {
            window.location.href = `/?s=${encodeURIComponent(query)}`;
        }
    });

    $input.on('input', function () {
        const search = $(this).val().trim();

        clearTimeout(debounceTimeout);

        if (search.length < 2) {
            $results.empty();
            return;
        }

        debounceTimeout = setTimeout(() => {
            $.get(TAProductSearch.ajax_url, {
                action: 'ta_product_search',
                term: search
            }, function (data) {
                results = data;
                currentTab = 'products';
                renderResults();
            });
        }, 300);
    });

    let clickedInside = false;

    $(document).on('mousedown', function (e) {
        clickedInside =
            $(e.target).closest('#ta-product-results').length > 0 ||
            $(e.target).closest('#ta-product-search').length > 0;
    });
    
    $(document).on('click', function () {
        if (!clickedInside) {
            $results.empty();
        }
    });
    
        
    $button.on('click', function () {
        const query = $input.val();
        if (query.length) {
            window.location.href = `/?s=${encodeURIComponent(query)}`;
        }
    });
});
