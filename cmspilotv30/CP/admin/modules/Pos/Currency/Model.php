<?
class CP_Admin_Modules_Pos_Currency_Model extends CP_Common_Lib_ModuleModelAbstract
{

    function getSQL() {
        $SQL = "
        SELECT c.*
        FROM currency c
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

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.currency_id = {$tv['record_id']}";

        } 
        
        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
            c.code LIKE '%{$tv['keyword']}%'
            OR c.title  LIKE '%{$tv['keyword']}%'
            )";
        }        
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('code', 'Please enter the Currency Code');

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
        $tv = Zend_Registry::get('tv');

        $validate->resetErrorArray();

        if ($tv['lang'] == 'eng') {
            $validate->validateData('code', 'Please enter the Currency Code');
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
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'code', '', true);
        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'description', '', true);

        return $fa;
    }

    function getCurrencyCodeSQL() {
        $SQL = "
        SELECT code 
        FROM currency
        ORDER BY code
        ";
        
        return $SQL;
    }

    /**
     *
     */
    function getCurrencySQL() {
    	$SQL = "
    	SELECT currency_id
              ,CONCAT_WS(' - ', code, title) AS title
    	FROM currency
    	ORDER BY code
    	";
        return $SQL;
    }
}
