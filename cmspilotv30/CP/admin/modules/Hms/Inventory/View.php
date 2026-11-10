<?
class CP_Admin_Modules_Hms_Inventory_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $count   = 0;
        $rows    = '';

        //TO CREATE INVENTORY RECORDS FROM PRODUCT RECORD
        $this->getCreateInventoryRecords();
        $stock = '';

        foreach ($dataArray as $row){

            $SQLStockTransfer = "
            SELECT  st.from_location
                    ,st.to_location
                    ,sh.product_id
                    ,SUM(sh.qty) AS Transfer_qty
            FROM stock_transfer st
            LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
            WHERE sh.product_id = {$row['product_id']} AND st.from_location = {$cpSiteIdSession}";

            $resultStockTransfer = $db->sql_query($SQLStockTransfer);
            $rowStockTransfer = $db->sql_fetchrow($resultStockTransfer);


            $SQLStockTransferto = "
            SELECT  st.from_location
                    ,st.to_location
                    ,sh.product_id
                    ,SUM(sh.qty) AS Transfer_qty_to
            FROM stock_transfer st
            LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
            WHERE sh.product_id = {$row['product_id']} AND st.to_location = {$cpSiteIdSession}";

            $resultStockTransferto = $db->sql_query($SQLStockTransferto);
            $rowStockTransferto = $db->sql_fetchrow($resultStockTransferto);

            $SQLOthersite = "
            SELECT
                (SELECT SUM(qty) FROM po_product pp
                 LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                 WHERE pp.product_id = {$row['product_id']} AND po.site_id = {$cpSiteIdSession}) as product_qty_purchased

               ,(SELECT SUM(invItem.qty) FROM invoice_item invItem
                LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                WHERE record_id = {$row['product_id']}
                  AND o.site_id = {$cpSiteIdSession}
                ) as product_qty_sold_from_quote

                ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
                LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                WHERE ini.record_id = {$row['product_id']}
                AND inv.site_id = {$cpSiteIdSession}
                ) as sales_return_qty

                ,(SELECT SUM(pp.damaged_qty) FROM po_product pp
                  LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                  WHERE pp.product_id = {$row['product_id']} AND po.site_id = {$cpSiteIdSession}
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
            AND e.site_id = {$cpSiteIdSession}
            AND ep.stock_deducted = 1
            ";
            $resultExpenseProduct = $db->sql_query($SqlExpenseProduct);
            $rowExpenseProduct    = $db->sql_fetchrow($resultExpenseProduct);

            $stock = $rowothersite['product_qty_purchased'] - $rowothersite['product_qty_sold_from_quote'] + $rowothersite['sales_return_qty'] - $rowothersite['damaged_qty'] - $rowStockTransfer['Transfer_qty'] + $rowStockTransferto['Transfer_qty_to'] - $rowExpenseProduct['qty'];
            $soldQty = $rowothersite['product_qty_sold_from_quote'] - $rowothersite['sales_return_qty'] - $rowothersite['damaged_qty'] + $rowExpenseProduct['qty'];

            /*$SQLUpdate = "
            update inventory set actual_stock = {$stock}
            WHERE inventory_id = {$row['inventory_id']}
            ";
            $result1 = $db->sql_query($SQLUpdate);*/

            $item_code = '';
            if($row['item_code'] != ''){
                $item_code ='PROD - '.$row['item_code'];
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($item_code)}
            {$listObj->getGoToDetailText($count, $row['product_name'])}
            {$listObj->getListDataCell($rowothersite['product_qty_purchased'])}
            {$listObj->getListDataCell($soldQty)}
            {$listObj->getListDataCell($row['actual_stock'.$cpSiteIdSession])}
            {$listObj->getListRowEnd($row['inventory_id'])}
            ";

            $count++ ;
        }

        $text = "
        <div class='floatbox'>
            <div class='float_left'>
                <a class='btn btn-info UpdateInventoryRecords'>Update Inventory</a>
            </div>
        </div>

        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Item Code', 'item_code')}
        {$listObj->getListHeaderCell('Name', 'product_name')}
        {$listObj->getListHeaderCell('Purchased / Stock Transfer Qty', '' )}
        {$listObj->getListHeaderCell('Sold Qty', '' )}
        {$listObj->getListHeaderCell('Available Stock', '' )}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $text = "
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $tv      = Zend_Registry::get('tv');
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $formObj->mode = $tv['action'];
        $expNoEdit  = array('isEditable' => 0);
        $rows ='';
        $sitename ='';
        $totalquantity ='';
        $soldquantity = '';
        $damagedquantity ='';
        $salesreturnqty = '';
        $text1 ='';
        $totalqty = '';

        $StockSql = "
        SELECT
            (SELECT SUM(qty) FROM po_product pp
            LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
            WHERE product_id = {$row['product_id']}) as product_qty_purchased
            
            ,(SELECT SUM(invItem.qty) FROM invoice_item invItem
            LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
            LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
            WHERE record_id = {$row['product_id']}
            ) as product_qty_sold_from_quote
            
            ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
            LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
            LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
            WHERE ini.record_id = {$row['product_id']}
              AND srh.status IS NULL
            ) as sales_return_qty

            ,(SELECT SUM(pp.damaged_qty) FROM po_product pp
              LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
              WHERE pp.product_id = {$row['product_id']} AND po.site_id = {$cpSiteIdSession}
             ) as damaged_qty
        ";
        $resultStockSql = $db->sql_query($StockSql);
        $rowStockSql    = $db->sql_fetchrow($resultStockSql);

        $item_code = '';
        if($row['item_code'] != ''){
            $item_code ='PROD - '.$row['item_code'];
        }

        $cpSiteIdSession  = $fn->getSessionParam('cp_site_id');
        $mol = $row['mol'.$cpSiteIdSession];

        $fieldset1 = "
        {$formObj->getTBRow('Name', 'product_name', $row['product_name'], $expNoEdit)}
        {$formObj->getTBRow('Item Code', 'item_code', $item_code, $expNoEdit)}
        {$formObj->getTBRow('MOL', 'mol'.$cpSiteIdSession, $mol)}
        {$formObj->getTBRow('MOL Type', 'molType', $row['pack_type'], $expNoEdit)}
        ";

        /*Begin Stock Details For The Location */

        $SQLsitedetail="
        SELECT site_id
               ,title
        FROM site
        ";
        $resultsitedetail = $db->sql_query($SQLsitedetail);

        $stockDetailsRow     = '';
        $total_available_qty = '';
        $total_sold_qty      = '';
        $total_damaged_qty   = '';
        $total_sales_qty     = '';
        $total_purchased_qty = '';
        $total_used_qty      = '';
        while($rowsitedetail = $db->sql_fetchrow($resultsitedetail)) {

            $SQLStockTransfer = "
            SELECT  st.from_location
                    ,st.to_location
                    ,sh.product_id
                    ,SUM(sh.qty) AS Transfer_qty
            FROM stock_transfer st
            LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
            WHERE sh.product_id = {$row['product_id']} AND st.from_location = {$rowsitedetail['site_id']}";

            $resultStockTransfer = $db->sql_query($SQLStockTransfer);
            $rowStockTransfer = $db->sql_fetchrow($resultStockTransfer);


            $SQLStockTransferto = "
            SELECT  st.from_location
                    ,st.to_location
                    ,sh.product_id
                    ,SUM(sh.qty) AS Transfer_qty_to
            FROM stock_transfer st
            LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
            WHERE sh.product_id = {$row['product_id']} AND st.to_location = {$rowsitedetail['site_id']}";

            $resultStockTransferto = $db->sql_query($SQLStockTransferto);
            $rowStockTransferto = $db->sql_fetchrow($resultStockTransferto);

            $SQLOthersite = "
            SELECT
                (SELECT SUM(qty) FROM po_product pp
                 LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                 WHERE pp.product_id = {$row['product_id']} AND po.site_id = {$rowsitedetail['site_id']}) as product_qty_purchased

               ,(SELECT SUM(invItem.qty) FROM invoice_item invItem
                LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                WHERE record_id = {$row['product_id']}
                  AND o.site_id = {$rowsitedetail['site_id']}
                ) as product_qty_sold_from_quote

                ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
                LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                WHERE ini.record_id = {$row['product_id']}
                AND inv.site_id = {$rowsitedetail['site_id']}
                ) as sales_return_qty

                ,(SELECT SUM(pp.damaged_qty) FROM po_product pp
                  LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                  WHERE pp.product_id = {$row['product_id']} AND po.site_id = {$rowsitedetail['site_id']}
                 ) as damaged_qty
            ";

            $resultothersite = $db->sql_query($SQLOthersite);

            $SqlExpenseProduct = "
            SELECT SUM(ep.qty) AS qty
            FROM expense_product ep
            LEFT JOIN expense e ON (e.expense_id = ep.expense_id)
            WHERE ep.product_id = {$row['product_id']}
            AND ep.status = 'Added'
            AND e.site_id = {$rowsitedetail['site_id']}
            AND ep.stock_deducted = 1
            ";
            $resultExpenseProduct = $db->sql_query($SqlExpenseProduct);
            $rowExpenseProduct    = $db->sql_fetchrow($resultExpenseProduct);

            while ($rowothersite = $db->sql_fetchrow($resultothersite)){
                    if ($rowsitedetail['site_id'] == $cpSiteIdSession && $rowStockTransfer['from_location'] == $cpSiteIdSession){
                        $totalqty = $rowothersite['product_qty_purchased'] - $rowothersite['product_qty_sold_from_quote'] + $rowothersite['sales_return_qty'] - $rowothersite['damaged_qty'] - $rowStockTransfer['Transfer_qty'] + $rowStockTransferto['Transfer_qty_to'] - $rowExpenseProduct['qty'];
                    }
                    else if ($rowsitedetail['site_id'] != $cpSiteIdSession && $rowStockTransfer['to_location'] == $cpSiteIdSession){
                        $totalqty = $rowothersite['product_qty_purchased']  - $rowothersite['product_qty_sold_from_quote'] + $rowothersite['sales_return_qty'] - $rowothersite['damaged_qty'] - $rowStockTransfer['Transfer_qty'] + $rowStockTransferto['Transfer_qty_to'] - $rowExpenseProduct['qty'];
                    }
                    else {
                       $totalqty = $rowothersite['product_qty_purchased'] - $rowothersite['product_qty_sold_from_quote'] + $rowothersite['sales_return_qty'] - $rowothersite['damaged_qty'] - $rowStockTransfer['Transfer_qty'] + $rowStockTransferto['Transfer_qty_to'] - $rowExpenseProduct['qty'];
                    }

                $soldQty = $rowothersite['product_qty_sold_from_quote'] - $rowothersite['sales_return_qty'] - $rowothersite['damaged_qty'] + $rowExpenseProduct['qty'];

                /*$fieldset2 = "
                {$formObj->getTBRow('Total Purchase Qty', 'total_purchase_qty', $rowStockSql['product_qty_purchased'], $expNoEdit)}
                {$formObj->getTBRow('Total Available Qty', 'total_available_qty', $rowStockSql['product_qty_purchased'] - $rowStockSql['product_qty_sold_pos']- $rowStockSql['product_qty_sold_from_quote'] + $rowStockSql['sales_return_qty'], $expNoEdit)}
                {$formObj->getTBRow('Total Sold Qty', 'total_sold_qty', $rowStockSql['product_qty_sold_pos'] + $rowStockSql['product_qty_sold_from_quote'] - $rowStockSql['sales_return_qty'], $expNoEdit)}
                {$formObj->getTBRow('Total Sales Return Qty', 'total_sales_qty', $rowStockSql['sales_return_qty'] , $expNoEdit)}
                ";*/

                $stockDetailsRow .= "
                    <tr>
                        <td>{$rowsitedetail['title']}</td>
                        <td>{$rowothersite['product_qty_purchased']}</td>
                        <td>{$soldQty}</td>
                        <td>{$rowothersite['damaged_qty']}</td>
                        <td>{$rowothersite['sales_return_qty']}</td>
                        <td>{$rowExpenseProduct['qty']}</td>
                        <td>{$totalqty}</td>
                    </tr>
                ";

                $total_available_qty += $totalqty;
                $total_purchased_qty += $rowothersite['product_qty_purchased'];
                $total_sold_qty      += $soldQty;
                $total_damaged_qty   += $rowothersite['damaged_qty'];
                $total_sales_qty     += $rowothersite['sales_return_qty'];
                $total_used_qty      += $rowExpenseProduct['qty'];

            }

        }

        $stocklist ="
         <table class='thinlist'>
            <thead>
                <tr>
                    <th>Location Name</th>
                    <th>Total Purchased Qty</th>
                    <th>Total Sold Qty</th>
                    <th>Total Damaged Qty</th>
                    <th>Total Sales Return Qty</th>
                    <th>Total Used Qty</th>
                    <th>Total Available Qty</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    {$stockDetailsRow}
                </tr>
                <tr bgcolor='#EEEEEE'>
                    <th class='txtRight'>Total</th>
                    <th>{$total_purchased_qty}</th>
                    <th>{$total_sold_qty}</th>
                    <th>{$total_damaged_qty}</th>
                    <th>{$total_sales_qty}</th>
                    <th>{$total_used_qty}</th>
                    <th>{$total_available_qty}</th>
                </tr>
            </tbody>
        </table>
        {$formObj->getTBRow('', '', '' , $expNoEdit)}
        ";

        $text1="
        {$formObj->getFieldSetWrapped('Stock Details', $stocklist)}
        ";

        /* End Stock Details For The Location */

        $maindetail ="
        {$fieldset1}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Product Details', $maindetail)}
        {$text1}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }
    /**
     *
     */
    function getPrintDetail($row){
        $db = Zend_Registry::get('db');
        return $this->getDetail($row);
    }

    /**
     *
     */
    function getRightPanel($row){
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');

        $text = "
        {$this->getPurchaseOrderDisplay($row)}
        {$this->getOrderDisplay($row)}
        {$this->getStockTransfer($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $text = "
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
           </select>
        </td>
        ";

        return $text;
    }
    /**
     *
     */
    function getCreateInventoryRecords() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');

        $SQLInventory = "
        SELECT p.product_id
              ,p.mol1
              ,p.mol2
              ,p.mol3
              ,p.mol4
              ,p.mol5
        FROM product p
        LEFT JOIN inventory inv ON inv.product_id = p.product_id
        WHERE inv.product_id IS NULL
       ";

        $resultInventory = $db->sql_query($SQLInventory);

        while ($rowInventory = $db->sql_fetchrow($resultInventory)) {

            $fa = array();
            $fa['mol1']           = $rowInventory['mol1'];
            $fa['mol2']           = $rowInventory['mol2'];
            $fa['mol3']           = $rowInventory['mol3'];
            $fa['mol4']           = $rowInventory['mol4'];
            $fa['mol5']           = $rowInventory['mol5'];
            $fa['product_id']     = $rowInventory['product_id'];
            $fa['creation_date']  = date('Y-m-d H:i:s');

            $inventory_id = $fn->addRecord($fa, 'inventory');
        }
    }

    /**
     *
     */
    function getUpdateStockProductsRecords() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpSiteIdSession  = $fn->getSessionParam('cp_site_id');

        $SQLInventory = "
        SELECT p.product_id
              ,p.mol1
              ,p.mol2
              ,p.mol3
              ,p.mol4
              ,p.mol5
        FROM product p
        LEFT JOIN inventory inv ON inv.product_id = p.product_id
        WHERE inv.product_id IS NULL
       ";

        $resultInventory = $db->sql_query($SQLInventory);

        while ($rowInventory = $db->sql_fetchrow($resultInventory)) {

            $fa = array();
            $fa['mol1']           = $rowInventory['mol1'];
            $fa['mol2']           = $rowInventory['mol2'];
            $fa['mol3']           = $rowInventory['mol3'];
            $fa['mol4']           = $rowInventory['mol4'];
            $fa['mol5']           = $rowInventory['mol5'];
            $fa['product_id']     = $rowInventory['product_id'];
            $fa['creation_date']  = date('Y-m-d H:i:s');

            $inventory_id = $fn->addRecord($fa, 'inventory');
        }

        $SQLSite = "
        SELECT site_id
        FROM site
        ";
        $resultSite  = $db->sql_query($SQLSite);
        while ($rowSite = $db->sql_fetchrow($resultSite)) {
            $SQLInventory1 = "
            SELECT product_id
            FROM inventory
            ";
            $resultInventory1  = $db->sql_query($SQLInventory1);
            $numRowsInventory1 = $db->sql_numrows($resultInventory1);

            while ($rowInventory1 = $db->sql_fetchrow($resultInventory1)) {

                $SQLStockTransfer = "
                SELECT  st.from_location
                        ,st.to_location
                        ,sh.product_id
                        ,SUM(sh.qty) AS Transfer_qty
                FROM stock_transfer st
                LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
                WHERE sh.product_id = {$rowInventory1['product_id']} AND st.from_location = {$rowSite['site_id']}";

                $resultStockTransfer = $db->sql_query($SQLStockTransfer);
                $rowStockTransfer = $db->sql_fetchrow($resultStockTransfer);


                $SQLStockTransferto = "
                SELECT  st.from_location
                        ,st.to_location
                        ,sh.product_id
                        ,SUM(sh.qty) AS Transfer_qty_to
                FROM stock_transfer st
                LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
                WHERE sh.product_id = {$rowInventory1['product_id']} AND st.to_location = {$rowSite['site_id']}";

                $resultStockTransferto = $db->sql_query($SQLStockTransferto);
                $rowStockTransferto = $db->sql_fetchrow($resultStockTransferto);

                $SQLOthersite = "
                SELECT
                    (SELECT SUM(qty) FROM po_product pp
                     LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                     WHERE pp.product_id = {$rowInventory1['product_id']} AND po.site_id = {$rowSite['site_id']}) as product_qty_purchased

                   ,(SELECT SUM(invItem.qty) FROM invoice_item invItem
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                    LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                    WHERE record_id = {$rowInventory1['product_id']}
                      AND o.site_id = {$rowSite['site_id']}
                    ) as product_qty_sold_from_quote

                    ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
                    LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                    LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                    WHERE ini.record_id = {$rowInventory1['product_id']}
                    AND inv.site_id = {$rowSite['site_id']}
                    ) as sales_return_qty

                    ,(SELECT SUM(pp.damaged_qty) FROM po_product pp
                      LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                      WHERE pp.product_id = {$rowInventory1['product_id']} AND po.site_id = {$rowSite['site_id']}
                     ) as damaged_qty
                ";
                $resultothersite = $db->sql_query($SQLOthersite);
                $rowothersite = $db->sql_fetchrow($resultothersite);

                $SqlExpenseProduct = "
                SELECT SUM(ep.qty) AS qty
                FROM expense_product ep
                LEFT JOIN expense e ON (e.expense_id = ep.expense_id)
                WHERE ep.product_id = {$rowInventory1['product_id']}
                AND ep.status = 'Added'
                AND e.site_id = {$rowSite['site_id']}
                AND ep.stock_deducted = 1
                ";
                $resultExpenseProduct = $db->sql_query($SqlExpenseProduct);
                $rowExpenseProduct    = $db->sql_fetchrow($resultExpenseProduct);

                $stock = $rowothersite['product_qty_purchased'] - $rowothersite['product_qty_sold_from_quote'] + $rowothersite['sales_return_qty'] - $rowothersite['damaged_qty'] - $rowStockTransfer['Transfer_qty'] + $rowStockTransferto['Transfer_qty_to'] - $rowExpenseProduct['qty'];

                $SQLUpdateProduct = "
                UPDATE product SET qty_in_stock{$rowSite['site_id']} = {$stock}
                WHERE product_id = '{$rowInventory1['product_id']}'
                ";
                $resultUpdateProduct  = $db->sql_query($SQLUpdateProduct);

                $SQLUpdateInventory = "
                UPDATE inventory SET actual_stock{$rowSite['site_id']} = {$stock}
                WHERE product_id = '{$rowInventory1['product_id']}'
                ";
                $resultUpdateInventory  = $db->sql_query($SQLUpdateInventory);
            }
        }
    }

    /**
     */
    function getStockTransfer($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $formAction = '';

        $text = "
        <tr class=''>
        <td>
            <div class='header'>Stock Transfer Linked</div>
            <div id='' class='stockTransferDisplay'>
                <form id='stockTransferPortal' class='' method='post' action='{$formAction}'>
                    <div id='invoicePortalOuter'>
                        {$this->getStockTransferDetail($row)}
                    </div>
                </form>
            </div>
        </td>
        </tr>
        ";

        return $text;
    }

    /**
     *
     */
    function getStockTransferDetail($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpSiteIdSession  = $fn->getSessionParam('cp_site_id');
        
        $rows  = "";

        $SQL ="
        SELECT st.from_location
              ,st.to_location
              ,st.date
              ,sth.qty AS Transfer_qty
              ,st.created_by
              ,st.creation_date
              ,st.modified_by
              ,st.modification_date
        FROM stock_transfer st
        LEFT JOIN stock_transfer_history sth ON (sth.stock_transfer_id = st.stock_transfer_id)
        WHERE sth.product_id = {$row['product_id']}
        AND st.from_location = {$cpSiteIdSession}
        ";

        $result   = $db->sql_query($SQL);
        $serialNo = 1;
        while ($row = $db->sql_fetchrow($result)) {

            $Sqlfrom ="
            select title as from_location
            FROM site  WHERE site_id='{$row['from_location']}'
            ";

            $resultfrom = $db->sql_query($Sqlfrom);
            $from = $db->sql_fetchrow($resultfrom);

            $SqlTo ="
            select title as To_location
            FROM site  WHERE site_id='{$row['to_location']}'
            ";

            $resultTo = $db->sql_query($SqlTo);
            $To = $db->sql_fetchrow($resultTo);

            if($row['modified_by'] != ''){
                $UpdateBy  = $row['modified_by'].' On '.$row['modification_date'];
            }else{
                $UpdateBy  = $row['created_by'].' On '.$row['creation_date'];
            }

            $rows .= "
            <tr>
                <td>{$serialNo}</td>
                <td>{$fn->getCPDate($row['date'], 'd-m-Y')}</td>
                <td>{$from['from_location']}</td>
                <td>{$To['To_location']}</td>
                <td>{$row['Transfer_qty']}</td>
                <td>{$UpdateBy}</td>
            </tr>
            ";
            $serialNo++;
        }

        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>S.No</th>
        <th>Date</th>
        <th>From</th>
        <th>To</th>
        <th>Qty</th>
        <th>Update By</th>
        </tr>
        ";

        $text = "
        <table class='thinlist'>
            {$header}
            {$rows}
        </table>
        ";

        return $text;
    }

    /**
     *
     */
    function getOrderDisplay($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $formAction = '';

        $text = "
        <tr class=''>
        <td>
            <div class='header'>Orders Linked</div>
            <div id='' class='orderDisplay'>
                <form id='orderItemPrint' class='' method='post' action='{$formAction}'>
                    <div id='invoicePortalOuter'>
                        {$this->getOrderDisplayDetail($row)}
                    </div>
                </form>
            </div>
        </td>
        </tr>
        ";

        return $text;
    }

    /**
     *
     */
    function getOrderDisplayDetail($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpSiteIdSession  = $fn->getSessionParam('cp_site_id');
        
        $rows  = "";
        $rowsPvt  = "";
        $links = "";
        $leftJoin  = "";
        $sqlAppend = "";

        $SQL = "
        SELECT DISTINCT o.order_id
              ,oi.order_item_id
              ,oi.item_title
              ,oi.unit_price
              ,oi.qty
              ,oi.qty * oi.unit_price
              ,o.discount as discount_percentage_amount_sum
              ,o.order_date
              ,o.record_type
              ,o.site_id
              ,o.link_stock
              ,com.company_name
              ,st.title as site_title
        FROM `order_item` oi
        LEFT JOIN `order` o ON o.order_id = oi.order_id
        LEFT JOIN `invoice` inv ON o.order_id = inv.order_id
        LEFT JOIN company com ON com.company_id = o.company_id
        LEFT JOIN site st ON st.site_id = o.site_id
        WHERE oi.record_id = {$row['product_id']}
        AND (o.order_status = 'Paid' || o.order_status = 'Due')
        AND (inv.status = 'Paid' || inv.status = 'Due' || inv.status = 'Partial Payment')
        AND o.site_id = {$cpSiteIdSession}
        ORDER BY order_date desc, site_id
        ";

        $result   = $db->sql_query($SQL);
        $client = '';
        while ($rowOI = $db->sql_fetchrow($result)) {
            if($rowOI['record_type'] == 'POS'){
                $client = 'POS';
            }
            else{
                $client = $rowOI['company_name'];
            }

            $sales_return_qty = $this->getSalesReturnQuantity($rowOI['order_item_id']);
            if($sales_return_qty>0){
                $sales_return_qty = -$sales_return_qty;
            }
            $class='';
            if($rowOI['link_stock'] != 1){
                $class = 'style="background-color:#F39EE6;"';
            }

            $rows .= "
            <tr {$class}>
                <td>{$rowOI['order_id']}</td>
                <td>{$rowOI['site_title']}</td>
                <td>{$fn->getCPDate($rowOI['order_date'], 'd-m-Y')}</td>
                <td>{$client}</td>
                <td>{$rowOI['unit_price']}</td>
                <td>{$rowOI['discount_percentage_amount_sum']}</td>
                <td>{$rowOI['qty']}</td>
                <td>{$sales_return_qty}</td>
            </tr>
            ";
        }

        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>Order Id</th>
        <th>Location</th>
        <th>Date</th>
        <th>Client</th>
        <th>Amount</th>
        <th>Discount</th>
        <th>Qty</th>
        <th>Sales Return</th>
        </tr>
        ";

        $text = "
        <table class='thinlist'>
            {$header}
            {$rows}
        </table>
        ";

        return $text;
    }

    /**
     */
    function getPurchaseOrderDisplay($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $formAction = '';

        $text = "
        <tr class=''>
        <td>
            <div class='header'>Purchase Orders Linked</div>
            <div id='' class='poDisplay'>
                <form id='poPrint' class='' method='post' action='{$formAction}'>
                    <div id='invoicePortalOuter'>
                        {$this->getPurchaseOrderDisplayDetail($row)}
                    </div>
                </form>
            </div>
        </td>
        </tr>
        ";

        return $text;
    }

    /**
     */
    function getPurchaseOrderDisplayDetail($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpSiteIdSession  = $fn->getSessionParam('cp_site_id');

        $rows  = "";
        $rowsPvt  = "";
        $links = "";
        $leftJoin  = "";
        $sqlAppend = "";
        $tdForSiteId = "";
        $thForSiteId = "";
        $leftjnAppend = "";

        if($cpCfg['cp.hasMultiUniqueSites']  == true){
            $sqlAppend = ",st.title as site_title";
            $leftjnAppend = "
            LEFT JOIN site st ON st.site_id = po.site_id";
        }

        $SQL = "
        SELECT pop.price
              ,pop.qty
              ,com.company_name AS supplier_name
              ,po.po_code
              ,po.purchase_order_date
              {$sqlAppend}
        FROM po_product pop
        LEFT JOIN purchase_order po ON po.purchase_order_id = pop.purchase_order_id
        LEFT JOIN company com ON pop.supplier_id = com.company_id
        {$leftjnAppend}
        WHERE pop.product_id = {$row['product_id']}
        AND po.site_id = {$cpSiteIdSession}
        ";

        $result   = $db->sql_query($SQL);

        while ($rowPo = $db->sql_fetchrow($result)) {
            if($cpCfg['cp.hasMultiUniqueSites']  == true){
                $tdForSiteId = "<td>{$rowPo['site_title']}</td>";
            }

            $rows .= "
            <tr>
                <td>{$rowPo['po_code']}</td>
                {$tdForSiteId}
                <td>{$fn->getCPDate($rowPo['purchase_order_date'], 'd-m-Y')}</td>
                <td>{$rowPo['price']}</td>
                <td>{$rowPo['qty']}</td>
                <td>{$rowPo['supplier_name']}</td>
            </tr>
            ";
        }

        if($cpCfg['cp.hasMultiUniqueSites']  == true){
            $thForSiteId = "<th>Location</th>";
        }
        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>PO Code</th>
        {$thForSiteId}
        <th>Date</th>
        <th>Amount</th>
        <th>Qty</th>
        <th>Supplier</th>
        </tr>
        ";

        $text = "
        <table class='thinlist'>
            {$header}
            {$rows}
        </table>
        ";

        return $text;
    }

    /**
     *
     */
    function getSalesReturnQuantity($order_item_id){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        //TO FIND SALES RETURN QUANTITY
        $sales_return_qty = '';
        $sqlInvoiceItem = "
        SELECT invItem.* FROM invoice_item invItem
        WHERE invItem.order_item_id = {$order_item_id}
        ";
        $resultInvoiceItem = $db->sql_query($sqlInvoiceItem);
        while ($rowII = $db->sql_fetchrow($resultInvoiceItem)) {
            $sqlQty = "
            SELECT SUM(srh.qty_return) AS sales_return_qty
            FROM sales_return_history srh
            WHERE srh.invoice_id = {$rowII['invoice_id']}
             AND srh.invoice_item_id = {$rowII['invoice_item_id']}
             AND srh.status IS NULL
            ";
            $resultQty = $db->sql_query($sqlQty);
            $rowQty = $db->sql_fetchrow($resultQty);
            $sales_return_qty += $rowQty['sales_return_qty'];
        }

        return $sales_return_qty;
    }
}