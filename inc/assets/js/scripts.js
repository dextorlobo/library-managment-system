jQuery(document).ready(function($) {
	jQuery( ".lms-start-date" ).datepicker({
		//dateFormat: "yy-mm-dd",
		minDate: 0,
		onSelect: function(dateText, inst) {
			console.log(dateText);
			console.log(inst);
			console.log(inst.selectedDay);
			console.log(inst.selectedMonth);
			console.log(inst.selectedYear);
			lms_get_avilable_seats({
				selectedDay: inst.selectedDay,
				selectedMonth: inst.selectedMonth +1,
				selectedYear: inst.selectedYear
			})
		}
	});
});

jQuery( document ).on( "click", '.lms-checkout-submit', on_submit_buy_now_form );

/**
 * Handles the submission of the "Buy Now" form.
 *
 * Prevents the default form submission behavior, retrieves the product ID from the form,
 * generates a JSON object from the form fields, validates the form, and if valid,
 * performs server-side validations.
 *
 * @param {Event} event - The event object representing the form submission event.
 */
function on_submit_buy_now_form( event ) {
	event.preventDefault();
	var product_id = jQuery( this ).data( 'product-id' );
	jsonObj        = buy_now_form_field_json_obj( 'buy-now-fields-product-' + product_id );
	var isValid    = buy_now_form_validation( jsonObj );
	if ( isValid ) {
		try {
			server_side_validations( jsonObj );
		} catch (error) {
			console.log(error);
		}
	}
}

/**
 * Generates a JSON object from form fields with a specified class.
 *
 * This function iterates over all input and select elements with the given class,
 * retrieves their id and value, and constructs a JSON object with this data.
 *
 * @param {string} fieldsClass - The class name of the form fields to be included in the JSON object.
 * @returns {Array<Object>} An array of objects, each containing the id and value of a form field.
 */
function buy_now_form_field_json_obj( fieldsClass ) {
	jsonObj = [];
	jQuery( "input." + fieldsClass + ", select."+ fieldsClass ).each( function() {
		var id    = jQuery(this).attr( "id" );
		var type  = jQuery(this).attr( "type" );
		var name  = jQuery(this).attr( "name" );
		var value = jQuery(this).val();

		item           = {}
		item ["id"]    = id;
		item ["value"] = value;
		item ["name"]  = name;
		item ["type"]  = type;
		jsonObj.push( item );
	} );

	return jsonObj;
}

/**
 * Validates the form data provided in the jsonObj.
 * 
 * This function checks if the form fields are empty or contain errors, and displays appropriate error messages.
 * 
 * @param {Array} jsonObj - An array of objects representing form fields and their values.
 * @param {string} jsonObj[].id - The ID of the form field.
 * @param {string} jsonObj[].value - The value of the form field.
 * @param {boolean} [jsonObj[].error] - Indicates if there is an error with the form field.
 * @param {string} [jsonObj[].msg] - The error message to display if there is an error.
 * 
 * @returns {boolean} - Returns true if the form is valid and can be submitted, otherwise false.
 */
function buy_now_form_validation( jsonObj ) {
	console.log(jsonObj);
	jQuery('.inline-error-message').text('');
	var allowSubmit = true;
	jQuery( jsonObj ).each( function( i, item ) {
		//console.log(item);
		if( isEmpty( item.value ) ) {
            //console.log(item);
            var itemId = item.id.replace(/_/g, ' ');
			jQuery('#' + item.id ).next('span').text('Please enter ' + itemId);
			allowSubmit = false;
			return false;
		}

		if( item.type == 'radio' && jQuery('input[name="' + item.id + '"]:checked').length == 0 ) {
			var itemId = item.id.replace(/_/g, ' ');
			jQuery('.lms-radio-wrap').next('span').text('Please select any.' );
			allowSubmit = false;
			return false;
		}

		if( item.error ) {
			jQuery('#' + item.id ).next('span').text(item.msg);
		}
	} )

	return allowSubmit;
}

/**
 * Checks if a given value is empty.
 *
 * A value is considered empty if it is:
 * - A string that is either empty or contains only whitespace characters.
 * - Undefined.
 * - Null.
 *
 * @param {*} value - The value to check.
 * @returns {boolean} - Returns true if the value is empty, otherwise false.
 */
