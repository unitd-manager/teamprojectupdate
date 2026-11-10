<?
class CP_Admin_Modules_Directory_SocialMedia_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $ln = Zend_Registry::get('ln');

        $rowCounter = 0;
        $rows = '';

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['url'])}
            {$listObj->getListDataCell($row['country_title'])}
            {$listObj->getListSortOrderField($row, 'social_media_id')}
            {$listObj->getListDataCell($row['social_media_id'], 'center')}
            {$listObj->getListRowEnd($row['social_media_id'])}
			";

        	$rowCounter++;
		}
         
        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.directory.socialMedia.lbl.title'), 'sm.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.socialMedia.lbl.url'), 'sm.url')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.socialMedia.lbl.country'), 'c.title')}
        {$listObj->getListSortOrderImage('sm')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.socialMedia.lbl.id'), 'sm.social_media_id', 'headerCenter')}
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
        {$formObj->getTBRow($ln->gd('m.directory.socialMedia.lbl.title'), 'title')}
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
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        
        $formObj->mode = $tv['action'];
        
        $expCountry = array('detailValue' => $row['country_title']);
        $sqlCountry = $fn->getDDSql('common_country');
        
        $fieldset1  = "
        {$formObj->getTBRow($ln->gd('m.directory.socialMedia.lbl.title'), 'title', $row['title'])}
        {$formObj->getTBRow($ln->gd('m.directory.socialMedia.lbl.url'), 'url', $row['url'])}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.socialMedia.lbl.country'), 'country_id', $sqlCountry, $row['country_id'], $expCountry)}
        {$formObj->getTBRow($ln->gd('m.directory.socialMedia.lbl.cssStyle'), 'css_style', $row['css_style'])}
        {$formObj->getTARow($ln->gd('m.directory.socialMedia.lbl.embedCode1'), 'embed_code1', $row['embed_code1'])}
        {$formObj->getTARow($ln->gd('m.directory.socialMedia.lbl.embedCode2'), 'embed_code2', $row['embed_code2'])}
		";

        $fieldset2 = $formObj->getHTMLEditor($ln->gd('m.directory.socialMedia.lbl.description'), 'description', $ln->gfv($row, 'description', '0'));

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.socialMedia.lbl.mainDetails'), $fieldset1)}
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.socialMedia.lbl.description'), $fieldset2)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
	function getRightPanel($row) {
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        
        $text = "
        {$media->getRightPanelMediaDisplay($ln->gd('m.directory.socialMedia.link.logo'), 'directory_socialMedia', 'picture', $row)}
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