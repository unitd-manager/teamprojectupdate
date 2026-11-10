<?
class CP_Admin_Modules_AgileIms_ContactLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    function getSQL() {
        
        $SQL = "
        SELECT DISTINCT c.contact_id
               ,c.*
               ,CONCAT_WS(' ', c.first_name, c.last_name ) AS contact_name
               ,b.title AS batch_title
        FROM contact c
        LEFT JOIN (batch_history bh) ON (c.contact_id = bh.contact_id)
        LEFT JOIN (batch b)          ON (bh.batch_id  = b.batch_id)
        ";

        return $SQL;
    }

    /**
    */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $validate->validateData('first_name', 'Please enter the name');
        $validate->validateData('id_card_no' , 'Please enter NRIC / Passport No.');
        $validate->validateData('nationality' , 'Please select nationality');
        $validate->validateData('date_of_birth' , 'Please enter date of birth');
        
        $id_card_no = $fn->getPostParam('id_card_no', '', true);
        
        if ($id_card_no != ''){
            $rec = $fn->getRecordByCondition('contact', "id_card_no = '{$id_card_no}'");
            $expIdCard = array('displayText' => 'Go to this record', 'target' => '_blank');
            $IdCardlink = $fn->getRecordDetailLink('agileIms_contact', 'record_id', $rec['contact_id'], $expIdCard);
    
            if (is_array($rec)){
                $validate->errorArray['id_card_no']['name'] = "id_card_no";
                $validate->errorArray['id_card_no']['msg']  = "NRIC / Passport No. already exists. '{$IdCardlink}'";
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
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();

        $rowPrefix    = $fn->getRecordByCondition('setting', "key_text = 'registrationCodePrefix'");
        $current_year = date('Y');
        
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextRegistrationNo'";
        $resultUpdate = $db->sql_query($SQLUpdate);
        $nextRegNo = $fn->getSettingsValueByKey("nextRegistrationNo");

        if ($nextRegNo < 10) {
            $nextRegNo = $rowPrefix['value'] . '-' . $current_year . '-0000' . $nextRegNo;
        } else if ($nextRegNo < 100) {
            $nextRegNo = $rowPrefix['value'] . '-' . $current_year . '-000' . $nextRegNo;
        } else if ($nextRegNo > 99) {
            $nextRegNo = $rowPrefix['value'] . '-' . $current_year . '-00' . $nextRegNo;
        } else {
            $nextRegNo = $rowPrefix['value'] . '-' . $current_year . '-0000' . $nextRegNo;
        }
        
        $fa['registration_no'] = $nextRegNo;

        $id = $fn->addRecord($fa);
        return $validate->getSuccessMessageXML();
    }

    /**
    */
    function getEditPortalValidate() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $id_card_no = $fn->getPostParam('id_card_no', '', true);
        $contact_id = $fn->getReqParam('contact_id');

        $validate->validateData('first_name', 'Please enter the name');
        $validate->validateData('id_card_no' , 'Please enter NRIC/FIN/Work Permit No.');
        $validate->validateData('nationality' , 'Please select nationality');
        $validate->validateData('date_of_birth' , 'Please enter date of birth');

        if ($id_card_no != ''){
            $rec = $fn->getRecordByCondition('contact', "id_card_no = '{$id_card_no}' AND contact_id != {$contact_id}");
            $expIdCard = array('displayText' => 'Go to this record', 'target' => '_blank');
            $IdCardlink = $fn->getRecordDetailLink('agileIms_contact', 'record_id', $rec['contact_id'], $expIdCard);
    
            if (is_array($rec)){
                $validate->errorArray['id_card_no']['name'] = "id_card_no";
                $validate->errorArray['id_card_no']['msg']  = "NRIC / Passport No. already exists. '{$IdCardlink}'";
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
        $fa = $fn->addToFieldsArray($fa, 'gender');
        $fa = $fn->addToFieldsArray($fa, 'date_of_birth');
        $fa = $fn->addToFieldsArray($fa, 'marital_status');
        $fa = $fn->addToFieldsArray($fa, 'id_card_no');
        $fa = $fn->addToFieldsArray($fa, 'nationality');
        $fa = $fn->addToFieldsArray($fa, 'race');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'pass_word');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'mobile');
        $fa = $fn->addToFieldsArray($fa, 'qualification');
        $fa = $fn->addToFieldsArray($fa, 'yr_of_exp');
        $fa = $fn->addToFieldsArray($fa, 'is_citizen');
        $fa = $fn->addToFieldsArray($fa, 'eng_read_write');
        $fa = $fn->addToFieldsArray($fa, 'physical_activities');
        $fa = $fn->addToFieldsArray($fa, 'safety_shoe');
        
        return $fa;
    }
    
    /**
    */
    function setSearchVar($linkRecType) {
        $searchVar = Zend_Registry::get('searchVar');

        $modObj = getCPModuleObj('agileIms_contact');
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
