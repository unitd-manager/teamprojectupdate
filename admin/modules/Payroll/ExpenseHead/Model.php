<?
class CPL_Admin_Modules_Payroll_ExpenseHead_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL ="
        SELECT eg.*
        FROM expense_group eg
        ";

        return $SQL;
    }

    /**
     *
     */

    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $searchVar = Zend_Registry::get('searchVar');
        $fn = Zend_Registry::get('fn');
        $searchVar->mainTableAlias = 'eg';

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "eg.expense_group_id = {$tv['record_id']}";
        }

        $searchVar->sortOrder = "eg.expense_group_id";
    }

    /**
    *
    */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        //$validate->validateData('date', 'Please enter date');
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
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $current_date = date('Y-m-d');

        $fa = $this->getFields();

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $id = $fn->addRecord($fa);

        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter title');

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
        $db = Zend_Registry::get('db');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'created_by');
        $fa = $fn->addToFieldsArray($fa, 'creation_date');
        $fa = $fn->addToFieldsArray($fa, 'modified_by');
        $fa = $fn->addToFieldsArray($fa, 'modification_date');

        return $fa;
    }
    /**
     *
     */
    function getExpenseSubHeadFormSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        if (!$this->getExpenseSubHeadValidate()){
            return $validate->getErrorMessageXML();
        }

        $expense_group_id = $fn->getPostParam('expense_group_id');
        $title            = $fn->getPostParam('title');

        $fa = array();

        $fa['title']            = $title;
        $fa['expense_group_id'] = $expense_group_id;
        $fa['creation_date']    = date("Y-m-d H:i:s");
        $fa['created_by']       = $fn->getSessionParam('userName');
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $fa['site_id'] = $cpSiteIdSession;
        }

        $insertExpenseHeadSQL = $dbUtil->getInsertSQLStringFromArray($fa, 'expense_sub_group');
        $resultExpenseHeadSQL = $db->sql_query($insertExpenseHeadSQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditExpenseSubHeadFormSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        if (!$this->getEditExpenseSubHeadValidate()){
            return $validate->getErrorMessageXML();
        }

        $expense_group_id     = $fn->getPostParam('expense_group_id');
        $title                = $fn->getPostParam('title');
        $expense_sub_group_id = $fn->getPostParam('expense_sub_group_id');


        $fa1 = array();

        $fa1['title']                 = $title;
        $fa1['expense_sub_group_id']  = $expense_sub_group_id;
        $fa1['modification_date']     = date("Y-m-d H:i:s");
        $fa1['modified_by']           = $fn->getSessionParam('userName');
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $fa1['site_id'] = $cpSiteIdSession;
        }

        $whereCondition      = "WHERE expense_sub_group_id = {$expense_sub_group_id}" ;
        $sqlUpdateExpense    = $dbUtil->getUpdateSQLStringFromArray($fa1, "expense_sub_group", $whereCondition);
        $resultUpdateExpense = $db->sql_query($sqlUpdateExpense);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getExpenseSubHeadValidate() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $expense_group_id = $fn->getPostParam('expense_group_id');
        $title            = $fn->getPostParam('title');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter Title');

        if ($title != '') {
            $count = $fn->getRecordCountBySQL('expense_sub_group', "title = '{$title}' AND expense_group_id = {$expense_group_id}");
            if ($count > 0) {
                $validate->errorArray['title']['name'] = "title";
                $validate->errorArray['title']['msg']  = 'Title already exists in Expense Head';
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
    function getEditExpenseSubHeadValidate() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $expense_sub_group_id = $fn->getPostParam('expense_sub_group_id');
        $title                = $fn->getPostParam('title');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter Title');

        if ($title != '') {
            $rec = $fn->getRecordRowById('expense_sub_group', 'expense_sub_group_id', $expense_sub_group_id);
            $count = $fn->getRecordCountBySQL('expense_sub_group', "title = '{$title}' AND expense_group_id = {$rec['expense_group_id']} AND expense_sub_group_id != '{$expense_sub_group_id}'");
            if ($count > 0) {
                $validate->errorArray['title']['name'] = "title";
                $validate->errorArray['title']['msg']  = 'Title already exists in Expense Head';
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
    function getDeleteExpenseSubHead(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $expense_group_id = $fn->getReqParam('expense_group_id');
        $expense_sub_group_id = $fn->getReqParam('expense_sub_group_id');

        $SQL ="
               DELETE FROM expense_sub_group
               WHERE expense_sub_group_id = {$expense_sub_group_id}
               ";
        $result = $db->sql_query($SQL);
    }

}