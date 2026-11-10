<?
class CPL_Admin_Widgets_Payroll_DormitoryReport_Model extends CP_Common_Lib_WidgetModelAbstract
{

	/**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT e.*
              ,date_format(e.work_permit_expiry_date, '%d %b %Y') AS work_permit_expiry_date_formatted
              ,date_format(e.date_of_birth, '%d %b %Y') AS date_of_birth_formatted
        FROM employee e
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'e';

        $dormitory_id = $fn->getReqParam('dormitory_id');

        if ($dormitory_id != '') {
            $searchVar->sqlSearchVar[] = "e.dormitory_id = '{$dormitory_id}'";
        }

        $searchVar->sqlSearchVar[] = "e.status = 'Current'";
        
        $searchVar->sortOrder = 'e.employee_name ASC';
    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enggCrm_dormitoryReport');

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