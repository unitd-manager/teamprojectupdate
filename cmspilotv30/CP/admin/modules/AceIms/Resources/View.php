<?
class CP_Admin_Modules_AceIms_Resources_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
		    {$listObj->getGoToDetailText($rowCounter, $row['book_name'])}
            {$listObj->getListDataCell($row['author'])}
            {$listObj->getListDataCell($row['cost'], 'center')}
            {$listObj->getListDataCell($row['resources_id'], 'center')}
            {$listObj->getListRowEnd($row['resources_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Book Name', 'book_name')}
        {$listObj->getListHeaderCell('Author', 'author')}
        {$listObj->getListHeaderCell('Cost', 'cost' , 'headerCenter')}
        {$listObj->getListHeaderCell('ID', 'resources_id' , 'headerCenter')}
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
        {$formObj->getTBRow('Book Name', 'book_name')}
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
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $book_available = 0;
    
        $sqlDepartment = $fn->getValueListSQL('department');        
        $expVl         = array('sqlType' => 'OneField');
        $expEdit       = array('isEditable' => 0);
        
        $fieldset1 = "
        {$formObj->getTBRow('Book Name', 'book_name', $row['book_name'])}
        {$formObj->getTBRow('Author', 'author', $row['author'])}
        {$formObj->getTBRow('Cost', 'cost', $row['cost'])}
   		";
		
        $fieldset2 = "
        {$formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'))}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Resources Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Description', $fieldset2)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     */
    function getRightPanel($row){
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        
        $record_id = $fn->getIssetParam($row, 'resources_id');
        
        $text = "
        {$displayLinkData->getLinkPortalMain('aceIms_resources', 'aceIms_contactLink', 'Students Linked', $row)}
        ";

        return $text;
    }

    /**
     */
    function getQuickSearch() {

        $text = "";        
        
        return $text;
    }
}