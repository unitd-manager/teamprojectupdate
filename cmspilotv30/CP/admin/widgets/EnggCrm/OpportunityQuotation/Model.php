<?
class CP_Admin_Widgets_EnggCrm_OpportunityQuotation_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT o.opportunity_id
              ,o.opportunity_code
              ,o.status
              ,c.company_name
              ,q.quote_code
              ,qi.quote_id
              ,qi.amount
              ,qi.description
              ,qi.quantity
        FROM opportunity o
        LEFT JOIN (company c)    ON (o.company_id = c.company_id)
        LEFT JOIN (quote q) ON (o.opportunity_id = q.opportunity_id)
        LEFT JOIN (quote_items qi) ON (q.quote_id = qi.quote_id)
        ";

        return $SQL;
    }
    
    /**
     *
     */
    function setSearchVar1() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'o';

        $status     = $fn->getReqParam('status');
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $category   = $fn->getReqParam('category');
        $current_date = date('Y-m-d');
        
        if ($start_date == '') {
            $start_date = date('Y-m-d', mktime (0,0,0,date("m")-12, date("d"), date("Y")));
        }
        
        if ($end_date == '') {
            $end_date = date('Y-m-d');
        }

        $searchVar->sqlSearchVar[] = "o.enquiry_date BETWEEN '{$start_date}' AND '{$end_date}'";

        if ($status != '') {
            $searchVar->sqlSearchVar[] = "o.status = '{$status}'";
        }

        if ($category != '') {
            $searchVar->sqlSearchVar[] = "o.category = '{$category}'";
        }
        
        $searchVar->sortOrder = 'o.opportunity_code DESC';
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'o';

        //$start_date = $fn->getReqParam('start_date');
        //$end_date   = $fn->getReqParam('end_date');
        $status     = $fn->getReqParam('status');
        $category   = $fn->getReqParam('category');

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $month      = $fn->getReqParam('month');
        $year       = $fn->getReqParam('year');
        $current_date = date('Y-m-d');
        $current_year = date('Y');
        $current_month = date('m');

        $start_date = $current_year . '-' . $current_month . '-' . '01';
        $end_date = $current_year . '-' . $current_month . '-' . '31';
        $searchVar->sqlSearchVar[] = "o.enquiry_date='{$current_date}'";

        /*$current_date = date('Y-m-d');

        if ($start_date != '' && $end_date == '') {
            $searchVar->sqlSearchVar[] = "o.enquiry_date BETWEEN '{$start_date}' AND '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = substr($end_date, 0, 8) . '01';
            $searchVar->sqlSearchVar[] = "o.enquiry_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $searchVar->sqlSearchVar[] = "o.enquiry_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {
            $start_date = date('Y-m-d',mktime (0,0,0,date("m")-1,date("d"), date("Y")));
            $searchVar->sqlSearchVar[] = "o.enquiry_date BETWEEN '{$start_date}' AND '{$current_date}'";
        }*/

        if ($status != '') {
            $searchVar->sqlSearchVar[] = "o.status = '{$status}'";
        }

        if ($category != '') {
            $searchVar->sqlSearchVar[] = "o.category = '{$category}'";
        }
        
        $searchVar->sortOrder = 'o.opportunity_code DESC';
    }    
    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enggCrm_opportunityReport');

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
              'opportunity_code' => $phpExcel->getFldObj('Opp.code')
             ,'quote_code'       => $phpExcel->getFldObj('Quote Code')
             ,'company_name'     => $phpExcel->getFldObj('Company')
             ,'amount'           => $phpExcel->getFldObj('Amount for quote')
             ,'status'           => $phpExcel->getFldObj('Status')
        );

        $file_name = "OpportunityQuotation_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }
}