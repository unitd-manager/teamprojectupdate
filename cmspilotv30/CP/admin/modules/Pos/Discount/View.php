<?
class CP_Admin_Modules_Pos_Discount_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getGoToDetailText($rowCounter, $row['type'])}
            {$listObj->getListDataCell($row['interest_title'])}
            {$listObj->getListDataCell($row['discount_id'], 'center')}
            {$listObj->getListRowEnd($row['discount_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Discount Code', 'd.code')}
        {$listObj->getListHeaderCell('Type', 'd.type')}
        {$listObj->getListHeaderCell('Member Group', 'i.title')}
        {$listObj->getListHeaderCell('ID', 'd.discount_id', 'headerCenter')}
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
        {$formObj->getTBRow('Discount Code', 'code')}
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
        
        $expInterest = array('detailValue' => $row['interest_title']);
        $sqlShop = getCPModuleObj('pos_shop')->model->getShopCodeSQL();

        $sqlCurrency = getCPModuleObj('pos_currency')->model->getCurrencyCodeSQL();

        $fielset1 = "
        {$formObj->getTBRow('Discount Code', 'code', $row['code'])}
        {$formObj->getDateRow('Start Date', 'start_date', $row['start_date'])}
        {$formObj->getTimeRow('Start Time', 'start_time', $row['start_time'])}
        {$formObj->getDateRow('End Date', 'end_date', $row['end_date'])}
        {$formObj->getTimeRow('End Time', 'end_time', $row['end_time'])}
        {$formObj->getDDRowByVL('Type', 'type', 'discountType', $row['type'])}
        {$formObj->getDDRowBySQL('Member Group', 'interest_id', $fn->getDDSql('pos_interest'), $row['interest_id'], $expInterest)}
        {$formObj->getTBRow('Discount Percentage', 'discount_percentage', $row['discount_percentage'])}
        {$formObj->getTBRow('Less Amount', 'less_amount', $row['less_amount'])}
        {$formObj->getTBRow('Mix Quantity Required', 'mix_qty_required', $row['mix_qty_required'])}
        {$formObj->getDDRowByVL('Mix Rules', 'mix_rules', 'mixRules', $row['mix_rules'])}
        {$formObj->getDDRowBySQL('Mix Currency', 'mix_currency', $sqlCurrency, $row['mix_currency'], array('sqlType' => 'OneField'))}
        {$formObj->getDDRowByVL('Mix Amount Required', 'mix_amount_required', 'mixAmountRequired', $row['mix_amount_required'])}
        ";
        
        $text = "
        {$formObj->getFieldSetWrapped('Discount Maintenance Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    //==================================================================//
    function getRightPanel($row){
        $media = Zend_Registry::get('media');
        $displayLinkData = Zend_Registry::get('displayLinkData');
                
        $text ="
        {$displayLinkData->getLinkPortalMain('pos_discount', 'pos_shopLink', 'Shop Linked', $row)}        
        {$displayLinkData->getLinkPortalMain("pos_discount", "pos_productLink", "Products Linked", $row)}
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