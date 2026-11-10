<?
class CP_Admin_Modules_Pos_Mwo_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL = "
        SELECT * 
        FROM mwo m
        ";
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'm';

        if ($tv['record_id'] != '' ) {
            $searchVar->sqlSearchVar[] = "m.mwo_id  = '{$tv['record_id']}'";
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
                m.code LIKE '%{$tv['keyword']}%'
                OR m.cost_value LIKE '%{$tv['keyword']}%'  
                OR m.currency       LIKE '%{$tv['keyword']}%'
            )";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('code', 'Please enter the code');

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
        $validate->validateData('code', 'Please enter the code');

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
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'code');
        $fa = $fn->addToFieldsArray($fa, 'cost_value');
        $fa = $fn->addToFieldsArray($fa, 'currency');
        $fa = $fn->addToFieldsArray($fa, 'operator');
        $fa = $fn->addToFieldsArray($fa, 'meal_allowance_days');
        $fa = $fn->addToFieldsArray($fa, 'day_off_allowance_days');
        $fa = $fn->addToFieldsArray($fa, 'working_time');
        $fa = $fn->addToFieldsArray($fa, 'meal_day');
        $fa = $fn->addToFieldsArray($fa, 'no_of_day_off');
        
        return $fa;
    }

    function getMwoCodeSQL() {
        $SQL = "
        SELECT code 
        FROM mwo
        ";
        
        return $SQL;
    }

}
