<?
class CP_Admin_Modules_Tradingsg_StockTransfer_View extends CP_Common_Lib_ModuleViewAbstract
{
   function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $text = '';
        $rows = '';
        $readonly = '';
        $OrderItems = '';

        $rowCounter = 0;

        $SQLdeleteHistory ="
        DELETE FROM stock_transfer_history
        WHERE stock_transfer_id NOT IN (SELECT stock_transfer_id FROM stock_transfer)
        ";
        $resultdelhis = $db->sql_query($SQLdeleteHistory);
        $deletehistory = $db->sql_fetchrow($resultdelhis);

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $Sqlfrom ="
            select title as from_location
            FROM site  WHERE site_id='{$row['from_location']}'
            ";

            $resultfrom = $db->sql_query($Sqlfrom);
            $from = $db->sql_fetchrow($resultfrom);

            $stock_transfer_date = $fn->getCPDate($row['date'],"d-m-Y");

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $stock_transfer_date)}
            {$listObj->getListDataCell($from['from_location'])}
            {$listObj->getListDataCell($row['location_name'])}
            {$listObj->getListDataCell($row['stock_transfer_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Date', 'date')}
        {$listObj->getListHeaderCell('From Location', 'location_name')}
        {$listObj->getListHeaderCell('To Location', 'from_location')}
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
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $expNoEdit  = array('isEditable' => 0);

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $siteRec = $fn->getRecordRowByID('site', 'site_id', $cpSiteIdSession);


        $sqlstocktrans = "
        SELECT site_id, title 
        FROM site 
        WHERE site_id != {$cpSiteIdSession}
        ";
        $resulttrans = $db->sql_query($sqlstocktrans);
        $row1 = $db->sql_fetchrow($resulttrans);

        $fieldset = "
        {$formObj->getTBRow('From Location', 'from_location', $siteRec['title'], $expNoEdit)}
        {$formObj->getTBRow('', '','', $expNoEdit)}
        {$formObj->getDDRowBySQL('To Location', 'to_location', $sqlstocktrans, $row1['site_id'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Select Site', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $current_date = date('Y-m-d');
        $text = '';
        $rows = '';

        //if($tv['newRecord'] == 1){
          //  $stock_transfer_id = $fn->getReqParam('record_id');
       // }
        //else{
            $stock_transfer_id   = $row['stock_transfer_id'];

            $stock_transfer_date = $fn->getCPDate($row['date'],"d-m-Y");
       // }

        //print_r($row);
        //print($row['stock_transfer_id']);
        $OrderItems = $this->getOrderItems($stock_transfer_id);

        $siteRec = $fn->getRecordRowByID('site', 'site_id', $row['from_location']);

        $urlExportAsPdf = "index.php?module=tradingsg_stockTransfer&_spAction=printExportAsPdf&id={$row['stock_transfer_id']}&showHTML=0";

        $text = "
        <table class='list thinlist topTable'>
            <tr>
                <th>
                    <div class='locationTitle'><label>From Location :</label>{$siteRec['title']}
                    </div>
                </th>
                <th>
                    <div class='locationTitle'><label>To Location :</label>{$row['location_name']}
                    </div>
                </th>
                <th>
                    <div class='locationTitle'><label>Date : </label>{$stock_transfer_date}
                    </div>
                </th>
            </tr>
            <tr>
                <th>
                    <div class='locationTitle'><label>Created By : </label>{$row['created_by']} {$row['creation_date']}
                    </div>
                </th>
                <th>
                    <div class='locationTitle'><label>Modified By : </label>{$row['modified_by']} {$row['modification_date']}
                    </div>
                </th>
            </tr>
        </table>
        <div class='addProduct'>
            Search by Product / Carton No: <input type='text' value='' id='fld_product_title' class='text' name='product_title' stock_transfer_id={$row['stock_transfer_id']}>
        </div>
        <div class='float_right button mt15'>
            <a href='{$urlExportAsPdf}' target = 'blank' id='exportasPdfStockTransfer'>Export as PDF</a>
        </div>
        <table class='list thinlist'>
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Product Name</th>
                    <th>Carton No</th>
                    <th>From Location Qty</th>
                    <th>Transfer Qty</th>
                    <th>To Location Qty</th>
                    <th>Created By</th>
                    <th>Modified By</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody id='orderItems'>
                {$OrderItems}
            </tbody>
        </table>
        ";

        return $text;
    }

    /**
     *
     */
    function getOrderItemsOld($stock_transfer_id=''){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        
        $text = '';
        $rows = '';

        if ($stock_transfer_id == ''){
            $stock_transfer_id = $fn->getReqParam('stock_transfer_id');

        }


        /*if($tv['newRecord'] == 1){
           $stock_transfer_id = $fn->getReqParam('record_id');
        }*/


            /*$Stocksiteqty = "
            SELECT p.title
                  ,p.part_number
                  ,sh.qty 
                  ,po.qty AS stockSite
                  ,sh.stock_transfer_history_id
                  ,sh.created_by
                  ,sh.modified_by
                  ,sh.creation_date
                  ,sh.modification_date
                  ,st.stock_transfer_id 
            FROM `product` p
            LEFT JOIN stock_transfer_history sh ON (sh.product_id = p.product_id)
            LEFT JOIN stock_transfer st ON (st.stock_transfer_id=sh.stock_transfer_id)
            LEFT JOIN po_product po ON (po.product_id=sh.product_id)
            LEFT JOIN purchase_order pr ON (pr.purchase_order_id = po.purchase_order_id)
            WHERE p.published='1' 
            AND p.product_id= sh.product_id 
            AND sh.stock_transfer_id = {$stock_transfer_id} 
            AND pr.site_id = {$cpSiteIdSession}
            AND st.from_location = {$cpSiteIdSession}        
            ";
            $resultStocksiteqty = $db->sql_query($Stocksiteqty);
            $rowsiteqty = $db->sql_fetchrow($resultStocksiteqty);

            if($rowsiteqty['stockSite']== '')
            {
                $stockSite = 0;
            }
            else{
                $stockSite = $rowsiteqty['stockSite'];
            }*/

            $StockSql = "
            SELECT p.title
                  ,p.carton_no
                  ,sh.qty
                  ,po.qty AS stock
                  ,sh.stock_transfer_history_id
                  ,sh.created_by
                  ,sh.product_id
                  ,sh.modified_by
                  ,sh.creation_date
                  ,sh.modification_date
                  ,st.stock_transfer_id 
                  ,st.from_location
                  ,st.to_location
            FROM `product` p
            LEFT JOIN stock_transfer_history sh ON (sh.product_id = p.product_id)
            LEFT JOIN stock_transfer st ON (st.stock_transfer_id=sh.stock_transfer_id)
            LEFT JOIN po_product po ON (po.product_id=sh.product_id)
            where p.published='1' 
            AND p.product_id= sh.product_id 
            AND sh.stock_transfer_id = {$stock_transfer_id}         
            ";
            $resultStockSql = $db->sql_query($StockSql);
            $rowCounter = 1;
            while ($rowz = $db->sql_fetchrow($resultStockSql)) {

            $SQLsitedetail="
            SELECT site_id
               ,title 
            FROM site
            ";
            $resultsitedetail = $db->sql_query($SQLsitedetail);
            $rowsitedetail = $db->sql_fetchrow($resultsitedetail);

            Print $Stocksiteqty = "
                SELECT p.title
                  ,p.part_number
                  ,sh.qty 
                  ,po.qty AS stockSite
                  ,sh.stock_transfer_history_id
                  ,sh.created_by
                  ,sh.modified_by
                  ,sh.creation_date
                  ,sh.modification_date
                  ,st.stock_transfer_id

                  ,(SELECT SUM(qty) FROM po_product pp
                 LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                 WHERE pp.product_id = {$rowz['product_id']} AND po.site_id = {$cpSiteIdSession}) as product_qty_purchased
                 
                ,(SELECT SUM(oi.qty) FROM order_item oi
                LEFT JOIN (`order` o) ON (o.order_id = oi.order_id)
                WHERE record_id = {$rowz['product_id']}
                  AND o.order_status = 'Paid'
                  AND o.record_type = 'POS'
                  AND o.site_id = {$cpSiteIdSession}
                ) as product_qty_sold_pos

               ,(SELECT SUM(invItem.qty) FROM invoice_item invItem             
                LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                WHERE record_id = {$rowz['product_id']}
                  AND o.record_type != 'POS'
                  AND o.link_stock = 1
                  AND o.site_id = {$cpSiteIdSession}
                ) as product_qty_sold_from_quote
                
                ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
                LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                WHERE ini.record_id = {$rowz['product_id']}
                AND inv.site_id = {$cpSiteIdSession}
                ) as sales_return_qty

                ,(SELECT po.damaged_qty FROM product po
                WHERE po.product_id = {$rowz['product_id']}
                ) as damaged_qty 

            FROM `product` p
            LEFT JOIN stock_transfer_history sh ON (sh.product_id = p.product_id)
            LEFT JOIN stock_transfer st ON (st.stock_transfer_id=sh.stock_transfer_id)
            LEFT JOIN po_product po ON (po.product_id=sh.product_id)
            LEFT JOIN purchase_order pr ON (pr.purchase_order_id = po.purchase_order_id)
            WHERE p.published='1' 
            AND p.product_id= {$rowz['product_id']} 
            AND sh.stock_transfer_id = {$stock_transfer_id} 
            AND pr.site_id = {$cpSiteIdSession}
            AND st.from_location = {$cpSiteIdSession}        
            ";
            $resultStocksiteqty = $db->sql_query($Stocksiteqty);
            $rowsiteqty = $db->sql_fetchrow($resultStocksiteqty);

            $SQLStockTransfer = "
            SELECT  st.from_location 
                    ,st.to_location
                    ,sh.product_id
                    ,SUM(sh.qty) AS Transfer_qty
            FROM stock_transfer st
            LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
            WHERE sh.product_id = {$rowz['product_id']} 
            AND st.from_location = {$rowz['from_location']}
            AND st.stock_transfer_id = {$stock_transfer_id}";

            $resultStockTransfer = $db->sql_query($SQLStockTransfer);
            $rowStockTransfer = $db->sql_fetchrow($resultStockTransfer);

            $SQLStockTransferto = "
            SELECT  st.from_location
                    ,st.to_location
                    ,sh.product_id
                    ,SUM(sh.qty) AS Transfer_qty_to
            FROM stock_transfer st
            LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
            WHERE sh.product_id = {$rowz['product_id']} 
            AND st.to_location = {$rowz['to_location']}
            AND st.stock_transfer_id = {$stock_transfer_id}";

            $resultStockTransferto = $db->sql_query($SQLStockTransferto);
            $rowStockTransferto = $db->sql_fetchrow($resultStockTransferto);

            $soldQty = $rowsiteqty['product_qty_sold_pos'] + $rowsiteqty['product_qty_sold_from_quote'] - $rowsiteqty['sales_return_qty'];

            if ($rowsitedetail['site_id'] == $cpSiteIdSession && $rowStockTransfer['from_location'] == $cpSiteIdSession){
                $totalqty = $rowsiteqty['product_qty_purchased'] - $rowsiteqty['product_qty_sold_pos'] - $rowsiteqty['product_qty_sold_from_quote'] - $rowsiteqty['sales_return_qty'] -$rowsiteqty['damaged_qty']- $rowStockTransfer['Transfer_qty'] + $rowStockTransferto['Transfer_qty_to'];
            }
            else if ($rowsitedetail['site_id'] != $cpSiteIdSession && $rowStockTransfer['to_location'] == $cpSiteIdSession){
                $totalqty = $rowsiteqty['product_qty_purchased'] - $rowsiteqty['product_qty_sold_pos'] - $rowsiteqty['product_qty_sold_from_quote'] - $rowsiteqty['sales_return_qty'] -$rowsiteqty['damaged_qty']- $rowStockTransfer['Transfer_qty'] + $rowStockTransferto['Transfer_qty_to'];
            }
            else {
                $totalqty = $rowsiteqty['product_qty_purchased'] - $rowsiteqty['product_qty_sold_pos'] - $rowsiteqty['product_qty_sold_from_quote'] - $rowsiteqty['sales_return_qty'] -$rowsiteqty['damaged_qty']- $rowStockTransfer['Transfer_qty'] + $rowStockTransferto['Transfer_qty_to']; 
            }
            
            $rows .= "
            <tr>
            <td>{$rowCounter}</td>
            <td class='w25p'>{$rowz['title']}</td>
            <td>{$rowz['carton_no']}</td>
            <td>{$totalqty}</td>
            <td class='w100'><input type='text' value='{$rowz['qty']}' id='fld_qty' class='text w100' name='qty' stock_transfer_history_id='{$rowz['stock_transfer_history_id']}' stock_transfer_id= '{$rowz['stock_transfer_id']}' stock='{$totalqty}'></td>
            <td>{$rowsiteqty['qty']}</td>
            <td>{$rowz['created_by']}  {$rowz['creation_date']}</td>
            <td>{$rowz['modified_by']}  {$rowz['modification_date']} </td>
            <td><a href='#' class='deleteItem' stock_transfer_history_id='{$rowz['stock_transfer_history_id']}' stock_transfer_id= '{$rowz['stock_transfer_id']}'>Delete</a></td>
            </tr>
            ";
            $rowCounter++ ;
        }
        $text = "
        {$rows}
        ";
        return $text;
        
    }

    /**
     *totalqty
     */
    function getOrderItems($stock_transfer_id=''){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        
        $text = '';
        $rows = '';
        $totalquantity = '';

        if ($stock_transfer_id == ''){
            $stock_transfer_id = $fn->getReqParam('stock_transfer_id');

        }
            $StockSql = "
            SELECT p.title
                  ,p.carton_no
                  ,sh.qty
                  ,po.qty AS stock
                  ,sh.stock_transfer_history_id
                  ,sh.created_by
                  ,sh.product_id
                  ,sh.modified_by
                  ,sh.creation_date
                  ,sh.modification_date
                  ,st.stock_transfer_id 
                  ,st.from_location
                  ,st.to_location
            FROM `product` p
            LEFT JOIN stock_transfer_history sh ON (sh.product_id = p.product_id)
            LEFT JOIN stock_transfer st ON (st.stock_transfer_id=sh.stock_transfer_id)
            LEFT JOIN po_product po ON (po.product_id=sh.product_id)
            where p.published='1' 
            AND p.product_id= sh.product_id 
            AND sh.stock_transfer_id = {$stock_transfer_id}         
            ";
            $resultStockSql = $db->sql_query($StockSql);
            $rowCounter = 1;
            while ($rowz = $db->sql_fetchrow($resultStockSql)) {


            if ($rowz['from_location'] != ''){

            $SQLsitedetail="
            SELECT site_id
               ,title 
            FROM site WHERE site_id = {$rowz['from_location']}
            ";
            $resultsitedetail = $db->sql_query($SQLsitedetail);

            }
            if ($rowz['to_location'] != ''){

                $SQLsitedetailto="
                SELECT site_id
                   ,title 
                FROM site WHERE site_id = {$rowz['to_location']}
                ";
                $resultsitedetailto = $db->sql_query($SQLsitedetailto);

            }
            
            while ($rowsitedetail = $db->sql_fetchrow($resultsitedetail)){


            $SQLStockTransfer = "
            SELECT  st.from_location 
                    ,st.to_location
                    ,sh.product_id
                    ,SUM(sh.qty) AS Transfer_qty
            FROM stock_transfer st
            LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
            WHERE sh.product_id = {$rowz['product_id']} AND st.from_location = {$rowsitedetail['site_id']}";

            $resultStockTransfer = $db->sql_query($SQLStockTransfer);
            $rowStockTransfer = $db->sql_fetchrow($resultStockTransfer);


            $SQLStockTransferto = "
            SELECT  st.from_location
                    ,st.to_location
                    ,sh.product_id
                    ,SUM(sh.qty) AS Transfer_qty_to
            FROM stock_transfer st
            LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
            WHERE sh.product_id = {$rowz['product_id']} AND st.to_location = {$rowsitedetail['site_id']}";

            $resultStockTransferto = $db->sql_query($SQLStockTransferto);
            $rowStockTransferto = $db->sql_fetchrow($resultStockTransferto);
            $SQLOthersite = "
            SELECT
                (SELECT SUM(qty) FROM po_product pp
                 LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                 WHERE pp.product_id = {$rowz['product_id']} AND po.site_id = {$rowsitedetail['site_id']}) as product_qty_purchased
                 
                ,(SELECT SUM(oi.qty) FROM order_item oi
                LEFT JOIN (`order` o) ON (o.order_id = oi.order_id)
                WHERE record_id = {$rowz['product_id']}
                  AND o.order_status = 'Paid'
                  AND o.record_type = 'POS'
                  AND o.site_id = {$rowsitedetail['site_id']}
                ) as product_qty_sold_pos

               ,(SELECT SUM(invItem.qty) FROM invoice_item invItem             
                LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                WHERE record_id = {$rowz['product_id']}
                  AND o.record_type != 'POS'
                  AND o.link_stock = 1
                  AND o.site_id = {$rowsitedetail['site_id']}
                ) as product_qty_sold_from_quote
                
                ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
                LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                WHERE ini.record_id = {$rowz['product_id']}
                AND inv.site_id = {$rowsitedetail['site_id']}
                ) as sales_return_qty

                ,(SELECT po.damaged_qty FROM product po
                WHERE po.product_id = {$rowz['product_id']}
                ) as damaged_qty
            ";

        }

            $resultothersite = $db->sql_query($SQLOthersite);

            while ($rowothersite = $db->sql_fetchrow($resultothersite)){

                //if ($rowothersite['product_qty_purchased']!=''){
                    if ($rowsitedetail['site_id'] == $cpSiteIdSession && $rowStockTransfer['from_location'] == $cpSiteIdSession){
                        $totalqty = $rowothersite['product_qty_purchased'] - $rowothersite['product_qty_sold_pos'] - $rowothersite['product_qty_sold_from_quote'] + $rowothersite['sales_return_qty'] -$rowothersite['damaged_qty']- $rowStockTransfer['Transfer_qty'] + $rowStockTransferto['Transfer_qty_to'];
                    }
                    else if ($rowsitedetail['site_id'] != $cpSiteIdSession && $rowStockTransfer['to_location'] == $cpSiteIdSession){
                        $totalqty = $rowothersite['product_qty_purchased'] - $rowothersite['product_qty_sold_pos'] - $rowothersite['product_qty_sold_from_quote'] + $rowothersite['sales_return_qty'] -$rowothersite['damaged_qty']- $rowStockTransfer['Transfer_qty'] + $rowStockTransferto['Transfer_qty_to'];
                    }
                    else {
                       $totalqty = $rowothersite['product_qty_purchased'] - $rowothersite['product_qty_sold_pos'] - $rowothersite['product_qty_sold_from_quote'] + $rowothersite['sales_return_qty'] -$rowothersite['damaged_qty']- $rowStockTransfer['Transfer_qty'] + $rowStockTransferto['Transfer_qty_to']; 
                        }

            }
        
        while ($rowsitedetailto = $db->sql_fetchrow($resultsitedetailto)){


            $SQLStockTransfer = "
            SELECT  st.from_location 
                    ,st.to_location
                    ,sh.product_id
                    ,SUM(sh.qty) AS Transfer_qty
            FROM stock_transfer st
            LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
            WHERE sh.product_id = {$rowz['product_id']} AND st.from_location = {$rowsitedetailto['site_id']}";

            $resultStockTransfer = $db->sql_query($SQLStockTransfer);
            $rowStockTransfer = $db->sql_fetchrow($resultStockTransfer);


            $SQLStockTransferto = "
            SELECT  st.from_location
                    ,st.to_location
                    ,sh.product_id
                    ,SUM(sh.qty) AS Transfer_qty_to
            FROM stock_transfer st
            LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
            WHERE sh.product_id = {$rowz['product_id']} AND st.to_location = {$rowsitedetailto['site_id']}";

            $resultStockTransferto = $db->sql_query($SQLStockTransferto);
            $rowStockTransferto = $db->sql_fetchrow($resultStockTransferto);
            $SQLOthersiteto = "
            SELECT
                (SELECT SUM(qty) FROM po_product pp
                 LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                 WHERE pp.product_id = {$rowz['product_id']} AND po.site_id = {$rowsitedetailto['site_id']}) as product_qty_purchased
                 
                ,(SELECT SUM(oi.qty) FROM order_item oi
                LEFT JOIN (`order` o) ON (o.order_id = oi.order_id)
                WHERE record_id = {$rowz['product_id']}
                  AND o.order_status = 'Paid'
                  AND o.record_type = 'POS'
                  AND o.site_id = {$rowsitedetailto['site_id']}
                ) as product_qty_sold_pos

               ,(SELECT SUM(invItem.qty) FROM invoice_item invItem             
                LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                WHERE record_id = {$rowz['product_id']}
                  AND o.record_type != 'POS'
                  AND o.link_stock = 1
                  AND o.site_id = {$rowsitedetailto['site_id']}
                ) as product_qty_sold_from_quote
                
                ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
                LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                WHERE ini.record_id = {$rowz['product_id']}
                AND inv.site_id = {$rowsitedetailto['site_id']}
                ) as sales_return_qty

                ,(SELECT po.damaged_qty FROM product po
                WHERE po.product_id = {$rowz['product_id']}
                ) as damaged_qty
            ";

        }

        $resultothersiteto = $db->sql_query($SQLOthersiteto);

            while ($rowothersiteto = $db->sql_fetchrow($resultothersiteto)){

                //if ($rowothersite['product_qty_purchased']!=''){
                    if ($rowsitedetail['site_id'] == $cpSiteIdSession && $rowStockTransfer['from_location'] == $cpSiteIdSession){
                        $totalqtyto = $rowothersiteto['product_qty_purchased'] - $rowothersiteto['product_qty_sold_pos'] - $rowothersiteto['product_qty_sold_from_quote'] + $rowothersiteto['sales_return_qty'] -$rowothersiteto['damaged_qty']- $rowStockTransfer['Transfer_qty'] + $rowStockTransferto['Transfer_qty_to'];
                    }
                    else if ($rowsitedetail['site_id'] != $cpSiteIdSession && $rowStockTransfer['to_location'] == $cpSiteIdSession){
                        $totalqtyto = $rowothersiteto['product_qty_purchased'] - $rowothersiteto['product_qty_sold_pos'] - $rowothersiteto['product_qty_sold_from_quote'] + $rowothersiteto['sales_return_qty'] -$rowothersiteto['damaged_qty']- $rowStockTransfer['Transfer_qty'] + $rowStockTransferto['Transfer_qty_to'];
                    }
                    else {
                       $totalqtyto = $rowothersiteto['product_qty_purchased'] - $rowothersiteto['product_qty_sold_pos'] - $rowothersiteto['product_qty_sold_from_quote'] + $rowothersiteto['sales_return_qty'] -$rowothersiteto['damaged_qty']- $rowStockTransfer['Transfer_qty'] + $rowStockTransferto['Transfer_qty_to']; 
                        }

            }

        
            $rows .= "
            <tr>
            <td>{$rowCounter}</td>
            <td class='w25p'>{$rowz['title']}</td>
            <td>{$rowz['carton_no']}</td>
            <td>{$totalqty}</td>
            <td class='w100'><input type='text' value='{$rowz['qty']}' id='fld_qty' class='text w100' name='qty' stock_transfer_history_id='{$rowz['stock_transfer_history_id']}' stock_transfer_id= '{$rowz['stock_transfer_id']}' stock='{$totalqty}'></td>
            <td>{$totalqtyto}</td>
            <td>{$rowz['created_by']}  {$rowz['creation_date']}</td>
            <td>{$rowz['modified_by']}  {$rowz['modification_date']} </td>
            <td><a href='#' class='deleteItem' stock_transfer_history_id='{$rowz['stock_transfer_history_id']}' stock_transfer_id= '{$rowz['stock_transfer_id']}'>Delete</a></td>
            </tr>
            ";
            $rowCounter++ ;
            
        
        }
        $text = "
        {$rows}
        ";
        return $text;
        
    }
}