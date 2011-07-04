/**
 * @file   modules/contact/tpl/js/contact_admin.js
 * @author NHN (developers@xpressengine.com)
 * @brief  contact module template javascript
 **/

/* after insert contact us module */
function completeInsertContact(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];

    var page = ret_obj['page'];
    var module_srl = ret_obj['module_srl'];

    alert(message);

    var url = current_url.setQuery('act','dispContactAdminContactInfo');
    if(module_srl) url = url.setQuery('module_srl',module_srl);
    if(page) url.setQuery('page',page);
    location.href = url;
}

function completeInsertExtraVar(ret_obj) {
    alert(ret_obj['message']);
    location.href = current_url.setQuery('type','').setQuery('selected_var_idx','');
}

function doDeleteExtraKey(module_srl, var_idx) {
    var fo_obj = jQuery('#fo_delete')[0];
    fo_obj.module_srl.value = module_srl;
    fo_obj.var_idx.value = var_idx;
    return procFilter(fo_obj, delete_extra_var);
}

function moveVar(type, module_srl, var_idx) {
    var params = {
		type       : type,
		module_srl : module_srl,
		var_idx    : var_idx
	};
    var response_tags = ['error','message'];
    exec_xml('document','procDocumentAdminMoveExtraVar', params, function() { location.reload() });
}

/* after delete contact module */
function completeDeleteContact(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var page = ret_obj['page'];
    alert(message);

    var url = current_url.setQuery('act','dispContactAdminContent').setQuery('module_srl','');
    if(page) url = url.setQuery('page',page);
    location.href = url;
}

/* mass configuration*/
function doCartSetup(url) {
    var module_srl = new Array();
    jQuery('#fo_list input[name=cart]:checked').each(function() {
        module_srl[module_srl.length] = jQuery(this).val();
    });

    if(module_srl.length<1) return;

    url += "&module_srls="+module_srl.join(',');
    popopen(url,'modulesSetup');
}

/* after term inserted */
function completeTermInserted(ret_obj) {

	var error = ret_obj['error'];
    var message = ret_obj['message'];

    var page = ret_obj['page'];
    var module_srl = ret_obj['module_srl'];

    alert(message);

    var url = current_url.setQuery('act','dispContactAdminContactAgreement');
    if(module_srl) url = url.setQuery('module_srl',module_srl);
    if(page) url.setQuery('page',page);
    location.href = url;
}

