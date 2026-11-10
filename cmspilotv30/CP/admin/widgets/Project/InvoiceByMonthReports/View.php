<?
class CP_Admin_Widgets_Project_InvoiceByMonthReports_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $text = "
        <h2>Invoice by Last 12 Months</h2>
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
        $rows = '';
        $total = '';
        foreach($this->model->dataArray as $row){
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