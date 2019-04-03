/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

/**
 * Validation of birthdate and age (>= 18years)
 *
 * @since 1.1.0
 * @returns {boolean}
 */
function validateBirthdate() {
	var birthDate = new Date($("#ratepayinvoice-birthdate").val());
	birthDate.setHours(0,0,0,0);
	var limit = new Date();
	limit.setFullYear(limit.getFullYear() - 18);
	limit.setHours(0,0,0,0);
	if (birthDate <= limit) {
		$("#error-box-ratepayinvoice").hide();
		return true;
	} else {
		$("#error-box-ratepayinvoice").show();
		return false;
	}
}

/**
 * Validate if Terms Consent checkbox is checked
 * @returns {boolean}
 */
function validateTermConsent() {
	return $('#terms_wirecard_pg_ratepay').prop('checked');
}

/**
 * Validate all required fields.
 * If all data are valid enable confirm button.
 */
function validate() {
	if (validateTermConsent() && validateBirthdate()) {
		$("#button-confirm").prop("disabled", false);
	} else {
		$("#button-confirm").prop("disabled", true);
	}
}

/**
 * Disable confirm button and hide validate Birthday error message
 */
$(document).ready(function () {
	$("#error-box-ratepayinvoice").hide();
	$("#button-confirm").prop("disabled", true);
});



