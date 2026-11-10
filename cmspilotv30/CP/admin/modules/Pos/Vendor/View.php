<?
class CP_Admin_Modules_Pos_Vendor_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');

        $text = '';
        $rows = '';
        $rowCounter = 0;

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['code'])}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['email'])}
            {$listObj->getListDataCell($row['telephone'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($row['vendor_id'], 'center')}
            {$listObj->getListRowEnd($row['vendor_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Vendor Code', 'v.code')}
        {$listObj->getListHeaderCell('Vendor Name', 'v.title')}
        {$listObj->getListHeaderCell('Email', 'v.email')}
        {$listObj->getListHeaderCell('Telephone', 'v.telephone')}
        {$listObj->getListHeaderCell('Status', 'v.status')}
        {$listObj->getListHeaderCell('ID', 'v.vendor_id', 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    //==================================================================//
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fieldset = "
        {$formObj->getTBRow('Vendor Name', 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    //==================================================================//
    function getEdit($row) {
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');

        $sqlCurrency = getCPModuleObj('pos_currency')->model->getCurrencyCodeSQL();
        $sqlPayment = getCPModuleObj('pos_payment')->model->getPaymentSQL();
        $sqlShipment = getCPModuleObj('pos_shipment')->model->getShipmentSQL();

        $expEdit = array('isEditable' => 0);
        $rowVendor = $fn->getSettingsRowByKey('vendor');
        
        if ($rowVendor['auto_generate_no'] == 1) {
            $vendorCode = $formObj->getTBRow('Vendor Code', 'code', $row['code'], $expEdit);
        } else {
            $vendorCode = $formObj->getTBRow('Vendor Code', 'code', $row['code']);
        }
        
        $fielset1 = "
        {$vendorCode}
        {$formObj->getTBRow('Vendor Name', 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getTBRow('Address', 'address', $ln->gfv($row, 'address', '0'))}
        {$formObj->getTBRow('Contact Person', 'contact_person', $ln->gfv($row, 'contact_person', '0'))}
        {$formObj->getTBRow('Email', 'email', $ln->gfv($row, 'email', '0'))}
        {$formObj->getTBRow('Telephone', 'telephone', $ln->gfv($row, 'telephone', '0'))}
        {$formObj->getTBRow('Mobile', 'mobile', $ln->gfv($row, 'mobile', '0'))}
        {$formObj->getTBRow('Fax', 'fax', $ln->gfv($row, 'fax', '0'))}
        {$formObj->getDDRowBySQL('Default Currency', 'default_currency', $sqlCurrency, $row['default_currency'], array('sqlType' => 'OneField'))}
        {$formObj->getDDRowBySQL('Default Payment', 'payment_id', $sqlPayment, $row['payment_id'])}
        {$formObj->getDDRowBySQL('Default Shipment', 'shipment_id', $sqlShipment, $row['shipment_id'])}
        {$formObj->getDDRowBySQL('Default Ship Via', 'shipment_via_id', $sqlShipment, $row['shipment_via_id'])}
        {$formObj->getDDRowByVL('Status', 'status', 'vendorStatus', $row['status'])}
        ";
        
        $text = "
        {$formObj->getFieldSetWrapped('Vendor Maintenance Details', $fielset1)}
        ";

        return $text;
    }

    //==================================================================//
    function getRightPanel($row){
        $media = Zend_Registry::get('media');
        $displayLinkData = Zend_Registry::get('displayLinkData');
                
        $text ="
        {$displayLinkData->getLinkPortalMain("pos_vendor", "pos_productLink", "Products Linked", $row)}
        ";
        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
                
        $text = "
        ";

        
        return $text;
    }
}