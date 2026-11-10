<?
class CP_Admin_Modules_Pos_Payment_Model extends CP_Common_Lib_ModuleModelAbstract
{

    function getSQL() {

        $SQL = "
        SELECT p.*
        FROM payment p
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
        $searchVar->mainTableAlias = 'p';

      
        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "p.payment_id = {$tv['record_id']}";

        } 
        
        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
            p.code LIKE '%{$tv['keyword']}%'
            OR p.title  LIKE '%{$tv['keyword']}%'
            )";
        }        

    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('code', 'Please enter the Payment Code');

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
            $validate->validateData('code', 'Please enter the Payment Code');
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
        $fa = $fn->addToFieldsArray($fa, 'payment_type', '', true);
        $fa = $fn->addToFieldsArray($fa, 'days', '', true);

        return $fa;
    }

    /**
     *
     */
    function getPaymentSQL() {

        $SQL = "
        SELECT p.payment_id
              ,p.title
        FROM payment p
        WHERE payment_type = 'Purchase Order'
           OR payment_type = 'Purchase Order and Invoice'
        ";
        
        return $SQL;
    }

}
