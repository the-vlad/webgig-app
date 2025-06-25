jQuery(document).ready(function($) {
    $('.tab-btn').on('click', function() {
        var tabId = $(this).data('tab');

        // Switch active class on buttons
        $('.tab-btn').removeClass('active');
        $(this).addClass('active');

        // Switch active tab content
        $('.tab-content').removeClass('active');
        $('#tab-' + tabId).addClass('active');
    });
});