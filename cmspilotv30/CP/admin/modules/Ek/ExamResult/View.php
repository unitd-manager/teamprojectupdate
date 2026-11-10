<?
class CP_Admin_Modules_Ek_ExamResult_View extends CP_Common_Lib_ModuleViewAbstract
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

        $rows = '';
        $count = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['student_name'])}
            {$listObj->getListDataCell($row['class_title'])}
            {$listObj->getListDataCell($row['term'])}
            {$listObj->getListDataCell($row['subject_title'])}
            {$listObj->getListDataCell($row['marks'])}
            {$listObj->getListDataCell($row['grade'])}
            {$listObj->getListRowEnd($row['exam_result_id'])}
            ";
            $count++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Name', 's.first_name')}
        {$listObj->getListHeaderCell('Class', 'c.title')}
        {$listObj->getListHeaderCell('Term', 'er.term')}
        {$listObj->getListHeaderCell('Subject', 'sub.title')}
        {$listObj->getListHeaderCell('Marks', 'er.marks')}
        {$listObj->getListHeaderCell('Grade', 'er.grade')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    //==================================================================//
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fnModClass = includeCPClass('fnsMod', 'class', 'FunctionsMod');
        $sqlClass   = $fnModClass->getClassSQL();

        $fieldset = "
        {$formObj->getDDRowBySQL('Class', 'class_id', $sqlClass)}
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
        
        $fnModClass   = includeCPClass('fnsMod', 'class', 'FunctionsMod');
        $fnModStudent = includeCPClass('fnsMod', 'student', 'FunctionsMod');
        $fnModSubject = includeCPClass('fnsMod', 'subject', 'FunctionsMod');

        $sqlClass   = $fnModClass->getClassSQL();
        $sqlStudent = $fnModStudent->getStudentSQL($row['class_id']);
        $sqlSubject = $fnModSubject->getSubjectSQL();

        $sqlTerm    = $fn->getValueListSQL('term');

        $expClass   = array('detailValue' => $row['class_title']);
        $expStudent = array('detailValue' => $row['student_name']);
        $expSubject = array('detailValue' => $row['subject_title']);
        $expVl      = array('sqlType' => 'OneField');

        $fieldset = "
        {$formObj->getDDRowBySQL('Class', 'class_id', $sqlClass, $row['class_id'], $expClass)}
        {$formObj->getDDRowBySQL('Student', 'student_id', $sqlStudent, $row['student_id'], $expStudent)}
        {$formObj->getDDRowBySQL('Subject', 'subject_id', $sqlSubject, $row['subject_id'], $expSubject)}
        {$formObj->getDDRowBySQL('Term', 'term', $sqlTerm, $row['term'], $expVl)}
        {$formObj->getTBRow('Marks', 'marks', $row['marks'])}
        {$formObj->getTBRow('Grade', 'grade', $row['grade'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Result Details', $fieldset)}
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