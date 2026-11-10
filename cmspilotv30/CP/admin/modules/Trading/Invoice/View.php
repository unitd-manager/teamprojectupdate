<?
class CP_Admin_Modules_Trading_Invoice_View extends CP_Common_Lib_ModuleViewAbstract
{
    //========================================================//
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['invoice_code'])}
            {$listObj->getListDataCell($row['invoice_type'])}
            {$listObj->getListDataCell($row['so_code'])}
            {$listObj->getListDataCell($row['company_name'])}
            {$listObj->getListDateCell($row['invoice_date'])}
            {$listObj->getListDataCell($row['sell_currency'])}
            {$listObj->getListDataCell($row['invoice_amount'], 'right')}
            {$listObj->getListDateCell($row['invoice_due_date'])}
            {$listObj->getListDataCell($row['staff_name'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['invoice_id'])}
            ";

            $count++;
        }
        $rows = $listObj->getDisplayListRows($rows);

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Invoice Number', 'i.invoice_code')}
        {$listObj->getListHeaderCell('Invoice Type', 'i.invoice_type')}
        {$listObj->getListHeaderCell('Sales Order Number', 'i.so_code')}
        {$listObj->getListHeaderCell('Client Name', 'com.company_name')}
        {$listObj->getListHeaderCell('Invoice Date', 'i.invoice_date')}
        {$listObj->getListHeaderCell('Sell Currency', 'i.sell_currency')}
        {$listObj->getListHeaderCell('Invoice Amount', 'invoice_amount', 'txtRight')}
        {$listObj->getListHeaderCell('Invoice Due Date', 'i.invoice_due_date')}
        {$listObj->getListHeaderCell('Staff', 'staff_name')}
        {$listObj->getListHeaderCell('Invoice Status', 'i.status')}
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $tv = Zend_Registry::get('tv');

        $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');

        $formObj->mode = $tv['action'];

        $fnsModCompany = includeCPClass('ModuleFns', 'trading_company');
        $fnsModInvoice = includeCPClass('ModuleFns', 'trading_invoice');

        $expCustCont = array('detailValue' => $row['contact_name_customer']);
        $modContact = getCPModuleObj('trading_contact');
        $sqlCustomerCont = $modContact->model->getContactsByCompanySQL($row['company_id_customer']);

        $sqlCurrency = $fn->getValueListSQL('currency');
        $sqlInvoiceType = $fn->getValueListSQL('invoiceType');

        $expStaff = array('detailValue' => $row['staff_name']);
        $modStaff = getCPModuleObj('core_staff');
        $sqlSalesManager = $modStaff->model->getStaffByGroupSQL();

        $expNoEdit = array('isEditable' => 0);
        $expNoEditAutoFrmt = array_merge($expNoEdit, array('autoFormat' => 1));
        $expVl = array('sqlType' => 'OneField');

        $expSoCode = array('displayText' => $row['so_code']);
        $soCodeText = $fn->getRecordDetailLink('trading_salesOrder', 'record_id', $row['sales_order_id'], $expSoCode);

        $expComp = array('displayText' => $row['company_name']);
        $companyText = $fn->getRecordDetailLink('trading_company', 'record_id', $row['company_id_customer'], $expComp);

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

        $fieldset1 = "
        {$formObj->getTBRow('Invoice Number', 'invoice_code', $row['invoice_code'], $expNoEdit)}
        {$formObj->getDDRowBySQL('Invoice Type', 'invoice_type', $sqlInvoiceType, $row['invoice_type'], $expVl)}
        {$formObj->getTBRow('Sales Order Number', 'so_code', $soCodeText, $expNoEdit)}
        {$formObj->getTBRow('Client Ref.', 'client_so_no', $row['client_so_no'], $expNoEdit)}
        {$formObj->getTBRow('Client Name', 'company_name', $companyText, $expNoEdit)}
        {$formObj->getDDRowBySQL('Contact Person', 'contact_id_customer',
                   $sqlCustomerCont, $row['contact_id_customer'], $expCustCont)}
        {$formObj->getDateRow('Invoice Date', 'invoice_date', $row['invoice_date'])}
        {$formObj->getDateRow('Invoice Due Date', 'invoice_due_date', $row['invoice_due_date'])}
        {$formObj->getDDRowByArr('Invoice Status', 'status', $cpCfg['m.trading.invoice.statusArr'], $row['status'])}
        {$formObj->getTARow('Payment Terms', 'payment_terms', $row['payment_terms'], $expPaymentTerms)}
        {$formObj->getTARow('Delivery Terms', 'delivery_terms', $row['delivery_terms'], $expDeliveryTerms)}
        {$formObj->getDDRowBySQL('Staff Member Responsible', 'staff_id', $sqlSalesManager, $row['staff_id'], $expStaff)}
        {$formObj->getTARow('Note to Client', 'notes', $row['notes'])}
        ";

        $fieldset2 = "
        {$formObj->getDDRowBySQL('Sell Currency', 'sell_currency', $sqlCurrency, $row['sell_currency'], $expVl)}
        {$formObj->getTBRow('Sales Order Amount', 'order_value', $order_value, $expNoEdit)}
        {$formObj->getTBRow('VAT %', 'tax_percentage', $row['tax_percentage'], $expNoEdit)}
        {$formObj->getTBRow('Sales Order Amount + VAT', 'invoice_amount_tax',
                            $order_value_tax, $expNoEditAutoFrmt)}
        ";
        $fieldset3 = "
        {$formObj->getTBRow('This Invoice Amount', 'invoice_amount', $row['invoice_amount'])}
        {$formObj->getTBRow('Delivery Amount', 'delivery_amount', $row['delivery_amount'])}
        {$formObj->getTBRow('Other Cost Label', 'other_cost_lbl', $row['other_cost_lbl'])}
        {$formObj->getTBRow('Other Cost', 'other_cost', $row['other_cost'])}
        {$formObj->getTBRow('VAT %', 'tax_percentage', $row['tax_percentage'], $expNoEdit)}
        {$formObj->getTBRow('Total Amount', 'invoice_total_amount', $row['invoice_total_amount'], $expNoEditAutoFrmt)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Invoice Header', $fieldset1)}
        {$formObj->getFieldSetWrapped('Sales Order Values', $fieldset2)}
        {$formObj->getFieldSetWrapped('Invoice Values', $fieldset3)}
        {$formObj->getCreationModificationText($row)}
        {$formObj->getHiddenFldObj('enquiry_id', $row['enquiry_id'])}

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
        $record_id = $fn->getIssetParam($row, 'invoice_id');

        //SO created from SO itself not fro Enquiry process
        if (!$row['enquiry_id']) {
            $links = "
            {$displayLinkData->getLinkPortalMain('trading_invoice', 'trading_inventoryLink', 'Invoice Line From Inventory', $row)}
            ";
        } else {
            $links = "
            {$displayLinkData->getLinkPortalMain('trading_invoice', 'trading_productLink', 'Invoice Line', $row)}
            ";
        }

        $text = "
        {$links}
        {$media->getRightPanelMediaDisplay('Attachments', 'trading_invoice', 'attachment', $row)}
        {$comment->getView(array(
             'roomName' => 'trading_invoice'
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
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $status       = $fn->getReqParam('status');
        $invoice_type = $fn->getReqParam('invoice_type');

        $sqlInvoiceType = $fn->getValueListSQL('invoiceType');
        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $text = "
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.shipment.statusArr'], $status)}
            </select>
        </td>
        <td>
            <select name='invoice_type'>
                <option value=''>Invoice Type</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlInvoiceType, $invoice_type)}
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