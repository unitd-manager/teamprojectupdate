<?
class CP_Admin_Modules_Pos_PurchaseOrder_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');

        $count = 0;
        $rows  = '';

        foreach ($dataArray as $row){

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['po_code'])}
            {$listObj->getListDateCell($row['purchase_order_date'])}
            {$listObj->getListDataCell($row['staff_name'])}
            {$listObj->getListDataCell($row['buy_currency'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['purchase_order_id'])}
            ";

            $count++ ;
        }
        $rows = $listObj->getDisplayListRows($rows);

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Purchase Order Number', 'po.po_code')}
        {$listObj->getListHeaderCell('Purchase Order Creation Date', 'po.purchase_order_date')}
        {$listObj->getListHeaderCell('Staff', 'staff_name')}
        {$listObj->getListHeaderCell('Buy Currency', 'buy_currency')}
        {$listObj->getListHeaderCell('Purchase Order Status', 'po.status')}
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

        $fieldset = "
        {$formObj->getTBRow('Purchase Order No.', 'po_code')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Purchase Order Header', $fieldset)}
        ";

        return $text;

    }

    /**
     *
     */
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $_SESSION['purchase_order_id'] = $row['purchase_order_id'];
 
        $expNoEdit = array('isEditable' => 0);

        $expVl = array('sqlType' => 'OneField');

        $sqlPayment = getCPModuleObj('pos_payment')->model->getPaymentSQL();
        $sqlCurrency = getCPModuleObj('pos_currency')->model->getCurrencyCodeSQL();
        $sqlShipment = getCPModuleObj('pos_shipment')->model->getShipmentSQL();
        $sqlVendor = $fn->getRecordByCondition('vendor', "vendor_id='{$row['vendor_id']}'");
        $sqlWarehouse = $fn->getRecordByCondition('warehouse', "warehouse_id='{$row['warehouse_id']}'");
        $sqlStaff = getCPModuleObj('pos_staff')->model->getStaffByGroupSQL();
        $sqlShop = "SELECT shop_id ,title FROM shop";

        $expStaff = array('notesRight' => "
        <a class='editLinkSingle' href='' 
            link='{$fn->getOpenLinkUrl('pos_purchaseOrder', 'pos_staffLink', 'fld_staff_id')}'>Choose
        </a>
        ");

        $expVendor = array('notesRight' => "
        <a class='editLinkSingle vendorLink' href='' 
            link='{$fn->getOpenLinkUrl('pos_purchaseOrder', 'pos_vendorLink', 'fld_vendor_id')}'>Choose
        </a>
        ");

        $expShop = array('notesRight' => "
        <a class='editLinkSingle' href='' 
            link='{$fn->getOpenLinkUrl('pos_purchaseOrder', 'pos_shopLink', 'fld_shop_id')}'>Choose
        </a>
        ");

        $expWarehouse = array('notesRight' => "
        <a class='editLinkSingle' href='' 
            link='{$fn->getOpenLinkUrl('pos_purchaseOrder', 'pos_warehouseLink', 'fld_warehouse_id')}'>Choose
        </a>
        ");
        
        if ($row['status'] == ''){
            $status = 'Normal';
        } else {
            $status = $row['status'];
        }

        $po_no = $fn->getSettingsRowByKey('pfxPurchaseOrder');
        
        if ($po_no['auto_generate_no'] == 1) {
            $po_code = $formObj->getTBRow('Purchase Order Number', 'po_code', $row['po_code'], $expNoEdit);
        } else {
            $po_code = $formObj->getTBRow('Purchase Order Number', 'po_code', $row['po_code']);
        }

        //{$formObj->getTBRow('Handled By', 'staff_id', $row['staff_id'], $expStaff)}
        //{$formObj->getTBRow('Staff Name', 'staff_name', $row['staff_name'], $expNoEdit)}

        $fieldset1 = "
        {$po_code}
        {$formObj->getDateRow('Order Date', 'purchase_order_date', $row['purchase_order_date'])}
        {$formObj->getTBRow('Vendor Code', 'vendor_id', $sqlVendor['code'], $expVendor)}
        {$formObj->getTBRow('Vendor Name', 'vendor_name', $row['vendor_name'], $expNoEdit)}
        {$formObj->getTBRow('Reference No.', 'reference_no', $row['reference_no'])}
        {$formObj->getDDRowBySQL('Payment', 'payment_id', $sqlPayment, $row['payment_id'])}
        {$formObj->getDDRowBySQL('Currency', 'currency', $sqlCurrency, $row['currency'], array('sqlType' => 'OneField'))}
        {$formObj->getDDRowBySQL('Shipment', 'shipment_id', $sqlShipment, $row['shipment_id'])}
        {$formObj->getDDRowBySQL('Handled By', 'staff_id', $sqlStaff, $row['staff_id'], $expStaff)}
        ";

        //{$formObj->getTBRow('To Location', 'shop_id', $row['shop_id'], $expShop)}
        //{$formObj->getTBRow('Shop Name', 'shop_name', $row['shop_name'], $expNoEdit)}
        
        $fieldset2 = "
        {$formObj->getDDRowBySQL('To Location', 'shop_id', $sqlShop, $row['shop_id'], $expShop)}
        {$formObj->getTBRow('Warehouse', 'warehouse_id', $sqlWarehouse['code'], $expWarehouse)}
        {$formObj->getTBRow('Warehouse Name', 'warehouse_name', $row['warehouse_name'], $expNoEdit)}
        {$formObj->getTBRow('Address', 'address', $row['address'])}
        {$formObj->getTBRow('Telephone', 'phone', $row['phone'])}
        {$formObj->getTBRow('Contact Person', 'contact_person', $row['contact_person'])}
        {$formObj->getTBRow('Remark', 'notes', $row['notes'])}
        {$formObj->getTBRow('Delivery Date', 'delivery_date', $row['delivery_date'], $expNoEdit)}
        {$formObj->getTBRow('Receive Date', 'received_date', $row['received_date'], $expNoEdit)}
        {$formObj->getTBRow('Status', 'status', $status, $expNoEdit)}
        ";

        $text = "
        <div class='subcolumns'>
            <div class='c50l purchaseOrderLeft'>
                <div class='subcl'>
                    {$formObj->getFieldSetWrapped('Purchase Order', $fieldset1)}
                </div>
            </div>
            <div class='c50r purchaseOrderRight'>
                {$formObj->getFieldSetWrapped('Delivery Information', $fieldset2)}
            </div>
        </div>
        <div id='purchaseOrderItems'>
            {$this->getPurchaseOrderItems()}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getPurchaseOrderItems(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $purchase_order_id = isset($_SESSION['purchase_order_id']) ? $_SESSION['purchase_order_id']  : '';
        
        $formAction = "index.php?module=pos_purchaseOrder&_spAction=purchaseOrderItemSubmit&showHTML=0";
        $expNoEdit = array('isEditable' => 0);
        $rows = '';
        
        $SQL = "
        SELECT *
        FROM purchase_order_items
        WHERE purchase_order_id = {$purchase_order_id}
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)){ 

            $sqlProductItem = $fn->getRecordByCondition('product_item', "sku_no='{$row['sku_no']}'");
            $sqlProduct = $fn->getRecordByCondition('product', "product_id='{$sqlProductItem['product_id']}'");
            $expNote = array('tooltip' => $row['purchase_order_items_id']);
            
            $sku_fld_id = "sku_no_{$row['purchase_order_items_id']}";
            
            $expSku = array('notesRight' => "
            <a class='editLinkSingle' href='' 
                link='{$fn->getOpenLinkUrl('pos_purchaseOrder', 'pos_productLink', $sku_fld_id)}'>Choose
            </a>
            ", 'fldId' => $sku_fld_id);

            $total = ($row['qty'] * $row['unit_price']) - $row['discount'];
            $rows .= "
            <form id='purchaseOrderItemForm' name='purchaseOrderItemForm' class='purchaseOrderItemForm' method='post' action='{$formAction}'>
                <tr id='{$row['purchase_order_items_id']}'>
                    <td class='seqNo'></td>
                    <td class='action'>
                        <a class='deletePurchaseOrderItem' href='#' 
                            purchase_order_items_id = '{$row['purchase_order_items_id']}'><span>delete</span>
                        </a>
                    </td>
                    <td class='skuNo'>{$formObj->getTBRow('', 'sku_no', $row['sku_no'], $expSku)}</td>
                    <td class='name'>{$formObj->getTBRow('', 'item_title', $sqlProduct['title'], $expNoEdit)}</td>
                    <td class='vendorNo'>{$formObj->getTBRow('', 'vendor_sku_no', $row['vendor_sku_no'], $expNote)}</td>
                    <td class='qty'>{$formObj->getTBRow('', 'qty', $row['qty'], $expNote)}</td>
                    <td class='cost'>{$formObj->getTBRow('', 'unit_price', $row['unit_price'], $expNote)}</td>
                    <td class='discAmount'>{$formObj->getTBRow('', 'discount', $row['discount'], $expNote)}</td>
                    <td class='purchaseOrderItemtotal_{$row['purchase_order_items_id']} txtRight totalAmount'>{$total}</td>
                    <input type='hidden' name='purchase_order_items_id' value='{$row['purchase_order_items_id']}' />
                </tr>
            </form>
            ";
        }
        $text = "
        <table class='thinlist'>
            <thead>
                <tr>
                    <th colspan=10>
                        Ordered Items Details
                        <a href='#' id='addPurchaseOrderItems' purchase_order_id='{$purchase_order_id}'>ADD</a>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr class='portal-row2'>
                    <th class='seqNo'>Seq No.</th>
                    <th class='action'>Action</th>
                    <th class='skuNo'>SKU/Barcode</th>
                    <th class='name'>Name</th>
                    <th class='vendorNo'>Vendor SKU No.</th>
                    <th class='qty'>Qty</th>
                    <th class='cost'>Cost</th>
                    <th class='discAmount'>Disc. Amount</th>
                    <th class='totalAmount'>Total Amount</th>
                </tr>
                {$rows}
            </tbody>
            <tfoot>
                <tr>
                    <th colspan=8 class='txtRight'>Total Amount</th>
                    <th class='txtRight' id='purchaseOrderSubTotal'></th>
                </tr>
                <tr>
                    <th colspan=8 class='txtRight'>Discount(%)</th>
                    <th class='txtRight' id='orderInvoiceDisc'>
                        <input type='text' id='overallDiscount' class='w40' name='overall_discount' value='' purchase_order_id='{$purchase_order_id}'>
                    </th>
                </tr>
                <tr>
                    <th colspan=8 class='txtRight'>Less</th>
                    <th class='txtRight' id='overallDiscountAmount'></th>
                </tr>
                <tr>
                    <th colspan=8 class='txtRight'>Actual Amount</th>
                    <th class='txtRight' id='purchaseOrderNetTotal'></th>
                </tr>
            </tfoot>
        </table>
        ";

        return $text;
    }


    /**
     *
     */
    function getRightPanel($row){
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $comment = getCPPluginObj('common_comment');
        $fn = Zend_Registry::get('fn');

        $links = "
        ";

        $record_id = $fn->getIssetParam($row, 'purchase_order_id');

        $text = "
        {$links}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $status = $fn->getReqParam('status');
        $status = $fn->getReqParam('status');
        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $text = "
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.purchaseOrder.statusArr'], $status)}
            </select>
        </td>
        <td>
            <select class='w125' name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
           </select>
        </td>
        ";

        return $text;
    }

    function getEditInventoryForm() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $listObj = Zend_Registry::get('listObj');
        $formObj = Zend_Registry::get('formObj');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $purchase_order_items_id = $fn->getReqParam('purchase_order_items_id');

        $SQL = "
        SELECT DISTINCT
               i.*
              ,p.product_code
              ,p.title product_name
              ,p.collection_name
              ,e.enquiry_code
              ,po.po_code
              ,poi.buy_unit_price
        FROM inventory i
        LEFT JOIN product p ON p.product_id = i.product_id
        LEFT JOIN enquiry e ON e.enquiry_id = i.enquiry_id
        JOIN purchase_order po ON po.purchase_order_id = i.purchase_order_id
        JOIN purchase_order_items poi ON poi.purchase_order_items_id = i.purchase_order_items_id
        WHERE i.purchase_order_items_id = {$purchase_order_items_id}
        ORDER BY i.product_id
                ,i.serial_no
        ";

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows();
        $count = 0;

        $rows = '';

        while ($row = $db->sql_fetchrow($result)) {
            $statusText = "
            <select name='inv_status[{$row['inventory_id']}]' class='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.inventory.statusArr'], $row['status'])}
            </select>
            ";
            $locationText = "
            <select name='inv_location[{$row['inventory_id']}]' class='location'>
                <option value=''>Location</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.inventory.locationArr'], $row['location'])}
            </select>
            ";

            $exp = array('hasFlagInList' => false
                        ,'keyFieldValue' => $row['inventory_id']
                        ,'hasEditInList' => false
                        ,'hasRowNumber' => false
            );
            $rows .= "
            {$listObj->getListRowHeader($row, $count, '', $exp)}
            {$listObj->getGoToDetailText($count, $row['product_code'])}
            {$listObj->getListDataCell($row['serial_no'])}
            {$listObj->getListDataCell($row['collection_name'])}
            {$listObj->getListDataCell($row['product_name'])}
            {$listObj->getListDataCell($row['po_code'])}
            {$listObj->getListDataCell($statusText)}
            {$listObj->getListDataCell($locationText)}
            {$listObj->getListDateCell($row['creation_date'])}
            {$listObj->getListRowEnd($row['inventory_id'])}
            ";

            $count++;
        }

        $raiseBtn = "
        <form class='yform'>
            <div class='type-button float_right'>
            <input type='reset' value='Cancel' id='btnUpdateInventoryCancel' />
            <input type='button' value='Update' id='btnUpdateInventory' />
            </div>
        </form>
        ";

        $fnMod = getCPModelObj('trading_company');
        $sqlSupplier = $fnMod->getSupplierSQL();

        $exp = array('hasEditInList' => false
                    ,'hasRowNumber' => false
                    ,'hasFlagInList' => false
               );

        $rowSummary = "
        <tr class='even'>
        <td colspan='6'></td>

        <td>
        <select id='status_common'>
            <option value=''>Update Status</option>
            {$cpUtil->getDropDown1($cpCfg['m.trading.inventory.statusArr'])}
        </select>
        </td>
        <td>
        <select id='location_common'>
            <option value=''>Update Location</option>
            {$cpUtil->getDropDown1($cpCfg['m.trading.inventory.locationArr'])}
        </select>
        </td>

        <td colspan='2'></td>
        </tr>
        ";
        $text = "
        <div id='updateInventory'>
            {$raiseBtn}
            {$listObj->getListHeader($exp)}
            {$listObj->getListHeaderCell('Product Code')}
            {$listObj->getListHeaderCell('Serial')}
            {$listObj->getListHeaderCell('Collection')}
            {$listObj->getListHeaderCell('Product Name')}
            {$listObj->getListHeaderCell('Purchase Order #')}
            {$listObj->getListHeaderCell('Status')}
            {$listObj->getListHeaderCell('Location')}
            {$listObj->getListHeaderCell('Creation Date')}
            {$listObj->getListHeaderEnd()}
            {$rowSummary}
            {$rows}
            {$listObj->getListFooter()}
            {$formObj->getHiddenFldObj('purchase_order_items_id', $purchase_order_items_id)}
        </div>
        ";

        return $text;
    }

}