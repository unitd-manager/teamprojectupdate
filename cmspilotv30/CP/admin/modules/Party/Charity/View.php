<?
class CP_Admin_Modules_Party_Charity_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');

        $rowCounter = 0;
        $rows = '';

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['charity_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['charity_id'])}
            {$listObj->getListRowEnd($row['charity_id'])}
			";

        	$rowCounter++;
		}

        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'c.title')}
        {$listObj->getListHeaderCell('ID', 'c.charity_id', 'headerCenter')}
        {$listObj->getListHeaderCell('Published', 'c.published', 'headerCenter')}
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

        $fieldset = "
        {$formObj->getTBRow('Title', 'title')}
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

        $formObj->mode = $tv['action'];

        $fielset1  = "
        {$formObj->getTBRow('Title', 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
		";

        $fieldset2 = $formObj->getHTMLEditor('Description Short', 'description_short', $ln->gfv($row, 'description_short', '0'));
        $fieldset3 = $formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'));

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Description Short', $fieldset2)}
        {$formObj->getFieldSetWrapped('Description', $fieldset3)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
	function getRightPanel($row) {
        $media = Zend_Registry::get('media');

        $text = "
        {$media->getRightPanelMediaDisplay('Charity Image', 'party_charity', 'picture', $row)}
        {$media->getRightPanelMediaDisplay('Charity Logo Image', 'party_charity', 'logoImage', $row)}
        {$media->getRightPanelMediaDisplay('Other Thumb Image', 'party_charity', 'otherThumbImage', $row)}
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