function isEmpty(value) {
	return typeof value == 'string' && !value.trim() || typeof value == 'undefined' || value === null;
}

/**
 * Performs server-side validations for the checkout process.
 *
 * This function sends an AJAX POST request to the server to validate the checkout data.
 * It handles the response by displaying appropriate messages and enabling/disabling the submit button.
 *
 * @param {Object} jsonObj - The JSON object containing the data to be validated.
 */
function server_side_validations( jsonObj ) {
    jQuery.ajax({
		type: "post",
		dataType: "json",
		url: lms_ajax_object.ajax_url,
		data : { action: "lms_checkout_validation", data: jsonObj },
		beforeSend: function() {
			jQuery('.lms-message').text('');
			jQuery('.lms-message').removeClass('show info success error warning');
			jQuery('.lms-message').addClass('hide');
			jQuery('.lms-checkout-submit').prop('disabled', true);
			jQuery('.loader-overlay').removeClass('hide');
		},
		success: function( res ) {
			if ( res.success ) {
				console.log('success');
				jQuery('.lms-message').text('Success. Please wait...');
				jQuery('.lms-message').addClass('success');
				create_pending_order( res.data );
			} else {
				if ( 'header_msg' in res.data ) {
					jQuery('.lms-message').text(res.data.header_msg);
				}
				if ( 'fields_error' in res.data && Array.isArray(res.data.fields_error) ) {
					buy_now_form_validation(res.data.fields_error);
				}
				jQuery('.lms-message').addClass('error');
				jQuery('.lms-checkout-submit').prop('disabled', false);
				jQuery('.loader-overlay').addClass('hide');
			}
		},
		error: function ( xhr, textStatus, error ) {
			console.log(xhr);
			console.log(textStatus);
			console.log(error);
			jQuery('.lms-message').text(error);
			jQuery('.lms-message').addClass('error');
			jQuery('.lms-checkout-submit').prop('disabled', false);
			jQuery('.loader-overlay').addClass('hide');
		},
		complete:function(res) {
			jQuery('.lms-message').removeClass('hide');
			jQuery('.lms-message').addClass('show');
		}
	});
}

/**
 * Creates a pending order and generates a payment link.
 *
 * This function sends an AJAX request to create a pending order. If the order creation is successful,
 * it proceeds to generate a payment link. The function updates the UI with messages indicating the
 * progress and status of the operations.
 *
 * @param {Object} data - The data to be sent with the AJAX request for creating the pending order.
 */
function create_pending_order( data ) {
	jQuery.ajax({
		type: "post",
		dataType: "json",
		url: lms_ajax_object.ajax_url,
		data : { action: "lms_create_pending_order", data: data },
		beforeSend: function() {
			jQuery('.lms-message').text('Creating order. Please wait...');
		},
		success: function( res ) {
			console.log(res);
			if ( res.success ) {
				console.log('success');
				jQuery('.lms-message').text('Success. Please wait...');
				jQuery('.lms-message').addClass('success');
				generating_payment_link( res.data );
			} else {
				if ( 'header_msg' in res.data ) {
					jQuery('.lms-message').text(res.data.header_msg);
				} else {
					jQuery('.lms-message').text('Error during order creation. Please try again.');
				}
				jQuery('.lms-message').addClass('error');
				jQuery('.lms-checkout-submit').prop('disabled', false);
				jQuery('.loader-overlay').addClass('hide');
			}
		},
		error: function ( xhr, textStatus, error ) {
			console.log(xhr);
			console.log(textStatus);
			console.log(error);
			jQuery('.lms-message').text(error);
			jQuery('.lms-message').addClass('error');
			jQuery('.lms-checkout-submit').prop('disabled', false);
			jQuery('.loader-overlay').addClass('hide');
		},
		complete:function(res) {
			jQuery('.lms-message').removeClass('hide');
			jQuery('.lms-message').addClass('show');
		}
	});
}

/**
 * Sends an AJAX request to generate a payment link.
 * 
 * This function sends a POST request to the server to generate a payment link for an order.
 * It updates the UI with messages indicating the status of the request.
 * 
 * @function generating_payment_link
 * @returns {void}
 */
