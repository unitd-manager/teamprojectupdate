<?
class CP_Admin_Widgets_Tradingsg_EnquiryByYear_Model extends CP_Common_Lib_WidgetModelAbstract
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
        
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $specialSearch  = $fn->getReqParam('specialSearch');
        $c = &$this->controller;

        if($c->searcVarCondn){
            $searchVar->sqlSearchVar[] = "{$c->searcVarCondn}";
        }

        /*
        if ($start_date != ''){
            $searchVar->sqlSearchVar[] = "b.start_date >= '{$start_date}'";
        }

        if ($end_date != ''){
            $searchVar->sqlSearchVar[] = "b.end_date <= '{$end_date}'";
        }

        if ($specialSearch != ''){
            $searchVar->sqlSearchVar[] = "b.status = '{$specialSearch}'";
        }
        */

        $searchVar->groupBy = 'invoice_year';
        
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_enquiryByYear');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }
    
    /**
     *
     */
    function getExportToExcel($dataArray = ''){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        if (!is_array($dataArray)){
            $dataArray = $this->getDataArray();
        }

        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');
         
        $fa = array(
              'invoice_year'            => $phpExcel->getFldObj('Month')
             ,'invoice_amount_yearly'   => $phpExcel->getFldObj('Amount')
        );

        $file_name = "SalesByYear_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }
}