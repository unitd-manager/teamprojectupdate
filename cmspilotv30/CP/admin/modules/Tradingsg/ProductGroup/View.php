<?
class CP_Admin_Modules_Tradingsg_ProductGroup_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getListSortOrderField($row, 'product_group_id')}
            {$listObj->getListRowEnd($row['product_group_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'pg.title')}
        {$listObj->getListSortOrderImage('pg')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
            {$listObj->getListDataCell($row['code'])}
            {$listObj->getListDataCell($row['margin'])}
        {$listObj->getListHeaderCell('Code', 'pg.code')}
        {$listObj->getListHeaderCell('Margin', 'pg.margin')}
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

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
        ";

        $fieldset2 = $formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'));

        $text = "
        {$formObj->getFieldSetWrapped('Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Description', $fieldset2)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
        {$formObj->getTextBoxRow('Code', 'code', $row['code'])}
        {$formObj->getTextBoxRow('Margin', 'margin', $row['margin'])}
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
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');

        //{$displayLinkData->getLinkPortalMain('tradingsg_productGroup', 'tradingsg_categoryLink', 'Category Linked', $row)}

        $text = "
        ";

        return $text;
    }    
}