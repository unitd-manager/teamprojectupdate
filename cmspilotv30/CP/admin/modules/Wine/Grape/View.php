<?
class CP_Admin_Modules_Wine_Grape_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['chi_title'])}
            {$listObj->getListDataCell($row['synonyms'])}    
            {$listObj->getListDataCell($row['chi_synonyms'])}    
            {$listObj->getListDataCell($fn->getYesNo($row['show_in_nav']))}   
            {$listObj->getListSortOrderField($row, 'grape_id')}
            {$listObj->getListPublishedImage($row['published'], $row['grape_id'])}
            {$listObj->getListRowEnd($row['grape_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.wine.producer.lbl.title', 'Title'), 'g.title')}
        {$listObj->getListHeaderCell($ln->gd('m.wine.producer.lbl.titleChi', 'Title (Chinese)'), 'g.chi_title')}
        {$listObj->getListHeaderCell($ln->gd('m.wine.producer.lbl.synonyms', 'Synonyms'), 'g.title')}
        {$listObj->getListHeaderCell($ln->gd('m.wine.producer.lbl.synonymsChi', 'Synonyms (Chinese)'), 'g.chi_title')}
        {$listObj->getListHeaderCell($ln->gd('m.webBasic.section.lbl.showInNavigation', 'Show in Navigation'), 'g.show_in_nav')}
        {$listObj->getListSortOrderImage('g')}
        {$listObj->getListHeaderCell($ln->gd('cp.header.lbl.published', 'Published'), 'g.published', 'headerCenter')}
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
        {$formObj->getTBRow($ln->gd('m.wine.producer.lbl.title', 'Title'), 'title')}    
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
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $fieldset1 = "
        {$formObj->getTBRow($ln->gd('m.wine.producer.lbl.title', 'Title'), 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getTBRow($ln->gd('m.wine.producer.lbl.synonyms', 'Synonyms'), 'synonyms', $ln->gfv($row, 'synonyms', '0'))}
        {$formObj->getYesNoRRow($ln->gd('m.webBasic.section.lbl.showInNavigation', 'Show in Navigation'), 'show_in_nav', $row['show_in_nav'])}
        {$formObj->getYesNoRRow($ln->gd('cp.lbl.published', 'Published'), 'published', $row['published'])}
        ";


        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.wine.producer.lbl.producerDetails', 'Producer Details'), $fieldset1)}
        {$formObj->getCreationModificationText($row)}
        ";        
        
        return $text;
    }

   /**
     *
     */
    function getRightPanel($row){
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');

        $text = "
        ";

        return $text;
    }    
    
    /**
     *
     */
    function getQuickSearch() {
    }
    
    /**
     *
     */
    function getImportInstructions() {
        $cpPaths = Zend_Registry::get('cpPaths');

        $fn = Zend_Registry::get('fn');
        $importType = $fn->getReqParam('importType');
        
        $fileName = 'grape-import-template.xls';
        
        $url = "index.php?_spAction=streamFile&showHTML=0&modname=wine_grape&filename={$fileName}";
        
        $text = "
        <p>Accepted file type: xls</p>
        <p>Template: <a href='{$url}'>Download</a></p>
        ";
        
        return $text;
    }      
}