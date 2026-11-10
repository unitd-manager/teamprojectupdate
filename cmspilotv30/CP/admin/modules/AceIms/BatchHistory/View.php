<?
class CP_Admin_Modules_AceIms_BatchHistory_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $rows  = "";
        $email = "";
        $rowCounter = 0;
        
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){             
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['first_name'])}
            {$listObj->getListDataCell($row['last_name'])}
            {$listObj->getListDataCell($row['course_title'])}
            {$listObj->getListDataCell($row['batch_title'])}
            {$listObj->getListDataCell($row['teacher_name'])}
            {$listObj->getListYesNo($row['evaluate_status'])}
            {$listObj->getListDataCell($row['certificate_status'])}
            {$listObj->getListDataCell($row['email'])}
            {$listObj->getListDataCell($row['phone'])}
            {$listObj->getListDataCell($row['course_contact_id'], 'center')}
            {$listObj->getListRowEnd($row['course_contact_id'])}
            ";
            $rowCounter++ ;
        }
        
        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('First Name', 'ct.first_name')}
        {$listObj->getListHeaderCell('Last Name', 'ct.last_name')}
        {$listObj->getListHeaderCell('Course Title', 'c.title')}
        {$listObj->getListHeaderCell('Batch Title', 'b.title')}
        {$listObj->getListHeaderCell('Teacher Name', 'teacher_name')}
        {$listObj->getListHeaderCell('Evaluation Status', 'evaluate_status')}
        {$listObj->getListHeaderCell('Certificate Status', 'certificate_status')}
        {$listObj->getListHeaderCell('Email', 'ct.email')}
        {$listObj->getListHeaderCell('Phone', 'ct.phone')}
        {$listObj->getListHeaderCell('ID', 'b.course_contact_id' , 'headerCenter')}
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
        {$formObj->getTBRow('Title', 'title')}
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
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        
        $sqlCourse  = $fn->getDDSql('aceIms_course');
        $expCourse  = array('detailValue' => $row['course_title']);
        $expVl = array('sqlType' => 'OneField');
        $exp = array('isEditable' => 0);
        
        //$exp = array('titleFld' => "CONCAT_WS(' ', first_name, last_name )");
        $sqlTeacher  = $fn->getDDSql('aceIms_teacher', $exp);
        $expTeach  = array('detailValue' => $row['teacher_name']);
        $sqlCertStatus = $fn->getValueListSQL('certificateStatus');
        
        $fielset1 = "
        {$formObj->getTBRow('First Name', 'first_name', $row['first_name'], $exp)}
        {$formObj->getTBRow('Last Name', 'last_name', $row['last_name'], $exp)}
        {$formObj->getTBRow('Email', 'email', $row['email'], $exp)}
        {$formObj->getTBRow('Phone', 'phone', $row['phone'], $exp)}
        {$formObj->getTBRow('Course Name', 'course_title', $row['course_title'], $exp)}
        {$formObj->getTBRow('Batch Name', 'batch_title', $row['batch_title'], $exp)}
  		{$formObj->getDDRowBySQL('Certificate Status', 'certificate_status', $sqlCertStatus, $row['certificate_status'], $expVl)}
        {$formObj->getTBRow('Teacher Name', 'teacher_name', $row['teacher_name'], $exp)}
		";
		
        $text = "
        {$formObj->getFieldSetWrapped('Batch History Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');

        $url = "index.php?module=aceIms_batch&_spAction=bulkUpdateEvaluate&id={$row['course_contact_id']}&showHTML=0";
        
        $text ="
        {$media->getRightPanelMediaDisplay('Certificate', 'aceIms_batchHistory', 'attachment', $row)}
        ";
        
        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn'); 
        $cpUtil = Zend_Registry::get('cpUtil');

        $course_id    = $fn->getReqParam('course_id');
        $batch_id     = $fn->getReqParam('batch_id');
        $teacher_id   = $fn->getReqParam('teacher_id');
        $batch_status = $fn->getReqParam('batch_status', "Open");
        
        $sqlBatch = '';
        
        $spArray = array(
              "Open"
             ,"Closed"
        );

        if($course_id){
            $sqlBatch = "
            SELECT b.batch_id, b.title
            FROM batch b
            WHERE b.course_id = {$course_id}
            ";
        }

        $sqlCourse = "
        SELECT c.course_id, c.title
        FROM course c
        ";

        $sqlTeacher = "
        SELECT t.teacher_id
              ,CONCAT_WS(' ', t.first_name, t.last_name ) AS teacher_name
        FROM teacher t
        ";

        $text = "
        <td>
            <select name='course_id' >
                <option value=''>Course</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCourse, $course_id)}
            </select>
        </td>
        <td>
            <select name='batch_id' >
                <option value=''>Batch</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlBatch, $batch_id)}
            </select>
        </td>
        <td>
            <select name='batch_status'>
                <option value=''>Batch Status</option>
                {$cpUtil->getDropDown1($spArray, $batch_status)}
            </select>
        </td>
        <td>
            <select name='teacher_id' >
                <option value=''>Teacher</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlTeacher, $teacher_id)}
            </select>
        </td>        
        ";        
        
        return $text;
    }
}