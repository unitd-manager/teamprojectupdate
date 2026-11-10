<?
class CP_Admin_Modules_Pms_Batch_View extends CP_Common_Lib_ModuleViewAbstract
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
        {$listObj->getListHeaderCell($modulesArr['pms_course']['title'], 'course_title')}
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
            $sqlCourse  = "SELECT course_id ,title FROM course WHERE published = 1 ORDER BY sort_order ASC";
        } else {
            $sqlCourse  = $fn->getDDSql('pms_course');
        }

        $expCourse  = array('detailValue' => $row['course_title']);
        $expVl = array('sqlType' => 'OneField');

        /*$exp = array('titleFld' => "CONCAT_WS(' ', first_name, last_name )");
        $sqlTeacher  = $fn->getDDSql('pms_teacher', $exp);
        $expTeach  = array('detailValue' => $row['teacher_name']);*/
        $sqlBatchStatus = $fn->getValueListSQL('batchStatus');
        $sqlBatchVenue  = $fn->getValueListSQL('batchVenue');

        // This is used to display Subject Pvt : Used in Mass IMS
        if($cpCfg['m.pms.batch.showSubjectPvt']){
            $sqlSubject = $fn->getDDSql('pms_subject');
            $expSubject = array('detailValue' => $row['subject_title']);
            $subject = $formObj->getDDRowBySQL('Subject Name', 'subject_id', $sqlSubject, $row['subject_id'], $expSubject);
        }

        if ($cpCfg['m.pms.batch.showClassPvt']) {
            $sqlClass = $fn->getDDSql('pms_class');
            $expClass = array('detailValue' => $row['class_title']);
            $class = $formObj->getDDRowBySQL('Class Name', 'class_id', $sqlClass, $row['class_id'], $expClass);
        }

        /* Declare all the labels here to change the label names using this config variable */
        $courseName = 'Course Name';
        if($cpCfg['m.pms.batch.hasLabelChangeEnt']){
            $courseName = 'Class';
        }

        $fielset1 = "
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getTBRow('Batch Code', 'batch_code', $row['batch_code'], $expEdit)}
  		{$formObj->getDDRowBySQL('Venue', 'venue', $sqlBatchVenue, $row['venue'], $expVl)}
        {$formObj->getDDRowBySQL($modulesArr['pms_course']['title'], 'course_id', $sqlCourse, $row['course_id'], $expCourse)}
  		{$class}
        {$subject}
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

        $enrollment_year = $fn->getReqparam('enrollment_year');

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
       /* if($cpCfg['m.pms.batch.takeAttendance']){
            $urlAttendance = "index.php?module=pms_batch&_spAction=takeAttendance&id={$row['batch_id']}&showHTML=0";
            $actionText .="
            <div class='float_right button mb5'>
                <a href='{$urlAttendance}' id='takeAttendance'>
                Take Attendance
                </a>
            </div>
            ";
        }*/

       /* if($cpCfg['m.pms.batch.showStudentFeedback']){
            $urlStudentFeedback = "index.php?module=pms_batch&_spAction=studentFeedback&id={$row['batch_id']}&showHTML=0";
            $actionText .="
            <div class='float_right button mb5'>
                <a href='{$urlStudentFeedback}' id='studentFeedback'>
                Student Feedback
                </a>
            </div>
            ";
        }*/

        if($cpCfg['m.pms.batch.showEvaluation']){
            $urlEvalaution = "index.php?module=pms_batch&_spAction=bulkUpdateEvaluate&id={$row['batch_id']}&showHTML=0";
            $actionText .="
            <div class='float_right button mb5'>
                <a href='{$urlEvalaution}' id='showEvaluation'>
                Assessment
                </a>
            </div>
            ";
        }

        if($cpCfg['m.pms.batch.printAttendanceExcell']){
            if ($cpCfg['cp.forAceIms']) {
                $urlAttendance = "index.php?module=pms_batch&_spAction=printAttendanceExcell&id={$row['batch_id']}&showHTML=0";
            } else {
                $urlAttendance = "index.php?module=pms_batch&_spAction=printAttendanceExcell&id={$row['batch_id']}&enrollment_year={$enrollment_year}&showHTML=0";
            }
        } else {
            $urlAttendance = "index.php?module=pms_batch&_spAction=printAttendance&id={$row['batch_id']}&showHTML=0";
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

        // This is used to display Attendance Portal : Used for Mass IMS
        if($cpCfg['m.pms.batch.takeAttendance']){
            $rows .= $this->getAttendancePortalDisplay($row);
        }

        // This is used to display Student Grade Portal : Used for Mass IMS
        if($cpCfg['m.pms.batch.hasStudentGrade']){
            $rows .= $this->getStudentGradePortalDisplay($row);
        }

        // This is used to display feedback Portal : Used for Mass IMS
        if($cpCfg['m.pms.batch.studentFeedback']){
            $rows .= $this->getStudentFeedbackDisplay($row);
        }

        $assessorLink = '';
        if($cpCfg['m.pms.batch.hasAssessorLink']){
            $assessorLink = $displayLinkData->getLinkPortalMain('pms_batch', 'pms_assessorLink', 'Assessor Linked', $row);
        }

        $text ="
        {$printText}
        {$actionText}
        {$displayLinkData->getLinkPortalMain('pms_batch', 'pms_contactLink', 'Contacts Linked', $row)}
        {$displayLinkData->getLinkPortalMain('pms_batch', 'pms_teacherLink', 'Trainers Linked', $row)}
        {$assessorLink}
        {$media->getRightPanelMediaDisplay('Attendance Attachment', 'pms_batch', 'attachment', $row)}
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

        $sqlCourse = "
        SELECT c.course_id, c.title
        FROM course c
        WHERE c.published = 1
        ORDER BY c.sort_order ASC
        ";
        $sqlStatus = $fn->getValueListSQL('batchStatus');
        $sqlYear = "SELECT DISTINCT cc.year_of_enrollment FROM course_contact cc";

        $enrollment_year_fld = "";
        if ($cpCfg['cp.forAceIms'] == 0) {
            if ($tv['searchDone'] == 0) {
                $enrollment_year = date('Y');
            }

            $enrollment_year_fld = "
            <td>
                <select name='enrollment_year' class='float_right m5'>
                    <option value=''>Enrollment Year</option>
                    {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $enrollment_year)}
                </select>
            </td>
            ";
        }

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

        {$enrollment_year_fld}
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
        SELECT date
              ,batch_id
              ,teacher_name
        FROM attendance
        WHERE batch_id = {$row['batch_id']}
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        while ($rowCC = $db->sql_fetchrow($result)) {

            $date = $dateUtil->formatDate($rowCC['date'], 'DD-MM-YYYY');

            if ($presentDate != $rowCC['date']) {
            $rows .= "
                <tr style='background-color: #F4F4F4; color:#000000;'>
                    <td class='portalActBtns'>
                        <div class='float_left'>
                            {$date}
                        </div>
                    </td>
                    <td class='portalActBtns'>
                        <div class='float_left'>
                            {$rowCC['teacher_name']}
                        </div>
                    </td>
                    <td class='portalActBtns'>
                        <div style='float:right'>
                            <a class='editAttendance' class='' h='350' w='650' dialogtitle='Edit Attendance' link='index.php?module=pms_batch&_spAction=editAttendance&id={$rowCC['batch_id']}&date={$rowCC['date']}&teacher_name={$rowCC['teacher_name']}&showHTML=0'>
                                <img border='0' title='Edit Record' src='/cmspilotv30/CP/admin/images/icons/btn_edit.png'>
                            </a>
                        </div>
                    </td>
                    <td class='portalActBtns'>
                        <div class='float_right'>
                            <a class='deleteAttendance' href='#'  batch_id='{$rowCC['batch_id']}' date='{$rowCC['date']}' teacher_name='{$rowCC['teacher_name']}'>
                                <img src='/cmspilotv30/CP/admin/images/icons/btn_remove.png'>
                            </a>
                        </div>
                    </td>
                </tr>
                ";
            }
            $presentDate = $rowCC['date'];
        }

        $header = "
        <th>Date</th>
        <th>Teacher Name</th>
        <th align='Right'>Edit</th>
        <th align='Right'>Delete</th>
        ";

        $text = "
        <div class='linkPortalWrapper pms_batch__pms_attendance' id='pms_batch#pms_attendance'>
            <div expanded='1' class='header'>
                <div class='linkPortalDataWrapper'>
                    <div class='actBtns'>
                        <a id='takeAttendance' class='button takeAttendance' dialogtitle='Take Attendance' href='index.php?module=pms_batch&_spAction=takeAttendance&id={$row['batch_id']}&showHTML=0'>
                        <h3>Click Here to Take Attendance</h3>
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
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        while ($rowCC = $db->sql_fetchrow($result)) {

            $exam_date = $dateUtil->formatDate($rowCC['exam_date'], 'DD-MM-YYYY');

            if ($presentDate != $rowCC['exam_date']){
                $rows .= "
                <tr style='background-color: #F4F4F4; color:#000000;'>
                    <td>{$exam_date}</td>
                    <td>{$rowCC['exam_type']}</td>
                    <td>
                        <a class='editStudentGrade' class='' h='350' w='650' recid={$rowCC['student_grade_id']} dialogtitle='Edit Attendance' link='index.php?module=pms_batch&_spAction=editStudentGrade&id={$rowCC['batch_id']}&type={$rowCC['exam_type']}&date={$rowCC['exam_date']}&student_grade_id={$rowCC['student_grade_id']}&showHTML=0'>
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
        <div class='linkPortalWrapper pms_batch__pms_studentGrade' id='pms_batch#pms_studentGrade'>
            <div expanded='1' class='header'>
                <div class='linkPortalDataWrapper'>
                    <div class='actBtns'>
                        <a id='studentGrade' class='button studentGrade' dialogtitle='Student Grade' href='index.php?module=pms_batch&_spAction=studentGrade&id={$row['batch_id']}&showHTML=0'>
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
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        while ($rowCC = $db->sql_fetchrow($result)) {

            $rows .= "
            <tr style='background-color: #F4F4F4; color:#000000;'>
                <td class='portalActBtns'>
                    <div class='float_left'>
                        {$rowCC['title']}
                    </div>
                    <div style='float:right'>
                        <a class='editStudentFeedback' class='' h='350' w='650' recid={$row['batch_id']} dialogtitle='Edit Student Feedback' href='index.php?module=pms_batch&_spAction=editStudentFeedback&id={$rowCC['batch_feedback_id']}&title={$rowCC['title']}&showHTML=0'>
                            <img border='0' title='Edit Record' src='/cmspilotv30/CP/admin/images/icons/btn_edit.png'>
                        </a>
                    </div>
                </td>
            </tr>
            ";
        }

        $text = "
        <div class='linkPortalWrapper pms_batch__pms_feedback' id='pms_batch#pms_feedback'>
            <div expanded='1' class='header'>
                <div class='linkPortalDataWrapper'>
                    <div class='actBtns'>
                        <a id='studentFeedback' class='button' dialogtitle='Student Feedback' href='index.php?module=pms_batch&_spAction=studentFeedback&id={$row['batch_id']}&showHTML=0'>
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
     */
     function getTakeAttendance() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $formObj = Zend_Registry::get('formObj');

        $batch_id = $fn->getReqParam('id');

        $rows = '';
        $formAction = "index.php?module=pms_batch&_spAction=takeAttendanceSubmit&showHTML=0";
        $currentDate  = date("Y-m-d");

        if ($cpCfg['m.pms.batch.contactLinkPvt']) {
            $extraField = "bh.batch_history_id";
            //$extraTable = "LEFT JOIN (batch_history bh) ON (c.contact_id = bh.contact_id)";
            $extraTable = "LEFT JOIN (contact c) ON (c.contact_id = bh.contact_id)";
            $condition = "bh.batch_id = {$batch_id}";
            $tableName = "batch_history bh";
        } else {
            $currentYear = date('Y');
            $extraField = "cc.course_contact_id";
            //$extraTable = "LEFT JOIN (course_contact cc) ON (c.contact_id = cc.contact_id)";
            $extraTable = "LEFT JOIN (contact c) ON (c.contact_id = cc.contact_id)";
            $condition = "cc.batch_id = {$batch_id}
                          AND cc.year_of_enrollment = {$currentYear}
                          AND c.status = 'Active'";
            $tableName = "course_contact cc";
        }

        $SQL = "
        SELECT c.first_name
              ,c.last_name
              ,{$extraField}
        FROM {$tableName}
        {$extraTable}
        WHERE {$condition}
        ";
        $result = $db->sql_query($SQL);
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $trainee_name = $row['first_name'] . ' ' . $row['last_name'];
            if ($cpCfg['m.pms.batch.contactLinkPvt']) {
                $rows .= $this->getAttendanceRecords($row['batch_history_id'], $trainee_name, $count);
            } else {
                $rows .= $this->getAttendanceRecords($row['course_contact_id'], $trainee_name, $count);
            }
            $count++;
        }

        $currentDateFormat = date("d-m-Y");
        $staff_name = $fn->getSessionParam('userName');
        $expEdit = array('isEditable' => 0);

        $attDate ="
        {$formObj->getDateRow('Date', 'date')}
        ";

        $headers ="
        <div id='nameDisplay'>{$formObj->getTBRow('Teacher Name', 'teacher_name', $staff_name)}</div>
        <th>S.No</th>
        <th>Trainee Name</th>
        <th>
            <a href='#' class='allPresent'><u>All Present</u></a> /
            <a href='#' class='allAbsent'><u>All Absent</u></a>
        </th>
        <th>Remarks</th>
        ";

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$attDate}
            <div>{$formObj->getTBRow('', "error_box", '', $expEdit)}</div>
            <table id='attDisplay' class='thinlist'>
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
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');
        $db = Zend_Registry::get('db');

        $batch_id = $fn->getReqParam('id');
        $rows = '';
        $formAction = "index.php?module=pms_batch&_spAction=studentGradeSubmit&showHTML=0";

        $current_date = date('Y-m-d');
        $SQLGrade = "
        SELECT contact_id
              ,marks
              ,grade
        FROM student_grade
        ";
        $resultGrade    = $db->sql_query($SQLGrade);
        $numRows        = $db->sql_numrows($resultGrade);

        if ($cpCfg['m.pms.batch.contactLinkPvt']) {
            $SQL = "
            SELECT c.first_name
                  ,c.last_name
                  ,bh.batch_history_id
            FROM contact c
            LEFT JOIN (batch_history bh) ON (c.contact_id = bh.contact_id)
            WHERE bh.batch_id = {$batch_id}
            ";
        } else {
            $SQL = "
            SELECT c.first_name
                  ,c.last_name
                  ,cc.course_contact_id
            FROM contact c
            LEFT JOIN (course_contact cc) ON (c.contact_id = cc.contact_id)
            WHERE cc.batch_id = {$batch_id}
            ";
        }
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $trainee_name = $row['first_name'] . ' ' . $row['last_name'];

            if ($cpCfg['m.pms.batch.contactLinkPvt']) {
                $history_id = $row['batch_history_id'];
            } else {
                $history_id = $row['course_contact_id'];
            }

            $rows .= $this->getStudentGradeRecords($history_id, $trainee_name);
        }

        if ($cpCfg['cp.forAceIms']) {
            $headers ="
            <th>Student Name</th>
            <th>Marks</th>
            <th>Grade</th>
            <th>Result</th>
            ";
        } else {
            $headers ="
            <th>Student Name</th>
            <th>Marks</th>
            <th>Remarks</th>
            ";
        }

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
     *
     */
     function getStudentFeedback() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');
        $db = Zend_Registry::get('db');

        $batch_id = $fn->getReqParam('id');
        $feedback_group = $fn->getReqParam('feedback_group');

        $rows = '';
        $headerRow = '';
        $questionList = '';

        $formAction = "index.php?module=pms_batch&_spAction=studentFeedbackSubmit&showHTML=0";

        $sqlFeedbackGroup = $fn->getValueListSQL('feedbackGroup');
        $expVL = array('sqlType' => 'OneField');

        $text = "
        <div id='studentFeedbackOuter'>
            <form id='portalForm' class='yform columnar pms_batch__pms_studentFeedbackLink' method='post' action='{$formAction}'>
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
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');
        $db = Zend_Registry::get('db');

        $batch_feedback_id = $fn->getReqParam('id');
        $title = $fn->getReqParam('title');

        $rows = '';
        $headerRow = '';

        $formAction = "index.php?module=pms_batch&_spAction=editStudentFeedbackSubmit&showHTML=0";

        $SQLfb = "
        SELECT feedback_id, title
        FROM feedback
        ";

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
        $numRows = $db->sql_numrows($resultFb);

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
    function getAttendanceRecords($history_table_id, $trainer_name, $count){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        if($cpCfg['m.pms.batch.contactLinkPvt']){
            $row = $fn->getRecordRowByID('batch_history', 'batch_history_id', $history_table_id);
            $pfx = $history_table_id . '_' ;
            $table_history_id = 'batch_history_id[]';
        } else {
            $row = $fn->getRecordRowByID('course_contact', 'course_contact_id', $history_table_id);
            $pfx = $history_table_id . '_' ;
            $table_history_id = 'course_contact_id[]';
        }

        $arr = array('Present' => 'Present', 'Absent' => 'Absent');

        $text = "
        <tr>
            <td>{$count}</td>
            <td>{$trainer_name}</td>
            <td>{$formObj->getRadioArrRow(' ', "{$pfx}status", '', $arr, '')}</td>
            <td>
                <textarea value='' id='remarks' class='text attendanceRemarks' name='{$pfx}remarks'></textarea>
            </td>
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
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        if($cpCfg['m.pms.batch.contactLinkPvt']){
            $row = $fn->getRecordRowByID('batch_history', 'batch_history_id', $batch_history_id);
        } else {
            $row = $fn->getRecordRowByID('course_contact', 'course_contact_id', $batch_history_id);
        }

        //$row = $fn->getRecordRowByID('batch_history', 'batch_history_id', $batch_history_id);
        $pfx = $batch_history_id . '_' ;
        $exp = array('fldId' => 'marks');
        $expGrade = array('fldId' => 'grades');

        $expEdit = array('isEditable' => 0);

        if ($cpCfg['cp.forAceIms']) {
            $text = "
            <tr id='{$batch_history_id}'>
                <td>{$trainer_name}</td>
                <td>{$formObj->getTBRow('', "{$pfx}marks", '', $exp)}</td>
                <td>{$formObj->getTBRow('', "{$pfx}grade")}</td>
                <td>{$formObj->getTBRow('', "{$pfx}student_result")}</td>
                <input type='hidden' name='batch_history_id[]'
                value='{$batch_history_id}' />
            </tr>
            ";
        } else {
            $text = "
            <tr id='{$batch_history_id}'>
                <td>{$trainer_name}</td>
                <td>{$formObj->getTBRow('', "{$pfx}marks", '', $exp)}</td>
                <td>{$formObj->getTARow('', "{$pfx}remarks", '', $exp)}</td>
                <input type='hidden' name='batch_history_id[]'
                value='{$batch_history_id}' />
            </tr>
            ";
        }

        return $text;
    }

    /**
     *
     */
     function getEditAttendance() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $formObj = Zend_Registry::get('formObj');
        $dateUtil = Zend_Registry::get('dateUtil');

        $batch_id        = $fn->getReqParam('id');
        $attendance_date = $fn->getReqParam('date');
        $teacher_name = $fn->getReqParam('teacher_name');
        $currentDate     = date("Y-m-d");

        $rows = '';
        $formAction = "index.php?module=pms_batch&_spAction=editAttendanceSubmit&showHTML=0";

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
        $count = 1;

        while ($row = $db->sql_fetchrow($result)) {
            $teacher_name = $row['teacher_name'];
            $trainee_name = $row['first_name'] . ' ' . $row['last_name'];
            $rows .= $this->getEditAttendanceRecords($row['attendance_id'], $trainee_name, $count);
            $count++;
        }

        $headers ="
        <th>S.No</th>
        <th>Trainee Name</th>
        <th>Attendance Status</th>
        <th>Remarks</th>
        ";

        $attendance_date = $dateUtil->formatDate($attendance_date, 'DD-MM-YYYY');

        $text = "
        <strong>Date : {$attendance_date}</strong><br><br>
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <div>{$formObj->getTBRow('Teacher Name', "teacher_name", $teacher_name)}</div>
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
    function getEditAttendanceRecords($attendance_id, $trainee_name, $count){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $row = $fn->getRecordRowByID('attendance', 'attendance_id', $attendance_id);
        $pfx = $attendance_id . '_' ;
        $arr = array('Present' => 'Present', 'Absent' => 'Absent');
        $text = "
        <tr>
            <td>{$count}</td>
            <td>{$trainee_name}</td>
            <td>{$formObj->getRadioArrRow(' ', "{$pfx}status", $row['status'], $arr, '')}</td>
            <td>
                <textarea value='{$row['remarks']}' id='remarks' class='text attendanceRemarks' name='{$pfx}remarks'>{$row['remarks']}</textarea>
            </td>
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

        $batch_id = $fn->getReqParam('id');
        $exam_type = $fn->getReqParam('type');
        $exam_date = $fn->getReqParam('date');
        $student_grade_id = $fn->getReqParam('student_grade_id');

        $rows = '';
        $formAction = "index.php?module=pms_batch&_spAction=editStudentGradeSubmit&showHTML=0";

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
            $exam_type = $row['exam_type'];
            $exam_date = $row['exam_date'];
            $trainee_name = $row['first_name'] . ' ' . $row['last_name'];

            $rows .= $this->getEditStudentGradeRecords($row['student_grade_id'], $trainee_name);
        }

        if ($cpCfg['cp.forAceIms']) {
            $headers ="
            <th>Trainer Name</th>
            <th>Marks</th>
            <th>Grade</th>
            <th>Result</th>
            ";
        } else {
            $headers ="
            <th>Trainer Name</th>
            <th>Marks</th>
            <th>Remarks</th>
            ";
        }

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

        if ($cpCfg['cp.forAceIms']) {
            $text = "
            <tr id='{$student_grade_id}'>
                <td>{$trainer_name}</td>
                <td>{$formObj->getTBRow(' ', "{$pfx}marks", $row['marks'], $exp)}</td>
                <td>{$formObj->getTBRow(' ', "{$pfx}grade", $row['grade'])}</td>
                <td>{$formObj->getTBRow(' ', "{$pfx}student_result", $row['student_result'])}</td>
                <input type='hidden' name='student_grade_id[]'
                value='{$student_grade_id}' />
            </tr>
            ";
        } else {
            $text = "
            <tr id='{$student_grade_id}'>
                <td>{$trainer_name}</td>
                <td>{$formObj->getTBRow(' ', "{$pfx}marks", $row['marks'], $exp)}</td>
                <td>{$formObj->getTARow(' ', "{$pfx}remarks", $row['remarks'])}</td>
                <input type='hidden' name='student_grade_id[]'
                value='{$student_grade_id}' />
            </tr>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getStudentFeedbackRecords($numRows, $batch_id, $feedback_group){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';

        if($cpCfg['m.pms.batch.contactLinkPvt']){
            $SQL = "
            SELECT cc.batch_history_id
                ,cc.contact_id
                ,CONCAT(c.first_name, ' ', c.last_name) AS trainee_name
            FROM batch_history cc
            LEFT JOIN contact c ON (c.contact_id = cc.contact_id)
            WHERE batch_id = {$batch_id}
            ";
        } else {
            $SQL = "
            SELECT cc.course_contact_id
                ,cc.contact_id
                ,c.first_name
                ,c.last_name
            FROM course_contact cc
            LEFT JOIN contact c ON (c.contact_id = cc.contact_id)
            WHERE batch_id = {$batch_id}
            ";
        }

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
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';

        if($cpCfg['m.pms.batch.contactLinkPvt']){
            $SQL = "
            SELECT cc.batch_history_id
                ,cc.contact_id
                ,c.first_name
                ,c.last_name
            FROM batch_history cc
            LEFT JOIN contact c ON (c.contact_id = cc.contact_id)
            WHERE batch_id = {$batch_id}
            ";
        } else {
            $SQL = "
            SELECT cc.course_contact_id
                ,cc.contact_id
                ,c.first_name
                ,c.last_name
            FROM course_contact cc
            LEFT JOIN contact c ON (c.contact_id = cc.contact_id)
            WHERE batch_id = {$batch_id}
            ";
        }

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
        $formAction = "index.php?module=pms_batch&_spAction=bulkUpdateEvaluateSubmit&showHTML=0";

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
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';
        $rowCounter = 1;
        $feedbackArray ='';

        $arr = array(1 => 1, 2 => 2, 3 => 3, 4 => 4);

        $batch_id = $fn->getReqParam('id');

        $feedbackArray = array();

        $SQLfb = "
        SELECT feedback_id, title
        FROM feedback
        WHERE feedback_group = '{$feedback_group}'
        ORDER BY feedback_id ASC
        ";
        $resultFb = $db->sql_query($SQLfb);
        $numRows = $db->sql_numrows($resultFb);

        while ($row = $db->sql_fetchrow($resultFb)) {

            $feedbackArray[]= $row['feedback_id'];
        }

        $row = $fn->getRecordRowByID('contact', 'contact_id', $contact_id);
        $pfx = $contact_id . '_' ;
        for($i=1; $i<=$numRowsStudent; $i++){
            $k = $i-1;
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
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');

        $rows = '';
        $rowCounter = 1;
        $feedbackArray ='';

        $arr = array(1 => 1, 2 => 2, 3 => 3, 4 => 4);

        $batch_feedback_id = $fn->getReqParam('id');
        $title = $fn->getReqParam('title');

        $SQLfb = "
        SELECT DISTINCT f.feedback_id
              ,f.title
              ,sf.batch_id
        FROM feedback f
        LEFT JOIN (student_feedback sf) ON (f.feedback_id = sf.feedback_id)
        WHERE sf.batch_feedback_id = {$batch_feedback_id}
        ";
        $resultFb = $db->sql_query($SQLfb);
        $numRows = $db->sql_numrows($resultFb);

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

        $feedback_group = $fn->getReqParam('feedback_group');
        $batch_id = $fn->getReqParam('batch_id');

        $rows = '';
        $headerRow = '';

        $SQLfb = "
        SELECT feedback_id, title
        FROM feedback
        WHERE feedback_group = '{$feedback_group}'
        ORDER BY feedback_id ASC
        ";
        $resultFb = $db->sql_query($SQLfb);
        $numRows = $db->sql_numrows($resultFb);

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