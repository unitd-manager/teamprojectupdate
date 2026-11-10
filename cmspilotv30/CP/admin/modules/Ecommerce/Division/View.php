<?
class CP_Admin_Modules_Ecommerce_Division_View extends CP_Common_Modules_Ecommerce_Division_View
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListSortOrderField($row, 'division_id')}
            {$listObj->getListDataCell($row['email'])}
            {$listObj->getListDataCell($row['division_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['division_id'])}
            {$listObj->getListRowEnd($row['division_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'd.title')}
        {$listObj->getListSortOrderImage()}
        {$listObj->getListHeaderCell('Admin Email', 'd.email')}
        {$listObj->getListHeaderCell('ID', 'd.division_id', 'headerCenter')}
        {$listObj->getListHeaderCell('Published', 'd.published', 'headerCenter')}
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
        $ln = Zend_Registry::get('ln');

        $fieldset = "
        {$formObj->getTextBoxRow('Title', 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $fieldset1 = "
        {$formObj->getTextBoxRow('Title', 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getTextBoxRow($ln->gd('Admin Email'), 'email', $row['email'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Division Details', $fieldset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {

        $text = '';

        return $text;
    }
    
    /**
     *
     */
    function getRightPanel($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $ln = Zend_Registry::get('ln');
        $media = Zend_Registry::get('media');

        $text = "
        {$media->getRightPanelMediaDisplay('Picture', 'ecommerce_division', 'picture', $row)}
        ";

        return $text;
    }    
}