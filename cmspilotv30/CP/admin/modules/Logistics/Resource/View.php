<?
class CP_Admin_Modules_Logistics_Resource_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $rows  = "";
        $rowCounter = 0;
        

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
			{$listObj->getGoToDetailText($rowCounter, $row['resource_code'])}
            {$listObj->getListDataCell($row['resource_name'])}
            {$listObj->getListDataCell($row['phone'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($row['resource_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['resource_id'])}
            {$listObj->getListRowEnd($row['resource_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Resource Code', 'resource_code')}
        {$listObj->getListHeaderCell('Name', 'r.resource_name')}
        {$listObj->getListHeaderCell('Phone', 'r.phone')}
        {$listObj->getListHeaderCell('status', 'r.status')}
        {$listObj->getListHeaderCell('Resource Id', 'resource_id' , 'headerCenter')}
        {$listObj->getListHeaderCell('Published', 'r.published', 'headerCenter')}
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
        {$formObj->getTBRow('Resource Code', 'resource_code')}
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
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        
        $sqlStatus = $fn->getValueListSQL('resourceStatus');
        $sqlCategory = $fn->getValueListSQL('resourceCategory');
        $expVl = array('sqlType' => 'OneField');

        $fieldset1 = "
        {$formObj->getTBRow('Resource Code', 'resource_code', $row['resource_code'])}
        {$formObj->getTBRow('Name', 'resource_name', $row['resource_name'])}
        {$formObj->getTBRow('Phone', 'phone', $row['phone'])}
        {$formObj->getTBRow('E-Mail', 'email', $row['email'])}
        {$formObj->getTBRow('Role', 'role', $row['role'])}
        {$formObj->getDDRowBySQL('Category', 'category', $sqlCategory, $row['category'], $expVl)}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
		";

        $fieldset2 = "
        {$formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'))}
        ";
		
        $text = "
        {$formObj->getFieldSetWrapped('Resource Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Description', $fieldset2)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $media = Zend_Registry::get('media');

        $text ="
        {$media->getRightPanelMediaDisplay('Attachments', 'logistics_resource', 'attachment', $row)}
        ";
        
        return $text;
    }
    
    /**
     *
     */
    function getQuickSearch() {
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');

        $text = "
        ";        
        
        return $text;
    }
}