<?
class CP_Admin_Modules_Directory_AltSpelling_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getGoToDetailText($rowCounter, $row['spelling'])}
            {$listObj->getListDataCell($row['alt_spelling'])}
            {$listObj->getListSortOrderField($row, 'alt_spelling_id')}
            {$listObj->getListDataCell($row['alt_spelling_id'], 'center')}
            {$listObj->getListRowEnd($row['alt_spelling_id'])}
			";

        	$rowCounter++;
		}
         
        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.directory.altSpelling.lbl.spelling'), 'asp.spelling')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.altSpelling.lbl.altSpelling'), 'asp.alt_spelling')}
        {$listObj->getListSortOrderImage('sm')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.altSpelling.lbl.id'), 'asp.alt_spelling_id', 'headerCenter')}
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
        {$formObj->getTBRow($ln->gd('m.directory.altSpelling.lbl.spelling'), 'spelling')}
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
        
        $fielset1  = "
        {$formObj->getTBRow($ln->gd('m.directory.altSpelling.lbl.spelling'), 'spelling', $row['spelling'])}
        {$formObj->getTBRow($ln->gd('m.directory.altSpelling.lbl.spelling'), 'alt_spelling', $row['alt_spelling'])}
		";


        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.altSpelling.lbl.mainDetails'), $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

	function getRightPanel($row) {
        $media = Zend_Registry::get('media');

        $text = "
		";

        return $text;
    }

    function getQuickSearch() {

        $text = "
        ";
        
        return $text;
    }
}