<?
class CP_Admin_Modules_Directory_Delivery_View extends CP_Common_Modules_Directory_Delivery_View
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
            {$listObj->getListDataCell($row['delivery_id'], 'center')}
            {$listObj->getListRowEnd($row['delivery_id'])}
			";

        	$rowCounter++;
		}
         
        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.directory.delivery.lbl.title'), 'sm.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.delivery.lbl.url'), 'sm.url')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.delivery.lbl.country'), 'c.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.delivery.lbl.id'), 'sm.delivery_id', 'headerCenter')}
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
        {$formObj->getTBRow($ln->gd('m.directory.delivery.lbl.title'), 'title')}
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
        {$formObj->getTBRow($ln->gd('m.directory.delivery.lbl.title'), 'title', $row['title'])}
        {$formObj->getTBRow($ln->gd('m.directory.delivery.lbl.url'), 'url', $row['url'])}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.delivery.lbl.country'), 'country_id', $sqlCountry, $row['country_id'], $expCountry)}
		";

        $fieldset2 = $formObj->getHTMLEditor($ln->gd('m.directory.delivery.lbl.description'), 'description', $ln->gfv($row, 'description', '0'));

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.delivery.lbl.mainDetails'), $fielset1)}
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.delivery.lbl.description'), $fieldset2)}
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
        {$media->getRightPanelMediaDisplay($ln->gd('m.directory.delivery.link.logo'), 'directory_delivery', 'picture', $row)}
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