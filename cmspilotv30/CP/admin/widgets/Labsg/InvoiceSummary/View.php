<?
class CP_Admin_Widgets_Labsg_InvoiceSummary_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');

        $bill_type           = $fn->getReqParam('bill_type');
        $company_patient_id  = $fn->getReqParam('company_patient_id');
        $start_date          = $fn->getReqParam('start_date');
        $end_date            = $fn->getReqParam('end_date');

        $company_patient_name = '';
        if ($bill_type == 'Company' && $company_patient_id != '') {
            $sqlOrder = "
            SELECT o.company_name AS company_patient_name
            FROM `order` o
            WHERE o.bill_type = 'Company'
              AND o.company_id = {$company_patient_id}
            ";
            $resultOrder = $db->sql_query($sqlOrder);
            $rowOrder = $db->sql_fetchrow($resultOrder);
            $company_patient_name = $rowOrder['company_patient_name'];
        } else if ($bill_type == 'Individual' && $company_patient_id != '') {
            $sqlOrder = "
            SELECT o.first_name AS company_patient_name
            FROM `order` o
            WHERE o.bill_type = 'Individual'
              AND o.patient_information_id = {$company_patient_id}
            ";
            $resultOrder = $db->sql_query($sqlOrder);
            $rowOrder = $db->sql_fetchrow($resultOrder);
            $company_patient_name = $rowOrder['company_patient_name'];
        }

        if ($start_date == '' & $end_date != '') {
            $end_date_year = substr($end_date, 0, 8);
            $start_date = $end_date_year . '01';
        } else if ($start_date != '' & $end_date == '') {
            $start_date_year = substr($start_date, 0, 8);
            $end_date = $start_date_year . '31';
        } else if ($start_date == '' & $end_date == ''){
            $month_year = date('Y') . '-' . date('m') . '-';
            $start_date = $month_year . '01';
            $end_date = $month_year . '31';
        }

        $start_date_formatted = $dateUtil->formatDate($start_date, 'DD/MM/YYYY');
        $end_date_formatted   = $dateUtil->formatDate($end_date, 'DD/MM/YYYY');

        $text = "
        <table class='thinlist summaryTable'>
            <thead>
                <th colspan='4'>Summary</th>
            </thead>
            <tr>
                <td>Bill type : {$bill_type}</td>
                <td>Patient / Company : {$company_patient_name}</td>
                <td>Start Date : {$start_date_formatted}</td>
                <td>End Date : {$end_date_formatted}</td>
            </tr>
        </table>
		<table class='thinlist'>
			<thead>
				<tr>
                    <th>S.No</th>
					<th>Company Name/ Patient Name</th>
					<th>Date</th>
					<th>Invoice Code</th>
					<th class='txtRight'>Invoice Amount</th>
					<th class='txtRight'>Paid</th>
                    <th class='txtRight'>Discount</th>
					<th class='txtRight'>Amount Due</th>
				</tr>
			</thead>
			<tbody>
				{$this->getRowsHTML()}
			</tbody>
		</table>
        ";

        return $text;
    }

    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $location_id     = $fn->getReqParam('location_id');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $company_id      = $fn->getReqParam('company_id');
        $bill_type       = $fn->getReqParam('bill_type');

        $rows = '';
		$siteTitle = '' ;
        $totalInvoiceAmount  = 0;
        $totalDiscountAmount = 0;
        $totalBalanceAmount  = 0;
        $totalPaidAmount     = 0;
        $count = 1;

        foreach($this->model->dataArray as $row){

            if($bill_type == 'Company'){
                $SQLInv = "
                SELECT inv.*
                ,(SELECT SUM(invh.amount)
                FROM invoice_receipt_history invh
                LEFT JOIN (receipt rcp) ON (invh.receipt_id = rcp.receipt_id)
                WHERE invh.invoice_id = inv.invoice_id
                  AND rcp.receipt_status = 'Paid'
                ) AS total_amount_paid
                ,o.company_name AS patient_name
                FROM invoice inv
                LEFT JOIN `order` o ON (o.order_id = inv.order_id)
                WHERE inv.status != 'Cancelled'
                  AND inv.invoice_id = {$row['invoice_id']}
                ";
            } else {
                $SQLInv = "
                SELECT inv.*
                ,(SELECT SUM(invh.amount)
                FROM invoice_receipt_history invh
                LEFT JOIN (receipt rcp) ON (invh.receipt_id = rcp.receipt_id)
                WHERE invh.invoice_id = inv.invoice_id
                  AND rcp.receipt_status = 'Paid'
                ) AS total_amount_paid
                ,CONCAT_WS(' ', o.first_name, o.middle_name, o.last_name ) AS patient_name
                FROM invoice inv
                LEFT JOIN `order` o ON (o.order_id = inv.order_id)
                WHERE o.patient_information_id = {$row['patient_information_id']}
                AND inv.status != 'Cancelled'
                ";
            }

            $resultInv = $db->sql_query($SQLInv);
            $invoice_amount  = '';

            while ($rowInv = $db->sql_fetchrow($resultInv)) {
        		$invoice_amount = $rowInv['invoice_amount'];
        		$balance_amount  = $invoice_amount - $rowInv['total_amount_paid'] - $rowInv['discount'];
                $totalInvoiceAmount += $invoice_amount;
                $totalDiscountAmount += $rowInv['discount'];
                $totalBalanceAmount += $balance_amount;
                $totalPaidAmount += $rowInv['total_amount_paid'];
                //$invoice_amount = number_format($invoice_amount);
                //$balance_amount = number_format($balance_amount);
        		//$rowInv['total_amount_paid'] = number_format($rowInv['total_amount_paid']);

                $todaylink = "<a target = '_blank' href = 'index.php?_topRm=finance&module=labsg_order&record_id={$rowInv['order_id']}&_action=edit'>";
                $invoiceCode = $rowInv['invoice_code'];

                $invoice_amount_formatted = number_format($invoice_amount, 2);
                $total_amount_paid_formatted = number_format($rowInv['total_amount_paid'], 2);
                $discount_amount_formatted = number_format($rowInv['discount'], 2);
                $balance_amount_formatted = number_format($balance_amount, 2);

			    $rows .= "
				<tr>
                    <td>{$count}</td>
					<td>{$rowInv['patient_name']}</td>
					<td>{$fn->getCPDate($rowInv['invoice_date'], 'd-m-Y')}</td>
					<td>{$todaylink}{$invoiceCode}</td>
					<td align='right'>{$invoice_amount_formatted}</td>
					<td align='right'>{$total_amount_paid_formatted}</td>
                    <td align='right'>{$discount_amount_formatted}</td>
					<td align='right'>{$balance_amount_formatted}</td>
				</tr>
				";
                $count++;
            }
        }

        $totalInvoiceAmount  = number_format($totalInvoiceAmount,2);
        $totalDiscountAmount = number_format($totalDiscountAmount,2);
        $totalBalanceAmount  = number_format($totalBalanceAmount,2);
        $totalPaidAmount     = number_format($totalPaidAmount,2);

        $text = "
        {$rows}
        <tr bgcolor='#CCCCCC'>
            <th colspan='4' class='txtRight'>TOTAL</th>
            <th class='txtRight'>{$totalInvoiceAmount}</th>
            <th class='txtRight'>{$totalPaidAmount}</th>
            <th class='txtRight'>{$totalDiscountAmount}</th>
            <th class='txtRight'>{$totalBalanceAmount}</th>
        </tr>
        ";

        return $text;
    }

}