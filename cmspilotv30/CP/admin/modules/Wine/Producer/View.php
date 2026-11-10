<?
class CP_Admin_Modules_Wine_Producer_View extends CP_Common_Modules_Wine_Producer_View
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $ln = Zend_Registry::get('ln');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['chi_title'])}
            {$listObj->getListDataCell($row['producer_code'])}    
            {$listObj->getListSortOrderField($row, 'producer_id')}
            {$listObj->getListPublishedImage($row['published'], $row['producer_id'])}
            {$listObj->getListRowEnd($row['producer_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.wine.producer.lbl.title', 'Title'), 'p.title')}
        {$listObj->getListHeaderCell($ln->gd('m.wine.producer.lbl.titleChi', 'Title (Chinese)'), 'p.chi_title')}
        {$listObj->getListHeaderCell($ln->gd('m.wine.producer.lbl.producerCode', 'Producer Code'), 'p.producer_code')}
        {$listObj->getListSortOrderImage('p')}
        {$listObj->getListHeaderCell($ln->gd('cp.header.lbl.published', 'Published'), 'p.published', 'headerCenter')}            
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
        {$formObj->getTBRow($ln->gd('m.wine.producer.lbl.producerCode', 'Producer Code'), 'producer_code')}    
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
        {$formObj->getTBRow($ln->gd('m.wine.producer.lbl.producerCode', 'Producer Code'), 'producer_code', $row['producer_code'])}               
        {$formObj->getTBRow($ln->gd('m.wine.producer.lbl.title', 'Title'), 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getYesNoRRow($ln->gd('cp.lbl.published', 'Published'), 'published', $row['published'])}
        ";

        $fieldset2 = $formObj->getHTMLEditor($ln->gd('m.wine.producer.lbl.description', 'Description'), 'description', $ln->gfv($row, 'description', '0'));

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.wine.producer.lbl.producerDetails', 'Producer Details'), $fieldset1)}
        {$formObj->getFieldSetWrapped($ln->gd('m.wine.producer.lbl.description', 'Description'), $fieldset2)}
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
        {$media->getRightPanelMediaDisplay($ln->gd('m.wine.producer.lbl.picture', 'Picture'), 'wine_producer', 'picture', $row)}
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
        
        $fileName = 'producer-import-template.xls';
        
        $url = "index.php?_spAction=streamFile&showHTML=0&modname=wine_producer&filename={$fileName}";
        
        $text = "
        <p>Accepted file type: xls</p>
        <p>Template: <a href='{$url}'>Download</a></p>
        ";
        
        return $text;
    }     
}