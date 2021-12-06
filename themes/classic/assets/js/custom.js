/*
 * Custom code goes here.
 * A template should always ship with an empty custom.js
 */
jQuery( document ).ready(function() {
		jQuery( ".bootstrap-touchspin" ).live("click", function(){
			var qtyMax = jQuery(this).parent().parent().find("input").attr("data-max");
			var qtyVal = jQuery(this).parent().parent().find("input").val();
			alert(qtyMax+'------'+qtyVal);
			if(qtyVal >= qtyMax){
				jQuery(this).parent().parent().find("input").val(qtyMax);
				jQuery("button."+this).prop('disabled','disabled');
			}
		});
});