function generating_payment_link( data ) {
	jQuery.ajax({
		type: "post",
		dataType: "json",
		url: lms_ajax_object.ajax_url,
		data : { action: "lms_generating_payment_link", data: data },
		beforeSend: function() {
			jQuery('.lms-message').text('Generating Payment Link. Please wait...');
		},
		success: function( res ) {
			console.log(res);
			if ( res.success ) {
				console.log('success');
				jQuery('.lms-message').text('Redirecting To Payment Link. Please wait...');
				jQuery('.lms-message').addClass('success');
				window.location.href = res.data.payment_link;
			} else {
				if ( 'header_msg' in res.data ) {
					jQuery('.lms-message').text(res.data.header_msg);
				} else {
					jQuery('.lms-message').text('Error during order creation. Please try again.');
				}
				jQuery('.lms-message').addClass('error');
				jQuery('.lms-checkout-submit').prop('disabled', false);
				jQuery('.loader-overlay').addClass('hide');
			}
		},
		error: function ( xhr, textStatus, error ) {
			console.log(xhr);
			console.log(textStatus);
			console.log(error);
			jQuery('.lms-message').text(error);
			jQuery('.lms-message').addClass('error');
			jQuery('.lms-checkout-submit').prop('disabled', false);
			jQuery('.loader-overlay').addClass('hide');
		},
		complete:function(res) {
			jQuery('.lms-message').removeClass('hide');
			jQuery('.lms-message').addClass('show');
		}
	});
}

/**
 * Sends an AJAX request to get the available seats for an admission date.
 * 
 * This function sends a POST request to the server to get the available seats for an admission date.
 * It updates the UI with messages indicating the status of the request.
 * 
 * @function lms_get_avilable_seats
 * @param {string} admission_date - The admission date.
 * @returns {void}
 */
function lms_get_avilable_seats( admission_date ) {
	console.log(admission_date);

	jQuery.ajax({
		type: "post",
		dataType: "json",
		url: lms_ajax_object.ajax_url,
		data : { action: "lms_get_avilable_seats", data: admission_date },
		beforeSend: function() {
			jQuery('.lms-message').text('Fatching Available Seats. Please wait...');
			jQuery('.loader-overlay').removeClass('hide');
		},
		success: function( res ) {
			console.log(res);
			if ( res.success ) {
				console.log('success');
				jQuery('.lms-message').text('Please select a seat.');
				jQuery('.lms-message').addClass('success');
				jQuery('.available-seats-wrap').html( convert_json_select_dropdown_html( res.data ) );
			} else {
				if ( 'header_msg' in res.data ) {
					jQuery('.lms-message').text(res.data.header_msg);
				} else {
					jQuery('.lms-message').text('Error during order creation. Please try again.');
				}
				jQuery('.lms-message').addClass('error');
				jQuery('.lms-checkout-submit').prop('disabled', false);
				jQuery('.loader-overlay').addClass('hide');
			}
		},
		error: function ( xhr, textStatus, error ) {
			console.log(xhr);
			console.log(textStatus);
			console.log(error);
			jQuery('.lms-message').text(error);
			jQuery('.lms-message').addClass('error');
			jQuery('.lms-checkout-submit').prop('disabled', false);
			jQuery('.loader-overlay').addClass('hide');
		},
		complete:function(res) {
			jQuery('.lms-message').removeClass('hide');
			jQuery('.lms-message').addClass('show');
			jQuery('.loader-overlay').addClass('hide');
		}
	});
}

/**
 * Converts a JSON object to HTML for a select dropdown.
 * 
 * @param {object} data - A JSON object containing the seat id.
 * @returns {string} The HTML for the select dropdown.
 */
function convert_json_select_dropdown_html( data ) {
	console.log(data);

	jQuery('.lms-message').text('Please select a seat.');
	var product_id = jQuery('#product_id').val();

	// Create the HTML for the select dropdown
	var html = '';
	html += '<select name="seat_no" id="seat_no" class="form-control lms-select buy-now-fields-product-' + product_id + '" required>';
	html += '<option value="">Select a seat</option>';
	for ( var i = 0; i < data.length; i++ ) {
		html += '<option value="' + data[i].id + '">' + data[i].title + '</option>';
	}
	html += '</select><span class="inline-error-message"></span>';

	return html;
}
