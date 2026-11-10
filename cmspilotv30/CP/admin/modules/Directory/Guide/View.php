<?
class CP_Admin_Modules_Directory_guide_View extends CP_Common_Modules_Directory_guide_View
{
    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $rowCounter = 0;
        $rows = '';

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['contact_name'])}
            {$listObj->getListDataCell($row['record_type'])}
            {$listObj->getListDataCell($row['no_of_business'], 'center')}
            {$listObj->getListDataCell($fn->getYesNo($row['recommended']), 'center')}
            {$listObj->getListDataCell($fn->getYesNo($row['open_guide']), 'center')}
            {$listObj->getListDataCell($row['guide_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['guide_id'])}
            {$listObj->getListRowEnd($row['guide_id'])}
            ";
            $rowCounter++;

        }
         
        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.directory.guide.lbl.title'), 'g.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.guide.lbl.contactName'), 'contact_name')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.guide.lbl.recordType'), 'record_type')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.guide.lbl.no.Places'), 'no_of_business', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.guide.lbl.recommended', 'Recommended'), 'g.recommended', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.guide.lbl.openGuide', 'Open Guide'), 'g.open_guide', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.guide.lbl.id'), 'g.guide_id', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.guide.lbl.published'), 'g.published', 'headerCenter')}
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
        {$formObj->getTBRow($ln->gd('m.directory.guide.lbl.title'), 'title')}
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
        $fn = Zend_Registry::get('fn');      
        $db = Zend_Registry::get('db');      

        $sqlContact = "
        SELECT c.contact_id
              ,CONCAT_WS(' ', c.first_name, c.last_name) AS contact_name
        FROM contact c 
        ";
        $expCont    = array('detailValue' => $row['contact_name']);
        
        $recTypeArr = array('Collection', 'To Do List');
        
        $fieldset1  = "
        {$formObj->getTBRow($ln->gd('m.directory.guide.lbl.title'), 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.guide.lbl.contact'), 'contact_id', $sqlContact, $row['contact_id'], $expCont)}
        {$formObj->getYesNoRRow($ln->gd('m.directory.guide.lbl.published'), 'published', $row['published'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.guide.lbl.openGuide'), 'open_guide', $row['open_guide'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.guide.lbl.recommended'), 'recommended', $row['recommended'])}
        {$formObj->getRRow($ln->gd('m.directory.guide.lbl.recordType'), 'record_type', $row['record_type'], $recTypeArr)}
        ";

        $fieldset2 = "
        {$formObj->getTARow($ln->gd('m.directory.guide.lbl.description'), 'description', $ln->gfv($row, 'description', '0'))}
        ";
        
        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.guide.lbl.mainDetails'), $fieldset1)}
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.guide.lbl.description'), $fieldset2)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
	function getRightPanel($row) {
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $fn = Zend_Registry::get('fn');
        $comment = getCPPluginObj('common_comment');
        $ln = Zend_Registry::get('ln');

        $record_id = $fn->getIssetParam($row, 'guide_id');

        $text = "
        {$media->getRightPanelMediaDisplay($ln->gd('m.directory.guide.link.picture'), 'directory_guide', 'picture', $row)}
        {$displayLinkData->getLinkPortalMain('directory_guide', 'directory_businessLink', $ln->gd('m.directory.guide.link.linkedBusinesses'), $row)}
        {$comment->getView(array(
             'roomName' => 'directory_guide'
            ,'recordId' => $record_id
        ))}
		";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');


        $text = "
        ";
        
        return $text;
    }
}