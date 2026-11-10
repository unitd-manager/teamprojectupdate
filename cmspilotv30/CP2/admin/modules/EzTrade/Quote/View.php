<?
class CP_Admin_Modules_EzTrade_Quote_View extends CP_Common_Lib_ModuleViewAbstract
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
            $enquiry_code = $fn->getRecordDetailLink('ezTrade_enquiry', 'record_id', $row['enquiry_id'], $expEnquiry);

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

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Quote Number', 'q.quote_code')}
        {$listObj->getListHeaderCell('Enquiry Number', 'enquiry_code')}
        {$listObj->getListHeaderCell('Client Name', 'customer_company_name')}
        {$listObj->getListHeaderCell('Contact person', 'customer_contact')}
        {$listObj->getListHeaderCell('Quote Title', 'e.subject')}
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
        $fnLink = Zend_Registry::get('fnLink');
        $ln = Zend_Registry::get('ln');
        $am = Zend_Registry::get('am');

        $formObj->mode = $tv['action'];
        $modContact = getCPModuleObj('ezTrade_contact');
        $sqlCustomerContact = $modContact->model->getContactsByCompanySQL($row['company_id_customer']);

        $modContact = getCPModuleObj('core_staff');
        $sqlSalesManager = $modContact->model->getStaffByGroupSQL();

        $fnsModQuote           = includeCPClass('ModuleFns', 'ezTrade_quote');

        $sqlShipToCountry = $fn->getValueListSQL('shipToCountry');
        $sqlCurrency      = $fn->getValueListSQL('currency');

        $expVl      = array('sqlType' => 'OneField');
        $expContact = array('detailValue' => $row['customer_contact']);
        $expEnquiry = array('displayText' => $row['enquiry_code']);
        $expShipToLoc = array('detailValue' => $row['ship_to_location']);
        $expNoEdit  = array('isEditable' => 0);
        $expStaff   = array('detailValue' => $row['staff_name']);

        $enquiryText = $fn->getRecordDetailLink('ezTrade_enquiry', 'record_id', $row['enquiry_id'], $expEnquiry);

        $expComp = array('displayText' => $row['customer_company_name']);
        $companyText = $fn->getRecordDetailLink('ezTrade_company', 'record_id', 
                            $row['company_id_customer'], $expComp);

        $expSalesAgent = array('displayText' => $row['sales_agent']);
        $salesAgentText = $fn->getRecordDetailLink('ezTrade_company', 'record_id', 
                               $row['company_id_sales_agent'], $expSalesAgent);

        $modPaymentTerms = getCPModuleObj('ezTrade_paymentTermsLink');
        $sqlPaymentTermsCustomer = $modPaymentTerms->model
                                   ->getPaymentTermsSupplierSQL($row['company_id_customer']);

        $expShipToLoc = array('detailValue' => $row['ship_to_location']);
        $modDeliveryAddress = getCPModuleObj('ezTrade_deliveryAddressLink');
        $sqlShipToLocation = $modDeliveryAddress->model->getShipToLocationSQL($row['company_id_customer']);

        $fieldset1 = "
        {$formObj->getTBRow('Quotation Number', 'quote_code', $row['quote_code'], $expNoEdit)}
        {$formObj->getTBRow('Enquiry Number', 'enquiry_code', $enquiryText, $expNoEdit)}
        {$formObj->getTBRow('Client Name', 'customer_company_name', $companyText, $expNoEdit)}
        {$formObj->getDDRowBySQL('Contact Person', 'contact_id_customer', $sqlCustomerContact, $row['contact_id_customer'], $expContact)}
        {$formObj->getTBRow('Client RFQ Number', 'customer_rfq_code', $row['customer_rfq_code'], $expNoEdit)}
        {$formObj->getTBRow('Enquiry Title', 'title', $row['subject'], $expNoEdit)}
        {$formObj->getDateRow('Quote Date', 'quote_date', $row['quote_date'])}
        {$formObj->getDDRowBySQL('Sell Currency', 'sell_currency', $sqlCurrency, $row['sell_currency'], $expVl)}
        {$formObj->getDDRowBySQL('Payment Terms', 'payment_terms_customer', $sqlPaymentTermsCustomer, $row['payment_terms_customer'], $expVl)}
        {$formObj->getDDRowByArr('Quote Status', 'status', $fnsModQuote->getQuoteStatusArray(), $row['status'])}
        {$formObj->getDateRow('Required Response Date', 'follow_up_date', $row['follow_up_date'])}
        {$formObj->getDDRowBySQL('Staff Member Responsible', 'staff_id', $sqlSalesManager, $row['staff_id'], $expStaff)}
        {$formObj->getTextAreaRow('Note to client', 'notes_customer', $row['notes_customer'])}
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

        $links = "
        {$displayLinkData->getLinkPortalMain("ezTrade_quote", "ezTrade_productLink", "Quote Line", $row)}
        ";

        $record_id = $fn->getIssetParam($row, 'quote_id');

        $text = "
        {$links}
        {$media->getRightPanelMediaDisplay("Attachments", "ezTrade_quote", "attachment", $row)}
        {$comment->getView(array(
             'roomName' => 'ezTrade_quote'
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
              ,qr.buy_currency
              ,qi.quantity
              ,qi.buy_unit_price
              ,qi.sell_unit_price
              ,qi.markup
              ,qi.valid_until
              ,qi.status
              ,q.quote_id
              ,q.sell_currency
        FROM quote_items qi
        JOIN quote q   ON (q.quote_id = qi.quote_id)
        JOIN product p ON (p.product_id = qi.product_id)
        JOIN quote_request_items qri ON (qri.quote_request_items_id = qi.quote_request_items_id)
        JOIN quote_request qr        ON (qr.quote_request_id = qri.quote_request_id)
        WHERE qi.quote_id = {$quote_id}
        ORDER BY qi.line_no
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows();
        $count = 0;

        $rows = '';

        while ($row = $db->sql_fetchrow($result)) {
            $quantityText = "
            <input class='quantity' type='text' value='{$row['quantity']}'
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
            {$listObj->getListDataCell($row['markup'])}
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
            {$listObj->getListHeaderCell('Item Number')}
            {$listObj->getListHeaderCell('Item Name')}
            {$listObj->getListHeaderCell('Quantity')}
            {$listObj->getListHeaderCell('UOM')}
            {$listObj->getListHeaderCell('Buy Currency')}
            {$listObj->getListHeaderCell('Buy Unit Price')}
            {$listObj->getListHeaderCell('Sell Currency')}
            {$listObj->getListHeaderCell('Sell Unit Price')}
            {$listObj->getListHeaderCell('Markup %')}
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
        $cpCfg = Zend_Registry::get('cpCfg');

        $status       = $fn->getReqParam('status');

        $text = "
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.enquiry.statusArr'], $status)}
            </select>
        </td>
        ";

        return $text;
    }
}