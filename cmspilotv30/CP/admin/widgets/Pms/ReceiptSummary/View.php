<?
class CP_Admin_Widgets_Pms_ReceiptSummary_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $viewHelper = Zend_Registry::get('viewHelper');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        $payment_mode   = $fn->getReqParam('payment_mode');
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $site_id        = $fn->getReqParam('site_id');
        
        $start_date_formatted = $dateUtil->formatDate($start_date, 'DD/MM/YYYY');
        $end_date_formatted   = $dateUtil->formatDate($end_date, 'DD/MM/YYYY');
        
        if(is_numeric($site_id)) {
            $siteRec = $fn->getRecordRowById('site', 'site_id', $site_id);
            $branch_name = $siteRec['title'];
        } else {
            $branch_name = "All Branches";
        }

        $rowsHTML = $this->getRowsHTML();
        $text = '';
        
        $summaryRec = $this->model->getSqlForCount($site_id);
        $grand_total = number_format($summaryRec['grand_total'], 2);

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist summaryTable'>
                <thead>
                    <th colspan='6'>Summary</th>
                </thead>
                <tr>
                    <td>Branch : {$branch_name}</td>
                    <td>Mode of Payment : {$payment_mode}</td>
                    <td>Payment Start Date : {$start_date_formatted}</td>
                    <td>Payment End Date : {$end_date_formatted}</td>
                    <td>Total no of Data : {$summaryRec['total_count']}</td>
                    <td class='txtRight'>Grand Total : {$grand_total}</td>
                </tr>
            </table>
            <table class='thinlist mt10'>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Receipt Code</th>
                        <th>Branch</th>
                        <th>Parent</th>
                        <th>Mode of Payment</th>
                        <th>Month/ Year for payment</th>
                        <th class='txtRight'>Amount</th>
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
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        
        $site_id      = $fn->getReqParam('site_id');
        $payment_mode = $fn->getReqParam('payment_mode');

        $rows = '';
        $print_total = '';
        $grand_total = 0;
        $mode_of_payment = '';
        $total_for_payment_mode = 0;

        foreach($this->model->dataArray as $row){

            // Printing total amount for each mode of payment. Eg: Cash, Nets, Giro etc
            if ($mode_of_payment == '' && (!is_numeric($site_id) || $site_id == $row['payment_site_id'] || $row['payment_site_id'] == '')) {
                $print_total = 0;
                $mode_of_payment = $row['mode_of_payment'];
                $total_for_payment_mode = $row['amount'];
            } else if ($mode_of_payment == $row['mode_of_payment'] && (!is_numeric($site_id) || $site_id == $row['payment_site_id'] || $row['payment_site_id'] == '')) {
                $print_total = 0;
                $total_for_payment_mode += $row['amount'];
                $mode_of_payment = $row['mode_of_payment'];
            } else if ($mode_of_payment != $row['mode_of_payment'] && (!is_numeric($site_id) || $site_id == $row['payment_site_id'] || $row['payment_site_id'] == '')) {
                $print_total = 1;
                $mode_of_payment = $row['mode_of_payment'];
                $payment_total = $total_for_payment_mode;
                $total_for_payment_mode = $row['amount'];
            }

            // If Mode of payment changes, printing the total amount for the specific payment mode.
            // Eg: If earlier payment mode was Cash, and for next receipt it is Giro, then printing the total.
            if ($print_total == 1) {
                $payment_total = number_format($payment_total, 2);
                $print_total = "
                <tr class='highlight'>
                    <td colspan='6'>Total</td>
                    <td class='txtRight'><strong>{$payment_total}</strong></td>
                </tr>
                ";
            } else {
                $print_total = "";
            }

            $amount = '';
            if (!is_numeric($site_id) || $site_id == $row['payment_site_id']
             || $row['payment_site_id'] == '') {
                $grand_total += $row['amount'];
                $amount = $row['amount'];
            }

            // Payment is done in other branch for the student studying in the chosen branch
            // Eg: Student studying in HQ but payment done in Jurong. This code will highlight the payment
            $payment_branch = '';
            $add_css_class  = '';
            if ($row['payment_site_id'] != '') {
                $siteRec = $fn->getRecordRowByID('site', 'site_id', $row['payment_site_id']);
                $payment_branch = " - Payment done at " . $siteRec['title'];
                $add_css_class  = 'alert';
            }

            // Parent name to be displayed
            $sqlParent = "
            SELECT p.first_name FROM parent p
            LEFT JOIN (`order` o) ON (p.parent_id = o.parent_id)
            LEFT JOIN (receipt r) ON (o.order_id = r.order_id)
            WHERE r.receipt_code = '{$row['receipt_code']}'
              AND r.site_id = '{$row['site_id']}'
            ";
            $resultParent = $db->sql_query($sqlParent);
            $rowParent = $db->sql_fetchrow($resultParent);

            // Finding invoice month and year for the paid receipt
            $sqlInv = "
            SELECT i.invoice_date, i.add_registration_fee, i.invoice_month FROM invoice i
            LEFT JOIN (invoice_receipt_history irh) ON (i.invoice_id = irh.invoice_id)
            LEFT JOIN (receipt r) ON (irh.receipt_id = r.receipt_id)
            WHERE r.receipt_code = '{$row['receipt_code']}'
            ";
            $resultInv  = $db->sql_query($sqlInv);
            $numRowsInv = $db->sql_numrows($resultInv);
            
            $countInv   = 1;
            $month_year = '';
            while ($rowInv = $db->sql_fetchrow($resultInv)) {
                $payment_year = substr($rowInv['invoice_date'],0 ,4);
                $payment_month = substr($rowInv['invoice_date'],5 ,2);

                if ($numRowsInv == $countInv) {
                    if ($rowInv['add_registration_fee'] == 1) {
                        $month_year .= $payment_month . '/' . $payment_year .' - (Reg fee)';
                    } else {
                        $month_year .= $payment_month . '/' . $payment_year;
                    }
                } else {
                    if ($rowInv['add_registration_fee'] == 1) {
                        $month_year .= $payment_month . '/' . $payment_year . ' - (Reg fee), ';
                    } else {
                        $month_year .= $payment_month . '/' . $payment_year . ', ';
                    }
                }
                $countInv++;
            }

            $rows .= "
            {$print_total}
            <tr>
                <td>{$row['receipt_date']}</td>
                <td>{$row['receipt_code']}</td>
                <td>{$row['site_name']}</td>
                <td>{$rowParent['first_name']}</td>
                <td class='{$add_css_class}'>{$row['mode_of_payment']}{$payment_branch}</td>
                <td>{$month_year}</td>
                <td class='txtRight'>{$amount}</td>
            </tr>
            ";
            
        }

        $total_for_payment_mode = number_format($total_for_payment_mode, 2);

        $grand_total_text = '';
        if ($payment_mode == 'All') {
            $grand_total = number_format($grand_total, 2);
            $grand_total_text = "
            <tr>
                <td colspan='6' class='highlight'>Grand Total</td>
                <td class='txtRight highlight'><strong>{$grand_total}</strong></td>
            </tr>
            ";
        }

        $text = "
        {$rows}
        <tr>
            <td colspan='6' class='highlight'>Total</td>
            <td class='txtRight highlight'><strong>{$total_for_payment_mode}</strong></td>
        </tr>
        {$grand_total_text}
        ";

        return $text;
    }

    /**
     *
     */
    function getRowsHTMLOld() {
        $fn = Zend_Registry::get('fn');
        
        $site_id      = $fn->getReqParam('site_id');
        $payment_mode = $fn->getReqParam('payment_mode');

        $rows = '';
        $print_total = '';
        $grand_total = 0;
        $mode_of_payment = '';
        $total_for_payment_mode = 0;
        
        foreach($this->model->dataArray as $row){

            if ($mode_of_payment == '' && (!is_numeric($site_id) || $site_id == $row['payment_site_id'] || $row['payment_site_id'] == '')) {
                $print_total = 0;
                $mode_of_payment = $row['mode_of_payment'];

                // Check whether payment is done for one or more invoices
                if ($row['amount'] > $row['invoice_amount']) {
                    $total_for_payment_mode = $row['invoice_amount'];
                } else {
                    $total_for_payment_mode = $row['amount'];
                }
            } else if ($mode_of_payment == $row['mode_of_payment'] && (!is_numeric($site_id) || $site_id == $row['payment_site_id'] || $row['payment_site_id'] == '')) {
                $print_total = 0;

                // Check whether payment is done for one or more invoices
                if ($row['amount'] > $row['invoice_amount']) {
                    $total_for_payment_mode += $row['invoice_amount'];
                } else {
                    $total_for_payment_mode += $row['amount'];
                }

                $mode_of_payment = $row['mode_of_payment'];
            } else if ($mode_of_payment != $row['mode_of_payment'] && (!is_numeric($site_id) || $site_id == $row['payment_site_id'] || $row['payment_site_id'] == '')) {
                $print_total = 1;
                $mode_of_payment = $row['mode_of_payment'];
                $payment_total = $total_for_payment_mode;

                // Check whether payment is done for one or more invoices
                if ($row['amount'] > $row['invoice_amount']) {
                    $total_for_payment_mode = $row['invoice_amount'];
                } else {
                    $total_for_payment_mode = $row['amount'];
                }
            }

            if ($print_total == 1) {
                $payment_total = number_format($payment_total, 2);
                $print_total = "
                <tr class='highlight'>
                    <td colspan='6'>Total</td>
                    <td class='txtRight'><strong>{$payment_total}</strong></td>
                </tr>
                ";
            } else {
                $print_total = "";
            }
            
            $amount = '';
            if (!is_numeric($site_id) || $site_id == $row['payment_site_id'] || $row['payment_site_id'] == '') {
                
                // Check payment is done for one or more invoices
                if ($row['amount'] > $row['invoice_amount']) {
                    $grand_total += $row['invoice_amount'];
                    $amount = $row['invoice_amount'] . '.00 / ' . $row['amount'];
                } else {
                    $grand_total += $row['amount'];
                    $amount = $row['amount'];
                }
            }
            

            $payment_branch = '';
            $add_css_class  = '';
            if ($row['payment_site_id'] != '') {
                $siteRec = $fn->getRecordRowByID('site', 'site_id', $row['payment_site_id']);
                $payment_branch = " - Payment done at " . $siteRec['title'];
                $add_css_class  = 'alert';
            }

            $payment_year = substr($row['invoice_date'],0 ,4);
            $payment_month = substr($row['invoice_date'],5 ,2);
            switch ($payment_month) {
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
            
            $rows .= "
            {$print_total}
            <tr>
                <td>{$row['receipt_date']}</td>
                <td>{$row['receipt_code']}</td>
                <td>{$row['site_name']}</td>
                <td>{$row['first_name']}</td>
                <td class='{$add_css_class}'>{$row['mode_of_payment']}{$payment_branch}</td>
                <td>{$prefix_month}/{$payment_year}</td>
                <td class='txtRight'>{$amount}</td>
            </tr>
            ";
            
        }
        
        $total_for_payment_mode = number_format($total_for_payment_mode, 2);
        
        $grand_total_text = '';
        if ($payment_mode == 'All') {
            $grand_total = number_format($grand_total, 2);
            $grand_total_text = "
            <tr>
                <td colspan='6' class='highlight'>Grand Total</td>
                <td class='txtRight highlight'><strong>{$grand_total}</strong></td>
            </tr>
            ";
        }
        
        $text = "
        {$rows}
        <tr>
            <td colspan='6' class='highlight'>Total</td>
            <td class='txtRight highlight'><strong>{$total_for_payment_mode}</strong></td>
        </tr>
        {$grand_total_text}
        ";

        return $text;
    }
}