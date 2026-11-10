<?
class CPL_Admin_Modules_Tradingin_Inventory_View extends CP_Admin_Modules_Tradingin_Inventory_View
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $cpCfg   = Zend_Registry::get('cpCfg');

        $count   = 0;
        $rows    = '';

        //TO CREATE INVENTORY RECORDS FROM PRODUCT RECORD
        $stock = '';
        foreach ($dataArray as $row){
            $stock = $row['stock'];

            $productCodeTd = $listObj->getListDataCell($row['item_code'], 'center');

            $adjust_stock = "
            <a class='batchStockList ml10' inventory_id='{$row['inventory_id']}' product_id='{$row['product_id']}'>
                <u>Adjust Stock</u>
            </a>
            <div class='overallStockForList'><input name='current_stock' value='{$stock}' class='txt currentStockEdit displayNone'/>
            <a class='btn btn-success saveCurrentStock displayNone currentStockSaveDisplay' inventory_id='{$row['inventory_id']}' product_id='{$row['product_id']}'>
                Save
            </a></div>
            <a inventory_id='{$row['inventory_id']}' class='viewAllUpdatedAdjustStockHistory ml20'><u>View</u>
            ";

            $adjustStockColumn = $listObj->getListDataCell($adjust_stock, 'center');

            $stock = "
            <span class='stockUpdateList_{$row['inventory_id']} pull-right'>{$stock}</span>
            ";

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell('STK-'.$row['inventory_code'])}
            {$listObj->getListDataCell($row['product_name'])}
            {$listObj->getListDataCell($row['product_type'])}
            {$productCodeTd}
            {$listObj->getListDataCell($row['unit'], 'center')}
            {$listObj->getListDataCell($stock)}
            {$adjustStockColumn}
            {$listObj->getListDataCell($row['minimum_order_level'], 'center')}
            {$listObj->getListRowEnd($row['inventory_id'])}
            ";

            $count++;
        }

        $weightColumn  = "";
        $productCodeTh = $listObj->getListHeaderCell('Item Code', 'item_code', 'txtCenter');

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Inventory Code', 'inventory_code')}
        {$listObj->getListHeaderCell('Name', 'product_name')}
        {$listObj->getListHeaderCell('Product Type', 'product_type')}
        {$productCodeTh}
        {$listObj->getListHeaderCell('UOM', 'i.unit', 'txtCenter')}
        {$listObj->getListHeaderCell('Stock', 'stock', 'txtRight')}
        {$listObj->getListHeaderCell('Adjust Stock', '')}
        {$listObj->getListHeaderCell('MOL', 'i.minimum_order_level', 'txtCenter')}
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
        $db      = Zend_Registry::get('db');

        $formObj->mode = $tv['action'];

        $expNoEdit    = array('isEditable' => 0);
        $stockArray   = $fn->getStockForProduct($row['product_id']);
        $stock        = $row['actual_stock'];
        $purchasedQty = $stockArray['PurchasedQty'];
        $soldQty      = $stockArray['SoldQty'];

        $creation_date     = $fn->getCPDate($row['creation_date'], 'd-m-Y H:i:s');
        $modification_date = $fn->getCPDate($row['modification_date'], 'd-m-Y H:i:s');

        $PurchasedWeight = '';
        $SoldWeight      = '';
        $StockWeight     = '';

        $StockRows = "
        <tr>
            <td>{$purchasedQty}</td>
            <td>{$soldQty}</td>
            <td>{$stock}</td>
        </tr>
        ";

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Product Details</div>
                    <div class='toggle'></div>
                    <div class='float_right'>Creation : {$row['created_by']} on {$creation_date} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Modified : {$row['modified_by']} {$modification_date}</div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td width='25%'>{$formObj->getTBRow('Inventory Code', 'inventory_code', 'STK-'.$row['inventory_code'], $expNoEdit)}</td>
                                <td width='25%'>{$formObj->getTBRow('Name', 'product_name', $row['product_name'], $expNoEdit)}</td>
                                <td width='25%'>{$formObj->getTBRow('Product Type', 'product_type', $row['product_type'], $expNoEdit)}</td>
                                <td width='25%'>{$formObj->getTBRow('Item Code', 'item_code', $row['item_code'], $expNoEdit)}</td>
                            </tr>
                            <tr>
                                <td width='25%'>{$formObj->getTBRow('UOM', 'unit', $row['unit'], $expNoEdit)}</td>
                                <td width='25%'>{$formObj->getTBRow('MOL', 'minimum_order_level', $row['minimum_order_level'])}</td>
                                <td width='25%'>{$formObj->getTARow('Notes', 'notes', $row['notes'])}</td>
                            </tr>
                        </tbody>
                    </table>
                    <table class='thinlist stockDetailsTable'>
                        <thead>
                            <tr>
                                <th>Total Purchased Qty</th>
                                <th>Total Sold Qty</th>
                                <th>Total Available Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$StockRows}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
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

        $supplier_id         = $fn->getReqParam('supplier_id');
        $category         = $fn->getReqParam('category');
        $minimum_order_level  = $fn->getReqParam('minimum_order_level');
        $product_type         = $fn->getReqParam('product_type');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $spArray1 = array(
            ""
           ,"MOL Products"
        );

        $sqlSupplier = "
        SELECT c.company_id
              ,c.company_name
        FROM company c
        WHERE c.category = 'Supplier'
        ORDER BY c.company_name
        ";

        $sqlCategory    = $fn->getValueListSQL('projectCategory');

        $productTypeArr = array(
           "Materials"
          ,"Tools"
        );

        $text = "
        <td>
            <select name='category'>
                <option value=''>Category</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlCategory, $category)}
            </select>
        </td>
        <td>
            <select name='product_type'>
                <option value=''>Product Type</option>
                {$cpUtil->getDropDown1($productTypeArr, $product_type)}
            </select>
        </td>
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
           </select>
        </td>
        <td>
            <select name='minimum_order_level'>
                <option value=''>Minimum Order Level</option
                {$cpUtil->getDropDown1($spArray1, $minimum_order_level)}
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

        $SQL = "
        SELECT p.product_id
        FROM product p
        WHERE p.product_id NOT IN(
            SELECT invent.product_id
            FROM inventory invent
        )
        ORDER BY p.product_id
       ";

       $SQL = "
        SELECT p.product_id
        FROM product p
        LEFT JOIN inventory inv ON inv.product_id = p.product_id
        WHERE inv.product_id IS NULL
       ";

        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {

            $fa = array();

            $fa['product_id']     = $row['product_id'];
            $fa['creation_date']  = date('Y-m-d H:i:s');
            $fa['inventory_code']     = $this->getUpdateInventoryCode();

            $inventory_id = $fn->addRecord($fa, 'inventory');
        }
    }

    /**
     *
     */
    function getUpdateInventoryCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Purchase order Code */
        $inventoryCode = $fn->getSettingsValueByKey("inventoryCode");

        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'inventoryCode'";
        $result = $db->sql_query($SQL);

        return $inventoryCode;
    }

    /**
     */
    function getOrderDisplay($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $formAction = '';

        $text = "
        <div class='linkPortalWrapper panel tradingin_inventory_orderLink'>
            <div class='panel panel-primary'>
                <div class='panel-heading'>
                    <div expanded='1'>
                        <div class='floatbox'>
                            <div class='float_left RightPanelHeading'>Project Linked (Materials Used)</div>
                            <div class='txtRight'>
                              <div class='toggle'></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='panel-body'>
                    <div class='linkPortalDataWrapper'>
                        <form id='orderItemPrint' class='' method='post' action='{$formAction}'>
                            <div id='invoicePortalOuter'>
                                {$this->getOrderDisplayDetail($row)}
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
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

        $rows  = "";
        $rowsPvt  = "";
        $links = "";
        $leftJoin  = "";
        $sqlAppend = "";

        $SQL = "
        SELECT DISTINCT p.project_id
              ,pm.material_used_date
              ,p.title
              ,com.company_name
              ,pm.quantity
        FROM `project_materials` pm
        LEFT JOIN project p ON (p.project_id = pm.project_id)
        LEFT JOIN company com ON (com.company_id = p.company_id)
        WHERE pm.product_id = {$row['product_id']}
        ";

        $result   = $db->sql_query($SQL);
        $client = '';
        while ($rowOI = $db->sql_fetchrow($result)) {
            $OrderEditLink = "index.php?_topRm=project&module=enggCrm_project&_action=edit&project_id={$rowOI['project_id']}";

            $rows .= "
            <tr class='orderRightPanelTr'>
                <td width='15%'>{$fn->getCPDate($rowOI['material_used_date'], 'd-m-Y')}</td>
                <td width='30%'>
                    <a href='{$OrderEditLink}' target='_blank'>
                        <u>{$rowOI['title']}</u>
                </td>
                <td width='30%'>{$rowOI['company_name']}</td>
                <td width='15%'>{$rowOI['quantity']}</td>
            </tr>
            ";
        }

        //style='background-color:#0F9191; color:#ffffff'

        $header ="
        <tr style='background-color:#EAEAE8;'>
            <th width='15%'>Date</th>
            <th width='30%'>Project Title</th>
            <th width='30%'>Client Name</th>
            <th width='15%'>Numbers</th>
        </tr>
        ";

        $text = "
        <table class='thinlist' width='100%'>
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
        <div class='linkPortalWrapper panel tradingin_inventory_purchaseOrderLink'>
            <div class='panel panel-primary'>
                <div class='panel-heading'>
                    <div expanded='1'>
                        <div class='floatbox'>
                            <div class='float_left RightPanelHeading'>Purchase Orders Linked</div>
                            <div class='txtRight'>
                              <div class='toggle'></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='panel-body'>
                    <div class='linkPortalDataWrapper'>
                        <form id='poPrint' class='' method='post' action='{$formAction}'>
                            <div id='invoicePortalOuter'>
                                {$this->getPurchaseOrderDisplayDetail($row)}
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
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
        SELECT pop.cost_price
              ,pop.qty
              ,com.company_name AS supplier_name
              ,po.po_code
              ,po.purchase_order_date
              ,po.purchase_order_id
              ,po.creation_date
              ,p.title
              ,c.company_name
              {$sqlAppend}
        FROM po_product pop
        LEFT JOIN purchase_order po ON po.purchase_order_id = pop.purchase_order_id
        LEFT JOIN project p ON p.project_id = po.project_id
        LEFT JOIN company c ON c.company_id = p.company_id
        LEFT JOIN supplier com ON pop.supplier_id = com.supplier_id
        {$leftjnAppend}
        WHERE pop.product_id = {$row['product_id']}
        ";

        $result   = $db->sql_query($SQL);

        while ($rowPo = $db->sql_fetchrow($result)) {
            if($cpCfg['cp.hasMultiUniqueSites']  == true){
                $tdForSiteId = "<td>{$rowPo['site_title']}</td>";
            }

            if($rowPo['purchase_order_date'] == '' || $rowPo['purchase_order_date'] == 0){
                $purchase_order_date = $fn->getCPDate($rowPo['creation_date'], 'd-m-Y');
            }
            else{
                $purchase_order_date = $fn->getCPDate($rowPo['purchase_order_date'], 'd-m-Y');
            }
            $po_code = "<a href='index.php?_topRm=finance&module=tradingsg_purchaseOrder&record_id={$rowPo['purchase_order_id']}&_action=edit'><u>{$rowPo['po_code']}</u></a>";

            $rows .= "
            <tr>
                <td>{$po_code}</td>
                {$tdForSiteId}
                <td>{$purchase_order_date}</td>
                <td>{$rowPo['title']}</td>
                <td>{$rowPo['company_name']}</td>
                <td>{$rowPo['cost_price']}</td>
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
        <th>Project Title</th>
        <th>Client Name</th>
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
     */
    function getUpdatedAdjustStockHistory(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $inventory_id = $fn->getReqParam('inventory_id');
        
        $rows = "";

        $SQL = "
        SELECT *
        FROM adjust_stock_log
        WHERE inventory_id = {$inventory_id}
          AND (current_stock IS NOT NULL OR current_stock = '')
        ORDER BY adjust_stock_log_id DESC
        ";
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
            <tr>
                <td>{$row['materials_used']}</td>
                <td>{$row['adjust_stock']}</td>
                <td>{$row['current_stock']}</td>
                <td><i>{$row['created_by']} - {$row['creation_date']}</i></td>
            </tr>
            ";
        }

        $header ="
        <tr style='background-color:#EAEAE8;'>
            <th>Materials Used</th>
            <th>Adjust Stock</th>
            <th>Actual Stock</th>
            <th>Created/Updated By</th>
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
}