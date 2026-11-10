<?
class CPL_Admin_Widgets_EnggCrm_ExpenseSummaryReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /*
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');
        
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $month      = $fn->getReqParam('month');
        $year       = $fn->getReqParam('year');
        $company_id = $fn->getReqParam('company_id');
        $status     = $fn->getReqParam('status');

        $supplierRec = $fn->getRecordRowById('supplier', 'supplier_id', $company_id);
                
        $current_date = date('d-m-Y');
        if ($start_date != '' && $end_date == '') {
            $start_date = $dateUtil->formatDate($start_date, 'DD-MM-YYYY');
            $end_date = $current_date;
        } else if ($start_date == '' && $end_date != ''){
            $start_date = '01-' . date('m') . '-' . date('Y');
            $end_date = $dateUtil->formatDate($end_date, 'DD-MM-YYYY');
        } else if ($start_date != '' && $end_date != '') {
            $start_date = $dateUtil->formatDate($start_date, 'DD-MM-YYYY');
            $end_date = $dateUtil->formatDate($end_date, 'DD-MM-YYYY');
        } else {
            $start_date = '01-' . date('m') . '-' . date('Y');
            $end_date = $current_date;
        }

        $text = "
        <table class='thinlist summaryTable'>
            <thead>
                <th colspan='4' class='txtCenter'>Summary</th>
            </thead>
            <tr>
                <td><b>Company Name :</b> {$supplierRec['company_name']}</td>
                <td><b>Status :</b> {$status}</td>
                <td><b>Start Date :</b> {$start_date}</td>
                <td><b>End Date :</b> {$end_date}</td>
            </tr>
        </table>

		<div class='tableOuter scroll-pane'>
		<table class='thinlist mt10'>
			<thead>
				<tr>
					<th>Date</th>
                    <th>Invoice No.</th>
                    <th>Company Name</th>
					<th class='txtRight'>No GST</th>
                    <th class='txtRight'>Amount Before GST</th>
                    <th class='txtRight'>GST</th>
                    <th class='txtRight'>Total Amount with GST</th>
                    <th class='txtRight'>Received</th>
                    <th class='txtRight'>Balance</th>
                    <th>GIRO</th>
                    <th>Cheque</th>
                    <th>Cheque No.</th>
                    <th>Cash</th>
                    <th>Cheque Issued Date</th>
                    <th>Paid</th>
                    <th>Unpaid</th>
                    <th>Payment Cleared Date</th>
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

    /*
     *
     */
    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
       
        $rows               = '';
        $overall_purchase   = 0;
        $overall_before_gst = 0;
        $overall_after_gst  = 0;
		
        foreach ($this->model->dataArray as $row) {
            $date = $fn->getCPDate($row['date'], 'd-m-Y');
            $totalAmt = $row['service_charge'] + $row['amount'] + $row['gst_amount'];

            $sqlReceipt = "SELECT DISTINCT payment_id, date, mode_of_payment, bank_name, cheque_no, cheque_date FROM payment WHERE record_id = '{$row['expense_id']}'";
            $resultReceipt = $db->sql_query($sqlReceipt);
            $numRowsReceipt = $db->sql_numrows($resultReceipt);
            $count = 1;
            $bank = '';
            $cheque_no = '';
            $cheque_date = '';
            $receipt_date = '';
            $mode_of_payment = '';
            $giro = '';
            $cash = '';
            while ($rowReceipt = $db->sql_fetchrow($resultReceipt)) {
                $receipt_date .= $fn->getCPDate($rowReceipt['date'], 'd-m-Y') . ', ';
                $mode_of_payment .= $rowReceipt['mode_of_payment'] . ', ';
                $bank .= $rowReceipt['bank_name'] . ', ';
                $cheque_no .= $rowReceipt['cheque_no'] . ', ';
                $cheque_date .= $fn->getCPDate($rowReceipt['cheque_date'], 'd-m-Y') . ', ';

                if($rowReceipt['mode_of_payment'] == 'GIRO'){
                    $giro .= 'Yes, ';
                }
                if($rowReceipt['mode_of_payment'] == 'Cash'){
                    $cash .= 'Yes, ';
                }
                $count++;
            }

            $bank = rtrim($bank, ', ');
            $cheque_no = rtrim($cheque_no, ', ');
            $cheque_date = rtrim($cheque_date, ', ');
            $giro = rtrim($giro, ', ');
            $cash = rtrim($cash, ', ');

            $payment_status = '';
            if($row['payment_status'] == 'Paid'){
                $payment_status = 'Yes';
            }

            $payment_status_unpaid = '';
            if($row['payment_status'] != 'Paid'){
                $payment_status_unpaid = 'Yes';
            }

            $sqlRec = "
            SELECT SUM(p.amount) AS total_invoice_amount_paid
            FROM payment p
            WHERE p.record_id = {$row['expense_id']}
            ";
            $resultRec = $db->sql_query($sqlRec);
            $rowRec    = $db->sql_fetchrow($resultRec);

            $receipt_amount = $rowRec['total_invoice_amount_paid'];
            if ($rowRec['total_invoice_amount_paid'] == '') {
                $receipt_amount = 0.00;
            }

            $service_charge_formatted = number_format($row['service_charge'], 2);
            $amount_formatted = number_format($row['amount'], 2);
            $gst_amount_formatted = number_format($row['gst_amount'], 2);
            $totalAmt_formatted = number_format($totalAmt, 2);
            $receipt_amount_formatted = number_format($receipt_amount, 2);
            $balance = number_format($totalAmt - $receipt_amount, 2);
            //$balance = round($balance);

		    $rows .= "
			<tr>
				<td>{$date}</td>
                <td>{$row['invoice_code']}</td>
                <td>{$row['company_name']}</td>
				<td class='txtRight'>{$service_charge_formatted}</td>
				<td class='txtRight'>{$amount_formatted}</td>
                <td class='txtRight'>{$gst_amount_formatted}</td>
                <td class='txtRight'>{$totalAmt_formatted}</td>
                <td class='txtRight'>{$receipt_amount_formatted}</td>
                <td class='txtRight'>{$balance}</td>
                <td>{$giro}</td>
                <td>{$bank}</td>
                <td>{$cheque_no}</td>
                <td>{$cash}</td>
                <td>{$cheque_date}</td>
                <td>{$payment_status}</td>
                <td>{$payment_status_unpaid}</td>
                <td></td>
			</tr>
			";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }

}