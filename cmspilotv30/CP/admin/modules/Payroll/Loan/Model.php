<?
class CP_Admin_Modules_Payroll_Loan_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT l.*
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
        FROM `loan` l
        LEFT JOIN (employee e) ON (e.employee_id = l.employee_id)
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
        
        $searchVar->mainTableAlias = 'l';
        
        $status  = $fn->getReqParam('status');
        $loan_id = $fn->getReqParam('loan_id');

        if ($loan_id != "") {
            $searchVar->sqlSearchVar[] = "l.loan_id = '{$loan_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "l.loan_id = '{$tv['record_id']}'";
        } else {
            //$fn->setSearchVarForLinkData($searchVar, $linkRecType, 's.salary_id');

            if ($status != "") {
                $searchVar->sqlSearchVar[] = "l.status = '{$status}'";
            }

           /* if ($category != "") {
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
        $validate = Zend_Registry::get('validate');

        $amount       = $fn->getReqParam('amount');
        $month_amount = $fn->getReqParam('month_amount');

        $validate->resetErrorArray();
        $validate->validateData('employee_id', 'Please select the employee');
        $validate->validateData('amount', 'Please enter total loan amount');
        $validate->validateData('month_amount', 'Please enter amount payable(per month)');

        if ($month_amount > $amount) {
            $msg = 'Please enter Total loan amount more than Amount payable(per month)';
            $validate->validateData('error_box', $msg);
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
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $amount = $fn->getReqParam('amount');
        $month_amount = $fn->getReqParam('month_amount');

        $validate->resetErrorArray();

        $validate->validateData('status', 'Please select status');
        $validate->validateData('type', 'Please select type of loan');
        $validate->validateData('month_amount', 'Please enter amount payable(per month)');
        $validate->validateData('amount', 'Please enter total loan amount');
        if ($month_amount > $amount) {
            $msg = 'Please enter Total loan amount more than Amount payable(per month)';
            $validate->validateData('error_box', $msg);
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
        $cpCfg = Zend_Registry::get('cpCfg');

        $status = $fn->getReqParam('status');
        $loan_start_date = $fn->getReqParam('loan_start_date');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        if ($status == 'Active' && $loan_start_date == ''){
            $fa['loan_start_date'] = date('Y-m-d');
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
        $fa = $fn->addToFieldsArray($fa, 'amount');
        $fa = $fn->addToFieldsArray($fa, 'date');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'type');
        $fa = $fn->addToFieldsArray($fa, 'due_date');
        $fa = $fn->addToFieldsArray($fa, 'no_of_months');
        $fa = $fn->addToFieldsArray($fa, 'loan_closing_date');
        $fa = $fn->addToFieldsArray($fa, 'creation_date');
        $fa = $fn->addToFieldsArray($fa, 'month_amount');
        $fa = $fn->addToFieldsArray($fa, 'modification_date');
        $fa = $fn->addToFieldsArray($fa, 'created_by');
        $fa = $fn->addToFieldsArray($fa, 'modified_by');
        $fa = $fn->addToFieldsArray($fa, 'notes');

        return $fa;
    }

    /**
     *
     */
    function getPayrollLoanPayrollLoanRepaymentLinkSQL($id) {

        $SQL = "
        SELECT lrh.loan_repayment_history_id
              ,DATE_FORMAT(lrh.generated_date, '%d-%m-%Y') AS payment_date
              ,CONCAT_WS('/', pm.payroll_month, pm.payroll_year) AS payroll_month_year
              ,lrh.loan_repayment_amount_per_month
              ,lrh.remarks
        FROM loan_repayment_history lrh
        LEFT JOIN loan l ON (lrh.loan_id = l.loan_id)
        LEFT JOIN payroll_management pm ON (lrh.payroll_management_id = pm.payroll_management_id)
        WHERE lrh.loan_id = '{$id}'
        ORDER BY lrh.generated_date DESC
        ";

        return $SQL;
    }
}
