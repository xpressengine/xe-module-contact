<?php
    /**
     * @class  contactAdminController
     * @author NHN (developers@nhn.com)
     * @brief  contact module admin controller class
     **/

    class contactAdminController extends contact {

        /**
         * @brief initialization
         **/
        function init() {
        }

        /**
         * @brief insert Contact Us module
         **/
        function procContactAdminInsertContact($args = null) {
            // get module model/module controller
            $oModuleController = &getController('module');
            $oModuleModel = &getModel('module');

            // get variables from admin page form
            $args = Context::getRequestVars();
            $args->module = 'contact';
            $args->mid = $args->contact_name;
            unset($args->contact_name);

			// set up addtional variables
			if($args->enable_terms!='Y') $args->enable_terms = 'N';
			$args->admin_mail = trim($args->admin_mail);

			// if module_srl exists
            if($args->module_srl) {
                $module_info = $oModuleModel->getModuleInfoByModuleSrl($args->module_srl);
                if($module_info->module_srl != $args->module_srl) unset($args->module_srl);
            }

            // insert/update contact module, depending on whether module_srl exists or not 
            if(!$args->module_srl) {
                $output = $oModuleController->insertModule($args);
                $msg_code = 'success_registed';
            } else {
                $output = $oModuleController->updateModule($args);
                $msg_code = 'success_updated';
            }

            if(!$output->toBool()) return $output;

            $this->add('page',Context::get('page'));
            $this->add('module_srl',$output->get('module_srl'));
            $this->setMessage($msg_code);

        }

        /**
         * @brief insert/update contact form extra components (use document extrakey table)
         **/
        function procContactAdminInsertExtraVar() {
            $module_srl = Context::get('module_srl');
            $var_idx = Context::get('var_idx');
            $type = Context::get('type');
            $is_required = Context::get('is_required');
            $default = Context::get('default');
            $desc = Context::get('desc');
			$eid = Context::get('eid');
			$name = $eid;
			$search = 'N';

            if(!$module_srl || !$eid) return new Object(-1,'msg_invalid_request');

            // if idx is not defined, then set idx = max_idx +1
            if(!$var_idx) {
                $obj->module_srl = $module_srl;
                $output = executeQuery('document.getDocumentMaxExtraKeyIdx', $obj);
                $var_idx = $output->data->var_idx+1;
            }

			// check if extra key exists
			$obj->module_srl = $module_srl;
			$obj->var_idx = $var_idx;
			$obj->eid = $eid;

	
            $output = executeQuery('document.isExistsExtraKey', $obj);
            if(!$output->toBool() || $output->data->count) {
                return new Object(-1, 'msg_extra_name_exists');
            }

            // insert or update
            $oDocumentController = &getController('document');
            $output = $oDocumentController->insertDocumentExtraKey($module_srl, $var_idx, $name, $type, $is_required, $search, $default, $desc, $eid);
            if(!$output->toBool()) return $output;

            $this->setMessage('success_registed');
        }

        /**
         * @brief delete contact form extra components
         **/
		 function procContactAdminDeleteExtraVar() {
			$module_srl = Context::get('module_srl');
            $var_idx = Context::get('var_idx');
            if(!$module_srl || !$var_idx) return new Object(-1,'msg_invalid_request');

            $oDocumentController = &getController('document');
            $output = $oDocumentController->deleteDocumentExtraKeys($module_srl, $var_idx);
            if(!$output->toBool()) return $output;

            $this->setMessage('success_deleted');
		 }

		/**
         * @brief delete Contact Us module
         **/
        function procContactAdminDeleteContact() {
            $module_srl = Context::get('module_srl');

			$obj->module_srl = $module_srl;
			
			$oDoumentController = &getController('document');
			$oDoumentController->deleteDocumentExtraKeys($obj->module_srl);

            $oModuleController = &getController('module');
            $output = $oModuleController->deleteModule($module_srl);
            if(!$output->toBool()) return $output;

            $this->add('module','contact');
            $this->add('page',Context::get('page'));
            $this->setMessage('success_deleted');
        }

    }
?>
