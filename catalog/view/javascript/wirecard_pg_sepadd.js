/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/opencart-ee/blob/master/LICENSE
 */

function hideSepaMandate() {
	var button = $("#button-confirm");
	button.unbind("DOMSubtreeModified");
	$("#payment").show();
	$("#mandate-popup").hide();
	$("#mandate_confirmed").val(0);
	button.prop("disabled",false);
	$("#sepa-cancel-button").remove();
}

function checkSepaMandate(checkbox) {
	var button = $("#button-confirm");
	button.unbind("DOMSubtreeModified");
	if (checkbox.is(":checked")) {
		button.prop("disabled",false);
		$("#mandate_confirmed").val(1);
	} else {
		button.prop("disabled",true);
		$("#mandate_confirmed").val(0);
	}
}

