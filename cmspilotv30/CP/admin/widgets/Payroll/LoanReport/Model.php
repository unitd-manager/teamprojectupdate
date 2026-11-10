<?
class CP_Admin_Widgets_Payroll_LoanReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT l.*
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
              ,e.nric_no
              ,e.position
              ,e.department
        FROM `loan` l
        LEFT JOIN (employee e) ON (e.employee_id = l.employee_id)

        ";


        return $SQL;
    }
    
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'l';

        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $month          = date('m');
        $year           = date('Y');
        $due_date       = $fn->getReqParam('due_date');
        
        if ($start_date == '') {
            $start_date = date('Y-m-d', mktime (0,0,0,date("m")-12, date("d"), date("Y")));
        }
        
        if ($end_date == '') {
            $end_date = date('Y-m-d');
        } 

        //$searchVar->sqlSearchVar[] = "l.due_date BETWEEN '{$start_date}' AND '{$end_date}'";

        /*if ($status != '') {
            $searchVar->sqlSearchVar[] = "o.status = '{$status}'";
        }

        if ($category != '') {
            $searchVar->sqlSearchVar[] = "o.category = '{$category}'";
        }
        
        $searchVar->sortOrder = 'o.opportunity_code DESC';*/
    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'payroll_loanReport');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }
    
    /**
     *
     */
    function getExportToExcel($dataArray = ''){
        $dbUtil = Zend_Registry::get('dbUtil');
        
        if (!is_array($dataArray)){
            $dataArray = $this->getDataArray();
        }

        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');         
        $fa = array(
              'employee_name'       => $phpExcel->getFldObj('Emplloyee Name')
             ,'nric_no'             => $phpExcel->getFldObj('NRIC')
             ,'position'            => $phpExcel->getFldObj('Designation')
             ,'department'          => $phpExcel->getFldObj('Department')
             ,'no_of_months'        => $phpExcel->getFldObj('Loan Duration')
             ,'amount'              => $phpExcel->getFldObj('Loan Amount')
             ,'due_date'            => $phpExcel->getFldObj('Amount Returned')
             ,'loan_closing_date'   => $phpExcel->getFldObj('Balance to be Paid')
        );

        $file_name = "LoanReport_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }
}