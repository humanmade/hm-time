jQuery(document).ready(function($){
		$("input.tr_clone_add").on('click', function() {
			$("#work_hours tr:last").clone().find("input").each(function() {
				var newrow = $(this),
					i  	= newrow.data('rownum'),
					j	= i+ 1,
					type = newrow.attr('class');
				newrow.attr({
					'name': 'hm_tz_workhours['+j+']['+ type +']',
					'value': '',
					'data-rownum': j
				});
			}).end().appendTo("#work_hours");
		});

});