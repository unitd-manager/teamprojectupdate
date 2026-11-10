<?
class CP_Admin_Modules_Pos_Interest_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');

        $rowCounter = 0;
        $rows       = "";

        foreach ($dataArray as $row){
            $rows .="
    		{$listObj->getListRowHeader($row, $rowCounter)}
    		{$listObj->getGoToDetailText($rowCounter, $row['code'])}
    		{$listObj->getGoToDetailText($rowCounter, $row['title'])}
    		{$listObj->getListDataCell($row['interest_id']     , "center")}
    		{$listObj->getListPublishedImage($row['published'] , $row['interest_id'])}
    		{$listObj->getListRowEnd($row['interest_id'])}
			";

        	$rowCounter++;
		}
         
        $text = "
    	{$listObj->getListHeader()}
    	{$listObj->getListHeaderCell("Code", "a.code")}
    	{$listObj->getListHeaderCell("Title", "a.title")}
    	{$listObj->getListHeaderCell("ID", "a.interest_id", "headerCenter")}
    	{$listObj->getListHeaderCell("Published", "a.published", "headerCenter")}
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
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        
        $formObj->mode = $tv['action'];
        $discountDode = $fn->getDDSql('pos_uom');
        $redeemCode = $fn->getDDSql('pos_uom');
        
        $fielset1  = "
        {$formObj->getTBRow('Code', 'code', $ln->gfv($row, 'code', '0') )}
        {$formObj->getTBRow('Title', 'title', $ln->gfv($row, 'title', '0') )}
        {$formObj->getTARow('Description', 'description', $ln->gfv($row, 'description', '0') )}
        {$formObj->getTBRow('Auto Upgrade Total Amount', 'auto_upgrade_total_amt', $ln->gfv($row, 'auto_upgrade_total_amt', '0') )}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Interest Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row) {
        $displayLinkData = Zend_Registry::get('displayLinkData');

        $text  = "
        {$displayLinkData->getLinkPortalMain('pos_interest', 'common_contactLink', 'Contacts Linked', $row)}
		";
        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
    }
}