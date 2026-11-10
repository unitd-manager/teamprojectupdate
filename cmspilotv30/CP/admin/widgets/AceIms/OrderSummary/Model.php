<?
class CP_Admin_Widgets_AceIms_OrderSummary_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;

        //$searchVar->sqlSearchVar[] = " b.status = 'Current'";
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
        //$dataArray = $modelHelper->getWidgetDataArray($this->controller, 'aceIms_newEnrolment');

        //$this->dataArray = $dataArray;
        //return $this->dataArray;
    }
    /**
     *
     */
    function getTotalOutstandingInvoices($title='') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $site_id = $fn->getSessionParam('cp_site_id');
        
        $sqlAppend = '';
        $sqlAppendSite = '';
        
        if ($cpCfg['w.aceIms.orderSummary.outstandingInvoiceForInstitute']) {
            $tableAbb = 'i';
        } else {
            $tableAbb = 'o';
        }
        
        if ($site_id) {
            $sqlAppendSite .= "AND {$tableAbb}.site_id = {$site_id}";
        }
        
        if ($title == 'Subsidy Summary'){
            $sqlAppend .= " AND oi.module = 'aceIms_subsidy' ";
        }
        else if ($title == 'Invoice Summary'){
            $sqlAppend .= " AND oi.module = 'aceIms_course' ";
        }
        
        if ($cpCfg['w.aceIms.orderSummary.outstandingInvoiceForPvt']) {
            // Was there earlier
            /*$SQL = "
            SELECT
            (SELECT SUM(inv.invoice_amount)  
            FROM invoice inv ) -
            (SELECT SUM(irh.amount) 
            FROM invoice_receipt_history irh ) +
            (SELECT count(invoice_id) * 50 as invoice_count FROM invoice WHERE      add_registration_fee = 1)  AS total
            ";
            */
            
            $SQL = "
            SELECT ABS(ABS(SUM(irh.amount))) AS total
            FROM invoice_receipt_history irh
            WHERE irh.invoice_paid_status IS NULL
            ";
        } else if ($cpCfg['w.aceIms.orderSummary.outstandingInvoiceForInstitute']) {
            $SQL = "
            SELECT ABS(ABS(SUM(i.invoice_amount))) AS total
            FROM invoice i
            WHERE i.status = 'Due'
                 {$sqlAppendSite}
            ";
        } else {
            $SQL = "
            SELECT ABS(ABS(SUM(oi.unit_price))) AS total
            FROM `order` o
            JOIN order_item oi ON oi.order_id = o.order_id
            WHERE o.order_status = 'Due'
                {$sqlAppend}
                {$sqlAppendSite}
            ";
        }
        
        $result  = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        return number_format($row['total'], 2);
    }

    /**
     *
     */
    function getTotalInvoicesDueThisMonth($title='') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $site_id = $fn->getSessionParam('cp_site_id');
        
        $thisMonth = date('Y-m');
        
        $sqlAppend = '';
        $sqlAppendSite = '';

        if ($cpCfg['w.aceIms.orderSummary.invoiceDueThisMonthForInstitute']) {
            $tableAbb = 'i';
        } else {
            $tableAbb = 'o';
        }

        if ($site_id) {
            $sqlAppendSite .= "AND {$tableAbb}.site_id = {$site_id}";
        }

        if ($title == 'Subsidy Summary'){
            $sqlAppend .= " AND oi.module = 'aceIms_subsidy' ";
        }
        else if ($title == 'Invoice Summary'){
            $sqlAppend .= " AND oi.module = 'aceIms_course' ";
        }
        
        if ($cpCfg['w.aceIms.orderSummary.invoiceDueThisMonthForPvt']) {
            $SQL = "
            SELECT ABS(SUM(irh.amount)) AS total
            FROM invoice_receipt_history irh
            WHERE irh.invoice_paid_status IS NULL
              AND DATE_FORMAT(irh.invoice_date, '%Y-%m') = '{$thisMonth}'
            ";
        } else if ($cpCfg['w.aceIms.orderSummary.invoiceDueThisMonthForInstitute']) {
            $SQL = "
            SELECT ABS(SUM(i.invoice_amount)) AS total
            FROM `invoice` i
            WHERE i.status = 'Due'
              AND DATE_FORMAT(i.invoice_date, '%Y-%m') = '{$thisMonth}'
              {$sqlAppendSite}
            ";
        } else {
            $SQL = "
            SELECT ABS(SUM(oi.unit_price)) AS total
            FROM `order` o
            JOIN order_item oi ON oi.order_id = o.order_id
            WHERE o.order_status = 'Due'
              AND DATE_FORMAT(o.order_date, '%Y-%m') = '{$thisMonth}'
              {$sqlAppend}
              {$sqlAppendSite}
            ";
        }
        $result  = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        return number_format($row['total'], 2);
    }
    
    /**
     *
     */
    function getTotalLateInvoices($title='') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $today = date('Y-m-d');
        $sqlAppend = '';
        $sqlAppendSite = '';
        
        $site_id = $fn->getSessionParam('cp_site_id');
        
        if ($cpCfg['w.aceIms.orderSummary.lateInvoiceForInstitute']) {
            $tableAbb = 'i';
        } else {
            $tableAbb = 'o';
        }
        
        if ($site_id) {
            $sqlAppendSite .= "AND {$tableAbb}.site_id = {$site_id}";
        }
        if ($title == 'Subsidy Summary'){
            $sqlAppend .= " AND oi.module = 'aceIms_subsidy' ";
        }
        else if ($title == 'Invoice Summary'){
            $sqlAppend .= " AND oi.module = 'aceIms_course' ";
        }
        
        if ($cpCfg['w.aceIms.orderSummary.lateInvoiceForPvt']) {
            $SQL = "
            SELECT ABS(SUM(irh.amount)) AS total
            FROM invoice_receipt_history irh
            WHERE irh.invoice_paid_status IS NULL
              AND irh.invoice_date < '{$today}'
            ";
        } else if ($cpCfg['w.aceIms.orderSummary.lateInvoiceForInstitute'])  {
            $SQL = "
            SELECT ABS(SUM(i.invoice_amount)) AS total
            FROM `invoice` i
            WHERE i.status = 'Due'
              AND i.invoice_date <= '{$today}'
              {$sqlAppendSite}
            ";
        } else {
            $SQL = "
            SELECT ABS(SUM(oi.unit_price)) AS total
            FROM `order` o
            JOIN order_item oi ON oi.order_id = o.order_id
            WHERE o.order_status != 'Completed'
              AND o.order_status != 'Cancelled' 
              {$sqlAppend}
              {$sqlAppendSite}
            ";
        }
        $result  = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        return number_format($row['total'], 2);
    }
    /**
     *
     */
    function getTotalOverDueInvoices($title='') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $ninetyDaysBefore = date('Y-m-d', mktime (0,0,0,date('m'),date('d')-90, date('Y')));

        $sqlAppend = '';
        $sqlAppendSite = '';
        
        $site_id = $fn->getSessionParam('cp_site_id');
        
        if ($cpCfg['w.aceIms.orderSummary.overDueInvoiceForInstitute']) {
            $tableAbb = 'i';
        } else {
            $tableAbb = 'o';
        }

        if ($site_id) {
            $sqlAppendSite .= "AND {$tableAbb}.site_id = {$site_id}";
        }

        if ($title == 'Subsidy Summary'){
            $sqlAppend .= " AND oi.module = 'aceIms_subsidy' ";
        }
        else if ($title == 'Invoice Summary'){
            $sqlAppend .= " AND oi.module = 'aceIms_course' ";
        }
        
        /*
        $SQL = "
        SELECT CONCAT('{$this->getCurPfx()}', FORMAT(SUM(invoice_amount{$this->getFldSfx()}), 0)) AS total
        FROM invoice i
        WHERE LOWER(i.status) = 'late' 
          AND i.invoice_due_date < '{$ninetyDaysBefore}'
        ";
        */
        if ($cpCfg['w.aceIms.orderSummary.overDueInvoiceForPvt']) {
            $SQL = "
            SELECT ABS(SUM(irh.amount)) AS total
            FROM invoice_receipt_history irh
            WHERE irh.invoice_paid_status IS NULL
              AND irh.invoice_date < '{$ninetyDaysBefore}'
            ";
        } else if ($cpCfg['w.aceIms.orderSummary.overDueInvoiceForInstitute']) {
            $SQL = "
            SELECT ABS(SUM(i.invoice_amount)) AS total
            FROM `invoice` i
            WHERE i.status = 'Due'
              AND i.invoice_date < '{$ninetyDaysBefore}'
              {$sqlAppendSite}
            "; 
        } else {
            $SQL = "
            SELECT ABS(SUM(oi.unit_price)) AS total
            FROM `order` o
            JOIN order_item oi ON oi.order_id = o.order_id
            WHERE o.order_status = 'Due'
              AND o.order_date < '{$ninetyDaysBefore}'
              {$sqlAppend}
              {$sqlAppendSite}
            "; 
        }
        $result  = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        return number_format($row['total'], 2);
    }
    /**
     *
     */
    function getTotalInvoicesThisMonth($title='') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $thisMonth = date('Y-m');
        $sqlAppend = '';
        $sqlAppendSite = '';
        
        $site_id = $fn->getSessionParam('cp_site_id');
        
        if ($cpCfg['w.aceIms.orderSummary.invoiceThisMonthForInstitute']) {
            $tableAbb = 'i';
        } else {
            $tableAbb = 'o';
        }
        
        if ($site_id) {
            $sqlAppendSite .= "AND {$tableAbb}.site_id = {$site_id}";
        }

        if ($title == 'Subsidy Summary'){
            $sqlAppend .= " AND oi.module = 'aceIms_subsidy' ";
        }
        else if ($title == 'Invoice Summary'){
            $sqlAppend .= " AND oi.module = 'aceIms_course' ";
        }
        
        if ($cpCfg['w.aceIms.orderSummary.invoiceThisMonthForPvt']) {
            $SQL = "
            SELECT ABS(SUM(irh.amount)) AS total
            FROM invoice_receipt_history irh
            WHERE irh.invoice_paid_status IS NULL
              AND DATE_FORMAT(irh.invoice_date, '%Y-%m') = '{$thisMonth}'
            ";
        } else if ($cpCfg['w.aceIms.orderSummary.invoiceThisMonthForInstitute']) {
            $SQL = "
            SELECT ABS(SUM(i.invoice_amount)) AS total
            FROM `invoice` i
            WHERE DATE_FORMAT(i.invoice_date, '%Y-%m') = '{$thisMonth}'
              {$sqlAppendSite}
            ";
        } else {
            $SQL = "
            SELECT ABS(SUM(oi.unit_price)) AS total
            FROM `order` o
            JOIN order_item oi ON oi.order_id = o.order_id
            WHERE o.order_status = 'Due'
              AND DATE_FORMAT(o.order_date, '%Y-%m') = '{$thisMonth}'
              {$sqlAppend}
              {$sqlAppendSite}
            ";
        }

        $result  = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        return number_format($row['total'], 2);
    }

    /**
     *
     */
    function getTotalInvoiceLastMonth($title='') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $site_id    = $fn->getSessionParam('cp_site_id');        
        $lastMonth  = date('Y-m',mktime (0,0,0,date("m")-1,date("d"), date("Y")));
        
        $sqlAppend = '';
        $sqlAppendSite = '';
        
        if ($cpCfg['w.aceIms.orderSummary.invoiceDueLastMonthForInstitute']) {
            $tableAbb = 'i';
        } else {
            $tableAbb = 'o';
        }
        
        if ($site_id) {
            $sqlAppendSite .= "AND {$tableAbb}.site_id = {$site_id}";
        }

        if ($title == 'Subsidy Summary'){
            $sqlAppend .= " AND oi.module = 'aceIms_subsidy' ";
        }
        else if ($title == 'Invoice Summary'){
            $sqlAppend .= " AND oi.module = 'aceIms_course' ";
        }
        
        if ($cpCfg['w.aceIms.orderSummary.invoiceDueLastMonthForPvt']) {
            $SQL = "
            SELECT ABS(SUM(irh.amount)) AS total
            FROM invoice_receipt_history irh
            WHERE irh.invoice_paid_status IS NULL
              AND DATE_FORMAT(irh.invoice_date, '%Y-%m') = '{$lastMonth}'
            ";
        } else if ($cpCfg['w.aceIms.orderSummary.invoiceDueLastMonthForInstitute']) {
            $SQL = "
            SELECT ABS(SUM(i.invoice_amount)) AS total
            FROM `invoice` i
            WHERE DATE_FORMAT(i.invoice_date, '%Y-%m') = '{$lastMonth}'
              {$sqlAppendSite}
            ";
        } else {
            $SQL = "
            SELECT ABS(SUM(oi.unit_price)) AS total
            FROM `order` o
            JOIN order_item oi ON oi.order_id = o.order_id
            WHERE o.order_status = 'Due'
              AND DATE_FORMAT(o.order_date, '%Y-%m') = '{$lastMonth}'
              {$sqlAppend}
              {$sqlAppendSite}
            "; 
        }
        $result  = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        return number_format($row['total'], 2);
    }
    /**
     *
     */
    function getTotalInvoicesPaidThisMonth($title='') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $site_id = $fn->getSessionParam('cp_site_id');
        $thisMonth = date('Y-m');
        
        $sqlAppend = '';
        $sqlAppendSite = '';
        
        if ($cpCfg['w.aceIms.orderSummary.invoicePaidThisMonthForInstitute']) {
            $tableAbb = 'i';
        } else {
            $tableAbb = 'o';
        }
        
        if ($site_id) {
            $sqlAppendSite .= "AND {$tableAbb}.site_id = {$site_id}";
        }

        if ($title == 'Subsidy Summary'){
            $sqlAppend .= " AND oi.module = 'aceIms_subsidy' ";
        }
        else if ($title == 'Invoice Summary'){
            $sqlAppend .= " AND oi.module = 'aceIms_course' ";
        }
        
        if ($cpCfg['w.aceIms.orderSummary.invoicePaidThisMonthForPvt']) {
            $SQL = "
            SELECT ABS(SUM(irh.amount)) AS total
            FROM invoice_receipt_history irh
            WHERE irh.invoice_paid_status = 'Paid'
              AND DATE_FORMAT(irh.invoice_date, '%Y-%m') = '{$thisMonth}'
            ";
        } else if ($cpCfg['w.aceIms.orderSummary.outstandingInvoiceForInstitute']) {
            $SQL = "
            SELECT ABS(SUM(i.invoice_amount)) AS total
            FROM `invoice` i
            WHERE i.status = 'Paid'
              AND DATE_FORMAT(i.invoice_date, '%Y-%m') = '{$thisMonth}'
              {$sqlAppendSite}
            ";
        } else {
            $SQL = "
            SELECT ABS(SUM(oi.unit_price)) AS total
            FROM `order` o
            JOIN order_item oi ON oi.order_id = o.order_id
            WHERE o.order_status = 'Paid'
              AND DATE_FORMAT(o.order_date, '%Y-%m') = '{$thisMonth}'
              {$sqlAppend}
              {$sqlAppendSite}
            ";  
        }
        $result  = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        return number_format($row['total'], 2);
    }
    /**
     *
     */
    function getTotalInvoicesPaidLastMonth($title='') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $site_id = $fn->getSessionParam('cp_site_id');
        $lastMonth = date('Y-m',mktime (0,0,0,date("m")-1,date("d"), date("Y")));

        $sqlAppend = '';
        $sqlAppendSite = '';
        
        if ($cpCfg['w.aceIms.orderSummary.invoicePaidLastMonthForInstitute']) {
            $tableAbb = 'i';
        } else {
            $tableAbb = 'o';
        }
        
        if ($site_id) {
            $sqlAppendSite .= "AND {$tableAbb}.site_id = {$site_id}";
        }

        if ($title == 'Subsidy Summary'){
            $sqlAppend .= " AND oi.module = 'aceIms_subsidy' ";
        }
        else if ($title == 'Invoice Summary'){
            $sqlAppend .= " AND oi.module = 'aceIms_course' ";
        }

        if ($cpCfg['w.aceIms.orderSummary.invoicePaidLastMonthForPvt']) {
            $SQL = "
            SELECT ABS(SUM(irh.amount)) AS total
            FROM invoice_receipt_history irh
            WHERE irh.invoice_paid_status = 'Paid'
              AND DATE_FORMAT(irh.invoice_date, '%Y-%m') = '{$lastMonth}'
            ";
        } else if ($cpCfg['w.aceIms.orderSummary.invoicePaidLastMonthForInstitute']) {
            $SQL = "
            SELECT ABS(SUM(i.invoice_amount)) AS total
            FROM `invoice` i
            WHERE i.status = 'Paid'
              AND DATE_FORMAT(i.invoice_date, '%Y-%m') = '{$lastMonth}'
              {$sqlAppendSite}
            ";
        } else {
            $SQL = "
            SELECT ABS(SUM(oi.unit_price)) AS total
            FROM `order` o
            JOIN order_item oi ON oi.order_id = o.order_id
            WHERE o.order_status = 'Paid'
              AND DATE_FORMAT(o.order_date, '%Y-%m') = '{$lastMonth}'
              {$sqlAppend}
              {$sqlAppendSite}
            ";
        }
        $result  = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        return number_format($row['total'], 2);
    }

    /**
     *
     */
    function getTotalInvoicesPaidLastThreeMonth($title='') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $site_id = $fn->getSessionParam('cp_site_id');
        $ninetyDaysBefore = date('Y-m-d', mktime (0,0,0,date('m'),date('d')-90, date('Y')));
        
        $sqlAppend = '';
        $sqlAppendSite = '';

        if ($cpCfg['w.aceIms.orderSummary.invoicePaidLastThreeMonthForInstitute']) {
            $tableAbb = 'i';
        } else {
            $tableAbb = 'o';
        }
        
        if ($site_id) {
            $sqlAppendSite .= "AND {$tableAbb}.site_id = {$site_id}";
        }

        if ($title == 'Subsidy Summary'){
            $sqlAppend .= " AND oi.module = 'aceIms_subsidy' ";
        }
        else if ($title == 'Invoice Summary'){
            $sqlAppend .= " AND oi.module = 'aceIms_course' ";
        }

        if ($cpCfg['w.aceIms.orderSummary.invoicePaidLastThreeMonthForPvt']) {
            $SQL = "
            SELECT ABS(SUM(irh.amount)) AS total
            FROM invoice_receipt_history irh
            WHERE irh.invoice_paid_status = 'Paid'
              AND irh.invoice_date < '{$ninetyDaysBefore}'
            ";
        } else if ($cpCfg['w.aceIms.orderSummary.invoicePaidLastThreeMonthForInstitute']) {
            $SQL = "
            SELECT ABS(SUM(i.invoice_amount)) AS total
            FROM `invoice` i
            WHERE i.status = 'Paid'
              AND i.invoice_date < '{$ninetyDaysBefore}'
              {$sqlAppendSite}
            ";
        } else {
            $SQL = "
            SELECT ABS(SUM(oi.unit_price)) AS total
            FROM `order` o
            JOIN order_item oi ON oi.order_id = o.order_id
            WHERE o.order_status = 'Paid'
              AND o.order_date < '{$ninetyDaysBefore}'
              {$sqlAppend}
              {$sqlAppendSite}
            ";
        }
        $result  = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        return number_format($row['total'], 2);
    }

    /**
     *
     */
    function getTotalInvoicesPaidThisYear($title='') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $site_id    = $fn->getSessionParam('cp_site_id');
        $end_date   = date('Y-m-d');
        $start_date = date('Y-m-d',mktime (0,0,0,date("01"),date("01"), date("Y")));
        
        $sqlAppend = '';        
        $sqlAppendSite = '';
        
        if ($cpCfg['w.aceIms.orderSummary.invoicePaidThisYearForInstitute']) {
            $tableAbb = 'i';
        } else {
            $tableAbb = 'o';
        }
        
        if ($site_id) {
            $sqlAppendSite .= "AND {$tableAbb}.site_id = {$site_id}";
        }

        if ($title == 'Subsidy Summary'){
            $sqlAppend .= " AND oi.module = 'aceIms_subsidy' ";
        }
        else if ($title == 'Invoice Summary'){
            $sqlAppend .= " AND oi.module = 'aceIms_course' ";
        }

        if ($cpCfg['w.aceIms.orderSummary.invoicePaidThisYearForPvt']) {
            $SQL = "
            SELECT ABS(SUM(irh.amount)) AS total
            FROM invoice_receipt_history irh
            WHERE irh.invoice_paid_status = 'Paid'
              AND irh.invoice_date BETWEEN '{$start_date}' AND '{$end_date}' 
            ";
        } else if ($cpCfg['w.aceIms.orderSummary.invoicePaidThisYearForInstitute']) {
            $end_date = date('Y-m-d',mktime (0,0,0,date("12"),date("31"), date("Y")));
            $SQL = "
            SELECT ABS(SUM(i.invoice_amount)) AS total
            FROM `invoice` i
            WHERE i.status = 'Paid'
              AND i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'
              {$sqlAppendSite}
            ";
        } else {
            $SQL = "
            SELECT ABS(SUM(oi.unit_price)) AS total
            FROM `order` o
            JOIN order_item oi ON oi.order_id = o.order_id
            WHERE o.order_status = 'Paid'
              AND o.order_date BETWEEN '{$start_date}' AND '{$end_date}'
              {$sqlAppend}
              {$sqlAppendSite}
            ";
        }
        $result  = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        return number_format($row['total'], 2);
    }

    /**
     *
     */
    function getTotalSalesThisYear($title='') {
        $db = Zend_Registry::get('db');
        
        $thisYear = date('Y');
        $sqlAppend = '';
        
        if ($title == 'Subsidy Summary'){
            $sqlAppend = " AND oi.module = 'aceIms_subsidy' ";
        }
        else if ($title == 'Invoice Summary'){
            $sqlAppend = " AND oi.module = 'aceIms_course' ";
        }
        
        $SQL = "
        SELECT ABS(SUM(oi.unit_price)) AS total
        FROM `order` o
        JOIN order_item oi ON oi.order_id = o.order_id
        WHERE o.order_status = 'Paid'
          AND DATE_FORMAT(o.order_date, '%Y-%m') = '{$lastMonth}'
          {$sqlAppend}
        ";

        $result  = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        return $row['total'];
    }
}