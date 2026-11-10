<?
class CP_Admin_Modules_Pms_Student_Model extends CP_Common_Modules_Pms_Student_Model
{
    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $validate->validateData('first_name', 'Please enter the first name');
        $validate->validateData('last_name' , 'Please enter the last name');
        $validate->validateData('email' , 'Please enter a valid email address', 'email');

        $email = $fn->getPostParam('email', '', true);
        $rec = $fn->getRecordByCondition('student', "email = '{$email}'");
        if (is_array($rec)){
            $validate->errorArray['email']['name'] = "email";
            $validate->errorArray['email']['msg']  = "Email already exist.";
        }

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

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('first_name', 'Please enter the first name');
        $validate->validateData('last_name' , 'Please enter the last name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();

        if ($cpCfg['cp.hasPasswordSalt']) {
            $pass_word = $fa['pass_word'];
            $email = $fa['email'];
            if ($pass_word != '') {
                $arr = $cpUtil->getSaltAndPasswordArray($email, $pass_word);
                $fa['salt'] = $arr['salt'];
                $fa['pass_word'] = $arr['pass_word'];
            } else {
                //remove pass_word field from the fields array
                unset($fa['pass_word']);
            }
        }
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'salutation');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'phone_direct');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'mobile');

        $fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'position');
        $fa = $fn->addToFieldsArray($fa, 'department');

        $fa = $fn->addToFieldsArray($fa, 'address1');
        $fa = $fn->addToFieldsArray($fa, 'address2');
        $fa = $fn->addToFieldsArray($fa, 'address_area');
        $fa = $fn->addToFieldsArray($fa, 'address_city');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_country_code');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');

        $fa = $fn->addToFieldsArray($fa, 'gender');
        $fa = $fn->addToFieldsArray($fa, 'marital_status');
        $fa = $fn->addToFieldsArray($fa, 'id_card_no');
        $fa = $fn->addToFieldsArray($fa, 'nationality');
        $fa = $fn->addToFieldsArray($fa, 'race');
        $fa = $fn->addToFieldsArray($fa, 'date_of_birth');
        $fa = $fn->addToFieldsArray($fa, 'emergency_contact_name');
        $fa = $fn->addToFieldsArray($fa, 'emergency_contact_mobile');
        $fa = $fn->addToFieldsArray($fa, 'emergency_contact_office_no');
        $fa = $fn->addToFieldsArray($fa, 'school_name');
        $fa = $fn->addToFieldsArray($fa, 'school_country');
        $fa = $fn->addToFieldsArray($fa, 'school_from');
        $fa = $fn->addToFieldsArray($fa, 'school_to');
        $fa = $fn->addToFieldsArray($fa, 'school_highest_qual');
        $fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'position');
        $fa = $fn->addToFieldsArray($fa, 'company_phone');
        $fa = $fn->addToFieldsArray($fa, 'company_roc_no');
        $fa = $fn->addToFieldsArray($fa, 'company_address');
        $fa = $fn->addToFieldsArray($fa, 'company_po_code');
        $fa = $fn->addToFieldsArray($fa, 'company_fax');
        $fa = $fn->addToFieldsArray($fa, 'yr_of_exp');
        $fa = $fn->addToFieldsArray($fa, 'salary_range');
        $fa = $fn->addToFieldsArray($fa, 'apply_for_sdf');

        $fa = $fn->addToFieldsArray($fa, 'subscribe');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'pass_word');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'company_id');

        return $fa;
    }

}
