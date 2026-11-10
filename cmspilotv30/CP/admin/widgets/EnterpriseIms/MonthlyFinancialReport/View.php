<?
class CP_Admin_Widgets_EnterpriseIms_MonthlyFinancialReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $viewHelper = Zend_Registry::get('viewHelper');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        $year          = $fn->getReqParam('year');
        $month         = $fn->getReqParam('month');
        $payment_mode  = $fn->getReqParam('payment_mode');
        $site_id       = $fn->getSessionParam('cp_site_id');

        $siteRec     = $fn->getRecordRowById('site', 'site_id', $site_id);
        $branch_name = $siteRec['title'];
        
        $rowCount = $this->model->getSqlForCount();
        $total_outstanding_amount = number_format($rowCount['total_outstanding_amount'], 2);

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist summaryTable'>
                <thead>
                    <th colspan='6'>Summary</th>
                </thead>
                <tr>
                    <td>Branch : {$branch_name}</td>
                    <td>Month : {$this->getMonthVal($month)}</td>
                    <td>Year : {$year}</td>
                    <td>Mode of Payment : {$payment_mode}</td>
                    <td>Total no of Data : {$rowCount['total_students']}</td>
                    <td>Grand Total : {$total_outstanding_amount}</td>
                </tr>
            </table>
            <table class='thinlist mt10'>
                <thead>
                    <tr>
                        <th>S/No</th>
                        <th>Name of Student</th>
                        <th>Parent Name</th>
                        <th>Mobile</th>
                        <th>Invoice Code</th>
                        <th>Month</th>
                        <th>Status</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    {$rowsHTML}
                </tbody>
            </table>
            ";
        }

        return $text;
    }
    /**
     *
     */
    function getRowsHTML() {
        $rows = '';
        $serial_no = 0;
        $total_outstanding_amount = 0;
        $fn = Zend_Registry::get('fn');
		
        $month  = $fn->getReqParam('month');
        $year   = $fn->getReqParam('year');
        
        foreach($this->model->dataArray as $row){
            if ($row['amount_payable'] > 0) {
                $serial_no += 1;
                
                if ($row['status'] == 'Due' || $row['status'] == 'Partial Payment') {
                    $amount = $row['amount_payable'];
                    $total_outstanding_amount += $row['amount_payable'];
                } else {
                    $amount = '';
                }

                $rows .= "
                <tr>
                    <td>{$serial_no}</td>
                    <td>{$row['contact_name']}</td>
                    <td>{$row['parent_name']}</td>
                    <td>{$row['mobile']}</td>
                    <td>{$row['invoice_code']}</td>
                    <td>{$this->getMonthVal($month)}</td>
                    <td>{$row['status']}</td>
                    <td class='txtRight'>{$amount}</td>
                </tr>
                "; 
            }
        }

        $total_outstanding_amount_formatted = number_format($total_outstanding_amount, 2);
        $text = "
        {$rows}
        <tr>

            <td class='txtRight' colspan='7'>Total Outstanding Amount</td>
            <td class='txtRight'>{$total_outstanding_amount_formatted}</td>
        </tr>
        ";

        return $text;
    }

    /**
     *
     */
    function getStudentPaymentStatus($order_id, $contact_id, $month, $data_in) {
        $db = Zend_Registry::get('db');
        
        $year = date('Y');
        
        $start_date = $year . '-01-01';
        $end_date = $year . '-12-31';

        $SQLInv = "
        SELECT i.status
              ,i.invoice_amount
              ,i.discount_amount
        FROM invoice i
        WHERE i.contact_id = {$contact_id}
          AND i.invoice_month = {$month}
          AND i.order_id = {$order_id}
        ";
        $resultInv  = $db->sql_query($SQLInv);
        $numRowsInv = $db->sql_numrows($resultInv);
        
        $text = "";
        
        if ($numRowsInv) {
            $rowInv = $db->sql_fetchrow($resultInv);
            
            if ($rowInv['status'] == 'Paid') {
                $text = $rowInv['status'];
            } else {
                $balance_amount = $rowInv['invoice_amount'] - $rowInv['discount_amount'];
                $balance_amount_formatted = number_format($balance_amount, 2);
                
                /* Showing only the amount in excel sheet and in o/p of report with status */
                if ($data_in == 'excel') {
                    $text = $balance_amount; // 60 or 0 or amount due after discount
                } else {
                    $text = $rowInv['status'] . " [" . $balance_amount . "]"; //Paid or Due [60]
                }
            }
        }
        
        return $text;
    }

    /**
     *
     */
    function getOutstandingBalanceAmount($order_id, $month, $year) {
        $db = Zend_Registry::get('db');

        $sqlOrder = "
        SELECT SUM(i.invoice_amount) AS total_invoice_amount_due
              ,SUM(i.discount_amount) AS total_invoice_amount_discounted
          ,o.year_of_enrollment
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        WHERE i.order_id = {$order_id}
          AND i.invoice_month = {$month}
		  AND o.year_of_enrollment = {$year}	
          AND (i.status = 'Due'
           OR i.status = 'Partial Payment')
        ";
        $resultOrder = $db->sql_query($sqlOrder);        
        $rowOrder = $db->sql_fetchrow($resultOrder);
        
        $balance_amount = $rowOrder['total_invoice_amount_due'] - $rowOrder['total_invoice_amount_discounted'];

        return $balance_amount;
    }

    /**
     *
     */
    function getMonthVal($month) {
        switch ($month) {
            case 1: $prefix_month = 'Jan';
            break;
            case 2: $prefix_month = 'Feb';
            break;
            case 3: $prefix_month = 'Mar';
            break;
            case 4: $prefix_month = 'Apr';
            break;
            case 5: $prefix_month = 'May';
            break;
            case 6: $prefix_month = 'Jun';
            break;
            case 7: $prefix_month = 'Jul';
            break;
            case 8: $prefix_month = 'Aug';
            break;
            case 9: $prefix_month = 'Sep';
            break;
            case 10: $prefix_month = 'Oct';
            break;
            case 11: $prefix_month = 'Nov';
            break;
            case 12: $prefix_month = 'Dec';
            break;
        }
        
        return $prefix_month;
    }
}