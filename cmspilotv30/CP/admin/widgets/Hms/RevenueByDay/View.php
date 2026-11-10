<?
class CP_Admin_Widgets_Hms_RevenueByDay_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = "
        <h2><strong>Revenue By Day</strong></h2>
		<div class = 'tableOuter scroll-pane'>
		<table class='thinlist'>
			<thead>
				<tr>
					<th>Date</th>
					<th>Day</th>
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
		$siteTitle = '' ;
		$siteLocationTotal = '' ;

        foreach($this->model->dataArray as $row){
            $invoice_amount_monthly = number_format($row['invoice_amount'], 2);
			$creationDate = $fn->getCPDate($row['invoice_date'],"d-m-Y");
            $total += $row['invoice_amount'];

            $rows .= "
			<tr>
				<td>{$creationDate}</td>
				<td>{$row['day']}</td>
				<td class='txtRight'>{$invoice_amount_monthly}</td>
			</tr>
			";
        }
        
        $total = number_format($total, 2);
        $text = "
        {$rows}
        <tr>
            <td colspan='2' align='right'><strong>Total</strong></td>
            <td align='right'><strong>{$total}</strong></td>
        </tr>
        ";

        return $text;
    }
}