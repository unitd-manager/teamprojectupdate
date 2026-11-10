<?
class CP_Admin_Widgets_Tradingsg_PosSalesSummary_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>POS Sales Summary</h2>
		<div class = 'tableOuter scroll-pane'>
		<table class='thinlist'>
			<thead>
				<tr>
					<th>Date</th>
					<th>Code</th>
					<th>Amount</th>
					<th>Staff Name</th>
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
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';

        //$today  = date('Y-m-d');

        foreach($this->model->dataArray as $row){

        	$discount_sum_percent = 0;
            $discount_sum_value = 0;
            //TO CHECK IF THE SUM OF DISCOUNT TYPE(%) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
            $subSqlForPercentSum = "
            SELECT SUM(((oi.unit_price * oi.discount_percentage )/100)* oi.qty) as discount_sum_percent
            FROM order_item oi
            WHERE oi.order_id = {$row['order_id']}
              AND oi.discount_type = '%'
            ";
            $resultSubSql = $db->sql_query($subSqlForPercentSum);
            $rowSql       = $db->sql_fetchrow($resultSubSql);
            if($rowSql['discount_sum_percent'] > 0){
                $discount_sum_percent = $rowSql['discount_sum_percent'];
            }

            //TO CHECK IF THE SUM OF DISCOUNT TYPE(VALUE) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
            $subSqlForValueSum ="
            SELECT SUM(oi.discount_percentage  * oi.qty) as discount_sum_value
            FROM order_item oi
            WHERE oi.order_id = {$row['order_id']}
              AND oi.discount_type = 'Value'
            ";
            $resultSubSql1 = $db->sql_query($subSqlForValueSum);
            $rowSql1       = $db->sql_fetchrow($resultSubSql1);
            if($rowSql1['discount_sum_value'] > 0){
                $discount_sum_value = $rowSql1['discount_sum_value'];
            }

            $discount_percentage_amount_sum = $discount_sum_value + $discount_sum_percent;
            if($row['record_type'] == 'POS'){
                $order_amount = $row['order_amount'] - $discount_percentage_amount_sum;
                $companyName = $row['name_of_company'];
            } else {
                $order_amount = $row['order_amount'];
                $companyName = $row['company_name'];
            }

            if($row['sales_return_amount']){
                $order_amount = $row['order_amount'] - $row['sales_return_amount'];
            }

			//if($row['contact_date'] > $today || $row['contact_date'] == $today){
				$orderdate = $fn->getCPDate($row['order_date'],"d-m-Y");
				$order_amount = number_format($order_amount,2);
			    $rows .= "
				<tr>
					<td>{$orderdate}</td>
					<td><a href='index.php?_topRm=finance&module=tradingin_order&record_id={$row['order_id']}&_action=edit'><u>{$row['order_id']}</u></a></td>
					<td class='txtRight w100'>{$order_amount}</td>
					<td>{$row['created_by']}</td>
				</tr>
				";
			//}

        }

        $text = "
        {$rows}
        ";

        return $text;
    }

}