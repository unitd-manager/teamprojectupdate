<?
class CP_Admin_Modules_Project_StaffGroup_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
    function getList($dataArray) {
       global $db, $pager, $tv, $fn, $cpCfg, $modulesArr, $mediaArray, $listObj, $dateUtil;

        $rowCounter = 0;

        $rows = "";

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .="
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter , $row['title'])}
            {$listObj->getListDataCell($row['staff_group_id'], "center")}
            {$listObj->getListRowEnd($row['staff_group_id'])}
			";

        	$rowCounter++;
		}

        $text = "
		{$listObj->getListHeader()}
    	{$listObj->getListHeaderCell("Title", "a.title")}
    	{$listObj->getListHeaderCell("ID", "a.staff_group_id", "headerCenter")}
    	{$listObj->getListHeaderEnd()}
        {$rows}
	    {$listObj->getListFooter()}
		";
        return $text;
    }

    //==================================================================//
    function getNew() {
        $formObj = Zend_Registry::get('formObj');

        $fieldset = "
        {$formObj->getTBRow('Title', 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }


    //========================================================//
    //==================================================================//
    //==================================================================//
    //==================================================================//
    function getEdit($row) {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $ln = Zend_Registry::get('ln');
        $am = Zend_Registry::get('am');

        $formObj->mode = $tv['action'];

        $fielset1  = "
        {$formObj->getTBRow('Title', 'title', $row['title'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Staff Group Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    //========================================================//
    //==================================================================//
    //==================================================================//
    //==================================================================//
}