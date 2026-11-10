<?
class CPL_Admin_Widgets_Payroll_VacationReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
	/**
     *
     */
    function getSQL(){

        return "
        SELECT l.*
              ,date_format(l.from_date, '%d %b %Y') AS leave_start_date_formatted
              ,date_format(l.to_date, '%d %b %Y') AS leave_to_date_formatted
              ,e.first_name AS employee_name
              ,e.citizen
              ,e.nric_no
              ,e.fin_no
              ,e.work_permit
        FROM `leave` l
        LEFT JOIN (employee e) ON (l.employee_id = e.employee_id)
        ";
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'l';

        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $current_date = date('Y-m-d');
        $month        = date('m');
        $year         = date('Y');

        if ($start_date != '' && $end_date == '') {
            $searchVar->sqlSearchVar[] = "(l.from_date >= '{$start_date}' OR l.to_date <= '{$current_date}')";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $searchVar->sqlSearchVar[] = "(l.from_date >= '{$start_date}' OR l.to_date <= '{$end_date}')";
        } else if ($start_date != '' && $end_date != '') {
            $searchVar->sqlSearchVar[] = "(l.from_date >= '{$start_date}' OR l.to_date <= '{$end_date}')";
        } else if ($start_date == '' && $end_date == ''){
            $start_date = date('Y-m-') . '01';
            $searchVar->sqlSearchVar[] = "(l.from_date >= '{$start_date}' OR l.to_date <= '{$current_date}')";
        }

        $searchVar->sqlSearchVar[] = "l.status = 'Approved'";
        $searchVar->sqlSearchVar[] = "l.went_overseas = 1";
        $searchVar->sortOrder = 'l.from_date ASC';
    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enggCrm_vacationReport');

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
              'first_name'              => $phpExcel->getFldObj('Employee Name')
             ,'room_no'                 => $phpExcel->getFldObj('Room Number')
             ,'mobile'                  => $phpExcel->getFldObj('HP/Mobile No')
             ,'citizen'                 => $phpExcel->getFldObj('Pass Type')
             ,'fin_no'                  => $phpExcel->getFldObj('Fin No')
             ,'work_permit'             => $phpExcel->getFldObj('Work Permit No')
             ,'work_permit_expiry_date_formatted'   => $phpExcel->getFldObj('Work Permit Expiry')
             ,'date_of_birth_formatted' => $phpExcel->getFldObj('Date of Birth')
        );

        $file_name = "DormitoryReport_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    } 
}