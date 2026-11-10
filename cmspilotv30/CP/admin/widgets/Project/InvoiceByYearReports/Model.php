<?
class CP_Admin_Widgets_Project_InvoiceByYearReports_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT DATE_FORMAT(i.invoice_date, '%Y') AS invoice_year
              ,(SUM(i.invoice_amount)) AS invoice_amount_yearly
        FROM invoice i
        ";

        return $SQL;
    }
    
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'i';

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        
        if ($start_date == '') {
            $start_date = date('Y-m-d', mktime (0,0,0,date("m")-12, date("d"), date("Y")));
        }
        
        if ($end_date == '') {
            $end_date = date('Y-m-d');
        } 

        $searchVar->sqlSearchVar[] = "i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";

        $searchVar->sqlSearchVar[] = "i.status !='Cancelled'";
        $searchVar->groupBy = "DATE_FORMAT(i.invoice_date, '%Y')";

    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'project_invoiceByYearReports');

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
              'invoice_year'            => $phpExcel->getFldObj('Year')
             ,'invoice_amount_yearly'   => $phpExcel->getFldObj('Amount')
        );

        $file_name = "InvoiceByYear_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }
}