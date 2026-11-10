<?
class CP_Admin_Widgets_Pms_PaymentOutstandingReport_View extends CP_Common_Lib_WidgetViewAbstract
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
        $ageing_value  = $fn->getReqParam('ageing_value');
        $payment_mode  = $fn->getReqParam('payment_mode');
        $site_id       = $fn->getSessionParam('cp_site_id');
        
        $siteRec     = $fn->getRecordRowById('site', 'site_id', $site_id);
        $branch_name = $siteRec['title'];
        
        $start_date_year = date('Y',mktime (0,0,0,date("m")-$ageing_value,date("d"), date("Y")));
        
        if ($ageing_value != '' && $year != $start_date_year) {
            return $text = "<div class='txtCenter mt20'>Please choose the year " . $start_date_year . " in Enrollment Year drop down for Ageing report </div>";
        }

        $ageing_val = '';
        if ($ageing_value) {
            if ($ageing_value == 1) {
                $ageing_val = 'Ageing by 30 days';
            } else if ($ageing_value == 2) {
                $ageing_val = 'Ageing by 60 days';
            } else {
                $ageing_val = 'Ageing by 90 days';
            }
        }
        
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
                    <td>Year : {$year}</td>
                    <td>Mode of Payment : {$payment_mode}</td>
                    <td>Ageing Value : {$ageing_val}</td>
                    <td>Total no of Students : {$rowCount['total_students']}</td>
                    <td>Total Outstanding Amount : {$total_outstanding_amount}</td>
                </tr>
            </table>
            <table class='thinlist mt10'>
                <thead>
                    <tr>
                        <th>S/No</th>
                        <th>Name of Student</th>
                        <th>Parent Name</th>
                        <th>Mobile</th>
                        <th>Jan</th>
                        <th>Feb</th>
                        <th>Mar</th>
                        <th>Apr</th>
                        <th>May</th>
                        <th>Jun</th>
                        <th>Jul</th>
                        <th>Aug</th>
                        <th>Sep</th>
                        <th>Oct</th>
                        <th>Nov</th>
                        <th>Dec</th>
                        <th>Total Due</th>
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
        
        foreach($this->model->dataArray as $row){
            
            $balance_amount = $this->getOutstandingBalanceAmount($row['order_id']);
            /* Displaying the Students who owe to pay and neglecting the paid students */
            if ($balance_amount) {
                $serial_no += 1;

                $total_outstanding_amount += $balance_amount;
                $balance_amount_formatted = number_format($balance_amount, 2);
                $rows .= "
                <tr>
                    <td>{$serial_no}</td>
                    <td>{$row['contact_name']}</td>
                    <td>{$row['parent_name']}</td>
                    <td>{$row['mobile']}</td>
                    <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 1, '')}</td>
                    <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 2, '')}</td>
                    <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 3, '')}</td>
                    <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 4, '')}</td>
                    <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 5, '')}</td>
                    <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 6, '')}</td>
                    <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 7, '')}</td>
                    <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 8, '')}</td>
                    <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 9, '')}</td>
                    <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 10, '')}</td>
                    <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 11, '')}</td>
                    <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 12, '')}</td>
                    <td class='txtRight'>{$balance_amount_formatted}</td>
                </tr>
                "; 
            }
        }
        
        $total_outstanding_amount_formatted = number_format($total_outstanding_amount, 2);
        $text = "
        {$rows}
        <tr>
            <td colspan='16'>Total Outstanding Amount</td>
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
          AND i.add_registration_fee IS NULL
        ";
        $resultInv  = $db->sql_query($SQLInv);
        $numRowsInv = $db->sql_numrows($resultInv);
        
        $text = "";
        
        if ($numRowsInv) {
            $rowInv = $db->sql_fetchrow($resultInv);
            
            if ($rowInv['status'] == 'Paid' || $rowInv['status'] == 'Cancelled') {
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
    function getOutstandingBalanceAmount($order_id) {
        $db = Zend_Registry::get('db');

        $sqlOrder = "
        SELECT SUM(i.invoice_amount) AS total_invoice_amount_due
              ,SUM(i.discount_amount) AS total_invoice_amount_discounted
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        WHERE i.order_id = {$order_id}
          AND (i.status = 'Due'
           OR i.status = 'Partial Payment')
        ";
        $resultOrder = $db->sql_query($sqlOrder);        
        $rowOrder = $db->sql_fetchrow($resultOrder);
        
        $balance_amount = $rowOrder['total_invoice_amount_due'] - $rowOrder['total_invoice_amount_discounted'];

        return $balance_amount;
    }
}