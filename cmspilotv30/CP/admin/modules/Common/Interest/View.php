<?
class CP_Admin_Modules_Common_Interest_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $ln = Zend_Registry::get('ln');

        $rowCounter = 0;
        $rows       = "";

        foreach ($dataArray as $row){
            $rows .="
    		{$listObj->getListRowHeader($row, $rowCounter)}
    		{$listObj->getGoToDetailText($rowCounter, $row['title'])}
    		{$listObj->getListDataCell($row['interest_id']     , "center")}
    		{$listObj->getListPublishedImage($row['published'] , $row['interest_id'])}
    		{$listObj->getListRowEnd($row['interest_id'])}
			";

        	$rowCounter++;
		}
         
        $text = "
    	{$listObj->getListHeader()}
    	{$listObj->getListHeaderCell($ln->gd('m.common.interest.lbl.title', 'Title'), 'a.title')}
    	{$listObj->getListHeaderCell($ln->gd('m.common.interest.lbl.id', 'ID'), 'a.interest_id', 'headerCenter')}
    	{$listObj->getListHeaderCell($ln->gd('m.common.interest.lbl.published', 'Published'), 'a.published', 'headerCenter')}
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
        {$formObj->getTBRow($ln->gd('m.common.interest.lbl.title', 'Title'), 'title')}
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
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $formObj->mode = $tv['action'];

        $description    = '';
        $fieldset2      = '';
        $metaData       = '';

        if ($cpCfg['m.common.interest.showDesc'] == 1){
            $description = $formObj->getHTMLEditor('Description', 'description', $row['description']);

            $fieldset2  = "
            {$formObj->getFieldSetWrapped($ln->gd('m.common.interest.lbl.description', 'Description'), $description)}
            ";
        }

        if ($cpCfg['m.common.interest.showMetaData'] == 1) {
            $metaData = $formObj->getMetaData($row);
        }

        $fieldset1  = "
        {$formObj->getTBRow($ln->gd('m.common.interest.lbl.title', 'Title'), 'title', $ln->gfv($row, 'title', '0') )}
        {$formObj->getYesNoRRow($ln->gd('m.common.interest.lbl.published', 'Published'), 'published', $row['published'])}
		";
        
        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.common.interest.lbl.interestDetails', 'Interest Details'), $fieldset1)}
        {$fieldset2}
        {$metaData}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row) {
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $ln = Zend_Registry::get('ln');

        $text  = "
        {$displayLinkData->getLinkPortalMain('common_interest', 'common_contactLink', $ln->gd('m.common.interest.link.contactsLinked', 'Contacts Linked'), $row)}
		";
        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
    }
}