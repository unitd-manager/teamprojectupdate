<?
class CP_Admin_Modules_AceIms_ContactLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    function getSQL() {

        $SQL = "
        SELECT c.*
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
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $id_card_no = $fn->getPostParam('id_card_no', '', true);
        $email      = $fn->getPostParam('email', '', true);
        $mobile     = $fn->getPostParam('mobile');

        $validate->resetErrorArray();

        $validate->validateData('first_name', 'Please enter the name');
        $validate->validateData('gender', 'Please select gender');
        $validate->validateData('id_card_no' , 'Please enter NRIC / Passport No.');
        $validate->validateData('nationality' , 'Please select nationality');
        $validate->validateData('date_of_birth' , 'Please enter date of birth');
        $validate->validateData('email', 'Please enter email address');
        $validate->validateData('mobile', 'Please enter mobile number');

        if ($id_card_no != ''){
            $rec = $fn->getRecordByCondition('contact', "id_card_no = '{$id_card_no}'");
            $expIdCard = array('displayText' => 'Go to this record', 'target' => '_blank');
            $IdCardlink = $fn->getRecordDetailLink('aceIms_contact', 'record_id', $rec['contactId'], $expIdCard);

            if (is_array($rec)){
                $validate->errorArray['id_card_no']['name'] = "id_card_no";
                $validate->errorArray['id_card_no']['msg']  = "NRIC / Passport No. already exists. '{$IdCardlink}'";
            }
        }

        if ($email != ''){
            if(!$validate->isEmail($email)){
                $validate->errorArray['email']['name'] = "email";
                $validate->errorArray['email']['msg']  = "Please enter valid email";
            }
        }

        $mobile_no_start = substr($mobile, 0, 1);
        if ($mobile != '' && $mobile_no_start < '8') {
            $validate->errorArray['mobile']['name'] = "mobile";
            $validate->errorArray['mobile']['msg']  = "Please enter valid mobile no";
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

        $modObj = getCPModuleObj('aceIms_contact');
        $fa['registration_no'] = $modObj->model->getGenerateRegNoForContact();

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
        $email      = $fn->getPostParam('email', '', true);
        $mobile     = $fn->getPostParam('mobile');
        $contactId = $fn->getReqParam('contactId');

        $validate->validateData('first_name', 'Please enter the name');
        $validate->validateData('gender', 'Please select gender');
        $validate->validateData('id_card_no' , 'Please enter NRIC/FIN/Work Permit No.');
        $validate->validateData('nationality' , 'Please select nationality');
        $validate->validateData('date_of_birth' , 'Please enter date of birth');
        $validate->validateData('email', 'Please enter email address');
        $validate->validateData('mobile', 'Please enter mobile number');

        if ($id_card_no != ''){
            $rec = $fn->getRecordByCondition('contact', "id_card_no = '{$id_card_no}' AND contactId != {$contactId}");
            $expIdCard = array('displayText' => 'Go to this record', 'target' => '_blank');
            $IdCardlink = $fn->getRecordDetailLink('aceIms_contact', 'record_id', $rec['contactId'], $expIdCard);

            if (is_array($rec)){
                $validate->errorArray['id_card_no']['name'] = "id_card_no";
                $validate->errorArray['id_card_no']['msg']  = "NRIC / Passport No. already exists. '{$IdCardlink}'";
            }
        }


        if ($email != ''){
            if(!$validate->isEmail($email)){
                $validate->errorArray['email']['name'] = "email";
                $validate->errorArray['email']['msg']  = "Please enter valid email";
            }
        }

        $mobile_no_start = substr($mobile, 0, 1);
        if ($mobile != '' && $mobile_no_start < '8') {
            $validate->errorArray['mobile']['name'] = "mobile";
            $validate->errorArray['mobile']['msg']  = "Please enter valid mobile no";
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
        $fa = $fn->addToFieldsArray($fa, 'is_citizen');
        $fa = $fn->addToFieldsArray($fa, 'eng_read_write');

        return $fa;
    }

    /**
    */
    function setSearchVar($linkRecType) {
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'c';

        $contact_id    = $fn->getReqParam('contact_id');

        $modObj = getCPModuleObj('aceIms_contact');
        $modObj->model->setSearchVar($linkRecType);
        $searchVar->groupBy = "c.contact_id";

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
        $validate->validateData('contactId', 'Please select the student');

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

        $fa = $fn->addToFieldsArray($fa, 'contactId');
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
