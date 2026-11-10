<?
class CP_Admin_Widgets_EnggCrm_OpportunityReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT o.opportunity_id
              ,o.opportunity_code
              ,o.enquiry_date
              ,o.category
              ,o.title
              ,FORMAT(o.estimated_value, 0) AS estimated_value
              ,o.status
              ,date_format(o.enquiry_date, '%d %b %Y') AS enquiry_date
              ,date_format(o.follow_up_date, '%d %b %Y') AS follow_up_date
              ,c.company_name
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name
              ,(SELECT co.comment_id
                FROM comment co
                LEFT JOIN (opportunity op) ON (co.record_id = op.opportunity_id)
                WHERE co.room_name = 'enggCrm_opportunity'
                  AND co.record_id = o.opportunity_id
                ORDER BY co.comment_date DESC
                LIMIT 0,1) AS comment_record_id
        FROM opportunity o
        LEFT JOIN (company c)    ON (o.company_id = c.company_id)
        LEFT JOIN (contact cont) ON (o.contact_id = cont.contact_id)
        ";

        return $SQL;
    }
    
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'o';

        $status     = $fn->getReqParam('status');
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $category   = $fn->getReqParam('category');
        
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
              'opportunity_code'  => $phpExcel->getFldObj('Opp.code')
             ,'title'             => $phpExcel->getFldObj('Opp. Title')
             ,'company_name'      => $phpExcel->getFldObj('Company')
             ,'contact_name'      => $phpExcel->getFldObj('Contact')
             ,'category'          => $phpExcel->getFldObj('Category')
             ,'enquiry_date'      => $phpExcel->getFldObj('Enquiry Date')
             ,'follow_up_date'    => $phpExcel->getFldObj('Follow up Date')
             ,'status'            => $phpExcel->getFldObj('Status')
        );

        $file_name = "OpportunityReport_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }
}