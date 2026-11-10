<?
class CP_Admin_Modules_Common_Comment_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $rowCounter = 0;
        $rows = '';

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['recType'] . ' / ' . $row['record_title'])}
            {$listObj->getListDataCell($row['contact_name'])}
            {$listObj->getListDataCell($row['creation_date'])}
            {$listObj->getListDataCell($row['comments'])}
            {$listObj->getListDataCell($fn->getYesNo($row['published']), 'center')}
            {$listObj->getListDataCell($fn->getYesNo($row['abusive']), 'center')}
            {$listObj->getListDataCell($fn->getYesNo($row['verified']), 'center')}
            {$listObj->getListDataCell($row['comment_id'], 'center')}
            {$listObj->getListRowEnd($row['comment_id'])}
			";
        	$rowCounter++;
		}

        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.directory.comment.lbl.title'), 'record_title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.comment.lbl.contactName'), 'contact_name')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.comment.lbl.postDate'), 'c.creation_date')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.comment.lbl.comments'), '', 'w500')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.comment.lbl.published'), 'c.published', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.comment.lbl.abusive'), 'c.abusive', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.comment.lbl.verified'), 'c.verified', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.comment.lbl.id'), 'c.comment_id', 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
	    {$listObj->getListFooter()}
		";
        return $text;
    }

    function getQuickSearch() {
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpUtil = Zend_Registry::get('cpUtil');
        $formObj = Zend_Registry::get('formObj');

        $special_search = $fn->getReqParam('special_search');
        $business_id = $fn->getReqParam('business_id');
        $business_name = $fn->getReqParam('business_name');

        $spSearchArr = array(
            'Flagged'
           ,'Not-Flagged'
           ,'Published'
           ,'Not-Published'
           ,'Abusive'
           ,'Not-Abusive'
           ,'Verified'
           ,'Not-Verified'
        );

        $expComp  = array(
             'autoSgstModule' => 'directory_business'
            ,'autoSgstSrchFld' => 'business_name'
            ,'autoSgstActualFld' => 'business_id'
            ,'autoSgstActualFldVal' => $business_id
            ,'autoSgstCallBack' => 'Actions.submitSearchTop'
        );

        $text = "
        {$formObj->getTBRow($ln->gd('m.directory.comment.lbl.business'), 'business_name', $business_name, $expComp)}
        <div>
            <select name='special_search'>
                <option value=''>{$ln->gd('m.directory.comment.lbl.specialSearch')}</option>
                {$cpUtil->getDropDown1($spSearchArr, $special_search)}
            </select>
        </div>
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
        $expNoEdit = array('isEditable' => 0);

        $fieldset1  = "
        {$formObj->getTBRow($ln->gd('m.directory.comment.lbl.title'), 'business_id', $row['recType'] . ' / ' . $row['record_title'], $expNoEdit)}
        {$formObj->getTBRow($ln->gd('m.directory.comment.lbl.contactName'), 'contact_name', $row['contact_name'], $expNoEdit)}
        {$formObj->getTBRow($ln->gd('m.directory.comment.lbl.postDate'), 'creation_date', $row['creation_date'], $expNoEdit)}
        {$formObj->getTARow($ln->gd('m.directory.comment.lbl.comments'), 'comments', $row['comments'])}
		";

        $fieldset2  = "
        {$formObj->getYesNoRRow($ln->gd('m.directory.comment.lbl.published'), 'published', $row['published'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.comment.lbl.abusive'), 'abusive', $row['abusive'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.comment.lbl.verified'), 'verified', $row['verified'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.comment.lbl.mainDetails'), $fieldset1)}
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.comment.lbl.forAdmin'), $fieldset2)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    //==================================================================//
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');

        $text ="
        {$media->getRightPanelMediaDisplay('Picture', 'common_comment', 'picture', $row)}
        ";
        return $text;
    }
}