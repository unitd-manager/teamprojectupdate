<?
class CP_Admin_Modules_Edukloud_ContactLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    function getSQL() {
        
        $SQL = "
        SELECT c.*
              ,CONCAT_WS(' ', c.first_name, c.last_name ) AS contact_name
              ,cl.title AS class_title
        FROM contact c
        LEFT JOIN (student_class sc) ON (c.contact_id = sc.contact_id )
        LEFT JOIN (class cl) ON (cl.class_id = sc.class_id )
        ";

        return $SQL;
    }

    /**
    */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $validate->validateData('salutation', 'Please enter the title');
        $validate->validateData('first_name', 'Please enter the first name');
        $validate->validateData('last_name', 'Please enter the last name');
        $validate->validateData('email' , 'Please enter a valid email address', 'email');
        
        $email = $fn->getPostParam('email', '', true);        
        
        if ($email != ''){
            $rec = $fn->getRecordByCondition('contact', "email = '{$email}'");
            $expEmail = array('displayText' => $email);
            $emailLink = $fn->getRecordDetailLink('edukloud_contact', 'record_id', $rec['contact_id'], $expEmail);
    
            if (is_array($rec)){
                $validate->errorArray['email']['name'] = "email";
                $validate->errorArray['email']['msg']  = "Email already exists. '{$emailLink}'";
                
            }
        }
        
        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
    */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);
        return $validate->getSuccessMessageXML();
    }

    /**
    */
    function getEditPortalValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('salutation', 'Please enter the title'); 
        $validate->validateData('first_name', 'Please enter the first name');
        $validate->validateData('last_name', 'Please enter the last name');
        
    }

    /**
    */
    function getSave(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditPortalValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
        return $validate->getSuccessMessageXML();
    }

    /**
    */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'company_id');
        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'salutation');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'phone_direct');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'mobile');
        $fa = $fn->addToFieldsArray($fa, 'position');
        $fa = $fn->addToFieldsArray($fa, 'department');
        $fa = $fn->addToFieldsArray($fa, 'class_id');
        $fa = $fn->addToFieldsArray($fa, 'age');
        
        return $fa;
    }
    
    /**
    */
    function setSearchVar($linkRecType) {
        $searchVar = Zend_Registry::get('searchVar');

        $modObj = getCPModuleObj('edukloud_contact');
        $modObj->model->setSearchVar($linkRecType);
    }

    /**
    */
    function getAddNewGridItem(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = $this->getFields();
        $fa['batch_id'] = $tv['srcRoomId'];
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
    function getAddForResources(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidateForResources()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFieldsForResources();
        $id = $fn->addRecord($fa);
        return $validate->getSuccessMessageXML();
    }

    /**
    */
    function getNewValidateForResources() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('contact_id', 'Please select the student');
        
        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
    */
    function getFieldsForResources(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'contact_id');
        $fa = $fn->addToFieldsArray($fa, 'from_date');
        $fa = $fn->addToFieldsArray($fa, 'to_date');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'resources_id');
        
        return $fa;
    }

    /**
    */
    function getSaveForResources(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidateForResources()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFieldsForResources();
        $id = $fn->saveRecord($fa);
        return $validate->getSuccessMessageXML();
    }
}
