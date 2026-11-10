<?
class CPL_Admin_Widgets_Project_InvoiceSummaryChart_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT DATE_FORMAT(comment_date, '%b') AS yearMonth
              ,COUNT(comment_id) AS count_comment
        FROM `comment`
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;

        $last12Month = date('Y-m-d',mktime (0,0,0,date("m")-12,1, date("Y")));
        $today       = date('Y-m-d');

        //$searchVar->sqlSearchVar[] = "(enquiry_date BETWEEN '{$last12Month}' AND '{$today}')";
        $searchVar->sqlSearchVar[] = "room_name='opportunity'";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'project_opportunityChart');

        $arr = array();
        foreach ($dataArray as $row){
            $tmpArr = &$arr[];
            $tmpArr['yearMonth'] = $row['yearMonth'];
            $tmpArr['count_comment'] = $row['count_comment'];
        }

        $this->dataArray = $arr;
        return $this->dataArray;
    }

    //==================================================================//
    function getFldSfx() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($cpCfg['m.project.hasMultiCurrency'] == 1){
            return '_base';
        }
    }

    /**
     *
     */
    function getTotalOutstandingInvoices($month) {
        $db = Zend_Registry::get('db');
        $thisMonth = date('Y-m');
        $lastMonth = date('Y-m',mktime (0,0,0,date("m")-1,date("d"), date("Y")));

        if($month == 'Current Month' || $month == ''){
            $SQL = "
            SELECT SUM(invoice_amount + ((i.invoice_amount * i.gst_percentage) / 100)) AS total
            FROM invoice i
            WHERE DATE_FORMAT(i.invoice_date, '%Y-%m') = '{$thisMonth}'
              AND (LOWER(i.status) = 'due') 
            ";
        } else if($month == 'Previous Month') {
            $SQL = "
            SELECT SUM(invoice_amount + ((i.invoice_amount * i.gst_percentage) / 100)) AS total
            FROM invoice i
            WHERE DATE_FORMAT(i.invoice_date, '%Y-%m') = '{$lastMonth}'
              AND (LOWER(i.status) = 'due') 
            ";            
        } else {
            if($month == 2){
                $monthVal = date('Y-m-d',mktime (0,0,0,date("m")-2,1, date("Y")));
            }
            if($month == 5){
                $monthVal = date('Y-m-d',mktime (0,0,0,date("m")-5,1, date("Y")));
            }
            if($month == 8){
                $monthVal = date('Y-m-d',mktime (0,0,0,date("m")-8,1, date("Y")));
            }
            if($month == 11){
                $monthVal = date('Y-m-d',mktime (0,0,0,date("m")-11,1, date("Y")));
            }
            $today       = date('Y-m-d');
            $SQL = "
            SELECT SUM(invoice_amount + ((i.invoice_amount * i.gst_percentage) / 100)) AS total
            FROM invoice i
            WHERE (i.invoice_date BETWEEN '{$monthVal}' AND '{$today}')
              AND (LOWER(i.status) = 'due') 
            ";                        
        }

        $result  = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        return $row['total'];
    }

    /**
     *
     */
    function getTotalInvoicesThisMonth($month) {
        $db = Zend_Registry::get('db');
        
        $thisMonth = date('Y-m');
        $lastMonth = date('Y-m',mktime (0,0,0,date("m")-1,date("d"), date("Y")));

        if($month == 'Current Month' || $month == ''){
            $SQL = "
            SELECT SUM(invoice_amount + ((i.invoice_amount * i.gst_percentage) / 100)) AS total
            FROM invoice i
            WHERE DATE_FORMAT(i.invoice_date, '%Y-%m') = '{$thisMonth}'
              AND LOWER(i.status) != 'cancelled' 
            ";
        } else if($month == 'Previous Month') {
            $SQL = "
            SELECT SUM(invoice_amount + ((i.invoice_amount * i.gst_percentage) / 100)) AS total
            FROM invoice i
            WHERE DATE_FORMAT(i.invoice_date, '%Y-%m') = '{$lastMonth}'
              AND LOWER(i.status) != 'cancelled' 
            ";            
        } else {
            if($month == 2){
                $monthVal = date('Y-m-d',mktime (0,0,0,date("m")-2,1, date("Y")));
            }
            if($month == 5){
                $monthVal = date('Y-m-d',mktime (0,0,0,date("m")-5,1, date("Y")));
            }
            if($month == 8){
                $monthVal = date('Y-m-d',mktime (0,0,0,date("m")-8,1, date("Y")));
            }
            if($month == 11){
                $monthVal = date('Y-m-d',mktime (0,0,0,date("m")-11,1, date("Y")));
            }
            $today       = date('Y-m-d');
            $SQL = "
            SELECT SUM(invoice_amount + ((i.invoice_amount * i.gst_percentage) / 100)) AS total
            FROM invoice i
            WHERE (i.invoice_date BETWEEN '{$monthVal}' AND '{$today}')
              AND LOWER(i.status) != 'cancelled' 
            ";                        
        }

        $result  = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        return $row['total'];
    }

    /**
     *
     */
    function getTotalInvoicesPaidThisMonth($month) {
        $db = Zend_Registry::get('db');
        
        $thisMonth = date('Y-m');
        $lastMonth = date('Y-m',mktime (0,0,0,date("m")-1,date("d"), date("Y")));

        if($month == 'Current Month' || $month == ''){
            $SQL = "
            SELECT SUM(invoice_amount + ((i.invoice_amount * i.gst_percentage) / 100)) AS total
            FROM invoice i
            WHERE DATE_FORMAT(i.invoice_date, '%Y-%m') = '{$thisMonth}'
              AND (LOWER(i.status) = 'paid' OR LOWER(i.status) = 'partial payment') 
            ";
        } else if($month == 'Previous Month') {
            $SQL = "
            SELECT SUM(invoice_amount + ((i.invoice_amount * i.gst_percentage) / 100)) AS total
            FROM invoice i
            WHERE DATE_FORMAT(i.invoice_date, '%Y-%m') = '{$lastMonth}'
              AND (LOWER(i.status) = 'paid' OR LOWER(i.status) = 'partial payment') 
            ";            
        } else {
            if($month == 2){
                $monthVal = date('Y-m-d',mktime (0,0,0,date("m")-2,1, date("Y")));
            }
            if($month == 5){
                $monthVal = date('Y-m-d',mktime (0,0,0,date("m")-5,1, date("Y")));
            }
            if($month == 8){
                $monthVal = date('Y-m-d',mktime (0,0,0,date("m")-8,1, date("Y")));
            }
            if($month == 11){
                $monthVal = date('Y-m-d',mktime (0,0,0,date("m")-11,1, date("Y")));
            }
            $today       = date('Y-m-d');
            $SQL = "
            SELECT SUM(invoice_amount + ((i.invoice_amount * i.gst_percentage) / 100)) AS total
            FROM invoice i
            WHERE (i.invoice_date BETWEEN '{$monthVal}' AND '{$today}')
              AND (LOWER(i.status) = 'paid' OR LOWER(i.status) = 'partial payment') 
            ";                        
        }
        
        $result  = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        return $row['total'];
    }

    /**
     *
     */
    function getCurPfx() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($cpCfg['m.project.hasMultiCurrency'] == 1){
            return $cpCfg['baseCurrency'];
        } else {
            return '';
        }
    }

    /**
     *
     */
    function getWidgetDataJSON(){
      $db    = Zend_Registry::get('db');
      $fn    = Zend_Registry::get('fn');
      $cpCfg = Zend_Registry::get('cpCfg');
      $dbUtil = Zend_Registry::get('dbUtil');
      $dateUtil = Zend_Registry::get('dateUtil');
      $modelHelper = Zend_Registry::get('modelHelper');
      $formObj = Zend_Registry::get('formObj');
      $cpUtil  = Zend_Registry::get('cpUtil');

      $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'project_invoiceSummaryChart');

        $duration       = $fn->getReqParam('duration');
