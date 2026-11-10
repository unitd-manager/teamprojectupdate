<?
class CP_Admin_Modules_Trading_Rfq_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            $exp = array('displayText' => $row['enquiry_code']);
            $enquiry_code = $fn->getRecordDetailLink('trading_enquiry', 'record_id', $row['enquiry_id'], $exp);

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['quote_request_code'])}
            {$listObj->getListDataCell($enquiry_code)}
            {$listObj->getListDataCell($row['supplier_company_name'])}
            {$listObj->getListDataCell($row['title'])}
            {$listObj->getListDateCell($row['quote_request_date'])}
            {$listObj->getListDateCell($row['followup_date'])}
            {$listObj->getListDataCell($row['staff_name'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['quote_request_id'])}
            ";

            $count++ ;
        }
        $rows = $listObj->getDisplayListRows($rows);

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('RFQ Number', 'qr.quote_request_code')}
        {$listObj->getListHeaderCell('Enquiry Number', 'enquiry_code')}
        {$listObj->getListHeaderCell('Supplier Name', 'c.company_id')}
        {$listObj->getListHeaderCell('Enquiry Title', 'qr.title')}
        {$listObj->getListHeaderCell('RFQ Create Date', 'qr.quote_request_date')}
        {$listObj->getListHeaderCell('Required Response Date', 'qr.quote_request_date')}
        {$listObj->getListHeaderCell('Buyer', 'staff_name')}
        {$listObj->getListHeaderCell('RFQ Status', 'qr.status')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
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
        $expVl = array('sqlType' => 'OneField');

        $expEnquiry = array('displayText' => $row['enquiry_code']);
        $enquiryText = $fn->getRecordDetailLink('trading_enquiry', 'record_id', $row['enquiry_id'], $expEnquiry);

        $expSuppText = array('displayText' => $row['supplier_company_name']);
        $supplierText = $fn->getRecordDetailLink('trading_company', 'record_id', $row['company_id_supplier'], $expSuppText);
        $expSupp = array('detailValue' => $supplierText, 'hideFirstOption' => 1);
        $sqlSupplierName = $fn->getDDSql('trading_company', array('condn' => "category = 'Supplier'"));

        $expSuppContact = array('detailValue' => $row['supplier_contact']);
        $modContact = getCPModuleObj('trading_contact');
        $sqlSupplierContact = $modContact->model->getContactsByCompanySQL($row['company_id_supplier']);

        $sqlCurrency = $fn->getValueListSQL('currency');

        $expSalesManager = array('detailValue' => $row['staff_name']);
        $modStaff = getCPModuleObj('core_staff');
        $sqlSalesManager = $modStaff->model->getStaffByGroupSQL();

        $expDeliveryTermsCust = $fnsModGrp->getTermsParamArr('trading_deliveryTermsLink',
                                                             $row['company_id_customer'],
                                                             'fld_required_delivery_terms'
                                                            );
        $expDeliveryTermsSupp = $fnsModGrp->getTermsParamArr('trading_deliveryTermsLink',
                                                             $row['company_id_supplier'],
                                                             'fld_delivery_terms_supplier'
                                                            );
        $expPaymentTermsSupp = $fnsModGrp->getTermsParamArr('trading_paymentTermsLink',
                                                            $row['company_id_customer'],
                                                            'fld_payment_terms'
                                                           );
        
        $expShipToLoc = array('detailValue' => $row['ship_to_location']);
        $modDeliveryAddress = getCPModuleObj('trading_deliveryAddressLink');
        $sqlShipToLocation = $modDeliveryAddress->model->getShipToLocationSQL($row['company_id_customer']);

        $buyCurrencyPrev = $formObj->getHiddenFldObj('buy_currency_prev', $row['buy_currency'], 'fld_buy_currency_prev');
        $fieldset1 = "
        {$formObj->getTBRow('RFQ Number', 'quote_request_code', $row['quote_request_code'], $expNoEdit)}
        {$formObj->getTBRow('Enquiry Number', 'enquiry_code', $enquiryText, $expNoEdit)}
        {$formObj->getDDRowBySQL('Supplier name', 'company_id_supplier', $sqlSupplierName, $row['company_id_supplier'], $expSupp)}
        {$formObj->getDDRowBySQL('Contact Person', 'contact_id_supplier', $sqlSupplierContact, $row['contact_id_supplier'], $expSuppContact)}
        {$formObj->getTBRow('RFQ Title', 'title', $row['title'])}
        {$formObj->getDateRow('RFQ Date', 'quote_request_date', $row['quote_request_date'])}
        {$formObj->getDDRowBySQL('RFQ Buy Currency', 'buy_currency', $sqlCurrency, $row['buy_currency'], $expVl)}
        {$buyCurrencyPrev}
        {$formObj->getDDRowByArr('RFQ Status', 'status', $cpCfg['m.trading.rfq.statusArr'], $row['status'])}
        {$formObj->getDateRow('Required Response Date', 'followup_date', $row['followup_date'])}
        {$formObj->getDDRowBySQL('Staff Member Responsible', 'staff_id', $sqlSalesManager, $row['staff_id'], $expSalesManager)}
        ";

        $fieldset2 = "
        {$formObj->getTARow('Required Delivery Terms', 
                            'required_delivery_terms', 
                            $row['required_delivery_terms'], 
                            $expDeliveryTermsCust)}
        {$formObj->getDDRowBySQL('Ship to Location', 'delivery_address_id', $sqlShipToLocation, $row['delivery_address_id'], $expShipToLoc)}
        {$formObj->getTextAreaRow('Note to supplier', 'notes_to_supplier', $row['notes_to_supplier'])}
        ";

        $fieldset3 = "
        {$formObj->getTARow('Delivery Terms', 
                            'delivery_terms_supplier', 
                            $row['delivery_terms_supplier'], 
                            $expDeliveryTermsSupp)}
        {$formObj->getDateRow('Quote Validity', 'valid_until', $row['valid_until'])}
        {$formObj->getTARow('Payment Terms', 
                            'payment_terms', 
                            $row['payment_terms'], 
                            $expPaymentTermsSupp)}
        {$formObj->getTARow('Notes from Supplier', 'notes_from_supplier', $row['notes_from_supplier'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('RFQ Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('RFQ to Supplier', $fieldset2)}
        {$formObj->getFieldSetWrapped('Quote from Supplier', $fieldset3)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $comment = getCPPluginObj('common_comment');

        $links = "";
        $record_id = $fn->getIssetParam($row, 'quote_request_id');

        $links .= "
        {$displayLinkData->getLinkPortalMain("trading_rfq", "trading_productLink", "RFQ Line", $row)}
        ";

        $text = "
        {$links}
        {$media->getRightPanelMediaDisplay("Attachments", "trading_rfq", "attachment", $row)}
        {$comment->getView(array(
             'roomName' => 'trading_rfq'
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
        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );
        
        $text = "
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.rfq.statusArr'], $status)}
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