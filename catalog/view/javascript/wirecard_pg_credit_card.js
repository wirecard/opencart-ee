/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

var WirecardPaymentPage;
var debug = false;

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
	if (typeof error == "string") {
		$("#error-message span").html(error).parent().show();
	}
	if (debug) {
		console.log(error);
	}
}

/**
 * Get data with an ajax for the seamlessrenderform
 * @since 1.0.0
 */
function getCreditCardRequestData() {
	var maxWait = 500, WPPavailableInterval = setInterval( function () {
		maxWait--;
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
				error: logError
			} );
			clearInterval( WPPavailableInterval );
		}
		if ( maxWait <= 0 ) {
			logError("A time out error occurred during render form process. No successful response possible");
			clearInterval(WPPavailableInterval);
		}
	}, 20 );
}

/**
 * Set a saved credit card token.
 *
 * @param token
 * @since 1.1.0
 */
function setToken(token) {
	var tokenField = "#token-field";

	if (token == null) {
		$(tokenField).removeAttr("value");
		return;
	}

	$(tokenField).val(token);
}

/**
 * Delete a card from the vault.
 *
 * @param card
 * @param maskedPan
 * @since 1.1.0
 */
function deleteCardFromVault(card, maskedPan) {
	if (confirm("Are you sure you want to delete this credit card?")) {
		$.ajax({
			url: "index.php?route=extension/payment/wirecard_pg_creditcard/deleteCardFromVault",
			type: "post",
			dataType: "json",
			data: {
				card: card,
				masked_pan: maskedPan
			},
			success: function (data) {
				$("#success-message, #failure-message").hide();
				$("#deleted-pan").text(data.deleted_card);

				if (data.success) {
					$("#success-message").fadeIn();
					$(".credit-card-selector[data-pan='" + data.deleted_card + "']").fadeOut(300, function() {
						$(this).remove();
						setToken(null);

						if($("#list-existing-cards").children().length === 0) {
							$("#button-confirm").attr("disabled", "disabled");
						}
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
			$("#button-confirm").removeAttr("disabled");
			return;
		}

		if($("#list-existing-cards").children().length === 0) {
			setToken(null);
			$("#button-confirm").attr("disabled", "disabled");
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
	if (jQuery(window.newCardTab).length === 0
		|| jQuery(window.newCardTab).hasClass("active")
		|| WirecardPaymentMethod === "upi"
		|| WirecardPaymentMethod === "maestro") {
		console.log("Special submitting");
		WirecardPaymentPage.seamlessSubmitForm({
			onSuccess: setParentTransactionId,
			onError: logError
		});
	}
});
