<?
class CP_Admin_Modules_Ecommerce_Color_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getListDataCell($row['title'])}
            {$listObj->getGoToDetailText($rowCounter, $row['code'])}
            {$listObj->getListThumbMediaImage('color', 'picture', $row['color_id'])}
            {$listObj->getListRowEnd($row['color_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Color Name', 'c.title')}
        {$listObj->getListHeaderCell('Color Code', 'c.code')}
        {$listObj->getListHeaderCell()}
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
        {$formObj->getTBRow('Color', 'title')}
        {$formObj->getTBRow('Code', 'code')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Details', $fieldset)}
        ";

        return $text;
    }

    //==================================================================//
    function getEdit($row) {
        $formObj = Zend_Registry::get('formObj');
        $tv = Zend_Registry::get('tv');

        $formObj->mode = $tv['action'];

        $fieldset = "
        {$formObj->getTBRow('Color', 'title', $row['title'])}
        {$formObj->getTBRow('Code', 'code', $row['code'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Details', $fieldset)}
        {$formObj->getCreationModificationText($row)}
        ";
        
        return $text;
    }

    //==================================================================//
    function getRightPanel($row){
        $media = Zend_Registry::get('media');

        $text ="
        {$media->getRightPanelMediaDisplay('Picture', 'ecommerce_color', 'picture', $row)}
        ";
        return $text;
    }
}