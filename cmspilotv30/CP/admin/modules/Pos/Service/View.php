<?
class CP_Admin_Modules_Pos_Service_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getListDataCell($row['service_id'], 'center')}
            {$listObj->getListRowEnd($row['service_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Code', 's.code')}
        {$listObj->getListHeaderCell('Name', 's.name')}
        {$listObj->getListHeaderCell('Unit Price', 's.unit_price')}
        {$listObj->getListHeaderCell('Cost', 's.cost')}
        {$listObj->getListHeaderCell('ID', 's.service_id', 'headerCenter')}
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
        $redeem = array('sqlType' => 'OneField');

        $fieldset1 = "
        {$formObj->getTBRow('Code', 'code', $row['code'])}
        {$formObj->getTBRow('Name', 'title', $row['title'] )}
        {$formObj->getTARow('Description', 'description', $row['description'])}
        {$formObj->getDDRowBySQL('Unit Price Currency', 'unit_price_currency', $sqlCurrency, $row['unit_price_currency'], array('sqlType' => 'OneField'))}
        {$formObj->getTBRow('Unit Price', 'unit_price', $row['unit_price'])}
        {$formObj->getDDRowBySQL('Cost Currency', 'cost_currency', $sqlCurrency, $row['cost_currency'], array('sqlType' => 'OneField'))}
        {$formObj->getTBRow('Cost', 'cost', $row['cost'])}
        {$formObj->getDDRowBySQL('UOM', 'uom_code', $sqlUom, $row['uom_code'], array('sqlType' => 'OneField'))}
        ";
        
        $fieldset2 = "
        {$formObj->getYesNoRRow('Allow Redeem', 'allow_redeem', $row['allow_redeem'])}
        {$formObj->getYesNoRRow('Is Fixed Price', 'is_fixed_price', $row['is_fixed_price'])}
        {$formObj->getYesNoRRow('Allow Gift Item', 'allow_gift_item', $row['allow_gift_item'])}
        {$formObj->getDateRow('Expiry Date From', 'expiry_date_from', $row['expiry_date_from'])}
        {$formObj->getDateRow('Expiry Date To', 'expiry_date_to', $row['expiry_date_to'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Service Maintenance', $fieldset1)}
        {$formObj->getFieldSetWrapped('Options', $fieldset2)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    //==================================================================//
    function getRightPanel($row){
        $media = Zend_Registry::get('media');
                
        $text ="
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