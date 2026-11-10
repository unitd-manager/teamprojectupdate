<?
class CP_Admin_Modules_Directory_State_View extends CP_Common_Modules_Directory_State_View
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
            {$listObj->getListPublishedImage($row['published'], $row['state_id'])}
            {$listObj->getListDataCell($row['state_id'], 'center')}
            {$listObj->getListRowEnd($row['state_id'])}
			";

        	$rowCounter++;
		}
         
        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.directory.state.lbl.state'), 's.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.state.lbl.country'), 'c.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.state.lbl.published'), 's.published', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.state.lbl.id'), 's.state_id', 'headerCenter')}
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
        {$formObj->getTBRow($ln->gd('m.directory.state.lbl.state'), 'title')}
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
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        
        $expCountry = array('detailValue' => $row['country_title']);
        $sqlCountry = $fn->getDDSql('common_country');

        $fielset1  = "
        {$formObj->getTBRow($ln->gd('m.directory.state.lbl.state'), 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.state.lbl.country'), 'country_id', $sqlCountry, $row['country_id'], $expCountry)}
        {$formObj->getYesNoRRow($ln->gd('m.directory.state.lbl.published'), 'published', $row['published'] )} 
		";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.state.lbl.mainDetails'), $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
	function getRightPanel($row) {

        $text = "
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