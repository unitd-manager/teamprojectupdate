<?
class CP_Admin_Modules_ManPower_CandidatePass_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getListDataCell($row['candidate_pass_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['candidate_pass_id'])}
            {$listObj->getListRowEnd($row['candidate_pass_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'cp.title')}
        {$listObj->getListHeaderCell('ID', 'cp.candidate_pass_id', 'headerCenter')}
        {$listObj->getListHeaderCell('Published', 'cp.published', 'headerCenter')}
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
      
        $fieldset1 = "
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
		";
		
        $fieldset2 = "
        {$formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'))}
        ";

		
        $text = "
        {$formObj->getFieldSetWrapped('Candidate Pass Details', $fieldset1)}
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


        $text = "
        ";
        
        return $text;
    }
    
    /**
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');
        
        $text = '';

        return $text;
    }
}