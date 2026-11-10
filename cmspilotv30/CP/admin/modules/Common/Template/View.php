<?
class CP_Admin_Modules_Common_Template_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');

        $rowCounter = 0;
        $rows       = "";

        foreach ($dataArray as $row){
            $rows .="
    		{$listObj->getListRowHeader($row, $rowCounter)}
    		{$listObj->getGoToDetailText($rowCounter, $row['title'])}
    		{$listObj->getListDataCell($row['record_type'])}
    		{$listObj->getListDataCell($row['template_id'], 'center')}
    		{$listObj->getListRowEnd($row['template_id'])}
			";

        	$rowCounter++;
		}
         
        $text = "
    	{$listObj->getListHeader()}
    	{$listObj->getListHeaderCell('Title', 't.title')}
    	{$listObj->getListHeaderCell('Record Type', 't.record_type')}
    	{$listObj->getListHeaderCell('ID', 't.template_id', 'headerCenter')}
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
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode = $tv['action'];
        
        $arrType = array(
            'Broadcast'
        );
        $fielset1  = "
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getDDRowByArr('Record Type', 'record_type', $arrType, $row['record_type'])}
		";

        $fieldset2 = $formObj->getHTMLEditor('Description', 'description', $row['description']);

        $text = "
        {$formObj->getFieldSetWrapped('template Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Description', $fieldset2)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
    }
}