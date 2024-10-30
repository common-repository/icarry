
let ICarryShippingForWooCommerce = {


	emailField: null,
	passwordField: null,
	storeUrlField: null,
	isRateProviderField: null,



	validateConnectivityFields: () => {

		let valid = true;

		if (ICarryShippingForWooCommerce.emailField !== null && ICarryShippingForWooCommerce.passwordField !== null) {

			ICarryShippingForWooCommerce.emailField.classList.remove('invalid')
			ICarryShippingForWooCommerce.passwordField.classList.remove('invalid')

			ICarryShippingForWooCommerce.emailField.value = ICarryShippingForWooCommerce.emailField.value.replace(/\s/g, "")
			ICarryShippingForWooCommerce.passwordField.value = ICarryShippingForWooCommerce.passwordField.value.replace(/\s/g, "")

			if (ICarryShippingForWooCommerce.emailField.value === '') {
				ICarryShippingForWooCommerce.emailField.classList.add('invalid')
				valid = false
			}


			if (ICarryShippingForWooCommerce.passwordField.value === '') {
				ICarryShippingForWooCommerce.passwordField.classList.add('invalid')
				valid = false
			}

		}

		return valid;
	},

	checkConnectivity: (button) => {
		console.log('checkConnectivity');

		if (ICarryShippingForWooCommerce.validateConnectivityFields()) {

			var formData = new FormData();

			formData.append('action', 'icarry_shipping_for_woocommerce_check_connectivity_fetch_request');
			formData.append('nonce', ICarryShippingForWooCommerceVariables.nonce);
			formData.append('email', ICarryShippingForWooCommerce.emailField.value);
			formData.append('password', ICarryShippingForWooCommerce.passwordField.value);
			formData.append('storeUrl', ICarryShippingForWooCommerce.storeUrlField.value);
			formData.append('isRateProvider', ICarryShippingForWooCommerce.isRateProviderField.value);



			ICarryShippingForWooCommerce.successMessage.hide()
			ICarryShippingForWooCommerce.errorMessage.hide()

			button.querySelector('i').classList.add('show')

			fetch(ICarryShippingForWooCommerceVariables.AjaxUrl, {
				method: 'POST',
				body: formData,
				headers: {
					'Accept': 'application/json',
				}
			})
				.then((response) => {
					return response.json()
				})
				.then((response) => {
					console.log('response', response)

					if (response.type) {
						if (response.type == 'success') {
							ICarryShippingForWooCommerce.successMessage.show()
						}
						if (response.type == 'error') {
							ICarryShippingForWooCommerce.errorMessage.show()
						}
					}
				})
				.catch((error) => {
					console.error(error)
				})
				.finally(() => {
					button.querySelector('i').classList.remove('show')
				});
		}


	},

	errorMessage: {
		show: () => {
			document.querySelector('#icarry_shipping_for_woocommerce_check_connectivity_error_message').classList.add('show')
		},
		hide: () => {
			document.querySelector('#icarry_shipping_for_woocommerce_check_connectivity_error_message').classList.remove('show')
		},
	},

	successMessage: {
		show: () => {
			document.querySelector('#icarry_shipping_for_woocommerce_check_connectivity_success_message').classList.add('show')
		},
		hide: () => {
			document.querySelector('#icarry_shipping_for_woocommerce_check_connectivity_success_message').classList.remove('show')
		},
	},



	insertCheckConnectivityButton: () => {

		if (ICarryShippingForWooCommerce.passwordField !== null) {

			let passwordFieldTr = ICarryShippingForWooCommerce.passwordField.closest('tr')

			let connectivityButtonRow = '<tr valig ="top">'
				+ '<th scope="row" class="titledesc"></th>'
				+ '<td class="forminp">'

				+ '<div id="icarry_shipping_for_woocommerce_check_connectivity_error_message" class="check-connectivity-message error-message">'
				+ '<i class="dashicons dashicons-dismiss"></i>'
				+ '<span>Connection error. Please check your credentials.</span>'
				+ '</div>'

				+ '<div id="icarry_shipping_for_woocommerce_check_connectivity_success_message" class="check-connectivity-message success-message">'
				+ '<i class="dashicons dashicons-yes-alt"></i>'
				+ '<span>Connection successful.</span>'
				+ '</div>'

				+ '<button class="check-connectivity-button" type="button" onclick="ICarryShippingForWooCommerce.checkConnectivity(this)">'
				+ '<i class="dashicons dashicons-update rotating"></i>'
				+ 'Check Connectivity'
				+ '</button>'

				+ '</td>'
				+ '</tr>';

			passwordFieldTr.insertAdjacentHTML('afterend', connectivityButtonRow);

		}
	},



	icarry_init: () => {

		ICarryShippingForWooCommerce.emailField = document.querySelector('#woocommerce_icarry_shipping_for_woocommerce_email')

		ICarryShippingForWooCommerce.passwordField = document.querySelector('#woocommerce_icarry_shipping_for_woocommerce_password')
		ICarryShippingForWooCommerce.storeUrlField = document.querySelector('#woocommerce_icarry_shipping_for_woocommerce_store_url')
		ICarryShippingForWooCommerce.isRateProviderField = document.querySelector('#woocommerce_icarry_shipping_for_woocommerce_is_rate_provider')

		ICarryShippingForWooCommerce.insertCheckConnectivityButton()

	}
}

jQuery(document).ready(function($) {
    console.log('Document is ready.');
	$('#billing_country').val('');
    $('#shipping_country').val('');
    $('#billing_country, #shipping_country').on('change', function() {
        var countryIsoCode = $(this).val();
        console.log('Country selected: ' + countryIsoCode);

        // Determine whether the change event is from billing or shipping
        var isBilling = $(this).attr('id') === 'billing_country';
        
        // Find the respective city select field
        var $citySelect = isBilling ? $('#billing_city') : $('#shipping_city');
        console.log('City select field located.');

        // Clear the city dropdown before populating
        $citySelect.empty().append($('<option>').text('Select a city'));
        console.log('City dropdown cleared.');

        $.ajax({
            url: icarry_ajax_object.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'icarry_get_states_by_country',
                countryIsoCode: countryIsoCode,
                nonce: icarry_ajax_object.nonce
            },
            beforeSend: function() {
                console.log('Making AJAX request to: ' + icarry_ajax_object.ajax_url);
            },
            success: function(response) {
                console.log('AJAX request successful.');
                console.log('Response received:', response);
                if (response.success) {
                    $citySelect.empty().append($('<option>').text('Select a city'));
                    $.each(response.data, function(index, state) {
                        $citySelect.append($('<option>').val(state.id).text(state.name)); // Use the correct properties returned by your API
                    });
                    console.log('City dropdown populated with states.');
                } else {
                    console.error('Error in AJAX response:', response);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX request failed:', textStatus, errorThrown);
            }
        });
    });
});





window.addEventListener('load', () => {

	ICarryShippingForWooCommerce.icarry_init()

}, false);