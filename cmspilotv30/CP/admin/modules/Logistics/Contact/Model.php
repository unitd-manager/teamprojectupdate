<?
class CP_Admin_Modules_Logistics_Contact_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL   = "
        SELECT c.*
              ,CONCAT_WS(' ', c.first_name, c.last_name ) AS contact_name
              ,gc.name AS country_name
        FROM contact c
        LEFT JOIN geo_country gc ON (c.address_country_code = gc.country_code)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'c';

        $contact_id     = $fn->getReqParam('contact_id');
        $subscribe      = $fn->getReqParam('subscribe');
        $special_search = $fn->getReqParam('special_search');

        if ($contact_id != "") {
            $searchVar->sqlSearchVar['contact_id'] = "c.contact_id = '{$contact_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar['contact_id'] = "c.contact_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.contact_id');

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Subscribed") {
                $searchVar->sqlSearchVar['subscribe'] = "c.subscribe = 1";
            }

            if ($tv['special_search'] == "Not-Subscribed") {
                $searchVar->sqlSearchVar['subscribe_2'] = "(c.subscribe != 1 OR c.subscribe IS null)";
            }

            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar['flag'] = "c.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar['flag_2'] = "(c.flag != 1 OR c.flag IS null)";
            }

            if ($tv['special_search']  == 'Published') {
                $searchVar->sqlSearchVar['published'] = "c.published = 1";
            }

            if ($tv['special_search'] == 'Not-Published' ) {
                $searchVar->sqlSearchVar['published_2'] = "c.published = 0 OR c.published IS NULL OR c.published = ''";
            }

            if ($tv['special_search'] == 'Invalid Emails' ) {
                $searchVar->sqlSearchVar['invalid_email'] = "c.invalid_email = 1";
            }

            //------------------------------------------------------------------------//
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar['keyword'] = "(
                       c.first_name   LIKE '%{$tv['keyword']}%'
                    OR c.last_name    LIKE '%{$tv['keyword']}%'
                    OR c.company_name LIKE '%{$tv['keyword']}%'
                    OR c.email        LIKE '%{$tv['keyword']}%'
                )";
            }


            if ($tv['spAction'] == 'link' && $tv['module'] == 'broadcast' ){
                $searchVar->sqlSearchVar['subscribe_3'] = "c.subscribe = 1";
            }

            $searchVar->sortOrder = "c.last_name, c.first_name";
        }
    }

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
        $rec = $fn->getRecordByCondition('contact', "email = '{$email}'");
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

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

        $fa = $fn->addToFieldsArray($fa, 'subscribe');
        $fa = $fn->addToFieldsArray($fa, 'language');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'pass_word');
        $fa = $fn->addToFieldsArray($fa, 'notes');

        if ($cpCfg['m.logistics.contact.flagInvalidEmails'] && $fa['email'] != '' && !$validate->isEmail($fa['email'])){
            $fa['invalid_email'] = 1;
        }

        return $fa;
    }

    /**
     *
     */
    function getContactSQL() {
        $SQL = "
        SELECT contact_id
              ,CONCAT_WS(' ', first_name, last_name ) AS contact_name
        FROM contact
        WHERE published = 1
        ORDER BY contact_name
        ";

        return $SQL;
    }

}
