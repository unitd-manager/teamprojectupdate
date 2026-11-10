<?
class CP_Admin_Widgets_Tradingsg_InvoiceSummaryByProductGroup_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>Invoice Summary By Product Group</h2>
		<div class = 'tableOuter scroll-pane'>
			<table class='thinlist'>
				<thead>
					<tr>
						<th>S.No</th>
						<th>Sales Date</th>
						<th>Invoice Code</th>
						<th>Part Number</th>
						<th>Item Name</th>
						<th>Product Group Name</th>
						<th>Customer Name</th>
						<th>Quantity</th>
						<th>Unit Price</th>
						<th>Total Amount</th>
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

    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');

        $rows  = '';
		$count = 1 ;
		$total = 0;
		$price = 0;
		$amount = 0;

        foreach($this->model->dataArray as $row){

			$creationDate = $fn->getCPDate($row['invoice_date'],"d-m-Y");

			$totalAmount = $row['qty'] * $row['unit_price'];
			$amount += $totalAmount;
			$totalAmount = number_format($totalAmount,2);
			$unit_price = number_format($row['unit_price'],2);

		    $rows .= "
			<tr>
				<td>{$count}</td>
				<td>{$creationDate}</td>
				<td>{$row['invoice_code']}</td>
				<td>{$row['part_number']}</td>
				<td>{$row['item_title']}</td>
				<td>{$row['title']}</td>
				<td>{$row['cust_company_name']}</td>
				<td>{$row['qty']}</td>
				<td class='txtRight'>{$unit_price}</td>
				<td class='txtRight'>{$totalAmount}</td>
			</tr>
			";

            $count++;

            $total += $row['qty'];
            $price += $row['unit_price'];
        }
		$price = number_format($price,2);
		$amount = number_format($amount,2);

    	$qtyTotal = "
        <tr class=''>
            <td class='lastRowBgColor txtRight' colspan='7' >Total Qty</td>
            <td class='lastRowBgColor'>{$total}</td>
            <td class='lastRowBgColor txtRight'>{$price}</td>
            <td class='lastRowBgColor txtRight'>{$amount}</td>
        </tr>
        ";

        $text = "
        {$rows}
        {$qtyTotal}
        ";

        return $text;
    }

}