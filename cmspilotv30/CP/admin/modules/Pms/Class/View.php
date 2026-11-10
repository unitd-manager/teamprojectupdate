<?
class CP_Admin_Modules_Pms_Class_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
			{$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['class_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'title')}
        {$listObj->getListHeaderCell('Status', 'status')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
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
     */
    function getEdit($row) {
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $sqlStatus = $fn->getValueListSQL('classStatus');
        $expVL = array('sqlType' => 'OneField');

        $fielset1 = "
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'] , $expVL)}
        {$formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'))}
		";
		
        $text = "
        {$formObj->getFieldSetWrapped('Class Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     */
    function getRightPanel($row){
        $media = Zend_Registry::get('media');
        $displayLinkData = Zend_Registry::get('displayLinkData');

        $text ="
        {$media->getRightPanelMediaDisplay('Attachments', 'pms_class', 'attachment', $row)}
		{$displayLinkData->getLinkPortalMain('pms_class', 'pms_contactLink', 'Contact Linked', $row)}
        ";
        
        return $text;
    }
    
    /**
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $status = $fn->getReqParam('status');

        $sqlStatus = $fn->getValueListSQL('classStatus');

        $text = "
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlStatus, $status)}
            </select>
        </td>    
        ";        
        
        return $text;
    }
}