<?php
    /**
     * @class  ContactView
     * @author NHN (developers@xpressengine.com)
     * @brief  contact us module View class
     **/

    class contactView extends contact {


        /**
         * @brief initialize contact view class.
         **/
		function init() {
           /**
             * get skin template_path
             * if it is not found, default skin is xe_contact
             **/
            $template_path = sprintf("%sskins/%s/",$this->module_path, $this->module_info->skin);
            if(!is_dir($template_path)||!$this->module_info->skin) {
                $this->module_info->skin = 'xe_contact_official';
                $template_path = sprintf("%sskins/%s/",$this->module_path, $this->module_info->skin);
            }
            $this->setTemplatePath($template_path);

            /**
             * get extra variables from xe_document_extra_keys table, context set
             **/
            $oDocumentModel = &getModel('document');
            $form_extra_keys = $oDocumentModel->getExtraKeys($this->module_info->module_srl);
            Context::set('form_extra_keys', $form_extra_keys);
		}

        /**
         * @brief display contact content
         **/
        function dispContactContent() {

			Context::addJsFilter($this->module_path.'tpl/filter', 'search.xml');
			Context::addJsFilter($this->module_path.'tpl/filter', 'send_email.xml');

			// set template_file to be list.html
            $this->setTemplateFile('index');
        }


        /**
         * @brief display agreement term write form
         **/
        function dispContactTermWrite() {

			// only admin user can write contact term
			if(!Context::get('is_logged'))  return $this->setTemplateFile('input_password_form');
            $logged_info = Context::get('logged_info');
            if($logged_info->is_admin != 'Y') return $this->setTemplateFile('input_password_form');

			$oContactModel = &getModel('contact');
			$editor_content = $oContactModel->getEditor($this->module_info->module_srl);
			Context::set('editor_content', $editor_content);

            /** 
             * add javascript filter file insert_question
             **/
			$termText = $this->getTermText();
			Context::set('termText', $termText);

            Context::addJsFilter($this->module_path.'tpl/filter', 'insert_term.xml');

            $this->setTemplateFile('write_term_form');

		}

		function dispCompleteSendMail() {

			if(isset($_SESSION['mail_content'])){
				$mail_content = $_SESSION['mail_content'];
				Context::set('mail_content',$mail_content);
				Context::set('mail_title',$_SESSION['mail_title']);

			}else{
				Context::set('mail_content','');
				$url = getUrl('mid',$this->mid,'act','');
				header('Location: '.$url);
			}

			unset($_SESSION['mail_content']);

			$this->setTemplateFile('success_form');
		}

		function getTermText($strlen = 0) {
            if(!$this->module_info->module_srl) return;

			$term = $this->module_info->content;

			if($strlen) return cut_str(strip_tags($term),$strlen,'...');

			return htmlspecialchars($term);
		}

    }



?>
