jQuery(function($) {
	$('.inputs-row').on('click', '.qty-btn', function() {
		var $qty = $(this).closest('.inputs-row').find('.qty');
		var current = parseFloat($qty.val());
		var min = parseFloat($qty.attr('min')) || 1;
		var max = parseFloat($qty.attr('max')) || 999;

		if ($(this).hasClass('plus')) {
			if (current < max) $qty.val(current + 1).change();
		} else {
			if (current > min) $qty.val(current - 1).change();
		}
	});
});
