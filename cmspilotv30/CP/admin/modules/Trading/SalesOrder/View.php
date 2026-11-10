<?
class CP_Admin_Modules_Trading_SalesOrder_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $count   = 0;
        $rows    = '';

//        {$listObj->getListDataCell($row['enquiry_title'])}
        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['so_code'])}
            {$listObj->getListDataCell($row['company_name_customer'])}
            {$listObj->getListDataCell($row['client_so_no'])}
            {$listObj->getListDataCell($row['order_value'], 'right')}
            {$listObj->getListDataCell($row['sell_currency'])}
            {$listObj->getListDataCell($row['sales_order_date'])}
            {$listObj->getListDataCell($row['staff_name'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($row['order_type'])}
            {$listObj->getListRowEnd($row['sales_order_id'])}
            ";
            $count++;
        }
        $rows = $listObj->getDisplayListRows($rows);

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Sales Order Number', 'so.so_code')}
        {$listObj->getListHeaderCell('Client Name', 'com.company_name')}
        {$listObj->getListHeaderCell('Client Ref.', 'so.client_so_no')}
        {$listObj->getListHeaderCell('Sales Order Value', 'order_value', 'txtRight')}
        {$listObj->getListHeaderCell('Sell Currency', 'so.sell_currency')}
        {$listObj->getListHeaderCell('Order Date', 'so.sales_order_date')}
        {$listObj->getListHeaderCell('Staff', 'so.staff_id')}
        {$listObj->getListHeaderCell('Sales Order Status', 'so.status')}
        {$listObj->getListHeaderCell('Order Type', 'so.order_type')}
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

        $order_type = $fn->getReqParam('order_type');
        $cond = array('condn' => "category = 'Customer'");
        if ($order_type == 'Internal SO') {
            $cond = array('condn' => "category = 'Customer' AND internal_customer = 1");
        }
        $sqlCustomerComp = $fn->getDDSql('trading_company', $cond);
        $expCustComp = array('hideFirstOption' => 1);

        $order_type = $fn->getReqParam('order_type', 'general');

        $fieldset = "
        {$formObj->getDDRowBySQL('Client Name', 'company_id_customer', $sqlCustomerComp, '', $expCustComp)}
        {$formObj->getHiddenFldObj('order_type', $order_type)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Order Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');

        $expComp = array('displayText' => $row['company_name_customer']);
        $companyText = $fn->getRecordDetailLink('trading_company', 'record_id',
                            $row['company_id_customer'], $expComp);
        $expCustComp = array('detailValue' => $companyText, 'hideFirstOption' => 1);

        $sqlCustomerComp = $fn->getDDSql('trading_company', array('condn' => "category = 'Customer'"));

        $expCustCont = array('detailValue' => $row['contact_name_customer'], 'hideFirstOption' => 1);
        $modContact = getCPModuleObj('trading_contact');
        $sqlCustomerCont = $modContact->model->getContactsByCompanySQL($row['company_id_customer']);

        $expHideFO = array('hideFirstOption' => 1);
        $expVl = array('sqlType' => 'OneField');

        $expStaff = array('detailValue' => $row['staff_name']);
        $modStaff = getCPModuleObj('core_staff');
        $sqlSalesManager = $modStaff->model->getStaffByGroupSQL();

        $expNoEdit = array('isEditable' => 0);
        $expNoEditAutoFrmt = array_merge($expNoEdit, array('autoFormat' => 1));

        $sqlCurrency = $fn->getValueListSQL('currency');

        $expPaymentTerms = $fnsModGrp->getTermsParamArr('trading_paymentTermsLink',
                                                        $row['company_id_customer'],
                                                        'fld_payment_terms'
                                                        );
        $expDeliveryTerms = $fnsModGrp->getTermsParamArr('trading_deliveryTermsLink',
                                                        $row['company_id_customer'],
                                                        'fld_delivery_terms'
                                                        );
        $order_value = !$row['enquiry_id'] ?
                       $row['order_value_inventory'] :
                       $row['order_value'];

        $order_value_tax = !$row['enquiry_id'] ?
                           $row['order_value_tax_inventory'] :
                           $row['order_value_tax'];

        $expEnq = array('displayText' => $row['enquiry_code']);
        $enqCodeText = $fn->getRecordDetailLink('trading_enquiry', 'record_id',
                            $row['enquiry_id'], $expEnq);

        $expQuote = array('displayText' => $row['quote_code']);
        $quoteCodeText = $fn->getRecordDetailLink('trading_quote', 'record_id',
                            $row['quote_id'], $expQuote);

        $still_to_bill = $order_value - $row['invoice_amount_sum'];
        $fieldset1 = "
        {$formObj->getTBRow('Sales Order Number', 'so_code', $row['so_code'], array('isEditable' => 0))}
        {$formObj->getTBRow('Client Ref.', 'client_so_no', $row['client_so_no'])}
        {$formObj->getTBRow('Order Type', 'order_type', $row['order_type'], $expNoEdit)}
        {$formObj->getHiddenFldObj('order_type_hid', $row['order_type'])}
        {$formObj->getDDRowByArr('Sales Order Status', 'status', $cpCfg['m.trading.salesOrder.statusArr'], $row['status'])}
        {$formObj->getHiddenFldObj('status_prev', $row['status'])}
        {$formObj->getDDRowBySQL('Client Name', 'company_id_customer', $sqlCustomerComp, $row['company_id_customer'], $expCustComp)}
        {$formObj->getTBRow('Pricing Type', 'pricing_type', $row['pricing_type'], $expNoEdit)}
        {$formObj->getDDRowBySQL('Contact Person', 'contact_id_customer', $sqlCustomerCont, $row['contact_id_customer'], $expCustCont)}
        {$formObj->getTARow('Payment Terms', 'payment_terms', $row['payment_terms'], $expPaymentTerms)}
        {$formObj->getTARow('Delivery Terms', 'delivery_terms', $row['delivery_terms'], $expDeliveryTerms)}
        {$formObj->getTBRow('Consignee Name', 'consignee_name', $row['consignee_name'])}
        {$formObj->getTBRow('Consignee Address', 'consignee_address', $row['consignee_address'])}
        {$formObj->getPhoneNoRow2('Consignee Phone', 'consignee_phone_country_code', 'consignee_phone_area_code', 'consignee_phone',
                                  $row['consignee_phone_country_code'], $row['consignee_phone_area_code'], $row['consignee_phone'])}
        {$formObj->getTBRow('Consignee Contact Person', 'consignee_contact_person', $row['consignee_contact_person'])}
        {$formObj->getTARow('Notes', 'notes_customer', $row['notes_customer'])}
        {$formObj->getDateRow('Order Date', 'sales_order_date', $row['sales_order_date'])}
        {$formObj->getTBRow('Enquiry Code', 'enquiry_code', $enqCodeText, $expNoEdit)}
        {$formObj->getTBRow('Quote Code', 'quote_code', $quoteCodeText, $expNoEdit)}
        {$formObj->getHiddenFldObj('enquiry_id', $row['enquiry_id'])}

        ";

        $fieldset2 = "
        {$formObj->getDDRowBySQL('Sell Currency', 'sell_currency', $sqlCurrency, $row['sell_currency'], $expVl)}
        {$formObj->getTBRow('Sales Order Value', 'order_value', $order_value, $expNoEdit)}
        {$formObj->getTBRow('VAT %', 'tax_percentage', $row['tax_percentage'])}
        {$formObj->getTBRow('Sales Order Value incl. VAT', 'order_value_tax', $order_value_tax, $expNoEditAutoFrmt)}
        {$formObj->getTBRow('Billed Invoices ex. VAT', 'invoice_amount_sum', $row['invoice_amount_sum'], $expNoEditAutoFrmt)}
        {$formObj->getTBRow('Still to Bill ex. VAT', 'still_to_bill', $still_to_bill, $expNoEditAutoFrmt)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Sales Order Header', $fieldset1)}
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


        $links = '';

        $record_id = $fn->getIssetParam($row, 'sales_order_id');
        $rowSO = $fn->getRecordRowByID('sales_order', 'sales_order_id', $record_id);

        $url = '';
        $updateFromQuote = '';

        //SO created from SO itself not from Enquiry process
        if ($rowSO['order_type'] == 'Internal SO') {
            $links = "
            {$displayLinkData->getLinkPortalMain('trading_salesOrder',
                                                 'trading_productLink', 'Sales Order Line', $row)}
            ";
        } else {
            if (!$row['enquiry_id']) {
                $links = "
                {$displayLinkData->getLinkPortalMain('trading_salesOrder',
                                   'trading_inventoryLink', 'Sales Order Line From Inventory', $row)}
                ";
            } else {
                $links = "
                {$displayLinkData->getLinkPortalMain('trading_salesOrder',
                                                     'trading_productLink', 'Sales Order Line', $row)}
                ";
                $updateFromQuote = "
                <div class='float_right'>
                <a id='updateSellPriceFromQuote' href='#' sales_order_id='{$row['sales_order_id']}'>
                    Update Sell Price From quote
                </a>
                </div>
                ";
            }
        }

        $showInventory = "
        <div class='float_right'>
        <a id='showInventory' href='#' sales_order_id='{$row['sales_order_id']}'>Show Inventory</a>
        </div>
        ";

        $text = "
        <div class='floatbox'>
            {$showInventory}
            {$updateFromQuote}
        </div>
        {$links}
        {$displayLinkData->getLinkPortalMain('trading_salesOrder',
                                             'trading_invoiceLink', 'Invoice', $row)}
        {$displayLinkData->getLinkPortalMain('trading_salesOrder',
                                             'trading_purchaseOrderLink', 'Purchase Order', $row)}
        {$media->getRightPanelMediaDisplay('Attachments', 'trading_salesOrder', 'attachment', $row)}
        {$comment->getView(array(
             'roomName' => 'trading_salesOrder'
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
        $order_type = $fn->getReqParam('order_type');
        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $text = "
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.salesOrder.statusArr'], $status)}
            </select>
        </td>
        <td>
            <select name='order_type'>
                <option value=''>Order Type</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.salesOrder.orderTypeArr'], $order_type)}

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

    /**
     *
     */
    function getChooseSupplierForProduct(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $company_id_supplier = $fn->getReqParam('company_id_supplier');
        $sales_order_id      = $fn->getReqParam('sales_order_id');
        $product_id          = $fn->getReqParam('product_id');

        $SQLCheckHistoryAnyRec = "
        SELECT soi.sales_order_items_id
        FROM sales_order_items soi
        WHERE soi.sales_order_id = {$sales_order_id}
          AND soi.product_id = {$product_id}
          AND soi.company_id_supplier > 0
        ";
        $result = $db->sql_query($SQLCheckHistoryAnyRec);
        $numRowsAnySupplier = $db->sql_numrows($result);

        $SQLCheckHistoryCurrSupp = "
        SELECT soi.sales_order_items_id
        FROM sales_order_items soi
        WHERE soi.sales_order_id = {$sales_order_id}
          AND soi.product_id = {$product_id}
          AND soi.company_id_supplier = {$company_id_supplier}
        ";
        $result = $db->sql_query($SQLCheckHistoryCurrSupp);
        $numRowsCurrSupplier = $db->sql_numrows($result);

        $SQL = "
        SELECT soi.sales_order_items_id
        FROM sales_order_items soi
        WHERE soi.sales_order_id = {$sales_order_id}
          AND soi.product_id = {$product_id}
        ";
        $result = $db->sql_query($SQL);
        $numRowsHist = $db->sql_numrows($result);

        if ($numRowsHist == 1 && $numRowsAnySupplier == 0 && $numRowsCurrSupplier == 0) { //first record with no supplier yet
            $SQL = "
            UPDATE sales_order_items soi
            SET soi.company_id_supplier = {$company_id_supplier}
            WHERE soi.sales_order_id = {$sales_order_id}
              AND soi.product_id = {$product_id}
            ";
            $db->sql_query($SQL);

        } else if ($numRowsHist == 1 && $numRowsAnySupplier > 0 && $numRowsCurrSupplier > 0) { //first record with current supplier
            $SQL = "
            UPDATE sales_order_items soi
            SET soi.company_id_supplier = 0
            WHERE soi.sales_order_id = {$sales_order_id}
              AND soi.product_id = {$product_id}
            ";
            $db->sql_query($SQL);

        } else if ($numRowsHist == 1 && $numRowsCurrSupplier == 0) { //second new supplier record
            $fa = array();
            $fa['sales_order_id']      = $sales_order_id;
            $fa['product_id']          = $product_id;
            $fa['company_id_supplier'] = $company_id_supplier;
            $fa['creation_date']       = date('Y-m-d H:i:s');
            $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'sales_order_items');
            $result = $db->sql_query($SQL);

        } else if ($numRowsHist > 1) { //more records but no current supplier
            if ($numRowsCurrSupplier > 0) { //company_id_supplier already set just make it 0 (not to delete the history rec)
                $SQL = "
                DELETE FROM sales_order_items
                WHERE sales_order_id = {$sales_order_id}
                  AND product_id = {$product_id}
                  AND company_id_supplier = {$company_id_supplier}
                ";
                $db->sql_query($SQL);
            } else {
                $fa = array();
                $fa['sales_order_id']      = $sales_order_id;
                $fa['product_id']          = $product_id;
                $fa['company_id_supplier'] = $company_id_supplier;
                $fa['creation_date']       = date('Y-m-d H:i:s');
                $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'sales_order_items');
                $result = $db->sql_query($SQL);
            }
        }

    }

    /**
     *
     */
    function getRaiseShipmentList() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $listObj = Zend_Registry::get('listObj');

        $sales_order_id = $fn->getReqParam('sales_order_id');

        $SQL = "
        SELECT soi.sales_order_items_id
              ,p.product_id
              ,p.product_code
              ,p.title AS product_name
              ,soi.quantity AS quantity_ordered
              ,p.unit
              ,(SELECT SUM(si.quantity_shipped)
                FROM shipment_items si
                WHERE si.sales_order_items_id = soi.sales_order_items_id) AS quantity_shipped
              ,soi.sell_unit_price_base
              ,soi.sell_unit_price_base * soi.quantity AS sell_price_base
              ,soi.remarks
        FROM sales_order_items soi
        JOIN product p ON (p.product_id = soi.product_id)
        WHERE soi.sales_order_id = {$sales_order_id}
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows();
        $count = 0;

        $rows = '';

        while ($row = $db->sql_fetchrow($result)) {
            $remaining_quantity = $row['quantity_ordered'] - $row['quantity_shipped'];
            $quantityToShipText = "
            <input type='text' value='{$remaining_quantity}' class='quantities_to_ship w65'
                   name='quantities_to_ship[{$row['sales_order_items_id']}]' />
            ";

            $checkboxText = "
            <input class='choose' type='checkbox' value='{$row['sales_order_items_id']}'
                   name='sales_order_items_ids[]' checked='checked'/>
            ";

            // $scheduleShipText = "
            // <input class='choose' type='checkbox' value='{$row['product_id']}'
            //        name='schedule_ship[]'/>
            // ";

            $exp = array('hasFlagInList' => 0, 'hasEditInList' => false, 'keyFieldValue' => $row['sales_order_items_id']);
            $rows .= "
            {$listObj->getListRowHeader($row, $count, '', $exp)}
            {$listObj->getListDataCell($row['product_code'])}
            {$listObj->getListDataCell($row['product_name'])}
            {$listObj->getListDataCell($row['unit'])}
            {$listObj->getListDataCell($row['quantity_ordered'])}
            {$listObj->getListDataCell($row['quantity_shipped'])}
            {$listObj->getListDataCell($quantityToShipText)}
            {$listObj->getListDataCell($checkboxText)}
            {$listObj->getListRowEnd($row['sales_order_items_id'])}
            ";

            $count++;
        }

        $raiseShipmentBtn = "
        <form class='yform'>
            <div class='type-button float_right'>
            <input type='reset' value='Cancel' id='btnRaiseShipmentCancel' />
            <input type='button' value='Raise Shipment' id='btnRaiseShipment' />
            </div>
        </form>
        ";

        $exp = array('hasFlagInList' => 0, 'hasEditInList' => false);
        $text = "
        <div id='raiseList'>
            {$raiseShipmentBtn}
            {$listObj->getListHeader($exp)}
            {$listObj->getListHeaderCell('Product Code')}
            {$listObj->getListHeaderCell('Product Name')}
            {$listObj->getListHeaderCell('UOM')}
            {$listObj->getListHeaderCell('Order Quantity')}
            {$listObj->getListHeaderCell('Shipped Quantity')}
            {$listObj->getListHeaderCell('Quantity To Ship Now')}
            {$listObj->getListHeaderCell('Choose')}
            {$listObj->getListHeaderEnd()}
            {$rows}
            {$listObj->getListFooter()}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getRaiseInvoiceList() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $listObj = Zend_Registry::get('listObj');

        $sales_order_id = $fn->getReqParam('sales_order_id');

        $SQL = "
        SELECT soi.sales_order_items_id
              ,p.product_id
              ,CONCAT_WS('-', so.so_code, soi.line_no) AS line_no
              ,p.product_code
              ,p.title AS product_name
              ,p.unit
              ,so.sell_currency
              ,soi.quantity AS quantity_ordered
              ,soi.sell_unit_price
              ,soi.sell_unit_price * soi.quantity AS sell_price
              ,(SELECT SUM(ii.sell_price)
                FROM invoice_items ii
                WHERE ii.sales_order_items_id = soi.sales_order_items_id) AS sell_price_invoiced
              ,soi.sell_unit_price_base
              ,soi.sell_unit_price_base * soi.quantity AS sell_price_base
              ,soi.remarks
        FROM sales_order_items soi
        JOIN sales_order so ON (so.sales_order_id = soi.sales_order_id)
        JOIN product p ON (p.product_id = soi.product_id)
        WHERE soi.sales_order_id = {$sales_order_id}
        ";
        //  AND soi.sales_order_items_id NOT IN (
        //     SELECT ii.sales_order_items_id
        //     FROM invoice_items ii
        //     JOIN invoice i ON i.invoice_id = ii.invoice_id
        //     WHERE ii.sales_order_items_id = soi.sales_order_items_id
        //       AND i.status != 'cancelled'
        //  )

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows();
        $count = 0;

        $rows = '';

        while ($row = $db->sql_fetchrow($result)) {
            $sell_price_to_invoice = $row['sell_price'] - $row['sell_price_invoiced'];
            $toInvoiceText = "
            <input type='text' value='{$sell_price_to_invoice}' name='sell_price_to_invoice_{$row['sales_order_items_id']}'  />
            ";

            $checkboxText = "
            <input class='choose' type='checkbox' value='{$row['sales_order_items_id']}'
                   name='sales_order_items_ids[]' checked='checked'/>
            ";

            $exp = array('hasFlagInList' => 0, 'hasEditInList' => false
                        ,'keyFieldValue' => $row['sales_order_items_id'], 'hasRowNumber' => false);
            $rows .= "
            {$listObj->getListRowHeader($row, $count, '', $exp)}
            {$listObj->getListDataCell($row['line_no'])}
            {$listObj->getListDataCell($row['product_code'])}
            {$listObj->getListDataCell($row['product_name'])}
            {$listObj->getListDataCell($row['unit'])}
            {$listObj->getListDataCell($row['quantity_ordered'])}
            {$listObj->getListDataCell($row['sell_currency'])}
            {$listObj->getListDataCell($row['sell_unit_price'])}
            {$listObj->getListDataCell($row['sell_price'])}
            {$listObj->getListDataCell($row['sell_price_invoiced'])}
            {$listObj->getListDataCell($checkboxText)}
            {$listObj->getListRowEnd($row['sales_order_items_id'])}
            ";
            $count++;
        }

        $message = "There are no un-invoiced items to invoice";
        $rows = $listObj->getDisplayListRows($rows, $message);

        $raiseBtn = "
        <form class='yform'>
            <div class='type-button float_right'>
            <input type='reset' value='Cancel' id='btnRaiseInvoiceCancel' />
            <input type='button' value='Raise Invoice' id='btnRaiseInvoice' />
            </div>
        </form>
        ";

        $exp = array('hasFlagInList' => 0, 'hasEditInList' => false, 'hasRowNumber' => false);
        $text = "
        <div id='raiseList'>
            {$raiseBtn}
            {$listObj->getListHeader($exp)}
            {$listObj->getListHeaderCell('Sales Order Line No.')}
            {$listObj->getListHeaderCell('Product Code')}
            {$listObj->getListHeaderCell('Product Name')}
            {$listObj->getListHeaderCell('UOM')}
            {$listObj->getListHeaderCell('Order Quantity')}
            {$listObj->getListHeaderCell('Sell Currency')}
            {$listObj->getListHeaderCell('Sell Unit Price')}
            {$listObj->getListHeaderCell('Sell Total Price')}
            {$listObj->getListHeaderCell('Invoiced Amount')}
            {$listObj->getListHeaderCell('')}
            {$listObj->getListHeaderEnd()}
            {$rows}
            {$listObj->getListFooter()}
        </div>
        ";
        return $text;
    }

    /**
     * Raise invoice for invetory items. The SO is not created through Enquiry process
     *
     */
    function getRaiseInvoiceListInventory() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $listObj = Zend_Registry::get('listObj');

        $sales_order_id = $fn->getReqParam('sales_order_id');

        $SQL = "
        SELECT soi.sales_order_inventory_id
              ,p.product_id
              ,p.product_code
              ,p.title AS product_name
              ,p.unit
              ,so.sell_currency
              ,i.serial_no
              ,i.status
              ,i.location
              ,i.retail_unit_price
              ,i.retail_unit_price_discount
        FROM sales_order_inventory soi
        JOIN inventory i ON i.inventory_id = soi.inventory_id
        JOIN sales_order so ON so.sales_order_id = soi.sales_order_id
        JOIN product p ON p.product_id = i.product_id
        WHERE soi.sales_order_id = {$sales_order_id}
        ";

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows();
        $count = 0;

        $rows = '';

        while ($row = $db->sql_fetchrow($result)) {
            $checkboxText = "
            <input class='choose' type='checkbox' value='{$row['sales_order_inventory_id']}'
                   name='sales_order_inventory_ids[]' checked='checked'/>
            ";

            $exp = array('hasFlagInList' => 0, 'hasEditInList' => false
                        ,'keyFieldValue' => $row['sales_order_inventory_id'], 'hasRowNumber' => false);
            $rows .= "
            {$listObj->getListRowHeader($row, $count, '', $exp)}
            {$listObj->getListDataCell($row['product_code'])}
            {$listObj->getListDataCell($row['product_name'])}
            {$listObj->getListDataCell($row['serial_no'])}
            {$listObj->getListDataCell($row['unit'])}
            {$listObj->getListDataCell($row['sell_currency'])}
            {$listObj->getListDataCell($row['retail_unit_price'])}
            {$listObj->getListDataCell($row['retail_unit_price_discount'])}
            {$listObj->getListDataCell($checkboxText)}
            {$listObj->getListRowEnd($row['sales_order_inventory_id'])}
            ";
            $count++;
        }

        $message = "There are no un-invoiced items to invoice";
        $rows = $listObj->getDisplayListRows($rows, $message);

        $raiseBtn = "
        <form class='yform'>
            <div class='type-button float_right'>
            <input type='reset' value='Cancel' id='btnRaiseInvoiceCancel' />
            <input type='button' value='Raise Invoice' id='btnRaiseInvoice' />
            </div>
        </form>
        ";

        $exp = array('hasFlagInList' => 0, 'hasEditInList' => false, 'hasRowNumber' => false);
        $text = "
        <div id='raiseList'>
            {$raiseBtn}
            {$listObj->getListHeader($exp)}
            {$listObj->getListHeaderCell('Product Code')}
            {$listObj->getListHeaderCell('Product Name')}
            {$listObj->getListHeaderCell('Inventory Serial')}
            {$listObj->getListHeaderCell('UOM')}
            {$listObj->getListHeaderCell('Sell Currency')}
            {$listObj->getListHeaderCell('Retail Price')}
            {$listObj->getListHeaderCell('Retail Price (Discounted)')}
            {$listObj->getListHeaderCell('')}
            {$listObj->getListHeaderEnd()}
            {$rows}
            {$listObj->getListFooter()}
        </div>
        ";
        return $text;
    }

    /**
     *
     */
    function getRaisePOList() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $listObj = Zend_Registry::get('listObj');
        $formObj = Zend_Registry::get('formObj');

        $sales_order_id = $fn->getReqParam('sales_order_id');
        $rowSO = $fn->getRecordRowByID('sales_order', 'sales_order_id', $sales_order_id);

        if ($rowSO['order_type'] == 'Internal SO') {
            $SQL = "
            SELECT soi.sales_order_items_id
                  ,CONCAT_WS('-', so.so_code, soi.line_no) AS line_no
                  ,p.product_id
                  ,p.product_code
                  ,p.title AS product_name
                  ,p.unit
                  ,so.sell_currency
                  ,soi.quantity
                  ,soi.sell_unit_price
                  ,qr.buy_currency
                  ,qri.buy_unit_price
                  ,soi.remarks
                  ,soi.status
                  ,qr.company_id_supplier
                  ,c.company_name AS company_name_supplier
            FROM sales_order_items soi
            JOIN sales_order so ON so.sales_order_id = soi.sales_order_id
            LEFT JOIN product p ON p.product_id = soi.product_id
            LEFT JOIN quote_request_items qri ON qri.quote_request_items_id = p.quote_request_items_id
            LEFT JOIN quote_request qr ON qr.quote_request_id = qri.quote_request_id
            LEFT JOIN company c ON c.company_id = qr.company_id_supplier
            WHERE soi.sales_order_id = {$sales_order_id}
            ";
        } else {
            $SQL = "
            SELECT soi.sales_order_items_id
                  ,CONCAT_WS('-', so.so_code, soi.line_no) AS line_no
                  ,p.product_id
                  ,p.product_code
                  ,p.title AS product_name
                  ,p.unit
                  ,so.sell_currency
                  ,soi.quantity
                  ,soi.sell_unit_price
                  ,qr.buy_currency
                  ,soi.buy_unit_price
                  ,soi.remarks
                  ,soi.status
                  ,qr.company_id_supplier
                  ,c.company_name AS company_name_supplier
            FROM sales_order_items soi
            JOIN sales_order so ON so.sales_order_id = soi.sales_order_id
            LEFT JOIN product p ON p.product_id = soi.product_id
            LEFT JOIN quote_items qi ON qi.quote_items_id = soi.quote_items_id
            LEFT JOIN quote_request_items qri ON qri.quote_request_items_id = soi.quote_request_items_id
            LEFT JOIN quote_request qr ON qr.quote_request_id = qri.quote_request_id
            LEFT JOIN company c ON c.company_id = qr.company_id_supplier
            WHERE soi.sales_order_id = {$sales_order_id}
            ";
        }

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows();
        $count = 0;

        $rows = '';

        while ($row = $db->sql_fetchrow($result)) {
            $quantityText = "
            <input class='quantity w65' type='text' value='{$row['quantity']}' name='quantities[{$row['sales_order_items_id']}]' />
            ";
            $checkboxText = "
            <input class='choose' type='checkbox' value='{$row['sales_order_items_id']}' name='sales_order_items_ids[]' />
            ";

            $exp = array('hasFlagInList' => false
                        ,'keyFieldValue' => $row['sales_order_items_id']
                        ,'hasEditInList' => false
                        ,'hasRowNumber' => false
                   );
            $rows .= "
            {$listObj->getListRowHeader($row, $count, '', $exp)}
            {$listObj->getListDataCell($row['line_no'])}
            {$listObj->getListDataCell($row['product_code'])}
            {$listObj->getListDataCell($row['product_name'])}
            {$listObj->getListDataCell($row['unit'])}
            {$listObj->getListDataCell($row['company_name_supplier'])}
            {$listObj->getListDataCell($row['buy_currency'])}
            {$listObj->getListDataCell($row['buy_unit_price'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($quantityText)}
            {$listObj->getListDataCell($checkboxText)}
            {$listObj->getListRowEnd($row['sales_order_items_id'])}
            ";

            $count++;
        }

        $raiseBtn = "
        <form class='yform'>
            <div class='type-button float_right'>
            <input type='reset' value='Cancel' id='btnRaisePOCancel' />
            <input type='button' value='Raise PO' id='btnRaisePO' />
            </div>
        </form>
        ";

        $fnMod = getCPModelObj('trading_company');
        $sqlSupplier = $fnMod->getSupplierSQL();

        $exp = array('hasEditInList' => false
                    ,'hasRowNumber' => false
                    ,'hasFlagInList' => false
               );
        $text = "
        <div id='raiseList'>
            {$raiseBtn}
            {$listObj->getListHeader($exp)}
            {$listObj->getListHeaderCell('Line #')}
            {$listObj->getListHeaderCell('Product Code')}
            {$listObj->getListHeaderCell('Product Name')}
            {$listObj->getListHeaderCell('UOM')}
            {$listObj->getListHeaderCell('Supplier')}
            {$listObj->getListHeaderCell('Buy Currency')}
            {$listObj->getListHeaderCell('Buy Unit Price')}
            {$listObj->getListHeaderCell('SO Line Status')}
            {$listObj->getListHeaderCell('Quantity')}
            {$listObj->getListHeaderCell('')}
            {$listObj->getListHeaderEnd()}
            {$rows}
            {$listObj->getListFooter()}
        </div>
        ";

        return $text;
    }

    function getChooseInventoryLinkValidation() {
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');

        $enquiry_id = $fn->getReqParam('enquiry_id');
        $rowEnquiry = $fn->getRecordRowByID('enquiry', 'enquiry_id', $enquiry_id);

        $status = 'success';
        $errMsg = '';
        if ($rowEnquiry['status'] == 'cancelled') {
            $status = 'error';
            $errMsg = 'Enquiry is cancelled';
        }

        return $cpUtil->getJsonText($status, '', $errMsg);
    }


    function getEditInventoryForm() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $listObj = Zend_Registry::get('listObj');
        $formObj = Zend_Registry::get('formObj');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $sales_order_id = $fn->getReqParam('sales_order_id');
        $rowSO = $fn->getRecordRowByID('sales_order', 'sales_order_id', $sales_order_id);

        //if normal enquiry process flow
        if ($rowSO['enquiry_id']) {
            $SQL = "
            SELECT DISTINCT
                   i.*
                  ,p.product_code
                  ,p.title product_name
                  ,p.collection_name
                  ,so.so_code
            FROM inventory i
            JOIN product p ON p.product_id = i.product_id
            JOIN sales_order so ON so.sales_order_id = i.sales_order_id
            WHERE so.sales_order_id = {$sales_order_id}
            ORDER BY i.product_id
                    ,i.serial_no
            ";
        } else if ($rowSO['order_type'] == 'Internal SO') {
            $SQL = "
            SELECT DISTINCT
                   i.*
                  ,p.product_code
                  ,p.title product_name
                  ,p.collection_name
                  ,so.so_code
            FROM inventory i
            JOIN product p ON p.product_id = i.product_id
            JOIN sales_order so ON so.sales_order_id = i.sales_order_id
            WHERE so.sales_order_id = {$sales_order_id}
            ORDER BY i.product_id
                    ,i.serial_no
            ";
        } else { //SO created directly
            $SQL = "
            SELECT DISTINCT
                   i.*
                  ,p.product_code
                  ,p.title product_name
                  ,p.collection_name
                  ,so.so_code
            FROM inventory i
            JOIN product p ON p.product_id = i.product_id
            JOIN sales_order so ON so.sales_order_id = i.sales_order_id_inventory
            WHERE so.sales_order_id = {$sales_order_id}
            ORDER BY i.product_id
                    ,i.serial_no
            ";
        }


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
            <select name='location[{$row['inventory_id']}]' class='location'>
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
        <td colspan='5'></td>

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
            {$listObj->getListHeaderCell('Status')}
            {$listObj->getListHeaderCell('Location')}
            {$listObj->getListHeaderCell('Creation Date')}
            {$listObj->getListHeaderEnd()}
            {$rowSummary}
            {$rows}
            {$listObj->getListFooter()}
            {$formObj->getHiddenFldObj('sales_order_id', $sales_order_id)}
        </div>
        ";

        return $text;
    }

}