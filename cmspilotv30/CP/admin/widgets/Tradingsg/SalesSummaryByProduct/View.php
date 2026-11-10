<?
class CP_Admin_Widgets_Tradingsg_SalesSummaryByProduct_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>Sales Summary By Product</h2>
		<div class = 'tableOuter scroll-pane'>
			<table class='thinlist'>
				<thead>
					<tr>
						<th>S.No</th>
						<th>Sales Date</th>
						<th>Product Item Code</th>
						<th>Name of the Item</th>
						<th>Quantity</th>
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

        $rows = '';
		$count = 1 ;

        foreach($this->model->dataArray as $row){

			$creationDate = $fn->getCPDate($row['order_date'],"d-m-Y");

		    $rows .= "
			<tr>
				<td>{$count}</td>
				<td>{$creationDate}</td>
				<td>{$row['item_code']}</td>
				<td>{$row['item_title']}</td>
				<td>{$row['qty']}</td>
			</tr>
			";
            $count++;                

        }

        $text = "
        {$rows}
        ";

        return $text;
    }

}