<?
class CP_Admin_Modules_ManPower_Documents_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
	        {$listObj->getListDataCell($row['title'])}
	        {$listObj->getListDataCell($row['module_name'])}
            {$listObj->getListRowEnd($row['documents_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'title')}
        {$listObj->getListHeaderCell('Module', 'module_name')}
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
        

        $spArray = array(
            "Staff"
           ,"Candidate"
           ,"Client"
           ,"Agent"
        );

        $fieldset1 = "
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getDDRowByArr('Module', 'module_name', $spArray, $row['module_name'])}
		";
		
        $fieldset2 = "
        {$formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'))}
        ";

		
        $text = "
        {$formObj->getFieldSetWrapped('Documents Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Description', $fieldset2)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     */
    function getRightPanel($row){
        $media = Zend_Registry::get('media');
        $fn = Zend_Registry::get('fn');
        $comment = getCPPluginObj('common_comment');
        $displayLinkData = Zend_Registry::get('displayLinkData');


        $text ="
    ";
        
        return $text;
    }
    
    /**
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');

        $module_name = $fn->getReqParam('module_name');

        $spArray = array(
            "Staff"
           ,"Candidate"
           ,"Client"
           ,"Agent"
        );

        $text = "
        <td>
            <select name='module_name'>
                <option value=''>Module</option>
                {$cpUtil->getDropDown1($spArray, $module_name)}
            </select>
        </td>    
        ";        
        
        return $text;
    }
}