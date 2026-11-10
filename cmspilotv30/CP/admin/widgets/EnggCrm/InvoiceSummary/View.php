<?
class CP_Admin_Widgets_EnggCrm_InvoiceSummary_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');

        $company_id = $fn->getReqParam('company_id');
        $start_date = $dateUtil->formatDate($fn->getReqParam('start_date'), 'DD-MM-YYYY');
        $end_date   = $dateUtil->formatDate($fn->getReqParam('end_date'), 'DD-MM-YYYY');

        if ($start_date == '') {
            $start_date = date('d-m-Y', mktime (0,0,0,date("m")-1, date("d"), date("Y")));
        }

        if ($end_date == '') {
            $end_date = date('d-m-Y');
        }

        $companyRec = $fn->getRecordRowById('company', 'company_id', $company_id);

        $text = "
        <table class='thinlist summaryTable'>
            <thead>
                <th colspan='3'>Summary</th>
            </thead>
            <tr>
                <td>Company : {$companyRec['company_name']}</td>
                <td>Invoice Start Date : {$start_date}</td>
                <td>Invoice End Date : {$end_date}</td>
            </tr>
        </table>
		<div class = 'tableOuter scroll-pane'>
		<table class='thinlist'>
			<thead>
				<tr>
                    <th>S/No</th>
					<th>Company Name</th>
					<th>Date</th>
					<th>Code</th>
					<th align='right'>Invoice Amount</th>
                    <th align='right'>GST Amount</th>
                    <th align='right'>Total Amount</th>
					<th>Status</th>
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
        
        $company_id = $fn->getReqParam('company_id');

        $rows = '';
        $totalInvoiceAmount = 0;
        $count = 0;

        foreach($this->model->dataArray as $row){
            $count++;

            $amount_payable = $row['invoice_amount'] - $row['discount'];
            if ($row['gst_percentage']) {
                $gst_amount = (($amount_payable * $row['gst_percentage'])/100);
                /* Taking two decimal values for gst amount */
                $fraction_length = strlen(substr(strrchr($gst_amount, "."), 1)); // Checking the lingth of the fraction value
                if ($fraction_length > 2) {
                    list($integer, $fraction) = explode(".", (string) $gst_amount);
                    $fraction = substr($fraction, 0, 2);
                    $gst_amount = $integer . "." . $fraction;
                }

                $total = $amount_payable + $gst_amount;
            } else {
                $total = $amount_payable;
            }

            $amount_payable_formatted = number_format($amount_payable, 2);
            $gst_amount_formatted = number_format($gst_amount, 2);
            $total_formatted = number_format($total, 2);

            $inv_date = $fn->getCPDate($row['invoice_date'], 'ym/');
            $invoice_code = $inv_date . substr($row['invoice_code'], 2);

		    $rows .= "
			<tr>
                <td>{$count}</td>
				<td>{$row['company_name']}</td>
				<td>{$fn->getCPDate($row['invoice_date'], 'd-m-Y')}</td>
				<td>{$invoice_code}</td>
                <td align='right'>{$amount_payable_formatted}</td>
                <td align='right'>{$gst_amount_formatted}</td>
				<td align='right'>{$total_formatted}</td>
				<td>{$row['status']}</td>
			</tr>
			";

            $totalInvoiceAmount += $total;
        }

        $totalInvoiceAmount = number_format($totalInvoiceAmount, 2);

        $text = "
        {$rows}
        <tr bgcolor=\"#A9A9A9\">
            <th></th>
            <th></th>
            <th></th>
            <th>TOTAL</th>
            <th></th>
            <th></th>
            <th class='lastRowBgColor txtRight'>{$totalInvoiceAmount}</th>
            <th></th>
        </tr>
        ";

        return $text;
    }
}