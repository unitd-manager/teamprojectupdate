<?
class CP_Admin_Modules_AgileIms_Grade_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
		    {$listObj->getGoToDetailText($rowCounter, $row['exam_type'])}
		    {$listObj->getGoToDetailText($rowCounter, $row['contact_name'])}
		    {$listObj->getListDataCell($row['batch_title'])}
            {$listObj->getListDataCell($row['marks'], 'center')}
            {$listObj->getListDataCell($row['grade'], 'center')}
            {$listObj->getListRowEnd($row['grade_id'])}
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

        $sqlCont = $fn->getDDSql('agileIms_contact');

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

        $sqlBatch = $fn->getDDSql('agileIms_batch');
        $expBatch  = array('detailValue' => $row['batch_title']);

        $sqlCont = $fn->getDDSql('agileIms_contact');
        $expCont  = array('detailValue' => $row['contact_name']);

        $sqlSub = $fn->getDDSql('agileIms_subject');
        $expComp  = array('detailValue' => $row['subject_title']);

        $sqlClass = $fn->getDDSql('agileIms_class');
        $expClass  = array('detailValue' => $row['class_name']);

        $fieldset1 = "
        {$formObj->getDDRowBySQL('Exam Type', 'exam_type', $sqlExam, $row['exam_type'], $expVL)}
        {$formObj->getDDRowBySQL('Batch Name', 'batch_id', $sqlBatch, $row['batch_id'], $expBatch)}
        {$formObj->getDDRowBySQL('Subject Name', 'subject_id', $sqlSub, $row['subject_id'], $expComp)}
        {$formObj->getDDRowBySQL('Class Name', 'class_id', $sqlClass, $row['class_id'], $expClass)}
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

        $sqlExam  = $fn->getValueListSQL('examType');
        $sqlBatch = $fn->getDDSql('agileIms_batch');

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

    /**
     *
     */
    function getStudentGrade() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');
        $db = Zend_Registry::get('db');
               
        $batch_id = $fn->getReqParam('id');
        $rows = '';
        $formAction = "index.php?module=agileIms_grade&_spAction=studentGradeSubmit&showHTML=0";
        
        $current_date = date('Y-m-d');
        $SQLGrade = "
        SELECT contact_id
              ,marks
              ,grade
        FROM student_grade
        ";
        $resultGrade = $db->sql_query($SQLGrade);  
        $numRows     = $db->sql_numrows($resultGrade);
        
        $SQL = "
        SELECT c.first_name
              ,cc.course_contact_id
        FROM contact c
        LEFT JOIN (course_contact cc) ON (c.contact_id = cc.contact_id)
        WHERE cc.batch_id = {$batch_id}
        "; 
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $rows .= $this->getStudentGradeRecords($row['course_contact_id'], $row['first_name']);
        }
        
        $headers ="
        <th>Student Name</th>
        <th>Marks</th>
        <th>Grade</th>
        <th>Result</th>
        ";

        $sqlExamType = $fn->getValueListSQL('examType');
        $expVl = array('sqlType' => 'OneField');

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <table id='' class='thinlist'>
  		        {$formObj->getDDRowBySQL('Assessment Type', 'exam_type', $sqlExamType, '', $expVl)}
                {$formObj->getDateRow('Date', 'exam_date', $current_date)}
                {$headers}
                {$rows}
            </table>    
            <input type='hidden' name='batch_id' value='{$batch_id}' />
        </form>
        ";
        return $text;
    }

    /**
     * List of Student Name, Mark, Grade and Result
     */
    function getStudentGradeRecords($course_contact_id, $trainer_name){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $row = $fn->getRecordRowByID('course_contact', 'course_contact_id', $course_contact_id);

        $pfx = $course_contact_id . '_' ;
        $exp = array('fldId' => 'marks');
        $expGrade = array('fldId' => 'grades');
        $expEdit = array('isEditable' => 0);
        
        $text = "
        <tr id='{$course_contact_id}'>
            <td>{$trainer_name}</td>
            <td>{$formObj->getTBRow('', "{$pfx}marks", '', $exp)}</td>
            <td>{$formObj->getTBRow('', "{$pfx}grade")}</td>
            <td>{$formObj->getTBRow('', "{$pfx}student_result")}</td>
            <input type='hidden' name='course_contact_id[]' 
            value='{$course_contact_id}' />
        </tr>
        ";
        return $text;
    }

    /**
     *
     */
    function getEditStudentGrade() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');
        $db = Zend_Registry::get('db');
               
        $batch_id = $fn->getReqParam('id');
        $exam_type = $fn->getReqParam('type');
        $exam_date = $fn->getReqParam('date');
        $student_grade_id = $fn->getReqParam('student_grade_id');
        
        $rows = '';
        $formAction = "index.php?module=agileIms_grade&_spAction=editStudentGradeSubmit&showHTML=0";

        $SQL = "
        SELECT a.*
              ,c.first_name
        FROM student_grade a
        LEFT JOIN (contact c) ON (a.contact_id = c.contact_id)
        WHERE batch_id = '{$batch_id}'
        AND a.exam_type = '{$exam_type}'
        AND a.exam_date = '{$exam_date}'
        ";        
        $result = $db->sql_query($SQL);
        
        while ($row = $db->sql_fetchrow($result)) {
            $exam_type = $row['exam_type'];
            $exam_date = $row['exam_date'];

            $rows .= $this->getEditStudentGradeRecords($row['student_grade_id'], $row['first_name']);
        }

        $headers ="
        <th>Trainer Name</th>
        <th>Marks</th>
        <th>Grade</th>
        <th>Result</th>
        ";
        
        $expNoEdit = array('isEditable' => 0);

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <table id='' class='thinlist'>
  		        {$formObj->getTBRow('Assessment Type', 'exam_type', $exam_type, $expNoEdit)}
  		        {$formObj->getTBRow('Date', 'exam_date', $exam_date, $expNoEdit)}
                {$headers}
                {$rows}
            </table>    
            <input type='hidden' name='batch_id' value='{$batch_id}' />
        </form>
        ";
        return $text;
    }
   
    /**
     *
     */
    function getEditStudentGradeRecords($student_grade_id, $trainer_name){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $row = $fn->getRecordRowByID('student_grade', 'student_grade_id', $student_grade_id);
        $pfx = $student_grade_id . '_' ;
        $exp = array('fldId' => 'marks');

        $text = "
        <tr id='{$student_grade_id}'>
            <td>{$trainer_name}</td>
            <td>{$formObj->getTBRow(' ', "{$pfx}marks", $row['marks'], $exp)}</td>
            <td>{$formObj->getTBRow(' ', "{$pfx}grade", $row['grade'])}</td>
            <td>{$formObj->getTBRow(' ', "{$pfx}student_result", $row['student_result'])}</td>
            <input type='hidden' name='student_grade_id[]' value='{$student_grade_id}' />
        </tr>
        ";
        return $text;
    }
}