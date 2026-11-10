<?
class CPL_Admin_Widgets_EnggCrm_InvoiceSummary_View extends CP_Admin_Widgets_EnggCrm_InvoiceSummary_View
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');

        $record_type = $fn->getReqParam('record_type');
        $company_id  = $fn->getReqParam('company_id');
        $start_date  = $dateUtil->formatDate($fn->getReqParam('start_date'), 'DD-MM-YYYY');
        $end_date    = $dateUtil->formatDate($fn->getReqParam('end_date'), 'DD-MM-YYYY');

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
                <th colspan='4'>Summary</th>
            </thead>
            <tr>
                <td><b>Category :</b> {$record_type}</td>
                <td><b>Company :</b> {$companyRec['company_name']}</td>
                <td><b>Invoice Start Date :</b> {$start_date}</td>
                <td><b>Invoice End Date :</b> {$end_date}</td>
            </tr>
        </table>
		<div class = 'tableOuter scroll-pane'>
		<table class='thinlist'>
			<thead>
				<tr>
                    <th class='txtCenter'>S/No</th>
					<th>Company Name</th>
					<th class='txtCenter'>Date</th>
					<th class='txtCenter'>Invoice Code</th>
					<th class='txtRight'>Amount</th>
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

            if ($row['gst_percentage']) {
                $gst_amount = (($row['invoice_amount'] * $row['gst_percentage'])/100);
                /* Taking two decimal values for gst amount */
                $fraction_length = strlen(substr(strrchr($gst_amount, "."), 1)); // Checking the lingth of the fraction value
                if ($fraction_length > 2) {
                    list($integer, $fraction) = explode(".", (string) $gst_amount);

                    /* Checking whether 3rd decimal point is more than or equal to 5
                       If Yes, add 1 to 2nd decimal point
                     */
                    $gstDecimalMore = substr($fraction, 2, 1);
                    $fraction = substr($fraction, 0, 2);
                    if ($gstDecimalMore >= 5) {
                        $fraction = $fraction + 1;
                    }

                    $gst_amount = $integer . "." . $fraction;
                }

                $total = $row['invoice_amount'] + $gst_amount;
            } else {
                $total = $row['invoice_amount'];
            }

            $total_formatted = number_format($total, 2);

		    $rows .= "
			<tr>
                <td class='txtCenter'>{$count}</td>
				<td>{$row['company_name']}</td>
				<td class='txtCenter'>{$fn->getCPDate($row['invoice_date'], 'd-m-Y')}</td>
				<td class='txtCenter'>{$row['invoice_code']}</td>
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
            <th class='txtRight'>TOTAL</th>
            <th class='lastRowBgColor txtRight'>{$totalInvoiceAmount}</th>
            <th></th>
        </tr>
        ";

        return $text;
    }
}