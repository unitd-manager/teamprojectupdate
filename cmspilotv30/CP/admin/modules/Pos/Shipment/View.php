<?
class CP_Admin_Modules_Pos_Shipment_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getListDataCell($row['shipment_id'], 'center')}
            {$listObj->getListRowEnd($row['shipment_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Shipment Code', 's.code')}
        {$listObj->getListHeaderCell('Shipment Name', 's.title')}
        {$listObj->getListHeaderCell('ID', 's.shipment_id', 'headerCenter')}
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
        {$formObj->getTBRow('Shipment Code', 'code')}
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
        
        $fielset1 = "
        {$formObj->getTBRow('Shipment Code', 'code', $ln->gfv($row, 'code', '0'))}
        {$formObj->getTBRow('Shipment Name', 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getTARow('Description', 'description', $ln->gfv($row, 'description', '0'))}
        ";
        
        $text = "
        {$formObj->getFieldSetWrapped('Shipment Maintenance Details', $fielset1)}
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