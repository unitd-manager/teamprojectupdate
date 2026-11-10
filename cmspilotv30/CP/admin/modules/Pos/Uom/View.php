<?
class CP_Admin_Modules_Pos_Uom_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');

        $rows  = "";
        $value = "";

        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['code'])}
            {$listObj->getListDataCell($row['title'])}
            {$listObj->getListDataCell($row['description'])}
            {$listObj->getListDataCell($row['uom_id'], 'center')}
            {$listObj->getListRowEnd($row['uom_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Code', 'u.code')}
        {$listObj->getListHeaderCell('Name', 'u.title')}
        {$listObj->getListHeaderCell('Description', 'u.description')}
        {$listObj->getListHeaderCell('ID', 'u.uom_id', 'headerCenter')}
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

        $fieldset = "
        {$formObj->getTBRow('Code', 'code')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode = $tv['action'];
        
        $sqlUom = $fn->getDDSql('pos_uom');

        $fielset1 = "
        {$formObj->getTBRow('Code', 'code', $row['code'])}
        {$formObj->getTBRow('Name', 'title', $row['title'])}
        {$formObj->getTARow('Description', 'description', $row['description'])}
        {$formObj->getYesNoRRow('Use Exchange', 'use_exchange_rate', $row['use_exchange_rate'])}
        {$formObj->getDDRowBySQL('Exchange to Other UOM Code', 'exchange_other_uom_code', $sqlUom, $row['exchange_other_uom_code'])}
        {$formObj->getTBRow('Exchange', 'exchange_rage_to', $row['exchange_rage_to'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped('UOM Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
    }

    /**
     *
     */
    function getQuickSearch() {
    }
}