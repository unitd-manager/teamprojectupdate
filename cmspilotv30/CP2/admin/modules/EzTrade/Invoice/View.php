<?
class CP_Admin_Modules_EzTrade_Invoice_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getListDataCell($row['invoice_amount'])}
            {$listObj->getListDataCell($row['invoice_amount_received'])}
            {$listObj->getListDateCell($row['invoice_due_date'])}
            {$listObj->getListDataCell($row['staff_name'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['invoice_id'])}
            ";

            $count++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Invoice Number', 'i.invoice_code')}
        {$listObj->getListHeaderCell('Invoice Type', 'i.invoice_type')}
        {$listObj->getListHeaderCell('Sales Order Number', 'i.so_code')}
        {$listObj->getListHeaderCell('Client Name', 'com.company_name')}
        {$listObj->getListHeaderCell('Invoice Date', 'i.invoice_date')}
        {$listObj->getListHeaderCell('Sell Currency', 'i.sell_currency')}
        {$listObj->getListHeaderCell('Invoice Amount', 'invoice_amount')}
        {$listObj->getListHeaderCell('Received Amount', 'invoice_amount_received')}
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

        $formObj->mode = $tv['action'];

        $fnsModCompany = includeCPClass('ModuleFns', 'ezTrade_company');
        $fnsModInvoice = includeCPClass('ModuleFns', 'ezTrade_invoice');

        $expCustCont = array('detailValue' => $row['contact_name_customer']);
        $modContact = getCPModuleObj('ezTrade_contact');
        $sqlCustomerCont = $modContact->model->getContactsByCompanySQL($row['company_id_customer']);

        $sqlCurrency = $fn->getValueListSQL('currency');
        $sqlInvoiceType = $fn->getValueListSQL('invoiceType');

        $expStaff = array('detailValue' => $row['staff_name']);
        $modStaff = getCPModuleObj('core_staff');
        $sqlSalesManager = $modStaff->model->getStaffByGroupSQL();

        $expNoEdit = array('isEditable' => 0);
        $expVl = array('sqlType' => 'OneField');

        $expSoCode = array('displayText' => $row['so_code']);
        $soCodeText = $fn->getRecordDetailLink('ezTrade_salesOrder', 'record_id', $row['sales_order_id'], $expSoCode);

        $fieldset1 = "
        {$formObj->getTBRow('Invoice Number', 'invoice_code', $row['invoice_code'], $expNoEdit)}
        {$formObj->getDDRowBySQL('Invoice Type', 'invoice_type', $sqlInvoiceType, $row['invoice_type'], $expVl)}
        {$formObj->getTBRow('Sales Order Number', 'order_value', $soCodeText, $expNoEdit)}
        {$formObj->getTBRow('Client PO Number', 'client_so_no', $row['client_so_no'], $expNoEdit)}
        {$formObj->getTBRow('Client Name', 'company_name', $row['company_name'], $expNoEdit)}
        {$formObj->getDDRowBySQL('Contact Person', 'contact_id_customer', $sqlCustomerCont, $row['contact_id_customer'], $expCustCont)}
        {$formObj->getTBRow('Invoice Date', 'invoice_date', $row['invoice_date'], $expNoEdit)}
        {$formObj->getDateRow('Invoice Due Date', 'invoice_due_date', $row['invoice_due_date'])}
        {$formObj->getDDRowByArr('Invoice Status', 'status', $fnsModInvoice->getInvoiceStatusArray(), $row['status'])}
        {$formObj->getTBRow('Payment Terms', 'payment_terms', $row['payment_terms'], $expNoEdit)}
        {$formObj->getDDRowBySQL('Staff Member Responsible', 'staff_id', $sqlSalesManager, $row['staff_id'], $expStaff)}
        {$formObj->getTARow('Note to Client', 'notes', $row['notes'])}
        ";

        $fieldset2 = "
        {$formObj->getDDRowBySQL('Sell Currency', 'sell_currency', $sqlCurrency, $row['sell_currency'], $expVl)}
        {$formObj->getTBRow('Total Invoice Amount', 'invoice_amount', $row['invoice_amount'], $expNoEdit)}
        {$formObj->getTBRow('Total Received Amount', 'invoice_amount_received', $row['invoice_amount_received'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Invoice Header', $fieldset1)}
        {$formObj->getFieldSetWrapped('Values', $fieldset2)}
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
        $record_id = $fn->getIssetParam($row, 'invoice_id');

        $links .= "
        {$displayLinkData->getLinkPortalMain('ezTrade_invoice', 'ezTrade_productLink', 'Invoice Line', $row)}
        ";

        $text = "
        {$links}
        {$media->getRightPanelMediaDisplay('Attachments', 'ezTrade_invoice', 'attachment', $row)}
        {$comment->getView(array(
             'roomName' => 'ezTrade_invoice'
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
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $status       = $fn->getReqParam('status');
        $invoice_type = $fn->getReqParam('invoice_type');

        $sqlInvoiceType = $fn->getValueListSQL('invoiceType');

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
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlInvoiceType, $invoice_type)}
            </select>
        </td>
        ";

        return $text;
    }

}