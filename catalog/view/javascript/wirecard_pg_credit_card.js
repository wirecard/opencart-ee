/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

var WirecardPaymentPage;

/**
 * Set the paren transaction id to the form and submit it
 *
 * @param response
 * @since 1.0.0
 */
function setParentTransactionId(response) {
	var form = $("#wirecard-pg-form");

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
	$("#pg-spinner").fadeOut();
	$("#creditcard-form-div").height(500).fadeIn();
	$("#button-confirm").prop("disabled", false);
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
				url: "index.php?route=extension/payment/wirecard_pg_" + window.WirecardPaymentMethod + "/get" + window.WirecardPaymentMethod + "UiRequestData",
				type: "post",
				dataType: "json",
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
			console.error("WPP did not respond in " + Integer.valueOf(maxWait/1000) + "seconds");
			clearInterval(WPPavailableInterval);
		}
	}, waitStep );
}

/**
 * Set a saved credit card token.
 *
 * @param token
 * @since 1.1.0
 */
function setToken(token) {
	jQuery("#token-field").val(token);
}

/**
 * Delete a card from the vault.
 *
 * @param card
 * @param masked_pan
 * @since 1.1.0
 */
function deleteCardFromVault(card, masked_pan) {
	if (confirm("Are you sure you want to delete this credit card?")) {
		$.ajax({
			url: "index.php?route=extension/payment/wirecard_pg_creditcard/deleteCardFromVault",
			type: "post",
			dataType: "json",
			data: {
				card: card,
				masked_pan: masked_pan
			},
			success: function (data) {
				$("#success-message, #failure-message").hide();
				$("#deleted-pan").text(data.deleted_card);

				if (data.success) {
					$("#success-message").fadeIn();
					$(".credit-card-selector[data-pan='" + data.deleted_card + "']").fadeOut(300, function() {
						$(this).remove();
					});

					return;
				}

				$("#failure-message").fadeIn();
			}
		});
	}
}

/**
 * Handle tab changes for saved credit cards.
 *
 * @since 1.1.0
 */
function handleTabChanges() {
	$("a[data-toggle='tab']").on("shown.bs.tab", function (evt) {
		var saveCreditCard = ".save-credit-card";
		var target = $(evt.target).attr("href");

		if (target === "#new") {
			$(saveCreditCard).show();
			return;
		}

		$(saveCreditCard).hide();
	});
}

/**
 * When document loads get the data for the credit card form and attach a tab event handler
 *
 * @since 1.0.0
 */
$(document).ready(function() {
	$("#button-confirm").prop("disabled", true);
	$("#creditcard-form-div").hide();
	getCreditCardRequestData();
	handleTabChanges();
});

/**
 * On confirm button submit the form
 *
 * @since 1.0.0
 */
$("#button-confirm").on("click", function() {
	if (jQuery(window.newCardTab).length === 0 || jQuery(window.newCardTab).hasClass("active") || WirecardPaymentMethod === "upi") {
		WirecardPaymentPage.seamlessSubmitForm({
			onSuccess: setParentTransactionId,
			onError: logError
		});
	}
});
