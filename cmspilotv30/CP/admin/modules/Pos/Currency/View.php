<?
class CP_Admin_Modules_Pos_Currency_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getListDataCell($row['currency_id'], 'center')}
            {$listObj->getListRowEnd($row['currency_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Currency Code', 'c.code')}
        {$listObj->getListHeaderCell('Currency title', 'c.title')}
        {$listObj->getListHeaderCell('ID', 'c.currency_id', 'headerCenter')}
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
        {$formObj->getTBRow('Currency Code', 'code')}
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
        
        $sqlRedeemGroup  = $fn->getValueListSQL('redeemMemberGroup');
        $expVl = array('sqlType' => 'OneField');

        $fielset1 = "
        {$formObj->getTBRow('Currency Code', 'code', $ln->gfv($row, 'code', '0'))}
        {$formObj->getTBRow('Currency title', 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getTARow('Description', 'description', $ln->gfv($row, 'description', '0'))}
        ";
        
        $text = "
        {$formObj->getFieldSetWrapped('Currency Maintenance Details', $fielset1)}
        ";

        return $text;
    }

    //==================================================================//
    function getRightPanel($row){
        $media = Zend_Registry::get('media');
                
        $text ="
        {$media->getRightPanelMediaDisplay('Picture', 'pos_currency', 'picture', $row)}
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