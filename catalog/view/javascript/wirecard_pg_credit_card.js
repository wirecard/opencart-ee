/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

/**
 * When document loads get the data for the credit card form
 *
 * @since 1.0.0
 */
$(document).ready(function() {
	getCreditCardRequestData();
});

/**
 * On confirm button submit the form
 *
 * @since 1.0.0
 */
$('#button-confirm').on('click', function() {
	WirecardPaymentPage.seamlessSubmitForm({
		onSuccess: setParentTransactionId,
		onError: logError
	})
});

/**
 * Set the paren transaction id to the form and submit it
 *
 * @param response
 * @since 1.0.0
 */
function setParentTransactionId(response) {
	var form = $('#wirecard-pg-form');
	for (var key in response) {
		if (response.hasOwnProperty(key)) {
			form.append("<input type='hidden' name='" + key + "' value='" + response[key] + "'>");
		}
	}
	form.submit();
}

/**
 * On success set the hight of the iframe
 * @since 1.0.0
 */
function callback() {
	$('#creditcard-form-div').height(500);
}

/**
 * Log errors to console
 *
 * @param error
 * @since 1.0.0
 */
function logError(error) {
	console.log(error);
}

/**
 * Get data with an ajax for the seamlessrenderform
 * @since 1.0.0
 */
function getCreditCardRequestData() {
	var maxWait = 5000, waitStep = 250, WPPavailableInterval = setInterval( function () {
		maxWait -= waitStep;
		if ( typeof WirecardPaymentPage !== "undefined" ) {
			$.ajax( {
				url: 'index.php?route=extension/payment/wirecard_pg_creditcard/getCreditCardUiRequestData',
				type: 'post',
				dataType: 'json',
				success: function ( data ) {
					if ( data != null ) {
						WirecardPaymentPage.seamlessRenderForm( {
							requestData: data,
							wrappingDivId: "creditcard-form-div",
							onSuccess: callback,
							onError: logError
						} );
					}
				},
				error: function ( error ) {
					console.error( error );
				}
			} );
			clearInterval( WPPavailableInterval );
		}
		if ( maxWait <= 0 ) {
			console.error('WPP did not respond in ' + Integer.valueOf(maxWait/1000) + 'seconds');
		}
	}, waitStep );
}
