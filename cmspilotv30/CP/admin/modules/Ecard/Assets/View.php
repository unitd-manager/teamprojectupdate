<?
class CP_Admin_Modules_Ecard_Assets_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        
        $rows  = "";
        $rowCounter = 0;
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListSortOrderField($row, 'assets_id')}
            {$listObj->getListDataCell($row['content_type'])}
            {$listObj->getListDataCell($row['assets_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['assets_id'])}
            {$listObj->getListRowEnd($row['assets_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'a.title')}
        {$listObj->getListSortOrderImage()}
        {$listObj->getListHeaderCell('Content Type', 'a.content_type')}
        {$listObj->getListHeaderCell('ID', 'a.assets_id', 'headerCenter')}
        {$listObj->getListHeaderCell('Published', 'a.published', 'headerCenter')}
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
        {$formObj->getTBRow('Title', 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    //========================================================//
    //==================================================================//
    //==================================================================//
    //==================================================================//
    function getEdit($row) {
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $fnMod = Zend_Registry::get('fnMod');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $formObj->mode = $tv['action'];

        $fnMod = includeCPClass('ModuleFns', 'ecard_assets');
        $exp = array('sqlType' => 'OneField');

        $text = '';

        $fieldset1 = "
        {$formObj->getTBRow('Title', 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getDDRowByArr('Content Type', 'content_type', $fnMod->getContentRecordTypeArray(), $row['content_type'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        ";
        
        $fieldset2 = $formObj->getHTMLEditor('Description', 'description', $row['description']);

        $text = "
        {$formObj->getFieldSetWrapped('General Details', $fieldset1)}
        ";

        return $text;
    }

    //==================================================================//
    //========================================================//
    //==================================================================//
    function getRightPanel($row){
        $media = Zend_Registry::get('media');

        $text = "
        {$media->getRightPanelMediaDisplay('Picture', 'ecard_assets', 'picture', $row)}
        {$media->getRightPanelMediaDisplay('Music', 'ecard_assets', 'music', $row)}
        {$media->getRightPanelMediaDisplay('Attachment', 'ecard_assets', 'attachment', $row)}
        ";

        return $text;
    }

    //==================================================================//
    //==================================================================//


    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $am = Zend_Registry::get('am');
        $fn = Zend_Registry::get('fn');
        
        $text = '';

        
        return $text;
    }
}