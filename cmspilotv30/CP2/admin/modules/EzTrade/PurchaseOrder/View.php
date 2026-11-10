<?
class CP_Admin_Modules_EzTrade_PurchaseOrder_View extends CP_Common_Lib_ModuleViewAbstract
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
            $expSoCode = array('displayText' => $row['so_code']);
            $soCodeText = $fn->getRecordDetailLink('ezTrade_salesOrder', 'record_id', $row['sales_order_id'], $expSoCode);

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['po_code'])}
            {$listObj->getListDataCell($soCodeText)}
            {$listObj->getListDataCell($row['supplier_name'])}
            {$listObj->getListDateCell($row['purchase_order_date'])}
            {$listObj->getListDataCell($row['staff_name'])}
            {$listObj->getListDataCell($row['buy_currency'])}
            {$listObj->getListDataCell($row['po_sum_buy_price'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['purchase_order_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Purchase Order Number', 'po.po_code')}
        {$listObj->getListHeaderCell('Sales Order Number', 'so.so_code')}
        {$listObj->getListHeaderCell('Supplier Name', 'supplier_name')}
        {$listObj->getListHeaderCell('Purchase Order Creation Date', 'po.purchase_order_date')}
        {$listObj->getListHeaderCell('Staff', 'staff_name')}
        {$listObj->getListHeaderCell('Buy Currency', 'buy_currency')}
        {$listObj->getListHeaderCell('Purchase Order Value', 'po_sum_buy_price')}
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

        $sqlSupplier = $fn->getDDSql('ezTrade_company', array('condn' => "category = 'Supplier'"));
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

        $expNoEdit = array('isEditable' => 0);

        $expSoCode = array('displayText' => $row['so_code']);
        $soCodeText = $fn->getRecordDetailLink('ezTrade_salesOrder', 'record_id', $row['sales_order_id'], $expSoCode);

        $expSuppText = array('displayText' => $row['supplier_name']);
        $supplierText = $fn->getRecordDetailLink('ezTrade_company', 'record_id', $row['company_id_supplier'], $expSuppText);

        $expContact = array('detailValue' => $row['contact_name_supplier']);
        $modContact = getCPModuleObj('ezTrade_contact');
        $sqlSuppContact = $modContact->model->getContactsByCompanySQL($row['company_id_supplier']);

        $expCompany = array('sqlType' => 'OneField');
        $modCompany = getCPModuleObj('ezTrade_company');
        $sqlSuppPaymentTerm = $modCompany->model->getPaymentTermsSQL($row['company_id_supplier']);

        $expVl = array('sqlType' => 'OneField');
        $sqlCurrency = $fn->getValueListSQL('currency');

        $expBuyer = array('detailValue' => $row['staff_name']);
        $modStaff = getCPModuleObj('core_staff');
        $sqlBuyer = $modStaff->model->getStaffByGroupSQL();

        $fieldset1 = "
        {$formObj->getTBRow('Purchase Order Number', 'po_code', $row['po_code'], $expNoEdit)}
        {$formObj->getDateRow('Purchase Order Date', 'purchase_order_date', $row['purchase_order_date'])}
        {$formObj->getDDRowByArr('Purchase Order Status', 'status', $cpCfg['m.trading.purchaseOrder.statusArr'], $row['status'])}
        {$formObj->getTBRow('Sales Order Number', 'order_value', $soCodeText, $expNoEdit)}
        {$formObj->getTBRow('Supplier Name', 'supplier_name', $supplierText, $expNoEdit)}
        {$formObj->getDDRowBySQL('Contact Person', 'contact_id_supplier', $sqlSuppContact, $row['contact_id_supplier'], $expContact)}
        {$formObj->getDDRowBySQL('Payment Terms', 'payment_terms', $sqlSuppPaymentTerm, $row['payment_terms'], $expCompany)}
        {$formObj->getYesNoRRow('Deposit Paid', 'deposit_paid', $row['deposit_paid'])}
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
        {$displayLinkData->getLinkPortalMain('ezTrade_purchaseOrder', 'ezTrade_productLink', 'Purchase Order Line', $row)}
        ";

        $record_id = $fn->getIssetParam($row, 'purchase_order_id');

        $text = "
        {$links}
        {$media->getRightPanelMediaDisplay('Attachments', 'ezTrade_purchaseOrder', 'attachment', $row)}
        {$comment->getView(array(
             'roomName' => 'ezTrade_purchaseOrder'
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
                {$cpUtil->getDropDown1($cpCfg['m.trading.purchaseOrder.statusArr'], $status)}
            </select>
        </td>
        ";

        return $text;
    }
}