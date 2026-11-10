<?
class CP_Admin_Modules_EnterpriseIms_ContactLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
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
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('first_name', 'Please enter the name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');

        $site_id = $fn->getSessionParam('cp_site_id');

        $parent_id = $fn->getReqParam('parent_id');
        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }
        
        $fa = $this->getFields();
        if($cpCfg['m.enterpriseIms.contact.hasRegisterNo']){
            $rowPrefix    = $fn->getRecordByCondition('setting', "key_text = 'registrationCodePrefix'");
            $current_year = date('Y');
            
            $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextRegistrationNo'";
            $resultUpdate = $db->sql_query($SQLUpdate);
            $nextRegNo = $fn->getSettingsValueByKey("nextRegistrationNo");
            
            $fa['registration_no'] = $nextRegNo;

            if ($site_id == 2) {
                $fa['registration_no'] = $nextRegNo . 'J';
            }
        }
        
        $fa['status'] = 'Active';
        $id = $fn->addRecord($fa);
       
        $fa1 = array();
        $fa1['parent_id'] = $parent_id;
        $fa1['contact_id'] = $id;
       
        $id_parent_contact = $fn->addRecord($fa1, 'parent_contact');
        
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditPortalValidate() {
        return $this->getNewValidate();
    }

    /**
     *
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
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'gender');
        $fa = $fn->addToFieldsArray($fa, 'date_of_birth');
        $fa = $fn->addToFieldsArray($fa, 'age');
        $fa = $fn->addToFieldsArray($fa, 'id_card_no');
        $fa = $fn->addToFieldsArray($fa, 'is_citizen');
        $fa = $fn->addToFieldsArray($fa, 'nationality');
        $fa = $fn->addToFieldsArray($fa, 'continuing_to_next_year');
        
        return $fa;
    }
    
    /**
     *
     */
    function setSearchVar($linkRecType) {
        $searchVar = Zend_Registry::get('searchVar');

        $modObj = getCPModuleObj('enterpriseIms_contact');
        $modObj->model->setSearchVar($linkRecType);
    }

    /**
     *
     */
    function getAddNewGridItem(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = $this->getFields();
        $fa['batch_id'] = $tv['srcRoomId'];
        $id = $fn->addRecord($fa);
    }

    /**
     *
     */
    function getSaveGridItem(){
        $fn = Zend_Registry::get('fn');
        
        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
    }
}