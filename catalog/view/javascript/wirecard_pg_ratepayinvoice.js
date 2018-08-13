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
 */
function validateBirthdate() {
	var birthDate = new Date($("#ratepayinvoice-birthdate").val());
	birthDate.setHours(0,0,0,0);
	var limit = new Date();
	limit.setFullYear(limit.getFullYear() - 18);
	limit.setHours(0,0,0,0);
	if (birthDate <= limit) {
		$("#button-confirm").prop("disabled", false);
		$("#error-box-ratepayinvoice").hide();
	} else {
		$("#button-confirm").prop("disabled", true);
		$("#error-box-ratepayinvoice").show();
	}
}

$(document).ready(function() {
	$("#error-box-ratepayinvoice").hide();
});
