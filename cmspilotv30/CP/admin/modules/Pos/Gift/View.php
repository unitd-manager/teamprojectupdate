<?
class CP_Admin_Modules_Pos_Gift_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['cost'])}
            {$listObj->getListDataCell($row['discount'])}
            {$listObj->getListDataCell($row['type'])}
            {$listObj->getListDataCell($row['gift_id'], 'center')}
            {$listObj->getListRowEnd($row['gift_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Card Name', 'g.title')}
        {$listObj->getListHeaderCell('Cost', 'g.cost')}
        {$listObj->getListHeaderCell('Discount Value', 'g.discount')}
        {$listObj->getListHeaderCell('Type', 'g.type')}
        {$listObj->getListHeaderCell('ID', 'g.gift_id', 'headerCenter')}
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
        {$formObj->getTBRow('Card Name', 'title')}
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
        
        $sqlGiftType  = $fn->getValueListSQL('giftType');
        $expVl = array('sqlType' => 'OneField');
        $sqlCurrency = getCPModuleObj('pos_currency')->model->getCurrencyCodeSQL();

        $fieldset1 = "
        {$formObj->getTBRow('Card Start No', 'card_start_no', $row['card_start_no'])}
        {$formObj->getTBRow('Card End No', 'card_end_no', $row['card_end_no'])}
        {$formObj->getTBRow('Card Name', 'title', $row['title'])}
        {$formObj->getTARow('Description', 'description', $row['description'])}
        {$formObj->getYesNoRRow('Auto Generate Barcode', 'auto_bar_code', $row['auto_bar_code'])}
        {$formObj->getDDRowBySQL('Cost Currency', 'cost_currency', $sqlCurrency, $row['cost_currency'], array('sqlType' => 'OneField'))}
        {$formObj->getTBRow('Cost', 'cost', $row['cost'])}
        {$formObj->getDDRowBySQL('Amount Currency', 'amount_currency', $sqlCurrency, $row['amount_currency'], array('sqlType' => 'OneField'))}
        {$formObj->getTBRow('Amount', 'amount', $row['amount'])}
        {$formObj->getTBRow('Sell Amount', 'sell_amount', $row['sell_amount'])}
        {$formObj->getTBRow('Discount Value', 'discount', $row['discount'])}
        {$formObj->getDDRowBySQL('Type', 'type', $sqlGiftType, $row['type'], $expVl)}
        {$formObj->getDateRow('Effective Date From', 'eff_date_from', $row['eff_date_from'])}
        {$formObj->getDateRow('Effective Date To', 'eff_date_to', $row['eff_date_to'])}
        ";
        
        $text = "
        {$formObj->getFieldSetWrapped('Gift Card Maintenance Details', $fieldset1)}
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