//print $duration;
        $durationArray = array(
            "Current Month"  => "Current Month"
           ,"Previous Month" => "Previous Month"
           ,"Last 3 Months"  => "Last 3 Months"
           ,"Last 6 Months"  => "Last 6 Months"
           ,"Last 9 Months"  => "Last 9 Months"
           ,"Last 12 Months" => "Last 12 Months"
        );

        if($duration == "") {
            $month = "Current Month";
        }

        if($duration == "Current Month"){
            $month = 'Current Month';
        }
        if($duration == "Previous Month"){
            $month = 'Previous Month';
        }
        if($duration == "Last 3 Months"){
            $month = 2;
        }
        if($duration == "Last 6 Months"){
            $month = 5;
        }
        if($duration == "Last 9 Months"){
            $month = 8;
        }
        if($duration == "Last 12 Months"){
            $month = 11;
        }

      $myData = array();
        $invoiceRaised = $this->getTotalInvoicesThisMonth($month);
        $invoicePaid = $this->getTotalInvoicesPaidThisMonth($month);
        $invoiceOutstanding = $this->getTotalOutstandingInvoices($month);

        if($invoiceRaised == ''){
            $invoiceRaised = 0;
        }
        if($invoicePaid == ''){
            $invoicePaid = 0;
        }
        if($invoiceOutstanding == ''){
            $invoiceOutstanding = 0;
        }
        $datas = array('Total invoices raised', $invoiceRaised);
        array_push($myData, $datas);
        $datas = array('Total invoices paid', $invoicePaid);
        array_push($myData, $datas);
        $datas = array('Total outstanding invoices', $invoiceOutstanding);
        array_push($myData, $datas);

      return json_encode($myData, JSON_NUMERIC_CHECK);
    }
}