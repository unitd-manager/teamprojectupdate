<?
class CP_Admin_Modules_Trading_Quote_View extends CP_Common_Lib_ModuleViewAbstract
{
    //========================================================//
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        $mediaArray = Zend_Registry::get('mediaArray');
        $dateUtil = Zend_Registry::get('dateUtil');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            $expEnquiry = array('displayText' => $row['enquiry_code']);
            $enquiry_code = $fn->getRecordDetailLink('trading_enquiry', 'record_id', $row['enquiry_id'], $expEnquiry);

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['quote_code'])}
            {$listObj->getListDataCell($enquiry_code)}
            {$listObj->getListDataCell($row['customer_company_name'])}
            {$listObj->getListDataCell($row['customer_contact'])}
            {$listObj->getListDataCell($row['subject'])}
            {$listObj->getListDateCell($row['quote_date'])}
            {$listObj->getListDataCell($row['staff_name'])}
            {$listObj->getListDataCell($row['sales_agent'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['enquiry_id'])}
            ";
            $count++;
        }
        $rows = $listObj->getDisplayListRows($rows);

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Quote Number', 'q.quote_code')}
        {$listObj->getListHeaderCell('Enquiry Number', 'enquiry_code')}
        {$listObj->getListHeaderCell('Client Name', 'customer_company_name')}
        {$listObj->getListHeaderCell('Contact person', 'customer_contact')}
        {$listObj->getListHeaderCell('Enquiry Title', 'e.subject')}
        {$listObj->getListHeaderCell('Quote Date', 'q.quote_date')}
        {$listObj->getListHeaderCell('Staff', 'staff_name')}
        {$listObj->getListHeaderCell('Sales Agent', 'sales_agent')}
        {$listObj->getListHeaderCell('Quote Status', 'q.status')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    //==================================================================//
    function getEdit($row){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $ln = Zend_Registry::get('ln');
        $am = Zend_Registry::get('am');

        $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');

        $formObj->mode = $tv['action'];
        $modContact = getCPModuleObj('trading_contact');
        $sqlCustomerContact = $modContact->model->getContactsByCompanySQL($row['company_id_customer']);

        $modContact = getCPModuleObj('core_staff');
        $sqlSalesManager = $modContact->model->getStaffByGroupSQL();

        $fnsModQuote = includeCPClass('ModuleFns', 'trading_quote');

        $sqlShipToCountry = $fn->getValueListSQL('shipToCountry');
        $sqlCurrency      = $fn->getValueListSQL('currency');

        $expVl      = array('sqlType' => 'OneField');
        $expContact = array('detailValue' => $row['customer_contact']);
        $expEnquiry = array('displayText' => $row['enquiry_code']);
        $expShipToLoc = array('detailValue' => $row['ship_to_location']);
        $expNoEdit  = array('isEditable' => 0);
        $expStaff   = array('detailValue' => $row['staff_name']);

        $enquiryText = $fn->getRecordDetailLink('trading_enquiry', 'record_id', $row['enquiry_id'], $expEnquiry);

        $expComp = array('displayText' => $row['customer_company_name']);
        $companyText = $fn->getRecordDetailLink('trading_company', 'record_id',
                            $row['company_id_customer'], $expComp);

        $expSO = array('displayText' => $row['so_code']);
        $soText = $fn->getRecordDetailLink('trading_salesOrder', 'record_id',
                                           $row['sales_order_id'], $expSO);

        $expSalesAgent = array('displayText' => $row['sales_agent']);
        $salesAgentText = $fn->getRecordDetailLink('trading_company', 'record_id',
                               $row['company_id_sales_agent'], $expSalesAgent);

        $expPaymentTerms = $fnsModGrp->getTermsParamArr('trading_paymentTermsLink',
                                                        $row['company_id_customer'],
                                                        'fld_payment_terms_customer'
                                                        );
        $expDeliveryTerms = $fnsModGrp->getTermsParamArr('trading_deliveryTermsLink',
                                                        $row['company_id_customer'],
                                                        'fld_delivery_terms'
                                                        );

        $expShipToLoc = array('detailValue' => $row['ship_to_location']);
        $modDeliveryAddress = getCPModuleObj('trading_deliveryAddressLink');
        $sqlShipToLocation = $modDeliveryAddress->model->getShipToLocationSQL($row['company_id_customer']);

        //{$formObj->getDDRowByArr('Shipping Method', 'shipping_method',
        //           $cpCfg['m.trading.shippingMethodArr'], $row['shipping_method'])}
        $fieldset1 = "
        {$formObj->getTBRow('Quotation Number', 'quote_code', $row['quote_code'], $expNoEdit)}
        {$formObj->getTBRow('Enquiry Number', 'enquiry_code', $enquiryText, $expNoEdit)}
        {$formObj->getTBRow('Client Name', 'customer_company_name', $companyText, $expNoEdit)}
        {$formObj->getDDRowBySQL('Contact Person', 'contact_id_customer', $sqlCustomerContact, $row['contact_id_customer'], $expContact)}
        {$formObj->getTBRow('Client Ref.', 'customer_rfq_code', $row['customer_rfq_code'], $expNoEdit)}
        {$formObj->getTBRow('Type of Pricing', 'pricing_type', $row['pricing_type'], $expNoEdit)}
        {$formObj->getTBRow('Enquiry Title', 'title', $row['subject'], $expNoEdit)}
        {$formObj->getDateRow('Quote Date', 'quote_date', $row['quote_date'])}
        {$formObj->getDDRowBySQL('Sell Currency', 'sell_currency', $sqlCurrency, $row['sell_currency'], $expVl)}
        {$formObj->getTARow('Payment Terms', 'payment_terms_customer', $row['payment_terms_customer'], $expPaymentTerms)}
        {$formObj->getTARow('Delivery Terms', 'delivery_terms', $row['delivery_terms'], $expDeliveryTerms)}
        {$formObj->getDDRowByArr('Quote Status', 'status', $cpCfg['m.trading.quote.statusArr'],
                                 $row['status'])}
        {$formObj->getDateRow('Required Response Date', 'follow_up_date', $row['follow_up_date'])}
        {$formObj->getTBRow('VAT %', 'tax_percentage', $row['tax_percentage'])}
        {$formObj->getDDRowBySQL('Staff Member Responsible', 'staff_id', $sqlSalesManager,
                                 $row['staff_id'], $expStaff)}
        {$formObj->getTextAreaRow('Note to client', 'notes_customer', $row['notes_customer'])}
        {$formObj->getTBRow('Sales Order Number', 'so_code', $soText, $expNoEdit)}
        ";

        $fieldset2 = "
        {$formObj->getDDRowBySQL('Ship to Location', 'delivery_address_id', $sqlShipToLocation, $row['delivery_address_id'], $expShipToLoc)}
        ";

        $extraText = '';

        if ($tv['action'] == 'detail') {
            $extraText = "
            <input type='hidden' id='status' value='{$row['status']}' />
            ";
        }

        $text = "
        {$formObj->getFieldSetWrapped('Quote Header', $fieldset1)}
        {$formObj->getFieldSetWrapped('Shipping', $fieldset2)}
        {$formObj->getCreationModificationText($row)}
        {$extraText}
        ";

        return $text;
    }

    //==================================================================//
    function getRightPanel($row){
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $comment = getCPPluginObj('common_comment');

        //{$displayLinkData->getLinkPortalMain("trading_quote", "trading_inventoryLink", "Quote Line (Inventory)", $row)}
        $links = "
        {$displayLinkData->getLinkPortalMain("trading_quote", "trading_productLink", "Quote Line", $row)}
        ";

        $record_id = $fn->getIssetParam($row, 'quote_id');

        $text = "
        {$links}
        {$media->getRightPanelMediaDisplay("Attachments", "trading_quote", "attachment", $row)}
        {$comment->getView(array(
             'roomName' => 'trading_quote'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;
    }

    //==================================================================//
    function getSaveList(){
        $fn = Zend_Registry::get('fn');
        $fn->getSaveList();
    }

    /**
     *
     */
    function getRaiseSOList() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $listObj = Zend_Registry::get('listObj');
        $formObj = Zend_Registry::get('formObj');

        $quote_id = $fn->getReqParam('quote_id');

        $SQL = "
        SELECT qi.quote_items_id
              ,CONCAT_WS('-', q.quote_code, qi.line_no) AS line_no
              ,p.product_code
              ,p.product_id
              ,p.title AS product_name
              ,p.unit
              ,IF(qi.record_type = 'product', qr.buy_currency, po.buy_currency) AS buy_currency
              ,qi.quantity
              ,qi.buy_unit_price
              ,qi.sell_unit_price
              ,qi.valid_until
              ,qi.status
              ,q.quote_id
              ,q.sell_currency
        FROM quote_items qi
        JOIN quote q   ON (q.quote_id = qi.quote_id)
        JOIN product p ON (p.product_id = qi.product_id)
        LEFT JOIN quote_request_items qri ON (qri.quote_request_items_id = qi.quote_request_items_id)
        LEFT JOIN quote_request qr ON (qr.quote_request_id = qri.quote_request_id)
        LEFT JOIN purchase_order_items poi ON (poi.purchase_order_items_id = qi.purchase_order_items_id)
        LEFT JOIN purchase_order po ON (po.purchase_order_id = poi.purchase_order_id)
        WHERE qi.quote_id = {$quote_id}
        ORDER BY qi.line_no
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows();
        $count = 0;

        $rows = '';

        while ($row = $db->sql_fetchrow($result)) {
            $quantityText = "
            <input class='quantity w65' type='text' value='{$row['quantity']}'
                   name='quantity__{$row['quote_items_id']}' />
            ";
            $checkboxText = "
            <input class='choose' type='checkbox' value='{$row['quote_items_id']}'
                   name='quote_items_ids[]' checked='checked' />
            ";

            $exp = array('hasFlagInList' => false
                        ,'keyFieldValue' => $row['quote_items_id']
                        ,'hasEditInList' => false
                        ,'hasRowNumber' => false
                   );
            $rows .= "
            {$listObj->getListRowHeader($row, $count, '', $exp)}
            {$listObj->getListDataCell($row['line_no'])}
            {$listObj->getListDataCell($row['product_code'])}
            {$listObj->getListDataCell($row['product_name'])}
            {$listObj->getListDataCell($row['quantity'])}
            {$listObj->getListDataCell($row['unit'])}
            {$listObj->getListDataCell($row['buy_currency'])}
            {$listObj->getListDataCell($row['buy_unit_price'])}
            {$listObj->getListDataCell($row['sell_currency'])}
            {$listObj->getListDataCell($row['sell_unit_price'])}
            {$listObj->getListDataCell($row['valid_until'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($quantityText)}
            {$listObj->getListDataCell($checkboxText)}
            {$listObj->getListRowEnd($row['quote_items_id'])}
            ";

            $count++;
        }

        $raiseBtn = "
        <form class='yform'>
            <div class='type-button float_right'>
            <input type='reset' value='Cancel' id='btnRaiseSOCancel' />
            <input type='button' value='Raise SO' id='btnRaiseSO' />
            </div>
        </form>
        ";

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
            {$listObj->getListHeaderCell('Quantity')}
            {$listObj->getListHeaderCell('UOM')}
            {$listObj->getListHeaderCell('Buy Currency')}
            {$listObj->getListHeaderCell('Buy Unit Price')}
            {$listObj->getListHeaderCell('Sell Currency')}
            {$listObj->getListHeaderCell('Sell Unit Price')}
            {$listObj->getListHeaderCell('Valid Until')}
            {$listObj->getListHeaderCell('Status')}
            {$listObj->getListHeaderCell('Sales Order Quantity')}
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
    function getQuickSearch() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $status = $fn->getReqParam('status');
        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $text = "
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.enquiry.statusArr'], $status)}
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
}