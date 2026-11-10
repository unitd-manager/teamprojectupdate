<?
class CP_Admin_Modules_Edukloud_Batch_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj    = Zend_Registry::get('listObj');
        $modulesArr = Zend_Registry::get('modulesArr');

        $rows       = "";
        $email      = "";
        $rowCounter = 0;
        
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['batch_code'])}
            {$listObj->getListDataCell($row['course_title'])}
            {$listObj->getListDataCell($row['teacher_name'])}
            {$listObj->getListDataCell($row['attendee'])}
            {$listObj->getListDataCell($row['venue'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDateCell($row['start_date'])}
            {$listObj->getListDateCell($row['end_date'])}
            {$listObj->getListDataCell($row['start_time'])}
            {$listObj->getListDataCell($row['batch_id'], 'center')}
            {$listObj->getListRowEnd($row['batch_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'b.title')}
        {$listObj->getListHeaderCell('Batch code', 'b.batch_code')}
        {$listObj->getListHeaderCell($modulesArr['edukloud_course']['title'], 'course_title')}
        {$listObj->getListHeaderCell('Teacher Name', 'teacher_name')}
        {$listObj->getListHeaderCell('No of Attendee', 'attendee')}
        {$listObj->getListHeaderCell('Venue', 'b.venue')}
        {$listObj->getListHeaderCell('Status', 'b.status')}
        {$listObj->getListHeaderCell('Start Date', 'b.start_date')}
        {$listObj->getListHeaderCell('End Date', 'b.end_date')}
        {$listObj->getListHeaderCell('Start Time', 'b.start_time')}
        {$listObj->getListHeaderCell('ID', 'b.batch_id' , 'headerCenter')}
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
        {$formObj->getTBRow('Batch Code', 'batch_code')}
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
        $fn         = Zend_Registry::get('fn');
        $tv         = Zend_Registry::get('tv');
        $cpCfg      = Zend_Registry::get('cpCfg');
        $formObj    = Zend_Registry::get('formObj');
        $modulesArr = Zend_Registry::get('modulesArr');
        
        $expVl      = array('sqlType' => 'OneField');
        $expEdit    = array('isEditable' => 0);
        $expCourse  = array('detailValue' => $row['course_title']);

        $sqlBatchVenue  = $fn->getValueListSQL('batchVenue');
        $sqlBatchStatus = $fn->getValueListSQL('batchStatus');
        
        /* Adding or Ignoring site id from Course sql according to config variable */
        $site_id_ignored_modules = $cpCfg['w.common_multiUniqueSite.ignoreModules'];        
        if (in_array($tv['module'], $site_id_ignored_modules)) {
            $sqlCourse  = "SELECT course_id, title FROM course ORDER BY title";
        } else {
            $sqlCourse  = $fn->getDDSql('edukloud_course');
        }

        $course_module_name = $modulesArr['edukloud_course']['title'];
        $batch_module_name  = $modulesArr['edukloud_batch']['title'];
        $code_label         = $batch_module_name . ' Code';
        $status_label       = $batch_module_name . ' Status';
        
        $fielset1 = "
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getTBRow($code_label, 'batch_code', $row['batch_code'], $expEdit)}
  		{$formObj->getDDRowBySQL('Venue', 'venue', $sqlBatchVenue, $row['venue'], $expVl)}
        {$formObj->getDDRowBySQL($course_module_name, 'course_id', $sqlCourse, $row['course_id'], $expCourse)}
  		{$formObj->getDDRowBySQL($status_label, 'status', $sqlBatchStatus, $row['status'], $expVl)}
        {$formObj->getDateRow('Start Date', 'start_date', $row['start_date'])}
        {$formObj->getDateRow('End Date', 'end_date', $row['end_date'])}
        {$formObj->getTimeRow('Start Time', 'start_time', $row['start_time'])}
        {$formObj->getTimeRow('End Time', 'end_time', $row['end_time'])}
        {$formObj->getTBRow('No. of Hours', 'no_of_hours', $row['no_of_hours'])}
        {$formObj->getTBRow('Min. Enroll Count', 'min_enroll_count', $row['min_enroll_count'])}
        {$formObj->getTBRow('Max. Enroll Count', 'max_enroll_count', $row['max_enroll_count'])}
		";
		
        $text = "
        {$formObj->getFieldSetWrapped('Batch Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $fn              = Zend_Registry::get('fn');
        $db              = Zend_Registry::get('db');
        $media           = Zend_Registry::get('media');
        $cpCfg           = Zend_Registry::get('cpCfg');
        $displayLinkData = Zend_Registry::get('displayLinkData');

        $text       = "";
        $rows       = "";
        $printText  = "";
        $actionText = "";

        $SQLCC = "
        SELECT evaluate_status
        FROM course_contact
        WHERE batch_id = {$row['batch_id']}
          AND evaluate_status = 1
        ";
        $resultCC = $db->sql_query($SQLCC);
        $numRows  = $db->sql_numrows($resultCC);
        
        $printCert = '';
        if ($numRows > 0){
            $printCert = "
            <div class='float_right button mb5'>
                <a href='' id=''>Print Temp Certficate</a>
            </div> 
            <div class='float_right button mb5'>
                <a href='' id=''>Print Certficate</a>
            </div> 
            ";
        }

        if($cpCfg['m.edukloud.batch.printAttendanceExcell']){
            $urlAttendance = "index.php?module=edukloud_batch&_spAction=printAttendanceExcell&id={$row['batch_id']}&showHTML=0";
        } else {
            $urlAttendance = "index.php?module=edukloud_batch&_spAction=printAttendance&id={$row['batch_id']}&showHTML=0";
        }
        
        $printText .="
        <div class='floatbox actionBtnsDetail'>
            <div class='float_right button mb5'>
                <a href='{$urlAttendance}' id='printAttendance'>Print Attendance Format</a>
            </div> 
            {$printCert}
            <div class='float_right mb5'>
                <h3>PRINT</h3>
            </div> 
        </div>
        ";

        $actionText ="
        <div class='floatbox mt5'>
            {$actionText}
            <div class='float_right'>
                <h3>ACTION(S)</h3>
            </div> 
        </div>
        ";
        
        // This is used to display Attendance Portal
        if($cpCfg['m.edukloud.batch.takeAttendance']){
            $rows .= $this->getAttendancePortalDisplay($row);
        }

        // This is used to display Student Grade Portal
        if($cpCfg['m.edukloud.batch.hasStudentGrade']){
            $rows .= $this->getStudentGradePortalDisplay($row);
        }

        // This is used to display feedback Portal
        if($cpCfg['m.edukloud.batch.studentFeedback']){
            $rows .= $this->getStudentFeedbackDisplay($row);
        }
        
        $text = "
        {$printText}
        {$actionText}
        {$displayLinkData->getLinkPortalMain('edukloud_batch', 'edukloud_contactLink', 'Contacts Linked', $row)}
        {$displayLinkData->getLinkPortalMain('edukloud_batch', 'edukloud_teacherLink', 'Trainers Linked', $row)}
        {$media->getRightPanelMediaDisplay('Attendance Attachment', 'edukloud_batch', 'attachment', $row)}
        {$rows}
        ";
        
        return $text;
    }
    
    /**
     *
     */
    function getQuickSearch() {
        $db     = Zend_Registry::get('db');
        $fn     = Zend_Registry::get('fn');
        $tv     = Zend_Registry::get('tv');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $status          = $fn->getReqParam('status');
        $course_id       = $fn->getReqParam('course_id');
        $enrollment_year = $fn->getReqParam('enrollment_year');

        if ($tv['searchDone'] == 0) {
            $enrollment_year = date('Y');
        }

        /* Adding or Ignoring site id from Course sql according to config variable */
        $site_id_ignored_modules = $cpCfg['w.common_multiUniqueSite.ignoreModules'];        
        if (in_array($tv['module'], $site_id_ignored_modules)) {
            $sqlCourse  = "SELECT course_id, title FROM course ORDER BY title";
        } else {
            $sqlCourse  = $fn->getDDSql('edukloud_course');
        }

        $sqlStatus = $fn->getValueListSQL('batchStatus');
        $sqlYear   = "SELECT DISTINCT cc.year_of_enrollment FROM course_contact cc";

        $text = "
        <td>
            <select name='course_id' >
                <option value=''>Course</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCourse, $course_id)}
            </select>
        </td>        
        <td>
            <select name='status' >
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlStatus, $status)}
            </select>
        </td>
        <td>
            <select name='enrollment_year' class='float_right m5'>
                <option value=''>Enrollment Year</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $enrollment_year)}
            </select>
        </td>
        ";        
        
        return $text;
    }

    /**
     *
     */
    function getAttendancePortalDisplay($row){
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $tv       = Zend_Registry::get('tv');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $formObj  = Zend_Registry::get('formObj');
        $dateUtil = Zend_Registry::get('dateUtil');
        
        $rows        = "";
        $presentDate = "";

        $SQL = "
        SELECT date
              ,attendance_id
              ,batch_id
        FROM attendance
        WHERE batch_id = {$row['batch_id']}
        GROUP BY date
        ";
        $result  = $db->sql_query($SQL);  
        $numRows = $db->sql_numrows($result);
                        
        while ($rowCC = $db->sql_fetchrow($result)) {
        
            $date = $dateUtil->formatDate($rowCC['date'], 'DD-MM-YYYY');
            
            if ($presentDate != $rowCC['date']){
            $rows .= "
                <tr style='background-color: #F4F4F4; color:#000000;'>
                    <td class='portalActBtns'>
                        <div class='float_left'>
                            {$date}
                        </div>
                        <div style='float:right'>  
                            <a class='editAttendance' class='' h='350' w='650' recid={$rowCC['attendance_id']} dialogtitle='Edit Attendance' link='index.php?module=edukloud_batch&_spAction=editAttendance&id={$rowCC['batch_id']}&attendance_id={$rowCC['attendance_id']}
                            &attendance_date={$rowCC['date']}
                            &showHTML=0'>
                                <img border='0' title='Edit Record' src='/cmspilotv30/CP/admin/images/icons/btn_edit.png'>
                            </a>                        
                        </div>
                    </td>
                </tr>
                ";
            }
            $presentDate = $rowCC['date'];
        }
        
        $text = "
        <div class='linkPortalWrapper edukloud_batch__edukloud_attendance' id='edukloud_batch#edukloud_attendance'>
            <div expanded='1' class='header'>
                <div class='linkPortalDataWrapper'>
                    <div class='actBtns'>
                        <a id='takeAttendance' class='button' dialogtitle='Take Attendance' href='index.php?module=edukloud_batch&_spAction=takeAttendance&id={$row['batch_id']}&showHTML=0'> 
                        <h3>Click Here to Take Attendance</h3>
                        </a>
                    </div>
                    <table class='thinlist'>
                        {$rows}
                    </table>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getStudentGradePortalDisplay($row){
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $tv       = Zend_Registry::get('tv');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $formObj  = Zend_Registry::get('formObj');
        $dateUtil = Zend_Registry::get('dateUtil');
        
        $rows        = "";
        $presentDate = "";

        $SQL = "
        SELECT student_grade_id
              ,batch_id
              ,marks
              ,grade
              ,exam_type
              ,exam_date
        FROM student_grade
        WHERE batch_id = {$row['batch_id']}
        ";
        $result  = $db->sql_query($SQL);  
        $numRows = $db->sql_numrows($result);
                        
        while ($rowCC = $db->sql_fetchrow($result)) {

            $exam_date = $dateUtil->formatDate($rowCC['exam_date'], 'DD-MM-YYYY');

            if ($presentDate != $rowCC['exam_date']){
                $rows .= "
                <tr style='background-color: #F4F4F4; color:#000000;'>
                    <td>{$exam_date}</td>
                    <td>{$rowCC['exam_type']}</td>
                    <td>
                        <a class='editStudentGrade' class='' h='350' w='650' recid={$rowCC['student_grade_id']} dialogtitle='Edit Attendance' link='index.php?module=edukloud_batch&_spAction=editStudentGrade&id={$rowCC['batch_id']}&type={$rowCC['exam_type']}&date={$rowCC['exam_date']}&student_grade_id={$rowCC['student_grade_id']}&showHTML=0'>
                            <img border='0' title='Edit Record' src='/cmspilotv30/CP/admin/images/icons/btn_edit.png'>
                        </a>                        
                    </td>
                </tr>
                ";
            }
            $presentDate = $rowCC['exam_date'];
        }
        
        $header = "
        <th>Date</th>
        <th>Assessment Type</th>
        <th>Edit</th>
        ";

        $text = "
        <div class='linkPortalWrapper edukloud_batch__edukloud_studentGrade' id='edukloud_batch#edukloud_studentGrade'>
            <div expanded='1' class='header'>
                <div class='linkPortalDataWrapper'>
                    <div class='actBtns'>
                        <a id='studentGrade' class='button' dialogtitle='Student Grade' href='index.php?module=edukloud_batch&_spAction=studentGrade&id={$row['batch_id']}&showHTML=0'> 
                        <h3>Click Here for Student Grade</h3>
                        </a>
                    </div>
                    <table class='thinlist'>
                        {$header}
                        {$rows}
                    </table>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getStudentFeedbackDisplay($row){
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        
        $rows     = "";        
        $question = "";
        
        $SQL = "
        SELECT title
              ,batch_feedback_id
        FROM batch_feedback
        WHERE batch_id = {$row['batch_id']}
        ";
        $result  = $db->sql_query($SQL);  
        $numRows = $db->sql_numrows($result);

        while ($rowCC = $db->sql_fetchrow($result)) {   
            
            $rows .= "
            <tr style='background-color: #F4F4F4; color:#000000;'>
                <td class='portalActBtns'>
                    <div class='float_left'>
                        {$rowCC['title']}
                    </div>
                    <div style='float:right'>  
                        <a class='editStudentFeedback' class='' h='350' w='650' recid={$row['batch_id']} dialogtitle='Edit Student Feedback' href='index.php?module=edukloud_batch&_spAction=editStudentFeedback&id={$rowCC['batch_feedback_id']}&title={$rowCC['title']}&showHTML=0'>
                            <img border='0' title='Edit Record' src='/cmspilotv30/CP/admin/images/icons/btn_edit.png'>
                        </a>                        
                    </div>
                </td>
            </tr>
            ";
        }
        
        $text = "
        <div class='linkPortalWrapper edukloud_batch__edukloud_feedback' id='edukloud_batch#edukloud_feedback'>
            <div expanded='1' class='header'>
                <div class='linkPortalDataWrapper'>
                    <div class='actBtns'>
                        <a id='studentFeedback' class='button' dialogtitle='Student Feedback' href='index.php?module=edukloud_batch&_spAction=studentFeedback&id={$row['batch_id']}&showHTML=0'> 
                        <h3>Click Here for Student Feedback</h3>
                        </a>
                    </div>
                    <table class='thinlist'>
                        {$rows}
                    </table>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
     function getTakeAttendance() {
        $ln      = Zend_Registry::get('ln');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $db      = Zend_Registry::get('db');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl   = Zend_Registry::get('cpUrl');
               
        $rows = '';

        $batch_id    = $fn->getReqParam('id');
        $formAction  = "index.php?module=edukloud_batch&_spAction=takeAttendanceSubmit&showHTML=0";
        $currentDate = date("Y-m-d");
        
        $SQLAttendance = "
        SELECT date
              ,attendance_id
              ,batch_id
        FROM attendance
        WHERE date = '{$currentDate}'
        AND batch_id = {$batch_id}
        ";
        $resultAttendance = $db->sql_query($SQLAttendance);  
        $numRows          = $db->sql_numrows($resultAttendance);
        
        if ($numRows > 0) {
            return "<strong>Attendance already taken</strong>";
        }
        
        $SQL = "
        SELECT c.first_name
              ,c.last_name
              ,cc.course_contact_id
        FROM course_contact cc
        LEFT JOIN (contact c) ON (c.contact_id = cc.contact_id)
        WHERE cc.batch_id = {$batch_id}
        ";
         
        $result = $db->sql_query($SQL);
        
        while ($row = $db->sql_fetchrow($result)) {
            $trainee_name = $row['first_name'] . ' ' . $row['last_name'];          
            $rows .= $this->getAttendanceRecords($row['course_contact_id'], $trainee_name);
        }
        
        $headers ="
        <strong>Date : {$currentDate}</strong>
        <th>Trainee Name</th>
        <th>
            <a href='#' class='allPresent'>All Present / </a>
            <a href='#' class='allAbsent'>All Absent</a>
        </th>
        ";
        
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <table id='' class='thinlist'>
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
     function getStudentGrade() {
        $ln      = Zend_Registry::get('ln');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $db      = Zend_Registry::get('db');
        $cpUrl   = Zend_Registry::get('cpUrl');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
               
        $rows         = '';
        $current_date = date('Y-m-d');
        $batch_id     = $fn->getReqParam('id');

        $expVl        = array('sqlType' => 'OneField');
        $sqlExamType  = $fn->getValueListSQL('examType');
        $formAction   = "index.php?module=edukloud_batch&_spAction=studentGradeSubmit&showHTML=0";
        
        $SQL = "
        SELECT c.first_name
              ,c.last_name
              ,cc.course_contact_id
        FROM contact c
        LEFT JOIN (course_contact cc) ON (c.contact_id = cc.contact_id)
        WHERE cc.batch_id = {$batch_id}
        "; 
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $trainee_name = $row['first_name'] . ' ' . $row['last_name'];            
            $rows .= $this->getStudentGradeRecords($row['course_contact_id'], $trainee_name);
        }
        
        $headers ="
        <th>Student Name</th>
        <th>Marks</th>
        <th>Remarks</th>
        ";
        
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
     *
     */
     function getStudentFeedback() {
        $ln      = Zend_Registry::get('ln');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $db      = Zend_Registry::get('db');
        $cpUrl   = Zend_Registry::get('cpUrl');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
               
        $rows         = '';
        $headerRow    = '';
        $questionList = '';
        
        $batch_id       = $fn->getReqParam('id');
        $feedback_group = $fn->getReqParam('feedback_group');
        
        $expVL = array('sqlType' => 'OneField');
        
        $sqlFeedbackGroup = $fn->getValueListSQL('feedbackGroup');
        
        $formAction = "index.php?module=edukloud_batch&_spAction=studentFeedbackSubmit&showHTML=0";

        $text = "
        <div id='studentFeedbackOuter'>
            <form id='portalForm' class='yform columnar edukloud_batch__edukloud_studentFeedbackLink' method='post' action='{$formAction}'>
                <div class='floatbox'>
                    {$formObj->getDDRowBySQL('Group', 'group', $sqlFeedbackGroup, '', $expVL)}
        	        {$formObj->getTBRow('Feedback Title', 'title')}
                    <input type='hidden' name='batch_id' value='{$batch_id}' />
                </div>
	            <div id='questionsList'></div>
            </form>
        </div>
        ";

        return $text;
    }
    
    /**
     *
     */
     function getEditStudentFeedback() {
        $ln      = Zend_Registry::get('ln');
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $tv      = Zend_Registry::get('tv');
        $cpUrl   = Zend_Registry::get('cpUrl');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
               
        $rows      = '';
        $headerRow = '';
        
        $batch_feedback_id  = $fn->getReqParam('id');
        $title              = $fn->getReqParam('title');
        
        $formAction = "index.php?module=edukloud_batch&_spAction=editStudentFeedbackSubmit&showHTML=0";

        $SQLfb = "
        SELECT DISTINCT f.feedback_id
              ,f.title
              ,sf.batch_id
        FROM feedback f
        LEFT JOIN (student_feedback sf) ON (f.feedback_id = sf.feedback_id)
        WHERE sf.batch_feedback_id = {$batch_feedback_id}
          AND f.feedback_id = sf.feedback_id
        ";        
        $resultFb = $db->sql_query($SQLfb);
        $numRows  = $db->sql_numrows($resultFb);
        
        while ($row = $db->sql_fetchrow($resultFb)) {            
            $headerRow .="
            <th class='questionHeader'>{$row['title']}</th>        
            ";
            $feedback_id = $row['feedback_id'];
            $batch_id = $row['batch_id'];
        }

        $rows .= $this->getEditStudentFeedbackRecords($numRows, $batch_id);
        
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <table id='questionsList' class='thinlist'>
  		        {$formObj->getTBRow('Feedback Title', 'title', $title)}
                <thead>
                    <tr>
                        <th class='questionHeader'>Questions</th>
                        {$headerRow}
                    </tr>
                </thead>
                {$rows}
            </table>    
            <input type='hidden' name='batch_id' value='{$batch_id}' />
        </form>
        ";

        return $text;
    }

    /**
     * history_table_id - May be batch_history_id or course_contact_id according to the condition re-directed from
     */
    function getAttendanceRecords($history_table_id, $trainer_name){
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $db      = Zend_Registry::get('db');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        
        $row = $fn->getRecordRowByID('course_contact', 'course_contact_id', $history_table_id);
        $pfx = $history_table_id . '_' ;
        $table_history_id = 'course_contact_id[]';
        
        $arr = array('Present' => 'Present', 'Absent' => 'Absent');
        
        $text = "
        <tr>
            <td>{$trainer_name}</td>
            <td>{$formObj->getRadioArrRow(' ', "{$pfx}status", '', $arr, '')}</td>
            <input type='hidden' name='{$table_history_id}' 
            value='{$history_table_id}' />
        </tr>
        ";

        return $text;
    }

    /**
     * List of Student Name, Mark, Grade and Result
     */
    function getStudentGradeRecords($batch_history_id, $trainer_name){
        $fn      = Zend_Registry::get('fn');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');

        $row = $fn->getRecordRowByID('course_contact', 'course_contact_id', $batch_history_id);

        $pfx = $batch_history_id . '_' ;
        $exp = array('fldId' => 'marks');
        $expGrade = array('fldId' => 'grades');

        $expEdit = array('isEditable' => 0);
        
        $text = "
        <tr id='{$batch_history_id}'>
            <td>{$trainer_name}</td>
            <td>{$formObj->getTBRow('', "{$pfx}marks", '', $exp)}</td>
            <td>{$formObj->getTARow('', "{$pfx}remarks", '', $exp)}</td>
            <input type='hidden' name='batch_history_id[]' 
            value='{$batch_history_id}' />
        </tr>
        ";

        return $text;
    }

    /**
     *
     */
     function getEditAttendance() {
        $ln      = Zend_Registry::get('ln');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $db      = Zend_Registry::get('db');
        $cpUrl   = Zend_Registry::get('cpUrl');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
               
        $rows = '';

        $batch_id       = $fn->getReqParam('id');
        $attendance_id  = $fn->getReqParam('attendance_id');
        $attendance_date= $fn->getReqParam('attendance_date');
        $currentDate    = date("Y-m-d");
        
        $formAction = "index.php?module=edukloud_batch&_spAction=editAttendanceSubmit&showHTML=0";

        $SQL = "
        SELECT a.*
              ,c.first_name
              ,c.last_name
        FROM attendance a
        LEFT JOIN (contact c) ON (a.contact_id = c.contact_id)
        WHERE a.batch_id = '{$batch_id}'
        AND a.date = '{$attendance_date}'
        ";        
        $result = $db->sql_query($SQL);
        
        while ($row = $db->sql_fetchrow($result)) {
            $trainee_name = $row['first_name'] . ' ' . $row['last_name'];          
            $rows .= $this->getEditAttendanceRecords($row['attendance_id'], $trainee_name);
        }
        
        $headers ="
        <th>Trainer Name</th>
        <th>Attendance Status</th>
        ";
        
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <table id='' class='thinlist'>
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
    function getEditAttendanceRecords($attendance_id, $trainer_name){
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');

        $row = $fn->getRecordRowByID('attendance', 'attendance_id', $attendance_id);
        $pfx = $attendance_id . '_' ;
        $arr = array('Present' => 'Present', 'Absent' => 'Absent');
        $text = "
        <tr>
            <td>{$trainer_name}</td>
            <td>{$formObj->getRadioArrRow(' ', "{$pfx}status", $row['status'], $arr, '')}</td>
            <input type='hidden' name='attendance_id[]' 
            value='{$attendance_id}' />
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
               
        $rows = '';

        $batch_id         = $fn->getReqParam('id');
        $exam_type        = $fn->getReqParam('type');
        $exam_date        = $fn->getReqParam('date');
        $student_grade_id = $fn->getReqParam('student_grade_id');
        
        $formAction = "index.php?module=edukloud_batch&_spAction=editStudentGradeSubmit&showHTML=0";

        $SQL = "
        SELECT a.*
              ,c.first_name
              ,c.last_name
        FROM student_grade a
        LEFT JOIN (contact c) ON (a.contact_id = c.contact_id)
        WHERE batch_id = '{$batch_id}'
        AND a.exam_type = '{$exam_type}'
        AND a.exam_date = '{$exam_date}'
        ";
        
        $result = $db->sql_query($SQL);
        
        while ($row = $db->sql_fetchrow($result)) {
            $exam_type    = $row['exam_type'];
            $exam_date    = $row['exam_date'];
            $trainee_name = $row['first_name'] . ' ' . $row['last_name'];          

            $rows .= $this->getEditStudentGradeRecords($row['student_grade_id'], $trainee_name);
        }
        
        $headers ="
        <th>Trainer Name</th>
        <th>Marks</th>
        <th>Remarks</th>
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
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');

        $row = $fn->getRecordRowByID('student_grade', 'student_grade_id', $student_grade_id);
        $pfx = $student_grade_id . '_' ;
        $exp = array('fldId' => 'marks');

        $text = "
        <tr id='{$student_grade_id}'>
            <td>{$trainer_name}</td>
            <td>{$formObj->getTBRow(' ', "{$pfx}marks", $row['marks'], $exp)}</td>
            <td>{$formObj->getTARow(' ', "{$pfx}remarks", $row['remarks'])}</td>
            <input type='hidden' name='student_grade_id[]' 
            value='{$student_grade_id}' />
        </tr>
        
        ";

        return $text;
    }
   
    /**
     *
     */
    function getStudentFeedbackRecords($numRows, $batch_id, $feedback_group){
        $db    = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';

        $SQL = "
        SELECT cc.course_contact_id
            ,cc.contact_id
            ,c.first_name
            ,c.last_name
        FROM course_contact cc
        LEFT JOIN contact c ON (c.contact_id = cc.contact_id)
        WHERE batch_id = {$batch_id}
        ";        
        $result = $db->sql_query($SQL);
        
        while ($row = $db->sql_fetchrow($result)) {
            $trainee_name = $row['first_name'] . ' ' . $row['last_name'];          
            $rows .="
            <tr>
                <td>{$trainee_name}</td>        
                {$this->getStudentRemarksFunctionTD($numRows, $row['contact_id'], $feedback_group)}
            </tr>            
            ";
        }
                
        $text = "
        {$rows}         
        ";

        return $text;
    }
    
    /**
     *
     */
    function getEditStudentFeedbackRecords($numRows, $batch_id){
        $db    = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';

        $SQL = "
        SELECT cc.course_contact_id
            ,cc.contact_id
            ,c.first_name
            ,c.last_name
        FROM course_contact cc
        LEFT JOIN contact c ON (c.contact_id = cc.contact_id)
        WHERE batch_id = {$batch_id}
        ";        
        $result = $db->sql_query($SQL);
        
        while ($row = $db->sql_fetchrow($result)) {            
            $trainee_name = $row['first_name'] . ' ' . $row['last_name'];          
            $rows .="
            <tr>
                <td>{$trainee_name}</td>        
                {$this->getEditStudentRemarksFunctionTD($numRows, $row['contact_id'])}
            </tr>            
            ";
        }

        $text = "
        {$rows}         
        ";

        return $text;
    }
    
    /**
     *
     */
    function getBulkUpdateEvaluate() {
        $ln      = Zend_Registry::get('ln');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $db      = Zend_Registry::get('db');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $cpUrl   = Zend_Registry::get('cpUrl');
        $formObj = Zend_Registry::get('formObj');
               
        $rows = '';

        $batch_id  = $fn->getReqParam('id');
        
        $batchRec  = $fn->getRecordRowByID('batch', 'batch_id', $batch_id);
        $courseRec = $fn->getRecordRowByID('course', 'course_id', $batchRec['course_id']);

        $formAction = "index.php?module=edukloud_batch&_spAction=bulkUpdateEvaluateSubmit&showHTML=0";

        $SQL = "
        SELECT cc.course_contact_id
            ,CONCAT(c.first_name, ' ', c.last_name) AS trainee_name
            ,cc.creation_date
        FROM course_contact cc
        LEFT JOIN contact c ON (c.contact_id = cc.contact_id)
        WHERE batch_id = {$batch_id}
        ";
        $result = $db->sql_query($SQL);
        
        while ($row = $db->sql_fetchrow($result)) {
            $rows .= $this->getBatchRecords($row['course_contact_id'], $row['trainee_name']);
            $creation_date = $fn->getCPDate($row['creation_date'], 'Y-m-d');
        }
        
        $headers ="
        <th>Trainer Name</th>
        <th>Assessment Status</th>
        <th>Assessment Remarks</th>
        ";
        
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <div class='mb10'>Course Name : {$courseRec['title']}</div>
            <div class='mb10'>Batch Name : {$batchRec['title']}</div>            
            <div class='mb10'>Date : {$creation_date}</div>            
            <table id='' class='thinlist'>
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
    function getStudentRemarksFunctionTD($numRowsStudent, $contact_id, $feedback_group){
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $db      = Zend_Registry::get('db');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');

        $rows           = '';
        $feedbackArray  = '';
        $feedbackArray = array();
        
        $rowCounter = 1;        
        $arr        = array(1 => 1, 2 => 2, 3 => 3, 4 => 4);

        $batch_id = $fn->getReqParam('id');

        $SQLfb = "
        SELECT feedback_id, title
        FROM feedback
        WHERE feedback_group = '{$feedback_group}'
        ORDER BY feedback_id ASC
        ";        
        $resultFb = $db->sql_query($SQLfb);
        $numRows  = $db->sql_numrows($resultFb);
        
        while ($row = $db->sql_fetchrow($resultFb)) {            
            $feedbackArray[]= $row['feedback_id'];
        }

        $row = $fn->getRecordRowByID('contact', 'contact_id', $contact_id);
        $pfx = $contact_id . '_' ;
        for($i=1; $i<=$numRowsStudent; $i++){
            $k   = $i-1;
            $pfx = 'marks_' . $contact_id . '_' . $feedbackArray[$k];
            $rows .="
            <td>{$formObj->getRadioArrRow(' ', "{$pfx}", '', $arr, '')}</td>
            ";            
            $rowCounter++;          
        }

        $text = "
        {$rows} 
        ";

        return $text;
    }
    
    /**
     *
     */
    function getEditStudentRemarksFunctionTD($numRowsStudent, $contact_id ){
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $db      = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $rows           = '';
        $rowCounter     = 1;
        $feedbackArray  = '';
        
        $arr = array(1 => 1, 2 => 2, 3 => 3, 4 => 4);

        $batch_feedback_id  = $fn->getReqParam('id');
        $title              = $fn->getReqParam('title');

        $SQLfb = "
        SELECT DISTINCT f.feedback_id
              ,f.title
              ,sf.batch_id
        FROM feedback f
        LEFT JOIN (student_feedback sf) ON (f.feedback_id = sf.feedback_id)
        WHERE sf.batch_feedback_id = {$batch_feedback_id}
        ";        
        $resultFb = $db->sql_query($SQLfb);
        $numRows  = $db->sql_numrows($resultFb);

        $feedbackArray = array();
        
        while ($row = $db->sql_fetchrow($resultFb)) {
            $feedbackArray[]= $row['feedback_id']; 
            $batch_id = $row['batch_id'];           
        }
        
        $pfx = $contact_id . '_' ;
        for($i=1; $i<=$numRowsStudent; $i++){
            $k = $i-1;

            $batchFeedback = $fn->getRecordByCondition('batch_feedback', "title = '{$title}' AND batch_id = '{$batch_id}'");

            $studentFeedbackRow = $fn->getRecordByCondition('student_feedback', 
                "contact_id = '{$contact_id}' AND 
                 batch_id = '{$batch_id}' AND 
                 feedback_id = '{$feedbackArray[$k]}' AND 
                 batch_feedback_id = '{$batch_feedback_id}'"
            );
            
            $pfx = 'marks_' . $contact_id . '_' . $feedbackArray[$k] . '_' . $studentFeedbackRow['student_feedback_id'];

            $rows .="
            <td>{$formObj->getRadioArrRow(' ', "{$pfx}", $studentFeedbackRow['marks'], $arr, '')}</td>
            ";
            
            $rowCounter++;          
        }

        $text = "
        {$rows} 
        ";

        return $text;
    }

    /**
     *
     */     
    function getBatchRecords($course_contact_id, $trainer_name){
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');

        $row = $fn->getRecordRowByID('course_contact', 'course_contact_id', $course_contact_id);
        $pfx = $course_contact_id . '_' ;
        $text = "
        <tr>
            <td>{$trainer_name}</td>
            <td>{$formObj->getYesNoRRow('Competent', "{$pfx}evaluate_status", 
            $row['evaluate_status'])}</td>
            <td>{$formObj->getTBRow('', 'assessment_remarks', $row['assessment_remarks'])}</td>
            <input type='hidden' name='course_contact_id[]' 
            value='{$course_contact_id}' />
        </tr>
        ";

        return $text;
    }
    
    /**
     *
     */     
    function getFeedbackQuestions(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows       = '';
        $headerRow  = '';
        
        $feedback_group = $fn->getReqParam('feedback_group');
        $batch_id       = $fn->getReqParam('batch_id');
        
        $SQLfb = "
        SELECT feedback_id, title
        FROM feedback
        WHERE feedback_group = '{$feedback_group}'
        ORDER BY feedback_id ASC
        ";        
        $resultFb = $db->sql_query($SQLfb);
        $numRows  = $db->sql_numrows($resultFb);
        
        if ($numRows == 0) {
            return;
        }
        
        while ($row = $db->sql_fetchrow($resultFb)) {            
            $headerRow .="
            <th class='questionHeader'>{$row['title']}</th>        
            ";
            $feedback_id = $row['feedback_id'];
        }
        
        $rows .= $this->getStudentFeedbackRecords($numRows, $batch_id, $feedback_group);
        
        $text = "
        <table class='thinlist'>
            <thead>
                <tr>
                    <th class='questionHeader'>Questions</th>
                    {$headerRow}
                </tr>
            </thead>
            <tbody>
                {$rows}
            </tbody>
        </table>
        ";

        return $text;
    }
}