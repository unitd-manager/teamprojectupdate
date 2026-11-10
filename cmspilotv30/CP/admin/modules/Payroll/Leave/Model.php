<?
class CP_Admin_Modules_Payroll_Leave_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT l.*
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
              ,j.designation
              ,e.citizen
        FROM `leave` l
        LEFT JOIN (employee e) ON (l.employee_id = e.employee_id)
        LEFT JOIN (job_information j) ON (j.employee_id = l.employee_id)
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
        
        $searchVar->mainTableAlias = 'l';

        $leave_id    = $fn->getReqParam('leave_id');
        $employee_id = $fn->getReqParam('employee_id');
        $status      = $fn->getReqParam('status');

        if ($leave_id != "") {
            $searchVar->sqlSearchVar[] = "l.leave_id = '{$leave_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "l.leave_id = '{$tv['record_id']}'";
        } else {

            if ($employee_id != "") {
                $searchVar->sqlSearchVar[] = "l.employee_id = '{$employee_id}'";
            }

            if ($status != "") {
                $searchVar->sqlSearchVar[] = "l.status = '{$status}'";
            }

            //$fn->setSearchVarForLinkData($searchVar, $linkRecType, 's.salary_id');
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'l.leave_id');

          /*  if ($status != "") {
                $searchVar->sqlSearchVar[] = "c.status = '{$status}'";
            }

            if ($category != "") {
                $searchVar->sqlSearchVar[] = "c.category = '{$category}'";
            }

            if ($company_name != "") {
                $searchVar->sqlSearchVar[] = "c.company_name LIKE '%{$company_name}%'";
            }*/

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    l.reason  LIKE '%{$tv['keyword']}%'
                )";
            }

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "l.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(l.flag != 1 OR l.flag IS null)";
            }

            //$searchVar->sortOrder = "c.company_name";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $employee_id = $fn->getPostParam('employee_id');
        $from_date   = $fn->getPostParam('from_date');
        $to_date     = $fn->getPostParam('to_date');

        $validate->resetErrorArray();
        $validate->validateData('employee_id', 'Please select employee');
        $validate->validateData('from_date', 'Please select from date');
        $validate->validateData('to_date', 'Please select to date');
        $validate->validateData('leave_type', 'Please select type of leave');

        if ($to_date < $from_date) {
            $validate->errorArray['from_date']['name'] = "from_date";
            $validate->errorArray['from_date']['msg']  = "From date is greater than To date.";
        }

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite   = "";
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND site_id = {$cpSiteIdSession}";
        }

        /* Find leave dates already applied */
        if ($employee_id) {
            $sqlLeave = "
            SELECT leave_id FROM `leave`
            WHERE employee_id = {$employee_id}
              AND ((from_date BETWEEN '{$from_date}' AND '{$to_date}')
               OR (to_date BETWEEN '{$from_date}' AND '{$to_date}')
               OR (from_date <= '{$from_date}' AND to_date >= '{$to_date}'))
              {$appendSqlSite}
            ";
            $resultLeave = $db->sql_query($sqlLeave);
            $numRowsLeave = $db->sql_numrows($resultLeave);

            if ($from_date != '' && $numRowsLeave)  {
                $validate->errorArray['from_date']['name'] = "from_date";
                $validate->errorArray['from_date']['msg']  = "Leave already entered for selected dates";
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
        $fa['status'] = 'Applied';
        $fa['date'] = date('Y-m-d');
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $cpCfg    = Zend_Registry::get('cpCfg');

        $validate->resetErrorArray();

        $employee_id = $fn->getReqParam('emp_id');
        $from_date   = $fn->getPostParam('from_date');
        $to_date     = $fn->getPostParam('to_date');
        $leave_id    = $fn->getReqParam('leave_id');

        $leaveRec = $fn->getRecordRowById('leave', 'leave_id', $leave_id);

        $validate->validateData('leave_type', 'Please select type of leave');
        $validate->validateData('status', 'Please select status');
        $validate->validateData('from_date', 'Please select from date');
        $validate->validateData('to_date', 'Please select to date');
        $validate->validateData('no_of_days', 'Please enter No of dates');

        if ($to_date < $from_date) {
            $validate->errorArray['from_date']['name'] = "from_date";
            $validate->errorArray['from_date']['msg']  = "From date is greater than To date.";
        }

        if($employee_id != ''){
            $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
            $appendSqlSite   = "";
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSqlSite = "AND site_id = {$cpSiteIdSession}";
            }

            $SQL="
            SELECT * FROM `leave` 
            WHERE employee_id = {$employee_id}
              AND ((from_date BETWEEN '{$from_date}' AND '{$to_date}')
               OR (to_date BETWEEN '{$from_date}' AND '{$to_date}')
               OR (from_date <= '{$from_date}' AND to_date >= '{$to_date}'))
              AND leave_id != {$leave_id}
              {$appendSqlSite}
            ";
            $result   = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);

            if($numRows > 0){
                $validate->errorArray['from_date']['name'] = "from_date";
                $validate->errorArray['from_date']['msg']  = "Please note leave is applied for mentioned dates";
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $leave_type = $fn->getReqParam('leave_type');
        $emp_type   = $fn->getReqParam('emp_type');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();

        if ($leave_type == 'Annual Leave') {
            $fa['went_overseas'] = $fn->getPostParam('went_overseas');
        } else {
            $fa['went_overseas'] = '0';
        }

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
        $fa = $fn->addToFieldsArray($fa, 'employee_id');
        $fa = $fn->addToFieldsArray($fa, 'designation');
        $fa = $fn->addToFieldsArray($fa, 'from_date');
        $fa = $fn->addToFieldsArray($fa, 'to_date');
        $fa = $fn->addToFieldsArray($fa, 'leave_type');
        $fa = $fn->addToFieldsArray($fa, 'date');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'reason');
        $fa = $fn->addToFieldsArray($fa, 'creation_date');
        $fa = $fn->addToFieldsArray($fa, 'modification_date');
        $fa = $fn->addToFieldsArray($fa, 'created_by');
        $fa = $fn->addToFieldsArray($fa, 'modified_by');
        $fa = $fn->addToFieldsArray($fa, 'leave_id');
        $fa = $fn->addToFieldsArray($fa, 'no_of_days');
        $fa = $fn->addToFieldsArray($fa, 'no_of_days_next_month');

        return $fa;
    }
}
