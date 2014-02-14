$(document).ready(function() {
	$.each($('form.sifter-form'), function(k, v) {
		var $sifterForm = $(v);

		$.each($('input[type="text"]', $sifterForm), function(k, v) {
			var $input = $(v);
			
			if ($input.hasClass('datetime')) {
				$input.datepicker({
					dateFormat: "yy-mm-dd",
					minDate: $input.data('oldest'),
					maxDate: $input.data('newest'),
					changeMonth: true,
					changeYear: true,
					showWeek: true,
					showAnim: 'slideDown'
				});
			} else {
				$input.autocomplete({
					source: function(request, callback) {
						$('#SifterSearchField').val($input.data('field'));
						$('#SifterSearchValue').val(request.term);
						$input.parent().removeClass('has-error');
						$('.help-block', $input.parent()).remove();

						var $data = $sifterForm.serialize();
						$.ajax({
							type: 'POST',
							url: $sifterForm.data('ajax-source'),
							data: $data,
							success: function(data, status, request) {
								if (data.error) {
									$input.parent().addClass('has-error');
									$input.parent().append('<span class="help-block">' + data.message + '</span>')
									return;
								}
								callback(data.data);
							}
						});
					},
					minLength: $sifterForm.data('ajax-min-length') | 3,
					select: function( event, ui ) {
						
					}
				});
			}
		});
	});
});