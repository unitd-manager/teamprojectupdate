<?
class CP_Admin_Widgets_EnggCrm_AgeingBreakdownReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');

        $company_id = $fn->getReqParam('company_id');
        $search_by  = $fn->getReqParam('search_by');

        $companyRec = $fn->getRecordRowById('company', 'company_id', $company_id);

        if ($search_by == "31-60 Days") {
            $thirtyOneDays = date('Y-m-d', strtotime("-31 days"));
            $sixtyDays = date('Y-m-d', strtotime("-60 days"));

            $start_date = $dateUtil->formatDate($sixtyDays, 'DD/MM/YYYY');
            $end_date = $dateUtil->formatDate($thirtyOneDays, 'DD/MM/YYYY');
        } else if ($search_by == "61-90 Days") {
            $sixtyOneDays = date('Y-m-d', strtotime("-61 days"));
            $nintyDays = date('Y-m-d', strtotime("-90 days"));

            $start_date = $dateUtil->formatDate($nintyDays, 'DD/MM/YYYY');
            $end_date = $dateUtil->formatDate($sixtyOneDays, 'DD/MM/YYYY');
        } else {
            $nintyOneDays = date('Y-m-d', strtotime("-91 days"));

            $start_date = $dateUtil->formatDate($nintyOneDays, 'DD/MM/YYYY');
            $end_date = '';
        }

        $text = "
		<div class = 'tableOuter scroll-pane'>
            <table class='thinlist summaryTable'>
                <thead>
                    <th colspan='3'>Summary</th>
                </thead>
                <tr>
                    <td>Company Name : {$companyRec['company_name']}</td>
                    <td>Start Date : {$start_date}</td>
                    <td>End Date : {$end_date}</td>
                </tr>
            </table>
    		<table class='thinlist mt10'>
    			<thead>
    				<tr>
    					<th>S.NO</th>
    					<th>Company Name</th>
                        <th>Invoice Date</th>
                        <th>Invoice Number</th>
                        <th class='txtRight'>Invoice Amount</th>
                        <th class='txtRight'>Amount Paid</th>
                        <th class='txtRight'>Total Amount</th>
    				</tr>
    			</thead>
    			<tbody>
                    {$this->getRowsHTML()}
    			</tbody>
    		</table>
		</div>
        ";

        return $text;
    }

    /**
     *
     */
    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dateUtil = Zend_Registry::get('dateUtil');
        
        $rows = '';
        $serial_no = 1;
        $balance_amount = 0;
        foreach($this->model->dataArray as $row){

            $amount_payable = 0;
            $total_receipt_amount = 0;

            $inv_date = $fn->getCPDate($row['invoice_date'], 'ym/');
            $invoice_code = $inv_date . substr($row['invoice_code'], 2);

            $invoice_date = $dateUtil->formatDate($row['invoice_date'], 'DD-MM-YYYY');

            $invoice_amount_after_disc = $row['invoice_amount'] - $row['discount'];
            $gst_amount = 0;
            if ($row['gst_percentage']) {
                $gst_amount = round((($invoice_amount_after_disc * $row['gst_percentage']) / 100), 2);
            }
            $amount_payable = $invoice_amount_after_disc + $gst_amount;
            $amount_payable_formatted = number_format($amount_payable, 2);

            $sqlRec = "
            SELECT SUM(irh.amount) AS total_invoice_amount_paid
            FROM invoice_receipt_history irh
            LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
            LEFT JOIN (receipt r) ON (irh.receipt_id = r.receipt_id)
            WHERE i.invoice_id = {$row['invoice_id']}
              AND r.receipt_status = 'Paid'
            ";
            $resultRec = $db->sql_query($sqlRec);
            $rowRec    = $db->sql_fetchrow($resultRec);

            $total_receipt_amount = $rowRec['total_invoice_amount_paid'];
            $total_receipt_amount_formatted = number_format($total_receipt_amount, 2);

            $balance_amount += $amount_payable - $total_receipt_amount;
            $balance_amount_formatted = number_format($balance_amount, 2);

            $rows .= "
            <tr>
                <td>{$serial_no}</td>
                <td>{$row['company_name']}</td>
                <td>{$invoice_date}</td>
                <td>{$invoice_code}</td>
                <td class='txtRight'>{$amount_payable_formatted}</td>
                <td class='txtRight'>{$total_receipt_amount_formatted}</td>
                <td class='txtRight'>{$balance_amount_formatted}</td>
            </tr>
            ";
            $serial_no++;
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }

    /**
     *
     */
    function getOverallDueForCompany($company_id, $start_date, $end_date) {
        $db = Zend_Registry::get('db');

        $invoice_ids = $this->getInvoiceIdsForCompany($company_id, $start_date, $end_date);

        if ($invoice_ids == '') {
            return $total_amt_payable = 0;
        }

        $total_amt_payable = 0;
        $total_invoice_amount = 0;
        $total_receipt_amount = 0;

        $invoiceIds = explode(',', $invoice_ids);
        foreach($invoiceIds AS $invoice_id){
            $sqlInv = "
            SELECT i.invoice_amount
                  ,i.gst_percentage
                  ,i.discount
            FROM invoice i
            WHERE i.invoice_id = {$invoice_id}
            ";
            $resultInv = $db->sql_query($sqlInv);
            $rowInv    = $db->sql_fetchrow($resultInv);

            //if ($rowInv['gst_percentage'] > 0) {
            //    $gst_amount = (($rowInv['invoice_amount'] * $rowInv['gst_percentage'])/100);
            //    /* Taking two decimal values for gst amount */
            //    $fraction_length = strlen(substr(strrchr($gst_amount, "."), 1)); // Checking the lingth of the fraction value
            //    if ($fraction_length > 2) {
            //        list($integer, $fraction) = explode(".", (string) $gst_amount);
            //
            //        /* Checking whether 3rd decimal point is more than or equal to 5
            //           If Yes, add 1 to 2nd decimal point
            //         */
            //        $gstDecimalMore = substr($fraction, 2, 1);
            //        $fraction = substr($fraction, 0, 2);
            //        if ($gstDecimalMore >= 5) {
            //            if ($fraction == '99') { //Increasing integer to 1 if decimal is 99
            //                $fraction = '0.00';
            //                $integer = $integer + 1;
            //            } else {
            //                $fraction = $fraction + 1;
            //            }
            //        }
            //
            //        $fraction = substr($fraction, 0, 2);
            //        $gst_amount = $integer . "." . $fraction;
            //    } else if ($fraction_length == 2) {
            //        list($integer, $fraction) = explode(".", (string) $gst_amount);
            //        
            //        if ($fraction == '99') { //Increasing integer to 1 if decimal is 99
            //            $fraction = '0.00';
            //            $integer = $integer + 1;
            //        }
            //    }
            //    $total_invoice_amount += $rowInv['invoice_amount'] + $gst_amount;
            //} else {
            //    $total_invoice_amount += $rowInv['invoice_amount'];
            //}

            $invoice_amount_after_disc = $rowInv['invoice_amount'] - $rowInv['discount'];
            $gst_amount = 0;
            if ($rowInv['gst_percentage']) {
                $gst_amount = round((($invoice_amount_after_disc * $rowInv['gst_percentage']) / 100), 2);
            }

            $total_invoice_amount += $invoice_amount_after_disc + $gst_amount;

            $sqlRec = "
            SELECT SUM(irh.amount) AS total_invoice_amount_paid
            FROM invoice_receipt_history irh
            LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
            LEFT JOIN (receipt r) ON (irh.receipt_id = r.receipt_id)
            WHERE i.invoice_id = {$invoice_id}
              AND r.receipt_status = 'Paid'
            ";
            $resultRec = $db->sql_query($sqlRec);
            $rowRec    = $db->sql_fetchrow($resultRec);

            $total_receipt_amount += $rowRec['total_invoice_amount_paid'];
        }

        $total_amt_payable = $total_invoice_amount - $total_receipt_amount;
        //$total_amt_payable = $total_invoice_amount;

        return $total_amt_payable;
    }
}