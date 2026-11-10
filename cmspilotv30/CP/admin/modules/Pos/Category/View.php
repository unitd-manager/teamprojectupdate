<?
class CP_Admin_Modules_Pos_Category_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows  = "";

        $rowCounter = 0;
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['code'])}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['description'])}
            {$listObj->getListDataCell($row['category_id'], 'center')}
            {$listObj->getListRowEnd($row['category_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Code', 'c.code')}
        {$listObj->getListHeaderCell('Title', 'c.title')}
        {$listObj->getListHeaderCell('Description', 'c.description')}
        {$listObj->getListHeaderCell('ID', 'c.category_id', 'headerCenter')}
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
        {$formObj->getTBRow('Title', 'title')}
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
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');

        $fieldset1 = "
        {$formObj->getTBRow('Code', 'code', $ln->gfv($row, 'code', '0'))}
        {$formObj->getTBRow('Title', 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getTARow('Description', 'description', $ln->gfv($row, 'description', '0'))}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Category Details', $fieldset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $media = Zend_Registry::get('media');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($cpCfg['subCategory'] == 0) {
            $subCategory = '';
        } else {
            $subCategory = $displayLinkData->getLinkPortalMain('pos_category', 'pos_subCategoryLink', 'Sub Category Linked', $row);
        }

        $text  = "
        {$subCategory}        
        ";

        return $text;
    }



    /**
     *
     */
    function getQuickSearch() {
        $text = "";

        return $text;
    }
}