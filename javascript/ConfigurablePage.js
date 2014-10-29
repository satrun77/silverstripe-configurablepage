(function($) {
	$(document).ready(function() {
		var td = $('.ss-gridfield-table .ss-gridfield-items .col-Sort');
		td.live('dblclick', function() {
			$(this).find('input').toggle();
		});
	});
})(jQuery);
