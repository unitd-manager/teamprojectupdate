<?
class CP_Admin_Modules_Trading_PurchaseOrder_View extends CP_Common_Lib_ModuleViewAbstract
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
            $expSoCode = array('displayText' => $row['so_code']);
            $soCodeText = $fn->getRecordDetailLink('trading_salesOrder', 'record_id', $row['sales_order_id'], $expSoCode);

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['po_code'])}
            {$listObj->getListDataCell($soCodeText)}
            {$listObj->getListDataCell($row['shipment_no'])}
            {$listObj->getListDataCell($row['supplier_name'])}
            {$listObj->getListDateCell($row['purchase_order_date'])}
            {$listObj->getListDataCell($row['staff_name'])}
            {$listObj->getListDataCell($row['buy_currency'])}
            {$listObj->getListDataCell($row['po_sum_buy_price'], 'right')}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['purchase_order_id'])}
            ";

            $count++ ;
        }
        $rows = $listObj->getDisplayListRows($rows);

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Purchase Order Number', 'po.po_code')}
        {$listObj->getListHeaderCell('Sales Order Number', 'so.so_code')}
        {$listObj->getListHeaderCell('Shipment Number', 'shipment_no')}
        {$listObj->getListHeaderCell('Supplier Name', 'supplier_name')}
        {$listObj->getListHeaderCell('Purchase Order Creation Date', 'po.purchase_order_date')}
        {$listObj->getListHeaderCell('Staff', 'staff_name')}
        {$listObj->getListHeaderCell('Buy Currency', 'buy_currency')}
        {$listObj->getListHeaderCell('Purchase Order Value', 'po_sum_buy_price', 'txtRight')}
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

        $sqlSupplier = $fn->getDDSql('trading_company', array('condn' => "category = 'Supplier'"));
        $expSupplier = array('hideFirstOption' => 1);

        $fieldset = "
        {$formObj->getDDRowBySQL('Supplier', 'company_id_supplier', $sqlSupplier, '', $expSupplier)}
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

        $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');

        $expNoEdit = array('isEditable' => 0);

        $expSoCode = array('displayText' => $row['so_code']);
        $soCodeText = $fn->getRecordDetailLink('trading_salesOrder', 'record_id', $row['sales_order_id'], $expSoCode);

        $expSuppText = array('displayText' => $row['supplier_name']);
        $supplierText = $fn->getRecordDetailLink('trading_company', 'record_id', $row['company_id_supplier'], $expSuppText);

        $expContact = array('detailValue' => $row['contact_name_supplier']);
        $modContact = getCPModuleObj('trading_contact');
        $sqlSuppContact = $modContact->model->getContactsByCompanySQL($row['company_id_supplier']);

        $expCompany = array('sqlType' => 'OneField');
        $expPaymentTerms = $fnsModGrp->getTermsParamArr('trading_paymentTermsLink',
                                                        $row['company_id_supplier'],
                                                        'fld_payment_terms'
                                                        );

        $expVl = array('sqlType' => 'OneField');
        $sqlCurrency = $fn->getValueListSQL('currency');

        $expBuyer = array('detailValue' => $row['staff_name']);
        $modStaff = getCPModuleObj('core_staff');
        $sqlBuyer = $modStaff->model->getStaffByGroupSQL();

        $statusArr = $cpCfg['m.trading.purchaseOrder.statusArr'];
        if($row['status'] == 'confirmed'){ //if po confirmed, remove option 'new'
            unset($statusArr[array_search('new', $statusArr)]);
        }
 
        $fieldset1 = "
        {$formObj->getTBRow('Purchase Order Number', 'po_code', $row['po_code'], $expNoEdit)}
        {$formObj->getDateRow('Purchase Order Date', 'purchase_order_date', $row['purchase_order_date'])}
        {$formObj->getDDRowByArr('Purchase Order Status', 'status', $statusArr, $row['status'])}
        {$formObj->getHiddenFldObj('status_prev', $row['status'], 'fld_status_prev')}
        {$formObj->getTBRow('Sales Order Number', 'order_value', $soCodeText, $expNoEdit)}
        {$formObj->getTBRow('Supplier Name', 'supplier_name', $supplierText, $expNoEdit)}
        {$formObj->getDDRowBySQL('Contact Person', 'contact_id_supplier', $sqlSuppContact, $row['contact_id_supplier'], $expContact)}
        {$formObj->getTARow('Payment Terms', 'payment_terms', $row['payment_terms'], $expPaymentTerms)}
        {$formObj->getTBRow('Shipment Number', 'shipment_no', $row['shipment_no'])}
        {$formObj->getDateRow('Required Delivery Date', 'required_delivery_date', $row['required_delivery_date'])}
        {$formObj->getTARow('Note to Supplier', 'notes', $row['notes'])}
        {$formObj->getDDRowBySQL('Staff Member / Agent', 'staff_id', $sqlBuyer, $row['staff_id'], $expBuyer)}
        {$formObj->getTBRow('Port of Origin', 'port_of_origin', $row['port_of_origin'])}
        {$formObj->getTARow('Ship to Location', 'delivery_address', $row['delivery_address'])}
        {$formObj->getTBRow('Consignee Name', 'consignee_name', $row['consignee_name'])}
        {$formObj->getTBRow('Consignee Address', 'consignee_address', $row['consignee_address'])}
        {$formObj->getTBRow('Consignee Phone', 'consignee_phone', $row['consignee_phone'])}
        {$formObj->getTBRow('Consignee Contact Person', 'consignee_contact_person', $row['consignee_contact_person'])}
        ";

        $fieldset2 = "
        {$formObj->getDDRowBySQL('Buy Currency', 'buy_currency', $sqlCurrency, $row['buy_currency'], $expVl)}
        {$formObj->getTBRow('Purchase Order Value (Buy Currency)', 'po_sum_buy_price', $row['po_sum_buy_price'], $expNoEdit)}
        {$formObj->getTBRow('Purchase Order Value (GBP)', 'po_sum_base', $row['po_sum_base'], $expNoEdit)}
        {$formObj->getTBRow('Deposit', 'deposit_note', $row['deposit_note'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Purchase Order Header', $fieldset1)}
        {$formObj->getFieldSetWrapped('Values', $fieldset2)}
        {$formObj->getCreationModificationText($row)}
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
        {$displayLinkData->getLinkPortalMain('trading_purchaseOrder', 'trading_productLink', 'Purchase Order Line', $row)}
        ";

        $record_id = $fn->getIssetParam($row, 'purchase_order_id');

        $text = "
        {$links}
        {$media->getRightPanelMediaDisplay('Attachments', 'trading_purchaseOrder', 'attachment', $row)}
        {$comment->getView(array(
             'roomName' => 'trading_purchaseOrder'
            ,'recordId' => $record_id
        ))}
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
              ,so.so_code
              ,po.po_code
              ,poi.buy_unit_price
              ,soi.sell_unit_price
        FROM inventory i
        LEFT JOIN product p ON p.product_id = i.product_id
        LEFT JOIN enquiry e ON e.enquiry_id = i.enquiry_id
        LEFT JOIN sales_order so ON so.sales_order_id = i.sales_order_id
        LEFT JOIN sales_order_items soi ON soi.sales_order_items_id = i.sales_order_items_id
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
            {$listObj->getListDataCell($row['so_code'])}
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
            {$listObj->getListHeaderCell('Sales Order #')}
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