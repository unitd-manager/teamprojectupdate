<?
class CP_Admin_Modules_Pms_Grade_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            
            if ($cpCfg['cp.forAceIms']) {
                $gradeFooter = $listObj->getListRowEnd($row['grade_id']);
            } else {
                $gradeFooter = $listObj->getListRowEnd($row['student_grade_id']);
            }
            
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
		    {$listObj->getGoToDetailText($rowCounter, $row['exam_type'])}
		    {$listObj->getGoToDetailText($rowCounter, $row['contact_name'])}
		    {$listObj->getListDataCell($row['batch_title'])}
            {$listObj->getListDataCell($row['marks'], 'center')}
            {$listObj->getListDataCell($row['grade'], 'center')}
            {$gradeFooter}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Exam Type', 'exam_type')}
        {$listObj->getListHeaderCell('Contact Name', 'contact_name')}
        {$listObj->getListHeaderCell('Batch Name', 'batch_title')}
        {$listObj->getListHeaderCell('Marks', 'marks' , 'headerCenter')}
        {$listObj->getListHeaderCell('Grade', 'grade' , 'headerCenter')}
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
        $fn = Zend_Registry::get('fn');

        $sqlCont = $fn->getDDSql('pms_contact');

        $fieldset = "
        {$formObj->getDDRowBySQL('Contact Name', 'contact_id', $sqlCont)}
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
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $sqlExam   = $fn->getValueListSQL('examType');
        $expVL = array('sqlType' => 'OneField');

        $sqlBatch = $fn->getDDSql('pms_batch');
        $expBatch  = array('detailValue' => $row['batch_title']);

        $sqlCont = $fn->getDDSql('pms_contact');
        $expCont  = array('detailValue' => $row['contact_name']);

        $additionalFields = "";
        if ($cpCfg['cp.forAceIms']) {
            $sqlSub = $fn->getDDSql('pms_subject');
            $expComp  = array('detailValue' => $row['subject_title']);

            $sqlClass = $fn->getDDSql('pms_class');
            $expClass  = array('detailValue' => $row['class_name']);

            $additionalFields = "
            {$formObj->getDDRowBySQL('Subject Name', 'subject_id', $sqlSub, $row['subject_id'], $expComp)}
            {$formObj->getDDRowBySQL('Class Name', 'class_id', $sqlClass, $row['class_id'], $expClass)}
            ";
        }

        $fieldset1 = "
        {$formObj->getDDRowBySQL('Exam Type', 'exam_type', $sqlExam, $row['exam_type'], $expVL)}
        {$formObj->getDDRowBySQL('Batch Name', 'batch_id', $sqlBatch, $row['batch_id'], $expBatch)}
        {$additionalFields}
        {$formObj->getDDRowBySQL('Contact Name', 'contact_id', $sqlCont, $row['contact_id'], $expCont)}
        {$formObj->getTBRow('Marks', 'marks', $row['marks'])}
        {$formObj->getTBRow('Grade', 'grade', $row['grade'])}
   		";
		
        $text = "
        {$formObj->getFieldSetWrapped('Grade Details', $fieldset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     */
    function getRightPanel($row){
        
        $text = "
        ";

        return $text;
    }

    /**
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $sqlExam   = $fn->getValueListSQL('examType');
        $sqlBatch = $fn->getDDSql('pms_batch');

        $exam_type  = $fn->getReqParam('exam_type');
        $batch_id   = $fn->getReqParam('batch_id');

        $text = "
        <td>
            <select name='exam_type' >
                <option value=''>Exam Type</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlExam, $exam_type)}
            </select>
        </td>
        <td>
            <select name='batch_id' >
                <option value=''>Batch</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlBatch, $batch_id)}
            </select>
        </td>
        ";        
        
        return $text;
    }
}