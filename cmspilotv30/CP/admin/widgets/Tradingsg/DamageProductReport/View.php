<?
class CP_Admin_Widgets_Tradingsg_DamageProductReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>Stock</h2>
		<div class = 'tableOuter scroll-pane'>
		<table class='thinlist'>
			<thead>
				<tr>
					<th>Product Name</th>
                    <th>Carton Number</th>
                    <th>Purchased Qty</th>
                    <th>Damaged Qty</th>
                    <th>Available Stock(-Damaged)</th>
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
        $fn 	= Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');

        $rows = '';

        $linkToStock = '' ;

        if($cpCfg['cp.excludeStock'] == 1){
            $linkToStock = "AND o.link_stock = 1";
        }

        foreach($this->model->dataArray as $row){

            $StockSql = "
            SELECT
                (SELECT SUM(qty) FROM po_product
                WHERE product_id = {$row['product_id']}) as product_qty_purchased
                ,(SELECT SUM(oi.qty) FROM order_item oi
                LEFT JOIN (`order` o) ON (o.order_id = oi.order_id)
                WHERE record_id = {$row['product_id']}
                  AND o.order_status = 'Paid'
                  AND o.record_type = 'POS'
                ) as product_qty_sold_pos
                ,(SELECT SUM(invItem.qty) FROM invoice_item invItem
                LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                WHERE record_id = {$row['product_id']}
                  AND o.record_type != 'POS'
                  {$linkToStock}
                ) as product_qty_sold_from_quote
                ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
                LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                WHERE ini.record_id = {$row['product_id']}
                  AND srh.status IS NULL
                ) as sales_return_qty
            ";

            $resultStockSql = $db->sql_query($StockSql);
            $rowStockSql    = $db->sql_fetchrow($resultStockSql);

            $stock = $rowStockSql['product_qty_purchased'];
            $available_stock = $rowStockSql['product_qty_purchased']- $rowStockSql['product_qty_sold_pos'] - $rowStockSql['product_qty_sold_from_quote'] + $rowStockSql['sales_return_qty']-$row['damaged_qty'];

			    $rows .= "
				<tr>
					<td>{$row['title']}</td>
                    <td>{$row['carton_no']}</td>
                    <td>{$stock}</td>
                    <td>{$row['damaged_qty']}</td>
                    <td>{$available_stock}</td>
				</tr>
				";

        }

        $text = "
        {$rows}
        ";

        return $text;
    }

}