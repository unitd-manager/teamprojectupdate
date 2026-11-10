<?
class CP_Admin_Modules_EzTrade_Contact_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        
        $SQL = "
        SELECT c.*
              ,CONCAT_WS(' - ', c.phone_country_code, c.phone_area_code, c.phone) AS phone_full
              ,CONCAT_WS(' - ', c.mobile_country_code, c.mobile_area_code, c.mobile) AS mobile_full
              ,CONCAT_WS(' ', c.first_name, c.last_name ) AS contact_name
              ,com.company_name AS c_company_name
              ,com.phone_country_code AS c_phone_country_code
              ,com.phone AS c_phone
              ,com.fax_country_code AS c_fax_country_code
              ,com.fax AS c_fax
              ,com.address_street AS c_address_street
              ,com.address_town AS c_address_town
              ,com.address_state AS c_address_state
              ,com.address_po_code AS c_address_po_code
              ,com.address_country AS c_address_country
        FROM contact c
        LEFT JOIN (company com) ON (c.company_id = com.company_id)
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

        $contact_id = $fn->getReqParam('contact_id');
        $company_id       = $fn->getReqParam('company_id');

        if ($contact_id != '') {
            $searchVar->sqlSearchVar[] = "c.contact_id = {$contact_id}";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.contact_id = '{$tv['record_id']}'";
        } else {

            if ($company_id != "") {
                $searchVar->sqlSearchVar[] = "c.company_id = '{$company_id}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       CONCAT_WS(' ', c.first_name, c.last_name)  LIKE '%{$tv['keyword']}%'
                    OR c.email          LIKE '%{$tv['keyword']}%'
                    OR com.company_name LIKE '%{$tv['keyword']}%'
                )";
            }

            $fn->setSpecialSearch();
        }

        $searchVar->sortOrder = "
         c.last_name
        ,c.first_name
        ";
    }

    /**
     *
     */
    function getNewValidate() {
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $validate->validateData('first_name', 'Please enter the first name');
        $validate->validateData('last_name' , 'Please enter the last name');

        $email = $fn->getReqParam('email');
        
        if ($email != '') {
            $SQL = "
            SELECT COUNT(*) AS count
            FROM contact c
            WHERE c.email = '{$email}'
            ";
            $result = $db->sql_query($SQL);
            $row = $db->sql_fetchrow($result);
            if ($row['count'] > 0) {
                $validate->errorArray['email']['name'] = 'email';
                $validate->errorArray['email']['msg']  = 'Email is not unique.';
            }
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
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        
        $validate->resetErrorArray();

        $validate->validateData('first_name', 'Please enter the first name');
        $validate->validateData('last_name' , 'Please enter the last name');
        $validate->validateData('email' , 'Please enter the email');

        $email = $fn->getReqParam('email');
        $contact_id = $fn->getReqParam('contact_id');
        $rowCont = $fn->getRecordRowByID('contact', 'contact_id', $contact_id);

        if ($rowCont['email'] != $email) {
            $SQL = "
            SELECT COUNT(*) AS count
            FROM contact c
            WHERE c.email = '{$email}'
            ";
            $result = $db->sql_query($SQL);
            $row = $db->sql_fetchrow($result);
            if ($row['count'] > 0) {
                $validate->errorArray['email']['name'] = 'email';
                $validate->errorArray['email']['msg']  = 'Email is not unique.';
            }
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
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id, 'detail');
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'name');
        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'salutation');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'phone_country_code');
        $fa = $fn->addToFieldsArray($fa, 'phone_area_code');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'fax_country_code');
        $fa = $fn->addToFieldsArray($fa, 'fax_area_code');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'mobile_country_code');
        $fa = $fn->addToFieldsArray($fa, 'mobile_area_code');
        $fa = $fn->addToFieldsArray($fa, 'mobile');
        $fa = $fn->addToFieldsArray($fa, 'position');
        $fa = $fn->addToFieldsArray($fa, 'company_address_id');
        $fa = $fn->addToFieldsArray($fa, 'company_id');
        $fa = $fn->addToFieldsArray($fa, 'department');
        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'status');
        
        return $fa;
    }

    /**
     *
     */
    function getContactsByCompanySQL($company_id = 0) {

        $append = "WHERE c.company_id = {$company_id}";

        $sql = "
        SELECT c.contact_id
              ,CONCAT_WS(' ', c.first_name, c.last_name) AS contact_name
        FROM contact c
        {$append}
        ORDER BY contact_name
        ";
        return $sql;
    }
}
