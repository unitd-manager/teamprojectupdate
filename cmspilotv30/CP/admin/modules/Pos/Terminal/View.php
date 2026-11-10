<?
class CP_Admin_Modules_Pos_Terminal_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getGoToDetailText($rowCounter, $row['terminal_code'])}
            {$listObj->getListDataCell($row['shop_title'])}
            {$listObj->getListDataCell($row['terminal_id'], 'center')}
            {$listObj->getListRowEnd($row['terminal_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Name', 't.title')}
        {$listObj->getListHeaderCell('Code', 't.terminal_code')}
        {$listObj->getListHeaderCell('Shop Code', 's.code')}
        {$listObj->getListHeaderCell('ID', 't.terminal_id', 'headerCenter')}
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
        {$formObj->getTBRow('Terminal Name', 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    //==================================================================//
    function getEdit($row) {
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        
        $sqlShop = $fn->getDDSql('pos_shop');

        $fielset1 = "
        {$formObj->getTBRow('Name', 'title', $row['title'])}
        {$formObj->getTBRow('Code', 'terminal_code', $row['terminal_code'])}
        {$formObj->getTARow('Description', 'description', $row['description'])}
        {$formObj->getDDRowBySQL('Shop', 'shop_id', $sqlShop, $row['shop_id'])}
        {$formObj->getTBRow('Receipt Printer Model', 'receipt_printer_model', $row['receipt_printer_model'])}
        {$formObj->getTBRow('Barcode Printer Model', 'barcode_printer_model', $row['barcode_printer_model'])}
        {$formObj->getTBRow('Stock Take Device', 'stock_take_device', $row['stock_take_device'])}
        {$formObj->getTBRow('Smart Card No', 'smart_card_no', $row['smart_card_no'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        ";
        
        $text = "
        {$formObj->getFieldSetWrapped('Terminal Details', $fielset1)}
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