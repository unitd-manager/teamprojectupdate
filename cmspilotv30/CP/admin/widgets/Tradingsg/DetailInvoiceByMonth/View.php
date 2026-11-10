<?
class CP_Admin_Widgets_Tradingsg_DetailInvoiceByMonth_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $month      = $fn->getReqParam('month');
        if ($month != ''){
	        $dateObj   = DateTime::createFromFormat('!m', $month);
			$monthName = $dateObj->format('F');
			$monthName = $monthName.' Month';
		}else{
			$monthName  = 'All Months';
		}

        $text = "
        <h2>Invoice by {$monthName}</h2>
		<div class = 'tableOuter scroll-pane'>
		<table class='thinlist'>
			<thead>
				<tr>
					<th>S.No</th>
					<th>Date</th>
					<th>Invoice Code</th>
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
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';
        $total = 0;
		$siteTitle = '' ;
		$siteLocationTotal = '' ;
		$count = 1;

        foreach($this->model->dataArray as $row){

        	$invoice_amount = number_format($row['invoice_amount'],2);
        	$urlOrder = "index.php?_topRm=finance&module=tradingsg_order&_action=detail&record_id={$row['order_id']}";

            $rows .= "
			<tr>
				<td>{$count}</td>
				<td>{$row['invoice_date']}</td>
				<td><a target='_blank' href='{$urlOrder}'>{$row['invoice_code']}</a></td>
				<td class='txtRight'>{$invoice_amount}</td>
			</tr>
			";
			$count++;
            $total += $row['invoice_amount'];
        }

        $total = number_format($total, 2);


    	$siteLocationTotal = "
        <tr class=''>
            <td class='lastRowBgColor txtRight' colspan='3'>Total</td>
            <td class='txtRight lastRowBgColor'>{$total}</td>
        </tr>
        ";

        $text = "
        {$rows}
        {$siteLocationTotal}
        ";

        return $text;
    }
}