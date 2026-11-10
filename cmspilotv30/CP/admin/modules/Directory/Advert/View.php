<?
class CP_Admin_Modules_Directory_Advert_View extends CP_Common_Modules_Directory_Advert_View
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
            {$listObj->getListDataCell($row['country_title'])}
            {$listObj->getListDataCell($row['advert_id'], 'center')}
            {$listObj->getListRowEnd($row['advert_id'])}
			";

        	$rowCounter++;
		}
         
        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.directory.advert.lbl.title'), 'a.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.advert.lbl.country'), 'co.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.advert.lbl.id'), 'a.advert_id', 'headerCenter')}
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
        {$formObj->getTBRow($ln->gd('m.directory.advert.lbl.title'), 'title')}
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
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        
        $formObj->mode = $tv['action'];

        $expCountry = array('detailValue' => $row['country_title']);
        $sqlCountry = $fn->getDDSql('common_country');
        
        $fieldset1 = "
        {$formObj->getTBRow($ln->gd('m.directory.advert.lbl.title'), 'title', $row['title'])}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.advert.lbl.country'), 'country_id', $sqlCountry, $row['country_id'], $expCountry)}
        {$formObj->getTARow($ln->gd('m.directory.advert.lbl.description'), 'description', $row['description'])}
		";
        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.advert.lbl.mainDetails'), $fieldset1)}
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
        {$media->getRightPanelMediaDisplay($ln->gd('m.directory.advert.link.logo'), 'directory_advert', 'picture', $row)}
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