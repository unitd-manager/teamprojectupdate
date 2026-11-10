<?
class CP_Admin_Modules_Payroll_Training_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT t.*
        FROM training t
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv        = Zend_Registry::get('tv');
        $fn        = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');

        $searchVar->mainTableAlias = 't';
        
        $training_id = $fn->getReqParam('training_id');
        $status      = $fn->getReqParam('status');

        if ($training_id != "") {
            $searchVar->sqlSearchVar[] = "t.training_id = '{$training_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "t.training_id = '{$tv['record_id']}'";
        } else {
            /*$fn->setSearchVarForLinkData($searchVar, $linkRecType, 'a.company_id');

            if ($category != "") {
                $searchVar->sqlSearchVar[] = "c.category = '{$category}'";
            }

            if ($company_name != "") {
                $searchVar->sqlSearchVar[] = "c.company_name LIKE '%{$company_name}%'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    c.company_name  LIKE '%{$tv['keyword']}%'
                    OR c.group_name LIKE '%{$tv['keyword']}%'
                    OR c.email      LIKE '%{$tv['keyword']}%'
                )";
            }*/

            if ($status != "") {
                $searchVar->sqlSearchVar[] = "t.status = '{$status}'";
            }

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "t.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(t.flag != 1 OR t.flag IS null)";
            }

            //$searchVar->sortOrder = "c.company_name";
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
        $cpCfg = Zend_Registry::get('cpCfg');

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
    function getSaveList(){
        $fn = Zend_Registry::get('fn');
        $fn->getSaveList();
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'date');
        $fa = $fn->addToFieldsArray($fa, 'trainer');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'creation_date');
        $fa = $fn->addToFieldsArray($fa, 'modification_date');
        $fa = $fn->addToFieldsArray($fa, 'created_by');
        $fa = $fn->addToFieldsArray($fa, 'modified_by');
        $fa = $fn->addToFieldsArray($fa, 'training_company_name');
        $fa = $fn->addToFieldsArray($fa, 'training_company_address');
        $fa = $fn->addToFieldsArray($fa, 'training_company_email');
        $fa = $fn->addToFieldsArray($fa, 'training_company_phone');
        $fa = $fn->addToFieldsArray($fa, 'to_date');


        return $fa;
    }

    /**
     *
     */
    function getTrainingEmplyoeeFormSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        if (!$this->getTrainingEmplyoeeValidate()){
            return $validate->getErrorMessageXML();
        }

        $staff_id   = $fn->getPostParam('staff_id');
        $training_id = $fn->getPostParam('training_id');
        //$Medicine_Name= $fn->getPostParam('Medicine_Name');

        $fa = array();

        $fa['staff_id']     = $staff_id;
        $fa['training_id']  = $training_id;
        $fa['creation_date']= date("Y-m-d H:i:s");
        $fa['created_by']   = $fn->getSessionParam('userName');

        $insertTrainingSQL = $dbUtil->getInsertSQLStringFromArray($fa, 'training_staff');
        $resultTrainingSQL = $db->sql_query($insertTrainingSQL);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getTrainingEmplyoeeValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        //$validate->validateData('product_group_id', 'Please select the product group');
        $validate->validateData('staff_id', 'Please enter Emplyoee Name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getDeleteTrainingEmplyoee(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $training_id = $fn->getReqParam('training_id');
        $training_staff_id = $fn->getReqParam('training_staff_id');

        $SQL ="
               DELETE FROM training_staff
               WHERE training_staff_id = {$training_staff_id}
               ";
        $result = $db->sql_query($SQL);
    }

    /**
     *
     */    
    function getPayrollTrainingPayrollEmployeeLinkSQL($id) {
        $fn    = Zend_Registry::get('fn');
        $db    = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite = "";
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND b.site_id = {$cpSiteIdSession}";
        }

        return "
        SELECT a.contact_id
              ,a.first_name
              ,a.last_name
              ,a.email
              ,a.phone_direct
              ,a.mobile
              ,a.position
              ,a.department
        FROM company b, contact a
        WHERE a.company_id = b.company_id
          AND b.company_id = {$id}
          {$appendSqlSite}
        ";
    }

    /**
     *
     */
    function getLinkEmployeeToCourse() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $training_id = $fn->getReqParam('training_id');

        $fa = array();
        $fa['training_id'] = $training_id;
        $id = $fn->addRecord($fa, 'training_staff');
    }

    /**
     *
     */
    function getUpdateEmployeeId() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $training_staff_id = $fn->getReqParam('training_staff_id');
        $staff_id = $fn->getReqParam('staff_id');

        $SQLUpdateEmployee = "
        UPDATE training_staff SET staff_id = '{$staff_id}'
        WHERE training_staff_id = '{$training_staff_id}'
        ";
        $resultUpdateEmployee = $db->sql_query($SQLUpdateEmployee);
    }

    /**
     *
     */
    function getUpdateStaffFromDateForEmployeeLink() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $training_staff_id = $fn->getReqParam('training_staff_id');
        $from_date = $fn->getReqParam('from_date');

        $SQLUpdateEmployee = "
        UPDATE training_staff SET from_date = '{$from_date}'
        WHERE training_staff_id = '{$training_staff_id}'
        ";
        $resultUpdateEmployee = $db->sql_query($SQLUpdateEmployee);
    }

    /**
     *
     */
    function getUpdateStaffToDateForEmployeeLink() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $training_staff_id = $fn->getReqParam('training_staff_id');
        $to_date = $fn->getReqParam('to_date');

        $SQLUpdateEmployee = "
        UPDATE training_staff SET to_date = '{$to_date}'
        WHERE training_staff_id = '{$training_staff_id}'
        ";
        $resultUpdateEmployee = $db->sql_query($SQLUpdateEmployee);
    }
}
