<?
class CP_Admin_Widgets_Hms_StockReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');

	// **** THIS CONDITION HAS BEEN USED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\
		$siteLocation = '' ;
		if($cpCfg['cp.hasMultiUniqueSites']){
			$siteLocation = "
			<th>Location</th>
			";
		}

        $pdf = '';
        $exportStocksToPdfLink = "index.php?_topRm=reports&module=hms_reports&_spAction=exportStocksToPdf&start_date={$start_date}&end_date={$end_date}&month={$monthVal}&year={$yearVal}&showHTML=0";
        $pdf = "<a href='{$exportStocksToPdfLink}' target='blank' class='exportPdfLink button'>
                    <u1>Export to Pdf</u1>
                </a>";

        $text = "
        {$pdf}
        <h2>Stock</h2>
		<div class = 'tableOuter scroll-pane'>
    		<table class='thinlist'>
    			<thead>
    				<tr>
                        <th>Item Code</th>
                        <th>product Name</th>
                        <th class='txtRight'>Purchased / Stock Transfer Qty</th>
                        <th class='txtRight'>Sold Qty</th>
                        <th>Available Stock</th>
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
        $count = 1 ;
        $appendSql = '';
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');
        $current_date   = date('Y-m-d');
        $month          = date('m');
        $year           = date('Y');


        $location_id    = $fn->getReqParam('location_id');
        if ($location_id != '') {
            $appendSql = "WHERE site_id = {$location_id}";
        }

        foreach($this->model->dataArray as $row){
            $stock = 0;
            $purchased_qty = 0;
            $sold_qty = 0;

            $SQLSite = "
            SELECT site_id
            FROM site
            {$appendSql}
            ";
            $resultSite     = $db->sql_query($SQLSite);

            $appendSqlStockTransfer = '';
            $appendSqlExpense = '';
            $appendSqlInvoice = '';
            $appendSqlPoOrder = '';

            $appendSqlStockTransferCurrent = '';
            $appendSqlExpenseCurrent = '';
            $appendSqlInvoiceCurrent = '';
            $appendSqlPoOrderCurrent = '';

            while ($rowSite = $db->sql_fetchrow($resultSite)) {

                if ($start_date != '' && $end_date == '') {
                    $appendSqlStockTransfer = "AND st.date >= '{$start_date}' AND st.date <= '{$current_date}'";
                    $appendSqlExpense = "AND e.creation_date >= '{$start_date}' AND e.creation_date <= '{$current_date}'";
                    $appendSqlInvoice = "AND inv.invoice_date >= '{$start_date}' AND inv.invoice_date <= '{$current_date}'";
                    $appendSqlPoOrder = "AND po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$current_date}'";

                    $appendSqlStockTransferCurrent = "AND st.date >= '{$start_date}' AND st.date <= '{$current_date}'";
                    $appendSqlExpenseCurrent = "AND e.creation_date >= '{$start_date}' AND e.creation_date <= '{$current_date}'";
                    $appendSqlInvoiceCurrent = "AND inv.invoice_date >= '{$start_date}' AND inv.invoice_date <= '{$current_date}'";
                    $appendSqlPoOrderCurrent = "AND po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$current_date}'";
                } else if ($start_date == '' && $end_date != ''){
                    $month = date('m', strtotime(date('Y-m')." -3 month"));
                    $start_date = $year . '-' . $month . '-' . '01';
                    $appendSqlStockTransfer = "AND st.date >= '{$start_date}' AND st.date <= '{$end_date}'";
                    $appendSqlExpense = "AND e.creation_date >= '{$start_date}' AND e.creation_date <= '{$end_date}'";
                    $appendSqlInvoice = "AND inv.invoice_date >= '{$start_date}' AND inv.invoice_date <= '{$end_date}'";
                    $appendSqlPoOrder = "AND po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$end_date}'";

                    $appendSqlStockTransferCurrent = "AND st.date >= '{$start_date}' AND st.date <= '{$end_date}'";
                    $appendSqlExpenseCurrent = "AND e.creation_date >= '{$start_date}' AND e.creation_date <= '{$end_date}'";
                    $appendSqlInvoiceCurrent = "AND inv.invoice_date >= '{$start_date}' AND inv.invoice_date <= '{$end_date}'";
                    $appendSqlPoOrderCurrent = "AND po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$end_date}'";


                } else if ($start_date != '' && $end_date != '') {
                    $appendSqlStockTransfer = "AND st.date >= '{$start_date}' AND st.date <= '{$end_date}'";
                    $appendSqlExpense = "AND e.creation_date >= '{$start_date}' AND e.creation_date <= '{$end_date}'";
                    $appendSqlInvoice = "AND inv.invoice_date >= '{$start_date}' AND inv.invoice_date <= '{$end_date}'";
                    $appendSqlPoOrder = "AND po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$end_date}'";

                    $appendSqlStockTransferCurrent = "AND st.date >= '{$start_date}' AND st.date <= '{$end_date}'";
                    $appendSqlExpenseCurrent = "AND e.creation_date >= '{$start_date}' AND e.creation_date <= '{$end_date}'";
                    $appendSqlInvoiceCurrent = "AND inv.invoice_date >= '{$start_date}' AND inv.invoice_date <= '{$end_date}'";
                    $appendSqlPoOrderCurrent = "AND po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$end_date}'";

                } else if ($monthVal == '' && $yearVal == ''){
                    $start_date = $year . '-' . $month . '-' . '01';
                    $end_date   = $year . '-' . $month . '-' . '31';
                    $appendSqlStockTransfer = "AND st.date >= '{$start_date}' AND st.date <= '{$end_date}'";
                    $appendSqlExpense = "AND e.creation_date >= '{$start_date}' AND e.creation_date <= '{$end_date}'";
                    $appendSqlInvoice = "AND inv.invoice_date >= '{$start_date}' AND inv.invoice_date <= '{$end_date}'";
                    $appendSqlPoOrder = "AND po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$end_date}'";

                    $appendSqlStockTransferCurrent = "AND st.date >= '{$start_date}' AND st.date <= '{$end_date}'";
                    $appendSqlExpenseCurrent = "AND e.creation_date >= '{$start_date}' AND e.creation_date <= '{$end_date}'";
                    $appendSqlInvoiceCurrent = "AND inv.invoice_date >= '{$start_date}' AND inv.invoice_date <= '{$end_date}'";
                    $appendSqlPoOrderCurrent = "AND po.purchase_order_date >= '{$start_date}' AND po.purchase_order_date <= '{$end_date}'";
                }

                if ($monthVal != '') {
                    $appendSqlStockTransfer .= "AND DATE_FORMAT(st.date, '%m') = '{$monthVal}'" ;
                    $appendSqlExpense .= "AND DATE_FORMAT(e.creation_date, '%m') = '{$monthVal}'";
                    $appendSqlInvoice .= "AND DATE_FORMAT(inv.invoice_date, '%m') = '{$monthVal}'";
                    $appendSqlPoOrder .= "AND DATE_FORMAT(po.purchase_order_date, '%m') = '{$monthVal}'";

                    $appendSqlStockTransferCurrent .= "AND DATE_FORMAT(st.date, '%m') <= '{$monthVal}'";
                    $appendSqlExpenseCurrent .= "AND DATE_FORMAT(e.creation_date, '%m') <= '{$monthVal}'";
                    $appendSqlInvoiceCurrent .= "AND DATE_FORMAT(inv.invoice_date, '%m') <= '{$monthVal}'";
                    $appendSqlPoOrderCurrent .= "AND DATE_FORMAT(po.purchase_order_date, '%m') <= '{$monthVal}'";
                }

                if ($yearVal != '') {
                    $appendSqlStockTransfer .= "AND DATE_FORMAT(st.date, '%Y') = '{$yearVal}'" ;
                    $appendSqlExpense .= "AND DATE_FORMAT(e.creation_date, '%Y') = '{$yearVal}'";
                    $appendSqlInvoice .= "AND DATE_FORMAT(inv.invoice_date, '%Y') = '{$yearVal}'";
                    $appendSqlPoOrder .= "AND DATE_FORMAT(po.purchase_order_date, '%Y') = '{$yearVal}'";

                    $appendSqlStockTransferCurrent .= "AND DATE_FORMAT(st.date, '%Y') = '{$yearVal}'" ;
                    $appendSqlExpenseCurrent .= "AND DATE_FORMAT(e.creation_date, '%Y') = '{$yearVal}'";
                    $appendSqlInvoiceCurrent .= "AND DATE_FORMAT(inv.invoice_date, '%Y') = '{$yearVal}'";
                    $appendSqlPoOrderCurrent .= "AND DATE_FORMAT(po.purchase_order_date, '%Y') = '{$yearVal}'";
                }

                $SQLStockTransfer = "
                SELECT  st.from_location
                        ,st.to_location
                        ,sh.product_id
                        ,SUM(sh.qty) AS Transfer_qty
                FROM stock_transfer st
                LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
                WHERE sh.product_id = {$row['product_id']} AND st.from_location = {$rowSite['site_id']}
                {$appendSqlStockTransfer}
                ";

                $resultStockTransfer = $db->sql_query($SQLStockTransfer);
                $rowStockTransfer = $db->sql_fetchrow($resultStockTransfer);


                $SQLStockTransferto = "
                SELECT  st.from_location
                        ,st.to_location
                        ,sh.product_id
                        ,SUM(sh.qty) AS Transfer_qty_to
                FROM stock_transfer st
                LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
                WHERE sh.product_id = {$row['product_id']} AND st.to_location = {$rowSite['site_id']}
                {$appendSqlStockTransfer}
                ";

                $resultStockTransferto = $db->sql_query($SQLStockTransferto);
                $rowStockTransferto = $db->sql_fetchrow($resultStockTransferto);

                $SQLOthersite = "
                SELECT
                    (SELECT SUM(qty) FROM po_product pp
                     LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                     WHERE pp.product_id = {$row['product_id']} AND po.site_id = {$rowSite['site_id']}
                     {$appendSqlPoOrder}
                     ) as product_qty_purchased

                   ,(SELECT SUM(invItem.qty) FROM invoice_item invItem
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                    LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                    WHERE record_id = {$row['product_id']}
                      AND o.site_id = {$rowSite['site_id']}
                      {$appendSqlInvoice}
                    ) as product_qty_sold_from_quote

                    ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
                    LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                    WHERE ini.record_id = {$row['product_id']}
                      AND inv.site_id = {$rowSite['site_id']}
                    {$appendSqlInvoice}
                    ) as sales_return_qty

                    ,(SELECT SUM(pp.damaged_qty) FROM po_product pp
                      LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                      WHERE pp.product_id = {$row['product_id']} AND po.site_id = {$rowSite['site_id']}
                     ) as damaged_qty
                ";
                $resultothersite = $db->sql_query($SQLOthersite);
                $rowothersite = $db->sql_fetchrow($resultothersite);

                $SqlExpenseProduct = "
                SELECT SUM(ep.qty) AS qty
                FROM expense_product ep
                LEFT JOIN expense e ON (e.expense_id = ep.expense_id)
                WHERE ep.product_id = {$row['product_id']}
                AND ep.status = 'Added'
                AND e.site_id = {$rowSite['site_id']}
                AND ep.stock_deducted = 1
                {$appendSqlExpense}
                ";
                $resultExpenseProduct = $db->sql_query($SqlExpenseProduct);
                $rowExpenseProduct    = $db->sql_fetchrow($resultExpenseProduct);

                /*** The following SQL for Current Stock ***/
                    $SQLStockTransferCurrent = "
                    SELECT  st.from_location
                            ,st.to_location
                            ,sh.product_id
                            ,SUM(sh.qty) AS Transfer_qty
                    FROM stock_transfer st
                    LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
                    WHERE sh.product_id = {$row['product_id']} AND st.from_location = {$rowSite['site_id']}
                    {$appendSqlStockTransferCurrent}
                    ";

                    $resultStockTransferCurrent = $db->sql_query($SQLStockTransferCurrent);
                    $rowStockTransferCurrent = $db->sql_fetchrow($resultStockTransferCurrent);


                    $SQLStockTransfertoCurrent = "
                    SELECT  st.from_location
                            ,st.to_location
                            ,sh.product_id
                            ,SUM(sh.qty) AS Transfer_qty_to
                    FROM stock_transfer st
                    LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
                    WHERE sh.product_id = {$row['product_id']} AND st.to_location = {$rowSite['site_id']}
                    {$appendSqlStockTransferCurrent}
                    ";

                    $resultStockTransfertoCurrent = $db->sql_query($SQLStockTransfertoCurrent);
                    $rowStockTransfertoCurrent = $db->sql_fetchrow($resultStockTransfertoCurrent);

                    $SQLOthersiteCurrent = "
                    SELECT
                        (SELECT SUM(qty) FROM po_product pp
                         LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                         WHERE pp.product_id = {$row['product_id']} AND po.site_id = {$rowSite['site_id']}
                         {$appendSqlPoOrderCurrent}
                         ) as product_qty_purchased

                       ,(SELECT SUM(invItem.qty) FROM invoice_item invItem
                        LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                        LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                        WHERE record_id = {$row['product_id']}
                          AND o.site_id = {$rowSite['site_id']}
                          {$appendSqlInvoiceCurrent}
                        ) as product_qty_sold_from_quote

                        ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
                        LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                        LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                        WHERE ini.record_id = {$row['product_id']}
                          AND inv.site_id = {$rowSite['site_id']}
                        {$appendSqlInvoiceCurrent}
                        ) as sales_return_qty

                        ,(SELECT SUM(pp.damaged_qty) FROM po_product pp
                          LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                          WHERE pp.product_id = {$row['product_id']} AND po.site_id = {$rowSite['site_id']}
                         ) as damaged_qty
                    ";
                    $resultothersiteCurrent = $db->sql_query($SQLOthersiteCurrent);
                    $rowothersiteCurrent = $db->sql_fetchrow($resultothersiteCurrent);

                    $SqlExpenseProductCurrent = "
                    SELECT SUM(ep.qty) AS qty
                    FROM expense_product ep
                    LEFT JOIN expense e ON (e.expense_id = ep.expense_id)
                    WHERE ep.product_id = {$row['product_id']}
                    AND ep.status = 'Added'
                    AND e.site_id = {$rowSite['site_id']}
                    AND ep.stock_deducted = 1
                    {$appendSqlExpenseCurrent}
                    ";
                    $resultExpenseProductCurrent = $db->sql_query($SqlExpenseProductCurrent);
                    $rowExpenseProductCurrent    = $db->sql_fetchrow($resultExpenseProductCurrent);
                /*** Ends Here ***/

                $stock += $rowothersiteCurrent['product_qty_purchased'] - $rowothersiteCurrent['product_qty_sold_from_quote'] + $rowothersiteCurrent['sales_return_qty'] - $rowothersiteCurrent['damaged_qty'] - $rowStockTransferCurrent['Transfer_qty'] + $rowStockTransfertoCurrent['Transfer_qty_to'] - $rowExpenseProductCurrent['qty'];
                $purchased_qty += $rowothersite['product_qty_purchased'] - $rowStockTransfer['Transfer_qty'] + $rowStockTransferto['Transfer_qty_to'];
                $sold_qty += $rowothersite['product_qty_sold_from_quote'] + $rowExpenseProduct['qty'];
            }

            $item_code = '';
            if($row['item_code'] != ''){
                $item_code ='PROD - '.$row['item_code'];
            }

		    $rows .= "
			<tr>
                <td>{$item_code}</td>
                <td>{$row['product_name']}</td>
                <td class='txtRight'>{$purchased_qty}</td>
                <td class='txtRight'>{$sold_qty}</td>
                <td>{$stock}</td>
			</tr>
			";
        }

        $text = "
        {$rows}
        ";

        return $text;
    }

}