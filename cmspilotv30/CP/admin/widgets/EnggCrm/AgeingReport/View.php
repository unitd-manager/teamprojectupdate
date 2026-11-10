<?
class CP_Admin_Widgets_EnggCrm_AgeingReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');

        $thirtyOneDays = date('Y-m-d', strtotime("-31 days"));
        $sixtyDays = date('Y-m-d', strtotime("-60 days"));
        $sixtyOneDays = date('Y-m-d', strtotime("-61 days"));
        $nintyDays = date('Y-m-d', strtotime("-90 days"));
        $nintyOneDays = date('Y-m-d', strtotime("-91 days"));

        $thirtyOneDaysFormatted = $dateUtil->formatDate($thirtyOneDays, 'DD/MM/YYYY');
        $sixtyDaysFormatted = $dateUtil->formatDate($sixtyDays, 'DD/MM/YYYY');
        $sixtyOneDaysFormatted = $dateUtil->formatDate($sixtyOneDays, 'DD/MM/YYYY');
        $nintyDaysFormatted = $dateUtil->formatDate($nintyDays, 'DD/MM/YYYY');
        $nintyOneDaysFormatted = $dateUtil->formatDate($nintyOneDays, 'DD/MM/YYYY');

        $text = "
		<div class = 'tableOuter scroll-pane'>
		<table class='thinlist'>
			<thead>
				<tr>
					<th>S.NO</th>
					<th>COMPANY NAME</th>
                    <th class='txtRight'>31-60 Days<br/>{$sixtyDaysFormatted} - {$thirtyOneDaysFormatted}</th>
                    <th class='txtRight'>61-90 Days<br/>{$nintyDaysFormatted} - {$sixtyOneDaysFormatted}</th>
					<th class='txtRight'>Above 90 days<br/>Above {$nintyOneDaysFormatted}</th>
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
        
        $rows = '';
        $serial_no = 1;
        foreach($this->model->dataArray as $row){

            $thirtyOneDays = date('Y-m-d', strtotime("-31 days"));
            $sixtyDays = date('Y-m-d', strtotime("-60 days"));
            $sixtyOneDays = date('Y-m-d', strtotime("-61 days"));
            $nintyDays = date('Y-m-d', strtotime("-90 days"));
            $nintyOneDays = date('Y-m-d', strtotime("-91 days"));
            $days3160 = number_format($this->getOverallDueForCompany($row['company_id'], $thirtyOneDays, $sixtyDays),2);
            $days6190 = number_format($this->getOverallDueForCompany($row['company_id'], $sixtyOneDays, $nintyDays),2);
            $daysAbove90 = number_format($this->getOverallDueForCompany($row['company_id'], $nintyOneDays, 91),2);

            $rows .= "
            <tr>
                <td>{$serial_no}</td>
                <td>{$row['company_name']}</td>
                <td class='txtRight'>{$days3160}</td>
                <td class='txtRight'>{$days6190}</td>
                <td class='txtRight'>{$daysAbove90}</td>
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
    function getInvoiceIdsForCompany($company_id, $start_date, $end_date) {
        $db = Zend_Registry::get('db');

        if ($end_date == 91) {
            $sqlAppend = "<= '{$start_date}'";
        } else {
            $sqlAppend  = "BETWEEN '{$end_date}' AND '{$start_date}'";
        }

        $sql = "
        SELECT DISTINCT i.invoice_id
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        WHERE o.company_id = {$company_id}
          AND (i.status = 'Due'
           OR i.status = 'Partial Payment'
           OR i.status = 'Late')
           AND i.invoice_date {$sqlAppend}
        ";        
        $result  = $db->sql_query($sql);
        $numRows = $db->sql_numrows($result);
        $count   = 1;

        $rowsInvoice = '';
        if ($numRows) {
            while ($row = $db->sql_fetchrow($result)) {
                if ($count == $numRows) {
                    $rowsInvoice .= $row['invoice_id'];
                } else {
                    $rowsInvoice .= $row['invoice_id'] . ',';
                }
                $count++;
            }
        }

        return $rowsInvoice;
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