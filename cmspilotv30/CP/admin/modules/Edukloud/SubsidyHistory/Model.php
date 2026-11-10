<?
class CP_Admin_Modules_Edukloud_SubsidyHistory_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT sh.*
        FROM subsidy_paid_history sh 
        LEFT JOIN (`order` o) ON (sh.order_id = o.order_id)
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
        $searchVar->mainTableAlias = 'sh';


        $subsidy_history_id     = $fn->getReqParam('subsidy_history_id');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "sh.subsidy_history_id = '{$tv['record_id']}'";
            $searchVar->sqlSearchVar[] = "sh.subsidy_code = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'sh.subsidy_history_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                                        sh.order_id       LIKE '%{$tv['keyword']}%' OR
                                        sh.subsidy_code   LIKE '%{$tv['keyword']}%'
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
        $validate->validateData('title', 'Please enter the title');

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
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'order_id');
        $fa = $fn->addToFieldsArray($fa, 'paid_date');
        

        return $fa;
    }
    
    /**
     *
     */
    function getSaveFromList(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $subsidy_history_id = $fn->getReqParam('subsidy_paid_history_id');

        if (!$this->getEditFromListValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, 'subsidy_paid_history', 'subsidy_history_id', $subsidy_history_id);
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditFromListValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('paid_date', 'Please choose paid date');
        $validate->validateData('status', 'Please choose the status');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
}
