<?
class CPL_Admin_Widgets_EnggCrm_InvoiceByMonthReports_View extends CP_Admin_Widgets_EnggCrm_InvoiceByMonthReports_View
{
    /**
     *
     */
    function getWidget() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn    = Zend_Registry::get('fn');

        $record_type = $fn->getReqParam('record_type');
        $start_date  = date('d-m-Y', mktime (0,0,0,date("m")-12, date("d"), date("Y")));
        $end_date    = date('d-m-Y');

        $text = "
        <table class='thinlist summaryTable'>
            <thead>
                <th colspan='4' class='txtCenter'>Summary</th>
            </thead>
            <tr>
                <td><b>Category :</b> {$record_type}</td>
                <td>Invoice for Last 12 Months</td>
                <td><b>Invoice Start Date :</b> {$start_date}</td>
                <td><b>Invoice End Date :</b> {$end_date}</td>
            </tr>
        </table>

		<div class = 'tableOuter scroll-pane'>
		<table class='thinlist'>
			<thead>
				<tr>
					<th>Month</th>
					<th class='txtRight'>Amount</th>
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
        $total = 0;

        foreach($this->model->dataArray as $row) {
            $invoice_amount_monthly = number_format($row['invoice_amount_monthly'], 2);

            $rows .= "
			<tr>
				<td>{$row['invoice_month']}</td>
				<td class='txtRight'>{$invoice_amount_monthly}</td>
			</tr>
			";                
            $total += $row['invoice_amount_monthly'];
        }

        $total = number_format($total, 2);
        
        $text = "
        {$rows}
        <tr class=''>
            <td class='lastRowBgColor'>Total</td>
            <td class='txtRight lastRowBgColor'>{$total}</td>
        </tr>
        ";

        return $text;
    }
}