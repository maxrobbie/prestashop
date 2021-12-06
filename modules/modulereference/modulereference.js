/* global $ */
$(document).ready(function () {
    var $searchRefer = $('#autocomplete');
    var searchURL     = "index.php?controller=autofill&module=modulereference&fc=module"; 
	$searchRefer.autocomplete({
		maxResults: 10,
		source: function( request, response ) {
		   $.ajax({
				url: searchURL,
				type: 'post',
				dataType: "json",
				data: {
				 search: request.term,
				 action: 'GetRefer',
				},
				contentType: "application/x-www-form-urlencoded;charset=utf8",
				success: function( data ) {
				  response(data);
				}
		   });
		},
		select: function (event, ui) {
			$searchRefer.val(ui.item.label);
			return false;
		},
		focus: function(event, ui){
			$searchRefer.val( ui.item.label );
			return false;
		},
	});
});
