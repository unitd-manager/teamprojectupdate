<?
class CP_Admin_Widgets_Tradingsg_StockReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

	// **** THIS CONDITION HAS BEEN USED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\
		$siteLocation = '' ;
		if($cpCfg['cp.hasMultiUniqueSites']){
			$siteLocation = "
			<th>Location</th>
			";
		}

        $text = "
        <h2>Stock</h2>
		<div class = 'tableOuter scroll-pane'>
		<table class='thinlist'>
			<thead>
				<tr>
					<th>Product Name</th>
                    <th>Item Code</th>
                    <th>Model</th>
                    <th>Carton Number</th>
                    <th>Total Stock</th>
                    <th class='txtRight'>Cost Price/Qty</th>
                    <th class='txtRight'>Total Cost</th>
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
        $cpCfg 	= Zend_Registry::get('cpCfg');

        $rows = '';
		$siteTitle = '' ;
        $count = 1 ;
        $sum_purchase_cp_per_qty = '';
        $linkToStock = '' ;

        if($cpCfg['cp.excludeStock'] == 1){
            $linkToStock = "AND o.link_stock = 1";
        }

        foreach($this->model->dataArray as $row){

            $StockSql = "
            SELECT
                (SELECT SUM(qty) FROM po_product
                WHERE product_id = {$row['product_id']}) as product_qty_purchased
                ,(SELECT SUM(fc_price*xrate) FROM po_product
                WHERE product_id = {$row['product_id']}) as purchase_cp_per_qty
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
                ,(SELECT po.damaged_qty FROM product po
                WHERE po.product_id = {$row['product_id']}
                ) as damaged_qty
            ";

            $resultStockSql = $db->sql_query($StockSql);
            $rowStockSql    = $db->sql_fetchrow($resultStockSql);

            $stock = $rowStockSql['product_qty_purchased']- $rowStockSql['product_qty_sold_pos'] - $rowStockSql['product_qty_sold_from_quote'] + $rowStockSql['sales_return_qty'] - $rowStockSql['damaged_qty'];
            $sum_purchase_cp_per_qty = $stock * $rowStockSql['purchase_cp_per_qty'];

			// **** THIS CONDITION HAS BEEN ADDED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\

			if($cpCfg['cp.hasMultiUniqueSites'] == 1){
			    $siteRec = $fn->getRecordRowById('site', 'site_id', $row['site_id']);

				$siteTitle = "
				<td>{$siteRec['title']}</td>
				";
			}
            $rowStockSql['purchase_cp_per_qty'] = number_format($rowStockSql['purchase_cp_per_qty']);

            if($sum_purchase_cp_per_qty){
                $sum_purchase_cp_per_qty = number_format($sum_purchase_cp_per_qty);
            }

		    $rows .= "
			<tr>
				<td>{$row['product_title']}</td>
                <td>{$row['item_code']}</td>
                <td>{$row['model']}</td>
                <td>{$row['carton_no']}</td>
                <td>{$stock}</td>
                <td class='txtRight'>{$rowStockSql['purchase_cp_per_qty']}</td>
                <td class='txtRight'>{$sum_purchase_cp_per_qty}</td>
			</tr>
			";
        }

        $text = "
        {$rows}
        ";

        return $text;
    }

}