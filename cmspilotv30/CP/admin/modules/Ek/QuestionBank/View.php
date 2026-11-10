<?
class CP_Admin_Modules_Ek_QuestionBank_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        $mediaArray = Zend_Registry::get('mediaArray');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['question'])}
            {$listObj->getListDataCell($row['subject_title'])}
            {$listObj->getListDataCell($row['creation_date'])}
            {$listObj->getListPublishedImage($row['published'], $row['question_bank_id'])}
            {$listObj->getListDataCell($row['question_bank_id'], 'center')}
            {$listObj->getListRowEnd($row['question_bank_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Question', 'qb.question')}
        {$listObj->getListHeaderCell('Subject', 'sb.subject_id')}
        {$listObj->getListHeaderCell('Post Date', 'qb.creation_date')}
        {$listObj->getListHeaderCell('Published', 'qb.published', 'headerCenter')}
        {$listObj->getListHeaderCell('ID', 'qb.question_bank_id' , 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    //==================================================================//
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fieldset = "
        {$formObj->getTBRow('Question', 'question')}
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
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $am = Zend_Registry::get('am');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode = $tv['action'];
        
        
        $fnModSubject = includeCPClass('fnsMod', 'subject', 'FunctionsMod');
        $sqlSubject   = $fnModSubject->getSubjectSQL();
        $sqlType      = $fn->getValueListSQL('status');

        $expSubject = array('detailValue' => $row['subject_title']);        
        $expVl      = array('sqlType' => 'OneField');
        $gendArr    = array('Male', 'Female');

        $fielset1 = "
        {$formObj->getTBRow('Question', 'question', $row['question'])}
        {$formObj->getDDRowBySQL('Subject', 'subject_id', $sqlSubject, $row['subject_id'], $expSubject)}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        {$formObj->getDateRow('Post Date', 'creation_date', $row['creation_date'])}
        {$formObj->getDateRow('Launch Date', 'launch_date', $row['launch_date'])}
        {$formObj->getDateRow('Expiry Date', 'expiry_date', $row['expiry_date'])}
		";
		
        $fielset2 = "
		";
		
        $text = "
        {$formObj->getFieldSetWrapped('General Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Answers', $fielset2)}
        ";

        return $text;
    }

    //========================================================//
    //==================================================================//
    //==================================================================//
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $record_id = $fn->getIssetParam($row, 'student_id');

        $text ="
        ";
        
        return $text;
    }

    //==================================================================//
    //==================================================================//


    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $am = Zend_Registry::get('am');
        $fn = Zend_Registry::get('fn');

        $text = "
        ";
        
        
        return $text;
    }
}