<?
class CP_Admin_Modules_AgileIms_Batch_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $modulesArr = Zend_Registry::get('modulesArr');

        $rows  = "";
        $email = "";
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
            {$listObj->getListDataCell($row['max_enroll_count'])}
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
        {$listObj->getListHeaderCell($modulesArr['agileIms_course']['title'], 'course_title')}
        {$listObj->getListHeaderCell('Teacher Name', 'teacher_name')}
        {$listObj->getListHeaderCell('No of Attendee', 'attendee')}
        {$listObj->getListHeaderCell('Max Attendee', 'max_enroll_count')}
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
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');

        $expEdit = array('isEditable' => 0);
        $subject = '';
        $class = '';

        /* Adding or Ignoring site id from Course sql according to config variable */
        $site_id_ignored_modules = $cpCfg['w.common_multiUniqueSite.ignoreModules'];
        if (in_array($tv['module'], $site_id_ignored_modules)) {
            $sqlCourse  = "SELECT course_id ,title FROM course ORDER BY title";
        } else {
            $sqlCourse  = $fn->getDDSql('agileIms_course');
        }

        $expCourse  = array('detailValue' => $row['course_title']);
        $expVl = array('sqlType' => 'OneField');

        $sqlBatchStatus = $fn->getValueListSQL('batchStatus');
        $sqlBatchVenue  = $fn->getValueListSQL('batchVenue');

        $sqlSubject = $fn->getDDSql('agileIms_subject');
        $sqlSubject = '';
        if ($row['course_id'] != ''){
            $modSubjectLink = getCPModuleObj('agileIms_subjectLink');
            $sqlSubject = $modSubjectLink->model->getCourseRelatedSubjectsSQL($row['course_id']);
        }
        $expSubject = array('detailValue' => $row['subject_title']);

        $fielset1 = "
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getTBRow('Batch Code', 'batch_code', $row['batch_code'], $expEdit)}
  		{$formObj->getDDRowBySQL('Venue', 'venue', $sqlBatchVenue, $row['venue'], $expVl)}
        {$formObj->getDDRowBySQL($modulesArr['agileIms_course']['title'], 'course_id', $sqlCourse, $row['course_id'], $expCourse)}
  		{$class}
        {$formObj->getDDRowBySQL('Subject Name', 'subject_id', $sqlSubject, $row['subject_id'], $expSubject)}
  		{$formObj->getDDRowBySQL('Batch Status', 'status', $sqlBatchStatus, $row['status'], $expVl)}
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
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $text ="";
        $rows = "";
        $printText ="";
        $actionText ="";

        $SQLCC = "
        SELECT evaluate_status
        FROM course_contact
        WHERE batch_id = {$row['batch_id']}
          AND evaluate_status = 1
        ";
        $resultCC  = $db->sql_query($SQLCC);
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

        if($cpCfg['m.agileIms.batch.showEvaluation']){
            $urlEvalaution = "index.php?module=agileIms_batch&_spAction=bulkUpdateEvaluate&id={$row['batch_id']}&showHTML=0";
            $actionText .="
            <div class='float_right button mb5'>
                <a href='{$urlEvalaution}' id='showEvaluation'>
                Assessment
                </a>
            </div>
            ";
        }

        if($cpCfg['m.agileIms.batch.printAttendanceExcell']){
            $urlAttendance = "index.php?module=agileIms_batch&_spAction=printAttendanceExcell&id={$row['batch_id']}&showHTML=0";
        } else {
            $urlAttendance = "index.php?module=agileIms_batch&_spAction=printAttendance&id={$row['batch_id']}&showHTML=0";
        }

        $formAlertBatchChanges = "index.php?module=agileIms_batch&_spAction=alertBatchChangesForm&showHTML=0&batch_id={$row['batch_id']}";
        $urlEducationalConfirm = "index.php?module=agileIms_batch&_spAction=printEducationalInformation&id={$row['batch_id']}&showHTML=0";
        $urlAssessmentSummary  = "index.php?module=agileIms_batch&_spAction=printAssessmentSummary&id={$row['batch_id']}&showHTML=0";
        $urlPrintMom  = "index.php?module=agileIms_batch&_spAction=printMom&id={$row['batch_id']}&showHTML=0";

        $buttonsRightpanel = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Print Action</div>
                        <div class='toggle'></div>
                    </div>
                </div>
                <div>
                    <div class='linkPortalDataWrapper'>
                        <table class='enrollmentList'>
                            <tbody>
                                <tr class='even'>
                                    <td><strong>DESCRIPTION</strong></td>
                                    <td><strong>ACTION</strong></td>
                                </tr>
                                <tr class='odd'>
                                    <td>Print attendance format in Excel</td>
                                    <td class='txtRight button m5'><a href='{$urlAttendance}' id='printAttendance'>Print Attendance</a></td>
                                </tr>
                                <tr class='even'>
                                    <td>Alert students for changes in batch or any update</td>
                                    <td class='txtRight button m5'><a href='{$formAlertBatchChanges}' id='alertBatchChangesToStudents'>Student Alert</a></td>
                                </tr>
                                <tr class='odd'>
                                    <td>Print Educational confirmation in Excel</td>
                                    <td class='txtRight button m5'><a href='{$urlEducationalConfirm}' id='printEducationalConfirmation'>Educational Confirmation</a></td>
                                </tr>
                                <tr class='even'>
                                    <td>Print Assessment Summary in Excel</td>
                                    <td class='txtRight button m5'><a href='{$urlAssessmentSummary}' id='printAssessmentSummary'>Assessment Summary</a></td>
                                </tr>
                                <tr class='odd'>
                                    <td>Print MOM file for batch in TXT format</td>
                                    <td class='txtRight button m5'><a href='{$urlPrintMom}' id='printMom'>Print MOM</a></td>
                                </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        ";

        $actionText ="
        <div class='floatbox mt5'>
            {$actionText}
        </div>
        ";

        // This is used to display Attendance Portal : Used for Mass IMS
        if($cpCfg['m.agileIms.batch.takeAttendance']){
            $rows .= $this->getAttendancePortalDisplay($row);
        }

        // This is used to display Student Grade Portal : Used for Mass IMS
        if($cpCfg['m.agileIms.batch.hasStudentGrade']){
            $rows .= $this->getStudentGradePortalDisplay($row);
        }

        // This is used to display feedback Portal : Used for Mass IMS
        if($cpCfg['m.agileIms.batch.studentFeedback']){
            $rows .= $this->getStudentFeedbackDisplay($row);
        }

        //{$printText}

        $text ="
        {$buttonsRightpanel}
        {$actionText}
        {$this->getBatchStudentLink($row['batch_id'])}
        {$displayLinkData->getLinkPortalMain('agileIms_batch', 'agileIms_teacherLink', 'Trainers Linked', $row)}
        {$displayLinkData->getLinkPortalMain('agileIms_batch', 'agileIms_assessorLink', 'Assessor Linked', $row)}
        {$media->getRightPanelMediaDisplay('Attendance Attachment', 'agileIms_batch', 'attachment', $row)}
        {$rows}
        ";
        return $text;
    }
    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $course_id       = $fn->getReqParam('course_id');
        $status          = $fn->getReqParam('status');
        $enrollment_year = $fn->getReqParam('enrollment_year');
        $venue           = $fn->getReqParam('venue');

        $sqlCourse = "
        SELECT c.course_id, c.title
        FROM course c
        ";
        $sqlStatus = $fn->getValueListSQL('batchStatus');
        $sqlVenue  = $fn->getValueListSQL('batchVenue');
        $sqlYear = "SELECT DISTINCT cc.year_of_enrollment FROM course_contact cc";

        $text = "
        <td>
            <select name='course_id' >
                <option value=''>Course</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCourse, $course_id)}
            </select>
        </td>
        <td>
            <select name='venue'>
                <option value=''>Venue</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlVenue, $venue)}
            </select>
        </td>
        <td>
            <select name='status' >
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlStatus, $status)}
            </select>
        </td>
        ";

        return $text;
    }

    /**
     *
     */
    function getAttendancePortalDisplay($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $tv = Zend_Registry::get('tv');
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows = "";
        $presentDate = "";

        $SQL = "
        SELECT DISTINCT date
              ,attendance_id
              ,batch_id
        FROM attendance
        WHERE batch_id = {$row['batch_id']}
        ORDER BY date DESC
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
                            <a class='editAttendance' class='' h='350' w='650' recid={$rowCC['attendance_id']} dialogtitle='Edit Attendance' link='index.php?module=agileIms_attendance&_spAction=editAttendance&id={$rowCC['batch_id']}&attendance_date={$rowCC['date']}&showHTML=0'>
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
        <div class='linkPortalWrapper agileIms_batch__agileIms_attendance' id='agileIms_batch#agileIms_attendance'>
            <div expanded='1' class='header'>
                <div class='linkPortalDataWrapper'>
                    <div class='actBtns'>
                        <a id='takeAttendance' class='button' dialogtitle='Take Attendance' href='index.php?module=agileIms_attendance&_spAction=takeAttendance&id={$row['batch_id']}&showHTML=0'>
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $tv = Zend_Registry::get('tv');
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows = "";
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
        ORDER BY exam_date DESC
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
                        <a class='editStudentGrade' class='' h='350' w='650' recid={$rowCC['student_grade_id']} dialogtitle='Edit Attendance' link='index.php?module=agileIms_grade&_spAction=editStudentGrade&id={$rowCC['batch_id']}&type={$rowCC['exam_type']}&date={$rowCC['exam_date']}&student_grade_id={$rowCC['student_grade_id']}&showHTML=0'>
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
        <div class='linkPortalWrapper agileIms_batch__agileIms_studentGrade' id='agileIms_batch#agileIms_studentGrade'>
            <div expanded='1' class='header'>
                <div class='linkPortalDataWrapper'>
                    <div class='actBtns'>
                        <a id='studentGrade' class='button' dialogtitle='Student Grade' href='index.php?module=agileIms_grade&_spAction=studentGrade&id={$row['batch_id']}&showHTML=0'>
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $tv = Zend_Registry::get('tv');

        $rows = "";
        $question = "";

        $SQL = "
        SELECT title, batch_feedback_id
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
                        <a class='editStudentFeedback' class='' h='350' w='650' recid={$row['batch_id']} dialogtitle='Edit Student Feedback' href='index.php?module=agileIms_feedback&_spAction=editStudentFeedback&id={$rowCC['batch_feedback_id']}&title={$rowCC['title']}&showHTML=0'>
                            <img border='0' title='Edit Record' src='/cmspilotv30/CP/admin/images/icons/btn_edit.png'>
                        </a>
                    </div>
                </td>
            </tr>
            ";
        }

        $text = "
        <div class='linkPortalWrapper agileIms_batch__agileIms_feedback' id='agileIms_batch#agileIms_feedback'>
            <div expanded='1' class='header'>
                <div class='linkPortalDataWrapper'>
                    <div class='actBtns'>
                        <a id='studentFeedback' class='button' dialogtitle='Student Feedback' href='index.php?module=agileIms_feedback&_spAction=studentFeedback&id={$row['batch_id']}&showHTML=0'>
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
    function getBulkUpdateEvaluate() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');
        $db = Zend_Registry::get('db');

        $batch_id= $fn->getReqParam('id');
        $batchRec = $fn->getRecordRowByID('batch', 'batch_id', $batch_id);
        $courseRec = $fn->getRecordRowByID('course', 'course_id', $batchRec['course_id']);

        $rows = '';
        $formAction = "index.php?module=agileIms_batch&_spAction=bulkUpdateEvaluateSubmit&showHTML=0";

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
    function getBatchRecords($course_contact_id, $trainer_name){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $row = $fn->getRecordRowByID('course_contact', 'course_contact_id', $course_contact_id);
        $pfx = $course_contact_id . '_' ;
        $text = "
        <tr>
            <td>{$trainer_name}</td>
            <td>{$formObj->getYesNoRRow('Competent', "{$pfx}evaluate_status",
            $row['evaluate_status'])}</td>
            <td>{$formObj->getTBRow('', 'assessment_remarks', $row['assessment_remarks'])}</td>
            <input type='hidden' name='course_contact_id[]' value='{$course_contact_id}' />
        </tr>
        ";

        return $text;
    }

    /**
     *
     */
     function getAlertBatchChangesForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $batch_id = $fn->getReqParam('batch_id');

        $formAction = "index.php?module=agileIms_batch&_spAction=alertBatchChangesFormSubmit&showHTML=0";

        $expEdit = array('isEditable' => 0);

        $text = "
        <form id='portalForm' class='yform columnar alertBatchChangesForm' method='post' action='{$formAction}'>
            <div class=''>{$formObj->getTBRow('', "error_box", '', $expEdit)}</div>
            {$formObj->getTBRow('Subject', 'subject')}
            {$formObj->getTextAreaRow('Message', 'message')}
            <input type='hidden' name='batch_id' value='{$batch_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
     function getBatchStudentLink($batch_id) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $sql = "
        SELECT cc.course_contact_id
              ,c.first_name
              ,c.email
              ,cc.contact_id
        FROM course_contact cc
        LEFT JOIN contact c ON (c.contact_id = cc.contact_id)
        LEFT JOIN batch b ON (b.batch_id = cc.batch_id)
        WHERE cc.batch_id = '{$batch_id}'
        ORDER BY c.registration_no
        ";
        $result = $db->sql_query($sql);
        $count  = 1;
        $rows   = '';

        while ($rowBatch = $db->sql_fetchrow($result)) {
            if ($count % 2 == 0) {
                $class = 'even';
            } else {
                $class = 'odd';
            }
            
            $student_result = $this->model->getStudentGrade($batch_id, $rowBatch['contact_id']);
            if ($student_result == 'Not Passed') {
                $print_cert = '';
            } else {
                $link = "index.php?module=agileIms_batchHistory&_action=edit&course_contact_id={$rowBatch['course_contact_id']}";
                $print_cert = "<a href='{$link}'>Goto Detail</a>";
            }
            
            $rows .= "
            <tr class='{$class}'>
                <td>{$count}</td>
                <td>{$rowBatch['first_name']}</td>
                <td>{$rowBatch['email']}</td>
                <td>{$print_cert}</td>
            </tr>
            ";
            $count++;
        }

        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Students linked</div>
                        <div class='toggle'></div>
                    </div>
                </div>
                <div>
                    <div class='linkPortalDataWrapper'>
                        <table class='enrollmentList'>
                            <tbody>
                                <tr class='even'>
                                    <td>S.No</td>
                                    <td>Name</td>
                                    <td>Email</td>
                                    <td></td>
                                </tr>
                                {$rows}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        ";

        return $text;
    }
}