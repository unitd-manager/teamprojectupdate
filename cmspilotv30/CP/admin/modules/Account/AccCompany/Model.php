<?
class CP_Admin_Modules_Account_AccCompany_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL = "
        SELECT ac.*
        	  ,c.title AS currency_title
        FROM acc_company ac
        LEFT JOIN (currency c) ON (ac.base_currency_id = c.currency_id)
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

        $currency_id  = $fn->getReqParam('currency_id');

        if ($tv['record_id'] != '' ) {
            $searchVar->sqlSearchVar[] = "ac.acc_company_id  = '{$tv['record_id']}'";

        } else {
            if ($currency_id != '') {
                $searchVar->sqlSearchVar[] = "ac.base_currency_id = {$currency_id}";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       ac.company_name LIKE '%{$tv['keyword']}%'
                    OR ac.account_year LIKE '%{$tv['keyword']}%'
                )";
            }
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('company_name', 'Please enter the company name');

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

        $validate->validateData('company_name', 'Please enter the company name');

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
        $fn->returnAfterNewSave($id);
    }

    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'account_year');
        $fa = $fn->addToFieldsArray($fa, 'base_currency_id');

        return $fa;
    }

    function getBaseCurrencyCode() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $acc_company_id = $fn->getSessionParam('acc_company_id');
        
        $SQL = "
        SELECT c.code
        FROM currency c
        JOIN acc_company ac ON ac.base_currency_id = c.currency_id
        WHERE ac.acc_company_id = {$acc_company_id}
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        return $row['code'];
    }

}
