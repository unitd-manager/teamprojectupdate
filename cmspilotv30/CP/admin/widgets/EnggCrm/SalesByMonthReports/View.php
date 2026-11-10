<?
class CP_Admin_Widgets_EnggCrm_SalesByMonthReports_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn    = Zend_Registry::get('fn');

        $text = "
        <h2>Sales by Last 12 Months</h2>
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
            $order_amount_monthly = number_format($row['order_amount_monthly'], 2);

            $rows .= "
			<tr>
				<td>{$row['order_month']}</td>
				<td class='txtRight'>{$order_amount_monthly}</td>
			</tr>
			";                
            $total += $row['order_amount_monthly'];
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