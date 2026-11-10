<?
class CP_Admin_Modules_Trading_Inventory_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');

        $fromSO = $fn->getReqParam('fromSO');

        $count = 0;
        $rows  = '';

        $expRowHead = array('hasFlagInListBlue' => 1, 'hasFlagInListGreen' => 1);

        foreach ($dataArray as $row){
            $url = 'index.php?module=trading_inventory&_spAction=changeStatus'
                 . '&inventory_id=' . $row['inventory_id']
                 . '&showHTML=0';
            $title = "Edit Status: {$row['product_code']}";
            $status = "<a class='status' dialogTitle='{$title}'
                          url='{$url}' href='#'>{$row['status']}</a>";

            $damagedChecked = '';
            if ($row['damaged']) {
                $damagedChecked = 'checked';
            }
            $damagedText = "
            <input type='checkbox' value='1' inventory_id='{$row['inventory_id']}'
                   {$damagedChecked} name='damaged'/>
            ";

            $onSaleChecked = '';
            if ($row['on_sale']) {
                $onSaleChecked = 'checked';
            }
            $onSaleText = "
            <input type='checkbox' value='1' inventory_id='{$row['inventory_id']}'
                   {$onSaleChecked} name='on_sale'/>
            ";

            $exp = array('class' => 'price_discount');

            list($productText
                 ,$productCodeText
                 ,$enquiryCodeText
                 ,$soCodeText
                 ,$soCodeTextInventory
                 ,$poCodeText
                 ,$shipmentCodeText
                 ,$customerText
                 ,$customerTextInventory
                 ,$supplierText) = $this->getExpArr($row);

            $rows .= "
            {$listObj->getListRowHeader($row, $count, '', $expRowHead)}
            {$listObj->getGoToDetailText($count, $row['product_code'])}
            {$listObj->getListDataCell($row['web_code'])}
            {$listObj->getGoToDetailText($count, $row['serial_no'])}
            {$listObj->getListDataCell($row['collection_name'])}
            {$listObj->getListDataCell($productText)}
            {$listObj->getListDataCell($row['color'])}
            {$listObj->getListDataCell($soCodeText)}
            {$listObj->getListDataCell($poCodeText)}
            {$listObj->getListDataCell($supplierText)}
            {$listObj->getListDataCell($customerText)}
            {$listObj->getListDataCell($status)}
            {$listObj->getListDataCell($row['location'])}
            {$listObj->getListDataCell($shipmentCodeText)}
            {$listObj->getListDataCell($soCodeTextInventory)}
            {$listObj->getListDateCell($row['creation_date'])}
            {$listObj->getListDataCell($row['sell_unit_price_actual'], 'right', '', '', $exp)}
            {$listObj->getListDataCell($row['retail_unit_price_ex_vat'], 'right')}
            {$listObj->getListDataCell($row['retail_unit_price_inc_vat'], 'right')}
            {$listObj->getListDataCell($damagedText)}
            {$listObj->getListDataCell($onSaleText)}
            {$listObj->getListRowEnd($row['inventory_id'])}
            ";

            $count++;
        }

        $message = '';
        if ($fromSO == 1) {
            $message = "
            No records found. Please create inventory records from Purchase Order
            by changing it's status to <b>confirmed</b>
            ";

        }
        $rows = $listObj->getDisplayListRows($rows, $message);

        $text = "
        {$listObj->getListHeader($expRowHead)}
        {$listObj->getListHeaderCell('Product Code', 'p.product_code')}
        {$listObj->getListHeaderCell('Web Code', 'p.web_code')}
        {$listObj->getListHeaderCell('Serial', 'i.serial_no')}
        {$listObj->getListHeaderCell('Collection', 'p.collection_name')}
        {$listObj->getListHeaderCell('Product Name', 'p.title')}
        {$listObj->getListHeaderCell('Color', 'p.color')}
        {$listObj->getListHeaderCell('SO #', 'so.so_code')}
        {$listObj->getListHeaderCell('PO #', 'po.po_code')}
        {$listObj->getListHeaderCell('Supplier', 'po.company_id_supplier')}
        {$listObj->getListHeaderCell('Client', 'so.company_id_customer')}
        {$listObj->getListHeaderCell('Status', 'i.status')}
        {$listObj->getListHeaderCell('Location', 'i.location')}
        {$listObj->getListHeaderCell('Shipment #', 's.shipment_code')}
        {$listObj->getListHeaderCell('SO # Inventory', 'so_code_inventory')}
        {$listObj->getListHeaderCell('Creation Date', 'i.creation_date,i.serial_no')}
        {$listObj->getListHeaderCell('Actual Sell Price', 'i.sell_unit_price_actual', 'txtRight')}
        {$listObj->getListHeaderCell('RRP (-VAT)', 'i.retail_unit_price_ex_vat', 'txtRight')}
        {$listObj->getListHeaderCell('RRP (+VAT)', 'i.retail_unit_price_inc_vat', 'txtRight')}
        {$listObj->getListHeaderCell('Damaged', 'i.damaged')}
        {$listObj->getListHeaderCell('On Sale', 'i.on_sale')}
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

        $fieldset = "
        {$formObj->getTBRow('Product Code', 'product_code')}
        {$formObj->getTBRow('Product Name', 'product_name')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Region Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $expNoEdit = array('isEditable' => 0);

        list($productText
             ,$productCodeText
             ,$enquiryCodeText
             ,$soCodeText
             ,$soCodeTextInventory
             ,$poCodeText
             ,$shipmentCodeText
             ,$customerText
             ,$customerTextInventory
             ,$supplierText) = $this->getExpArr($row);

        //{$formObj->getTBRow('Retail Price', 'retail_unit_price', $row['retail_unit_price'], $expNoEdit)}
        $fieldset = "
        {$formObj->getTBRow('Product Code', 'product_code', $productCodeText, $expNoEdit)}
        {$formObj->getTBRow('Web Code', 'web_code', $row['web_code'], $expNoEdit)}
        {$formObj->getTBRow('Serial', 'serial_no', $row['serial_no'], $expNoEdit)}
        {$formObj->getTBRow('Collection', 'collection_name', $row['collection_name'], $expNoEdit)}
        {$formObj->getTBRow('Product Name', 'product_name', $productText, $expNoEdit)}
        {$formObj->getTBRow('Supplier', 'supplier_name', $supplierText, $expNoEdit)}
        {$formObj->getTBRow('Buy Currency', 'buy_currency', $row['buy_currency'], $expNoEdit)}
        {$formObj->getTBRow('Buy Price', 'buy_unit_price', $row['buy_unit_price'], $expNoEdit)}
        {$formObj->getTBRow('Sell Currency', 'sell_currency', $row['sell_currency'], $expNoEdit)}
        {$formObj->getTBRow('Retail Price (Discount)', 'retail_unit_price_discount',
                            $row['retail_unit_price_discount'])}
        {$formObj->getDDRowByArr('Inventory Status', 'status',
                                 $cpCfg['m.trading.inventory.statusArr'], $row['status'])}
        {$formObj->getDDRowByArr('Location', 'location',
                                 $cpCfg['m.trading.inventory.locationArr'], $row['location'])}

        {$formObj->getTBRow('Dimension H', 'dimension_h', $row['dimension_h'], $expNoEdit)}
        {$formObj->getTBRow('Dimension W', 'dimension_w', $row['dimension_w'], $expNoEdit)}
        {$formObj->getTBRow('Dimension D', 'dimension_d', $row['dimension_d'], $expNoEdit)}
        {$formObj->getTBRow('CBM per pc', 'cbm_per_pc', $row['cbm_per_pc'], $expNoEdit)}
        {$formObj->getTBRow('Hardware', 'hardware', $row['hardware'], $expNoEdit)}
        {$formObj->getTBRow('Material', 'material', $row['material'], $expNoEdit)}
        {$formObj->getTBRow('Colour', 'color', $row['color'], $expNoEdit)}
        {$formObj->getTBRow('Colour Inside', 'color_inside', $row['color_inside'], $expNoEdit)}

        {$formObj->getTBRow('Enquiry #', 'enquiry_code', $enquiryCodeText, $expNoEdit)}
        {$formObj->getTBRow('Sales Order #', 'so_code', $soCodeText, $expNoEdit)}
        {$formObj->getTBRow('Client', 'customer_name', $customerText, $expNoEdit)}
        {$formObj->getTBRow('Purchase Order #', 'po_code', $poCodeText, $expNoEdit)}
        {$formObj->getTBRow('Shipment #', 'shipment_code', $shipmentCodeText, $expNoEdit)}
        {$formObj->getTBRow('Sales Order # (Inventory)', 'so_code_inventory',
                            $soCodeTextInventory, $expNoEdit)}
        {$formObj->getTBRow('Client (Inventory)', 'customer_name_inventory',
                            $customerTextInventory, $expNoEdit)}
        {$formObj->getTBRow('Pricing Type', 'pricing_type', $row['pricing_type'], $expNoEdit)}
        {$formObj->getTBRow('Actual Sell Price', 'sell_unit_price_actual',
                            $row['sell_unit_price_actual'])}
        {$formObj->getYesNoDropDownRow('Damaged', 'damaged', $row['damaged'])}
        {$formObj->getYesNoDropDownRow('On Sale', 'on_sale', $row['on_sale'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Inventory Details', $fieldset)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    function getExpArr($row){
        $fn = Zend_Registry::get('fn');

        $expProduct = array('displayText' => $row['product_name']);
        $productText = $fn->getRecordDetailLink('trading_product', 'record_id',
                                                $row['product_id'], $expProduct);
        $expProductCode = array('displayText' => $row['product_code']);
        $productCodeText = $fn->getRecordDetailLink('trading_product', 'record_id',
                                                    $row['product_id'], $expProductCode);

        $expEnquiryCode = array('displayText' => $row['enquiry_code']);
        $enquiryCodeText = $fn->getRecordDetailLink('trading_enquiry', 'record_id',
                                                    $row['enquiry_id'], $expEnquiryCode);

        $expSOCode = array('displayText' => $row['so_code']);
        $soCodeText = $fn->getRecordDetailLink('trading_salesOrder', 'record_id',
                                               $row['sales_order_id'], $expSOCode);

        $expSOCodeInventory = array('displayText' => $row['so_code_inventory']);
        $soCodeTextInventory = $fn->getRecordDetailLink('trading_salesOrder', 'record_id',
                                               $row['sales_order_id_inventory'], $expSOCodeInventory);

        $expPOCode = array('displayText' => $row['po_code']);
        $poCodeText = $fn->getRecordDetailLink('trading_purchaseOrder', 'record_id',
                                               $row['purchase_order_id'], $expPOCode);

        $expShipmentCode = array('displayText' => $row['shipment_code']);
        $shipmentCodeText = $fn->getRecordDetailLink('trading_purchaseOrder', 'record_id',
                                                     $row['shipment_id'], $expShipmentCode);

        $expCustomer = array('displayText' => $row['customer_name']);
        $customerText = $fn->getRecordDetailLink('trading_company', 'record_id',
                                                 $row['company_id_customer'], $expCustomer);

        $expCustomerInventory = array('displayText' => $row['customer_name_inventory']);
        $customerTextInventory = $fn->getRecordDetailLink('trading_company', 'record_id',
                                 $row['company_id_customer_inventory'], $expCustomerInventory);

        $expSupplier = array('displayText' => $row['supplier_name']);
        $supplierText = $fn->getRecordDetailLink('trading_company', 'record_id',
                                                 $row['company_id_supplier'], $expSupplier);

        $arr = array(
             $productText
            ,$productCodeText
            ,$enquiryCodeText
            ,$soCodeText
            ,$soCodeTextInventory
            ,$poCodeText
            ,$shipmentCodeText
            ,$customerText
            ,$customerTextInventory
            ,$supplierText
        );

        return $arr;
    }

    /**
     *
     */
    function getRightPanel($row){
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');
        $comment = getCPPluginObj('common_comment');
        $displayLinkData = Zend_Registry::get('displayLinkData');

        $record_id = $fn->getIssetParam($row, 'inventory_id');

        $text = "
        {$media->getRightPanelMediaDisplay('Pictures', 'trading_product', 'picture', $row)}
        {$displayLinkData->getLinkPortalMain('trading_inventory', 'trading_pricingTypeLink', 'Pricing', $row)}
        {$comment->getView(array(
             'roomName' => 'trading_inventory'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;
    }

    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $company_id     = $fn->getReqParam('company_id');
        $special_search = $fn->getReqParam('special_search');
        $subCatOptions  = '';
        $collection_name = $fn->getReqParam('collection_name');
        $status = $fn->getReqParam('status');
        $location = $fn->getReqParam('location');
        $color = $fn->getReqParam('color');
        $on_sale = $fn->getReqParam('on_sale');
        $damaged = $fn->getReqParam('damaged');

        $modCat = getCPModuleObj('webBasic_category');

        $SQLCat = $modCat->model->getCategorySQLByType('Product');

        $catOptions = $dbUtil->getDropDownFromSQLCols2($db, $SQLCat, $tv['category_id']);

        $expUseKey = array('useKey' => 1);

        $subCatText = '';
        if ($tv['category_id'] != '') {
            $modSubCat = getCPModuleObj('webBasic_subCategory');
            $SQLSubCat = $modSubCat->model->getSubCategorySQLByCategory($tv['category_id']);
            $subCatOptions = $dbUtil->getDropDownFromSQLCols2($db, $SQLSubCat, $tv['sub_category_id']);
        }
        if ($tv['lnkRoom'] != '') {
            $subCatText = "
            <td class='fieldValue'>
            <select name='sub_category_id'>
                <option value=''>Sub Category</option>
                {$subCatOptions}
            </select>
            </td>
            ";
        }

        $sqlCollection = $fn->getValueListSQL('collection');
        $product = getCPModelObj('trading_product');
        $sqlColor = $product->getColorSQL();
        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
           ,"Flagged - Blue"
           ,"Not-Flagged - Blue"
           ,"Flagged - Green"
           ,"Not-Flagged - Green"
        );

        $text = "
        <td>
            <select name='damaged'>
                <option value=''>Damaged</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.yesNoArr'], $damaged, 1)}
            </select>
        </td>
        <td>
            <select name='on_sale'>
                <option value=''>On Sale</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.yesNoArr'], $on_sale, 1)}
            </select>
        </td>
        <td>
            <select name='collection_name'>
                <option value=''>Collection</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlCollection, $collection_name)}
            </select>
        </td>
        <td class='fieldValue'>
            <select name='category_id'>
                <option value=''>Category</option>
                {$catOptions}
            </select>
        </td>

        <td class='fieldValue'>
            <select name='color'>
                <option value=''>Color</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlColor, $color)}
            </select>
        </td>
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.inventory.statusArr'], $status)}
            </select>
        </td>
        <td>
            <select name='location'>
                <option value=''>Location</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.inventory.locationArr'], $location)}
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

    function getChangeStatus() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $inventory_id = $fn->getReqParam('inventory_id');
        $row = $fn->getRecordRowByID('inventory', 'inventory_id', $inventory_id);
        $expNoEdit = array('isEditable' => 0);

        $formAction = "index.php?_spAction=changeStatusSubmit&module={$tv['module']}&showHTML=0";
        $exp = array('sqlType' => 'OneField');

        $fieldset = "
        {$formObj->getDDRowByArr('Invetory Status', 'status', $cpCfg['m.trading.inventory.statusArr'], $row['status'])}
        ";

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getFieldSetWrapped('Change Status', $fieldset)}
            <input type='hidden' name='inventory_id' value='{$inventory_id}' />
        </form>
        ";

        return $text;
    }
}