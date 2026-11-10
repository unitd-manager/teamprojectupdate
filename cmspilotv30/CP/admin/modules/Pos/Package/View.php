<?
class CP_Admin_Modules_Pos_Package_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getListDataCell($row['unit_price'])}
            {$listObj->getListDataCell($row['cost'])}
            {$listObj->getListDataCell($row['package_id'], 'center')}
            {$listObj->getListRowEnd($row['package_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Code', 'p.code')}
        {$listObj->getListHeaderCell('Name', 'p.title')}
        {$listObj->getListHeaderCell('Unit Price', 'p.unit_price')}
        {$listObj->getListHeaderCell('Cost', 'p.cost')}
        {$listObj->getListHeaderCell('ID', 'p.package_id', 'headerCenter')}
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
        {$formObj->getTBRow('Name', 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    //==================================================================//
    function getEdit($row) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        
        $sqlUom = getCPModuleObj('pos_uom')->model->getUomCodeSQL();
        $sqlCurrency = getCPModuleObj('pos_currency')->model->getCurrencyCodeSQL();
        
        $type = $fn->getValuelistSql('packageType');
        $status = $fn->getValuelistSql('packageStatus');
        $exp = array('sqlType' => 'OneField');

        $fieldset1 = "
        {$formObj->getTBRow('Code', 'code', $row['code'])}
        {$formObj->getTBRow('Name', 'title', $row['title'] )}
        {$formObj->getTARow('Description', 'description', $row['description'])}
        {$formObj->getDDRowByVL('Type', 'type', 'packageType', $row['type'])}
        {$formObj->getTBRow('Unit Price', 'unit_price', $row['unit_price'])}
        {$formObj->getDDRowBySQL('Unit Price Currency', 'unit_price_currency', $sqlCurrency, $row['unit_price_currency'], array('sqlType' => 'OneField'))}
        {$formObj->getTBRow('Cost', 'cost', $row['cost'])}
        {$formObj->getDDRowBySQL('Cost Currency', 'cost_currency', $sqlCurrency, $row['cost_currency'], array('sqlType' => 'OneField'))}
        {$formObj->getTBRow('Quantitiy', 'quantity', $row['quantity'])}
        {$formObj->getDDRowBySQL('UOM', 'uom_code', $sqlUom, $row['uom_code'], array('sqlType' => 'OneField'))}
        {$formObj->getDDRowBySQL('Status', 'status', $status, $row['status'], $exp)}
        ";
        
        $fieldset2 = "
        {$formObj->getYesNoRRow('Allow Discount', 'allow_discount', $row['allow_discount'])}
        {$formObj->getYesNoRRow('Allow Member Discount', 'allow_member_discount', $row['allow_member_discount'])}
        {$formObj->getDateRow('Expiry Date From', 'expiry_date_from', $row['expiry_date_from'])}
        {$formObj->getDateRow('Expiry Date To', 'expiry_date_to', $row['expiry_date_to'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Package Maintenance', $fieldset1)}
        {$formObj->getFieldSetWrapped('Options', $fieldset2)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    //==================================================================//
    function getRightPanel($row){
        $media = Zend_Registry::get('media');
        $displayLinkData = Zend_Registry::get('displayLinkData');
                
        $text ="
        {$media->getRightPanelMediaDisplay('Picture', 'pos_package', 'picture', $row)}
        {$displayLinkData->getLinkPortalMain("pos_package", "pos_productLink", "Products Linked", $row)}
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