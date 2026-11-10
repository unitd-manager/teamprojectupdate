<?
class CP_Admin_Modules_Pos_Gift_Model extends CP_Common_Lib_ModuleModelAbstract
{

    function getSQL() {

        $SQL = "
        SELECT g.*
        FROM gift g
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
        $searchVar->mainTableAlias = 'g';

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "g.gift_id = {$tv['record_id']}";

        } 
    
        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
            g.title LIKE '%{$tv['keyword']}%'
            )";
        }        

    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('title', 'Please enter the Card No');

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
            $validate->validateData('title', 'Please enter the Card No');
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

        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'card_start_no');
        $fa = $fn->addToFieldsArray($fa, 'card_end_no');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'auto_bar_code');
        $fa = $fn->addToFieldsArray($fa, 'cost');
        $fa = $fn->addToFieldsArray($fa, 'amount');
        $fa = $fn->addToFieldsArray($fa, 'sell_amount');
        $fa = $fn->addToFieldsArray($fa, 'discount');
        $fa = $fn->addToFieldsArray($fa, 'type');
        $fa = $fn->addToFieldsArray($fa, 'eff_date_from');
        $fa = $fn->addToFieldsArray($fa, 'eff_date_to');
        $fa = $fn->addToFieldsArray($fa, 'cost_currency');
        $fa = $fn->addToFieldsArray($fa, 'amount_currency');


        return $fa;
    }

}
