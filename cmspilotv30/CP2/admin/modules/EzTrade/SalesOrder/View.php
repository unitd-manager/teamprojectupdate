<?
class CP_Admin_Modules_EzTrade_SalesOrder_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['so_code'])}
            {$listObj->getListDataCell($row['company_name_customer'])}
            {$listObj->getListDataCell($row['client_so_no'])}
            {$listObj->getListDataCell($row['order_value'])}
            {$listObj->getListDataCell($row['sell_currency'])}
            {$listObj->getListDataCell($row['sales_order_date'])}
            {$listObj->getListDataCell($row['staff_name'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['sales_order_id'])}
            ";
            $count++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Sales Order Number', 'so.so_code')}
        {$listObj->getListHeaderCell('Client Name', 'com.company_name')}
        {$listObj->getListHeaderCell('Client PO Number', 'so.client_so_no')}
        {$listObj->getListHeaderCell('Sales Order Value', 'order_value')}
        {$listObj->getListHeaderCell('Sell Currency', 'so.sell_currency')}
        {$listObj->getListHeaderCell('Order Date', 'so.sales_order_date')}
        {$listObj->getListHeaderCell('Staff', 'so.staff_id')}
        {$listObj->getListHeaderCell('Sales Order Status', 'so.status')}
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

        $sqlCustomerComp = $fn->getDDSql('ezTrade_company', array('condn' => "category = 'Customer'"));
        $expCustComp = array('hideFirstOption' => 1);

        $fieldset = "
        {$formObj->getDDRowBySQL('Client Name', 'company_id_customer', $sqlCustomerComp, '', $expCustComp)}
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

        $expComp = array('displayText' => $row['company_name_customer']);
        $companyText = $fn->getRecordDetailLink('ezTrade_company', 'record_id', $row['company_id_customer'], $expComp);
        $expCustComp = array('detailValue' => $companyText, 'hideFirstOption' => 1);
        $sqlCustomerComp = $fn->getDDSql('ezTrade_company', array('condn' => "category = 'Customer'"));

        $expCustCont = array('detailValue' => $row['contact_name_customer'], 'hideFirstOption' => 1);
        $modContact = getCPModuleObj('ezTrade_contact');
        $sqlCustomerCont = $modContact->model->getContactsByCompanySQL($row['company_id_customer']);

        $expVl = array('sqlType' => 'OneField');

        $expStaff = array('detailValue' => $row['staff_name']);
        $modStaff = getCPModuleObj('core_staff');
        $sqlSalesManager = $modStaff->model->getStaffByGroupSQL();

        $exp = array('isEditable' => 0);

        $sqlCurrency = $fn->getValueListSQL('currency');

        //After contact person in fieldset1
        //$formObj->getDDRowBySQL('Payment Terms', 'payment_terms', $sqlCompPaymentTerm, $row['payment_terms'], $expVl);
        //After sales representative in fieldset1
        //$formObj->getDDRowBySQL('Ship to Location', 'delivery_address_id', $sqlShipToLocation, $row['delivery_address_id'], $expShipToLoc);

        $fieldset1 = "
        {$formObj->getTBRow('Sales Order Number', 'so_code', $row['so_code'], array('isEditable' => 0))}
        {$formObj->getTBRow('Client PO Number', 'client_so_no', $row['client_so_no'])}
        {$formObj->getDDRowByArr('Sales Order Status', 'status', $cpCfg['m.trading.salesOrder.statusArr'], $row['status'])}
        {$formObj->getDDRowBySQL('Client Name', 'company_id_customer', $sqlCustomerComp, $row['company_id_customer'], $expCustComp)}
        {$formObj->getDDRowBySQL('Contact Person', 'contact_id_customer', $sqlCustomerCont, $row['contact_id_customer'], $expCustCont)}
        {$formObj->getDDRowBySQL('Sales Representative', 'staff_id', $sqlSalesManager, $row['staff_id'], $expStaff)}
        {$formObj->getTBRow('Consignee Name', 'consignee_name', $row['consignee_name'])}
        {$formObj->getTBRow('Consignee Address', 'consignee_address', $row['consignee_address'])}
        {$formObj->getPhoneNoRow2('Consignee Phone', 'consignee_phone_country_code', 'consignee_phone_area_code', 'consignee_phone',
                                  $row['consignee_phone_country_code'], $row['consignee_phone_area_code'], $row['consignee_phone'])}
        {$formObj->getTBRow('Consignee Contact Person', 'consignee_contact_person', $row['consignee_contact_person'])}
        {$formObj->getTARow('Notes From Client', 'notes_customer', $row['notes_customer'])}
        {$formObj->getDateRow('Order Date', 'sales_order_date', $row['sales_order_date'])}
        ";

        $fieldset2 = "
        {$formObj->getDDRowBySQL('Sell Currency', 'sell_currency', $sqlCurrency, $row['sell_currency'], $expVl)}
        {$formObj->getTBRow('Sales Order Value', 'order_value', $row['order_value'], $exp)}
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


        $links = "
        {$displayLinkData->getLinkPortalMain('ezTrade_salesOrder', 'ezTrade_productLink', 'Sales Order Line', $row)}
        ";

        $record_id = $fn->getIssetParam($row, 'sales_order_id');

        $text = "
        {$links}
        {$media->getRightPanelMediaDisplay('Attachments', 'ezTrade_salesOrder', 'attachment', $row)}
        {$comment->getView(array(
             'roomName' => 'ezTrade_salesOrder'
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
        $cpCfg = Zend_Registry::get('cpCfg');

        $status       = $fn->getReqParam('status');

        $text = "
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.salesOrder.statusArr'], $status)}
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
            <input type='text' value='{$remaining_quantity}' class='quantities_to_ship' name='quantities_to_ship[{$row['sales_order_items_id']}]' />
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
            {$listObj->getListHeaderCell('Item Number')}
            {$listObj->getListHeaderCell('Item Name')}
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
            {$listObj->getListDataCell($toInvoiceText)}
            {$listObj->getListDataCell($checkboxText)}
            {$listObj->getListRowEnd($row['sales_order_items_id'])}
            ";
            $count++;
        }

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
            {$listObj->getListHeaderCell('Item Number')}
            {$listObj->getListHeaderCell('Item Name')}
            {$listObj->getListHeaderCell('UOM')}
            {$listObj->getListHeaderCell('Order Quantity')}
            {$listObj->getListHeaderCell('Sell Currency')}
            {$listObj->getListHeaderCell('Sell Unit Price')}
            {$listObj->getListHeaderCell('Sell Total Price')}
            {$listObj->getListHeaderCell('Invoiced Amount')}
            {$listObj->getListHeaderCell('Amount To Invoice now')}
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
        JOIN sales_order so          ON (so.sales_order_id = soi.sales_order_id)
        JOIN product p               ON (p.product_id = soi.product_id)
        JOIN quote_items qi          ON (qi.quote_items_id = soi.quote_items_id)
        JOIN quote_request_items qri ON (qri.quote_request_items_id = qi.quote_request_items_id)
        JOIN quote_request qr        ON (qr.quote_request_id = qri.quote_request_id)
        JOIN company c               ON (c.company_id = qr.company_id_supplier)
        WHERE soi.sales_order_id = {$sales_order_id}
        ";

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows();
        $count = 0;

        $rows = '';

        while ($row = $db->sql_fetchrow($result)) {
            $quantityText = "
            <input class='quantity' type='text' value='{$row['quantity']}' name='quantities[{$row['sales_order_items_id']}]' />
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

        $fnMod = getCPModelObj('ezTrade_company');
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
            {$listObj->getListHeaderCell('Item Number')}
            {$listObj->getListHeaderCell('Item Name')}
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


}