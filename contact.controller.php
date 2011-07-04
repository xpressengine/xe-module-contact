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
	 * @brief insert/update agreement term 
	 **/
	function procContactInsertTerm() {

		// check permission
		if($this->module_info->module != "contact") return new Object(-1, "msg_invalid_request");
        $logged_info = Context::get('logged_info');

		// get form variables submitted
		$obj = Context::getRequestVars();
		$obj->module_srl = $this->module_srl;
		
		$oModuleModel = &getModel('module');
		$mExtraVars = $oModuleModel->getModuleExtraVars($obj->module_srl);

		//get exist extra variables
		$obj->enable_terms = $mExtraVars[$obj->module_srl]->enable_terms;
		$obj->admin_mail = $mExtraVars[$obj->module_srl]->admin_mail;

		//save term to mudule table content column
		if($obj->term) 
			$obj->content = $obj->term;
		else 
			$obj->content = "";

		unset($obj->term);

		//save agree_text to mudule table mcontent column 
		if($obj->agree_text) 
			$obj->mcontent = $obj->agree_text;
		else 
			$obj->mcontent = "";

		unset($obj->agree_text);
		
		$oModuleController = &getController('module');

		if($obj->module_srl) {
			$output = $oModuleController->updateModule($obj);
			$msg_code = 'success_updated';
			// if there is an error, then stop
			if(!$output->toBool()) return $output;
		}
		
		// return result
		$this->add('mid', Context::get('mid'));

		// output success inserted/updated message
		$this->setMessage($msg_code);
	}

	/**
	 * @brief send email 
	 **/
	function procContactSendEmail(){
		$logged_info = Context::get('logged_info');
		if(!$logged_info)
			return new Object(-1, 'Only logged user can send an email.');

		$oMail = new Mail();

		$oMail->setContentType("plain");

		// get form variables submitted
		$obj = Context::getRequestVars();
		if(!$obj->check_agree) return new Object(-1, 'You haven\'t read and agree to the terms of the license agreement.');

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


		$msg_code = 'success_email';
		$this->add('mid', Context::get('mid'));

		$this->setMessage($msg_code);

	}

}
?>
