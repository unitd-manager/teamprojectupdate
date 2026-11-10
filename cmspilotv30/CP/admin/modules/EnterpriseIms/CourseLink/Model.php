<?
class CP_Admin_Modules_EnterpriseIms_CourseLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    function getFields(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = array();

        if ($tv['srcRoom'] == 'enterpriseIms_company'){
            $fa = $fn->addToFieldsArray($fa, 'company_id');
        }
        $fa = $fn->addToFieldsArray($fa, 'course_id');
        $fa = $fn->addToFieldsArray($fa, 'batch_id');
        $fa = $fn->addToFieldsArray($fa, 'course_subsidy_history_id');
        $fa = $fn->addToFieldsArray($fa, 'contact_id');
        
        return $fa;
    }

    /**
    */
    function getAddNewGridItem(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = $this->getFields();
        $fa['contact_id'] = $tv['srcRoomId'];
        $id = $fn->addRecord($fa);

    }

    /**
    */
    function getSaveGridItem(){
        $fn = Zend_Registry::get('fn');
        
        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
    }
    
    /**
    */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->validateData('course_id', 'Please select the course');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
    */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
    */
    function getAdd() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $discount         = $fn->getPostParam('discount');
        $fa['discount']   = $discount;
        $id = $fn->addRecord($fa);

        return $validate->getSuccessMessageXML();
    }

    /**
    */
    function getSave() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');

        if (!$this->getEditValidate()) {
            return $validate->getErrorMessageXML();
        }

        return $validate->getSuccessMessageXML();
    }
}