jQuery( document ).ready( function ($) {

	/**
	 * Event handler for doing an ajax requesst to recreate all option values
	 */

	jQuery('#pro-reset-variations').on('click', function(){
		var ajaxurl = pro.ajaxurl;
		var action = 'reset-variations';
		var product_id = $('form.variations_form.cart').attr('data-product_id');
		var attributes = get_all_attributes();

		reset_selection(attributes);
		add_overlay();

		jQuery.post(ajaxurl, {
			'action': action,
			'product_id': product_id
		},
			function(response) {
			var result = JSON.parse(response);
			refresh_options(result);
			remove_overlay();
		});
	});

	/**
	 * Event handler for doing an ajax requesst to reduce  option values
	 */

	jQuery('.pro-attribute').on('change', function(){
		var selected_key = $(this).closest('select').attr('id');
		var selected_value = $(this).val();

		$('table.variations').attr('data-'+selected_key, selected_value);

		var ajaxurl = pro.ajaxurl;
		var action = 'reduce-variations';
		var product_id = $('.variations_form.cart').attr('data-product_id');
		add_overlay();

		jQuery.post(ajaxurl, {
			'action': action,
			'product_id': product_id
		},
			function(response) {
			var result = JSON.parse(response);
			var reduced_options =  result['attribute_'+selected_key];
			var new_options = reduced_options[selected_value];

			refresh_options(new_options);
			remove_overlay();
		});
	});

	/**
	 * Recreates the options based on the attributes selected
	 *
	 * @param new_options
	 * @param selected_value
	 */

	function refresh_options(new_options){

		if(!new_options) return;
		var keys = Object.keys(new_options);
		var attributes = get_all_attributes();
		var selection = get_selection(attributes);

		for(var i=0; i<keys.length; i++){

			var attribute_key = keys[i];
			var attribute = keys[i].replace('attribute_', '');

			if(attribute in selection){
				var selected_value = selection[attribute];
			}
			else {
				var selected_value = '';
			}

			var options_html = '<option value="">Choose an option</option>';
			var select_id = keys[i].replace('attribute_', '');

			new_options[attribute_key].forEach(function(value){
				if(value === selected_value){
					options_html += '<option value="'+value+'" selected>'+value+'</option>';
				}
				else {
					options_html += '<option value="'+value+'">'+value+'</option>';
				}
			});

			$('select#'+select_id).html(options_html);
		}
	}

	/**
	 * Creates an array of all attributes related to the product
	 *
	 * @returns {Array}
	 */

	function get_all_attributes(){
		var select_fields = $('table.variations').find('select');
		var selcted_values = [];
		select_fields.map(function (index, value){
			selcted_values.push(value.id)
		});
		return selcted_values;
	}

	function reset_selection(attributes){
		attributes.forEach(function(attribute) {
			var key = 'data-'+attribute;
			$('table.variations').removeAttr(key);
		});
	}

	/**
	 * Get the selected attributes that where already done before
	 *
	 * @param attributes
	 * @returns {{}}
	 */

	function get_selection(attributes){
		var selected = {};
		attributes.forEach(function(attribute) {
			var key = 'data-'+attribute;
			var value = $('table.variations').attr(key);
			if(value){
				selected[attribute] = value;
			}
		});
		return selected;
	}

	/**
	 * Adds the overlay loading box
	 */

	function add_overlay(){
		var overlay_html = '' +
			'<div id="custom-pro-overlay">' +
			'<div class="blockUI" style="display:none"></div>' +
			'<div class="blockUI blockOverlay" style="z-index: 1000; border: medium none; margin: 0px; padding: 0px; width: 100%; height: 100%; top: 0px; left: 0px; background: rgb(255, 255, 255) none repeat scroll 0% 0%; opacity: 0.585317; cursor: default; position: absolute;"></div>' +
			'<div class="blockUI blockMsg blockElement" style="z-index: 1011; display: none; position: absolute; left: 170.5px; top: 88px; cursor: default;"></div>' +
			'</div>';

		$('.variations_form.cart').css('position', 'relative');
		$('.variations_form.cart').append(overlay_html);
	}

	/**
	 * Removes the overlay loading box
	 */

	function remove_overlay(){
		$('.variations_form.cart').css('position', 'static');
		$('#custom-pro-overlay').remove();
	}
});