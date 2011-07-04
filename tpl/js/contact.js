/**
 * @file   modules/contact/tpl/js/contact.js
 * @author NHN (developers@xpressengine.com)
 * @brief  contact module template javascript
 **/

function completeSendEmail(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var mid = ret_obj['mid'];
    var url;
    url = current_url.setQuery('mid',mid).setQuery('act','dispCompleteSendMail');

    location.href = url;
}




