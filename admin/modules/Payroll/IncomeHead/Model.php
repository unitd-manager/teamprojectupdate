<?
class CPL_Admin_Modules_Payroll_IncomeHead_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $fn = Zend_Registry::get('fn');

        $SQL ="
        SELECT eg.*
        FROM income_group eg
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
            $searchVar->sqlSearchVar[] = "eg.income_group_id = {$tv['record_id']}";
        }


        $searchVar->sortOrder = "eg.income_group_id";

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
    function getIncomeSubHeadFormSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        if (!$this->getIncomeSubHeadValidate()){
            return $validate->getErrorMessageXML();
        }

        $income_group_id = $fn->getPostParam('income_group_id');
        $title            = $fn->getPostParam('title');

        $fa = array();

        $fa['title']            = $title;
        $fa['income_group_id']  = $income_group_id;
        $fa['creation_date']    = date("Y-m-d H:i:s");
        $fa['created_by']       = $fn->getSessionParam('userName');
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $fa['site_id'] = $cpSiteIdSession;
        }

        $insertIncomeHeadSQL = $dbUtil->getInsertSQLStringFromArray($fa, 'income_sub_group');
        $resultIncomeHeadSQL = $db->sql_query($insertIncomeHeadSQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditIncomeSubHeadFormSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        if (!$this->getEditIncomeSubHeadValidate()){
            return $validate->getErrorMessageXML();
        }

        $income_group_id     = $fn->getPostParam('income_group_id');
        $title               = $fn->getPostParam('title');
        $income_sub_group_id = $fn->getPostParam('income_sub_group_id');


        $fa1 = array();

        $fa1['title']                 = $title;
        $fa1['income_sub_group_id']   = $income_sub_group_id;
        $fa1['modification_date']     = date("Y-m-d H:i:s");
        $fa1['modified_by']           = $fn->getSessionParam('userName');
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $fa1['site_id'] = $cpSiteIdSession;
        }

        $whereCondition      = "WHERE income_sub_group_id = {$income_sub_group_id}" ;
        $sqlUpdateIncome    = $dbUtil->getUpdateSQLStringFromArray($fa1, "income_sub_group", $whereCondition);
        $resultUpdateIncome = $db->sql_query($sqlUpdateIncome);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getIncomeSubHeadValidate() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $income_group_id = $fn->getPostParam('income_group_id');
        $title           = $fn->getPostParam('title');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter Title');

        if ($title != '') {
            $count = $fn->getRecordCountBySQL('income_sub_group', "title = '{$title}' AND income_group_id = {$income_group_id}");
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
    function getEditIncomeSubHeadValidate() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $income_sub_group_id = $fn->getPostParam('income_sub_group_id');
        $title               = $fn->getPostParam('title');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter Title');

        if ($title != '') {
            $rec = $fn->getRecordRowById('income_sub_group', 'income_sub_group_id', $income_sub_group_id);
            $count = $fn->getRecordCountBySQL('income_sub_group', "title = '{$title}' AND income_group_id = {$rec['income_group_id']} AND income_sub_group_id != '{$income_sub_group_id}'");
            if ($count > 0) {
                $validate->errorArray['title']['name'] = "title";
                $validate->errorArray['title']['msg']  = 'Title already exists in Income Head';
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
    function getDeleteIncomeSubHead(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $income_group_id = $fn->getReqParam('income_group_id');
        $income_sub_group_id = $fn->getReqParam('income_sub_group_id');

        $SQL ="
               DELETE FROM income_sub_group
               WHERE income_sub_group_id = {$income_sub_group_id}
               ";
        $result = $db->sql_query($SQL);
    }

}