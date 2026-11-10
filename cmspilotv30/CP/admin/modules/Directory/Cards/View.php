<?
class CP_Admin_Modules_Directory_Cards_View extends CP_Common_Modules_Directory_Cards_View
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
            {$listObj->getListDataCell($row['card_id'], 'center')}
            {$listObj->getListRowEnd($row['card_id'])}
			";

        	$rowCounter++;
		}
         
        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.directory.cards.lbl.title'), 'c.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.cards.lbl.country'), 'co.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.cards.lbl.id'), 'c.card_id', 'headerCenter')}
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
        {$formObj->getTBRow($ln->gd('m.directory.cards.lbl.title'), 'title')}
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
        
        $fielset1  = "
        {$formObj->getTBRow($ln->gd('m.directory.cards.lbl.title'), 'title', $row['title'])}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.cards.lbl.country'), 'country_id', $sqlCountry, $row['country_id'], $expCountry)}
		";

        $fieldset2 = $formObj->getHTMLEditor($ln->gd('m.directory.cards.lbl.disclaimer'), 'description', $ln->gfv($row, 'description', '0'));

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.cards.lbl.mainDetails'), $fielset1)}
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.cards.lbl.description'), $fieldset2)}
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
        {$media->getRightPanelMediaDisplay($ln->gd('m.directory.cards.link.logo'), 'directory_cards', 'picture', $row)}
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