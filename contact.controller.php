<?php
/**
 * @class  contactController
 * @author NHN (developers@xpressengine.com)
 * @brief  contact module Controller class
 **/

class contactController extends contact {

	/**
	 * @brief initialization
	 **/
	function init() {
	}

	/**
	 * @brief send email 
	 **/
	function procContactSendEmail(){
		$logged_info = Context::get('logged_info');
		/*if(!$logged_info)
			return new Object(-1, 'Only logged user can send an email.');*/

		$oMail = new Mail();

		$oMail->setContentType("plain");

		// get form variables submitted
		$obj = Context::getRequestVars();
		if($obj->enable_terms == 'Y' && !$obj->check_agree) return new Object(-1, 'You haven\'t read and agree to the terms of the license agreement.');

		$obj->email = $obj->Email;
		$obj->subject = $obj->Subject;
		$obj->comment = $obj->Comment;
		
		$oDocumentModel = &getModel('document');
		$extra_keys = $oDocumentModel->getExtraKeys($obj->module_srl);

		$mail_content = array();

		$content = '';
		if(count($extra_keys)) {
			foreach($extra_keys as $idx => $extra_item) {
				$value = '';
				if(isset($obj->{'extra_vars'.$idx})) $value = trim($obj->{'extra_vars'.$idx});
				elseif(isset($obj->{$extra_item->name})) $value = trim($obj->{$extra_item->name});
				if(!isset($value)) continue;
				//check if extra item is required
				if($extra_item->is_required == 'Y' && $value==""){
					return new Object(-1, 'Please input a value for '.$extra_item->name);
				}
				//if the type of form component is email address
				if($extra_item->type == 'email_address' && !$oMail->isVaildMailAddress($value)){
					return new Object(-1, 'Please input a valid email for '.$extra_item->name);
				}
				$mail_content[$extra_item->name] = $value;
				$content .= $extra_item->name . ':  ' . $value . "\r\n";
			}
		}

		//if the admin mail is not set, then admin mail equals to admin registered email address
		if(!count($this->module_info->admin_mail)>0) {
			$this->module_info->admin_mail = $logged_info->email_address;
		}
		
		if(!$oMail->isVaildMailAddress($obj->email)){
			return new Object(-1, 'Please input your valid email address.');
		}

		$oMail->setTitle($obj->subject);
		$content_all = $content . "\r\nComments:\r\n" . htmlspecialchars($obj->comment);
		$mail_content['Comments'] = htmlspecialchars($obj->comment);

		$oMail->setContent(htmlspecialchars($content_all));
		$oMail->setSender("XE Contact Us", $obj->email);

		$target_mail = explode(',',$this->module_info->admin_mail);

		for($i=0;$i<count($target_mail);$i++) {
			$email_address = trim($target_mail[$i]);
			if(!$email_address) continue;
			if(!$oMail->isVaildMailAddress($email_address)) $email_address = $logged_info->email_address;
			$oMail->setReceiptor($email_address, $email_address);

			if($logged_info->is_admin != 'Y'){
				if($this->module_info->module_srl){
					$oModuleModel = &getModel('module');
					$moduleExtraVars = $oModuleModel->getModuleExtraVars($this->module_info->module_srl);
					if($moduleExtraVars[$this->module_info->module_srl]->interval){
						$interval = $moduleExtraVars[$this->module_info->module_srl]->interval;
						//transfer interval to mins
						$interval = $interval*60;
						$oContactModel = &getModel('contact');
						$output = $oContactModel->checkLimited($interval);	
						if(!$output->toBool()) return $output;
					}
				}
			}
			$oMail->send();
		}

		if(isset($_SESSION['mail_content']))
			unset($_SESSION['mail_content']);

		$_SESSION['mail_content'] = $mail_content;


		if($logged_info->is_admin != 'Y'){
			$oSpamController = &getController('spamfilter');
			$oSpamController->insertLog();
		}

		$msg_code = 'An email has been sent successfully.';
		$this->add('mid', Context::get('mid'));

		$this->setMessage($msg_code);

		if(!in_array(Context::getRequestMethod(),array('XMLRPC','JSON'))) {
			$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'act', 'dispCompleteSendMail','mid', $obj->mid);
			header('location:'.$returnUrl);
			return;
		}
	}

}
?>
