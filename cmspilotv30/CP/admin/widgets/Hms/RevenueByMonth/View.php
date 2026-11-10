<?
class CP_Admin_Widgets_Hms_RevenueByMonth_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = "
        <h2><strong>Revenue By Month</strong></h2>
		<div class = 'tableOuter scroll-pane'>
		<table class='thinlist'>
			<thead>
				<tr>
					<th>Month</th>
					<th class='txtRight'>Total</th>
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
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';
        $total = 0;

        foreach($this->model->dataArray as $row){
            $invoice_amount_monthly = number_format($row['invoice_amount_monthly'], 2);

            $rows .= "
			<tr>
				<td>{$row['invoice_month']}</td>
				<td class='txtRight'>{$invoice_amount_monthly}</td>
			</tr>
			";
        }

        $text = "
        {$rows}
        ";

        return $text;
    }
}