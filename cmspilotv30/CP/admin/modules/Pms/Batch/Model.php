<?
class CP_Admin_Modules_Pms_Batch_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $enrollment_year = $fn->getReqParam('enrollment_year');
        $site_id         = $fn->getSessionParam('cp_site_id');

        if ($tv['searchDone'] == 0){
            $enrollment_year = date('Y');
        }

        $appendSql = "";
        $appendSql .= " AND cc.year_of_enrollment = '{$enrollment_year}'";
        if ($site_id != '') {
            $appendSql .= " AND cc.site_id = '{$site_id}'";
        }

        $extraFieldNames = '';
        $extraTableNames = '';

        if ($cpCfg['m.pms.batch.showSubjectPvt']) {
            $extraFieldNames .= ",s.title AS subject_title";
            $extraTableNames .= "
            LEFT JOIN subject s ON (s.subject_id = b.subject_id)
            ";
        }

        if ($cpCfg['m.pms.batch.showClassPvt']) {
            $extraFieldNames .= ", cl.title AS class_title";
            $extraTableNames .= "
            LEFT JOIN class cl ON (b.class_id = cl.class_id)
            ";
        }

        if ($cpCfg['cp.forAceIms']) {
            $SQL = "
            SELECT b.*
                  ,c.title AS course_title
                  ,CONCAT_WS(' ', t.first_name, t.last_name ) AS teacher_name
                  ,(SELECT COUNT(*)
                    FROM course_contact
                    WHERE batch_id = b.batch_id) AS attendee
                  {$extraFieldNames}
            FROM batch b
            LEFT JOIN course c ON (c.course_id = b.course_id)
            LEFT JOIN teacher t ON (t.teacher_id = b.teacher_id)
            {$extraTableNames}
            ";
        } else {
            $SQL = "
            SELECT b.*
                  ,c.title AS course_title
                  ,CONCAT_WS(' ', t.first_name, t.last_name ) AS teacher_name
                  ,(SELECT COUNT(*)
                    FROM course_contact cc
                    LEFT JOIN (contact cont) ON (cc.contact_id = cont.contact_id)
                    WHERE cc.batch_id = b.batch_id
                      AND cont.status = 'Active'
                      {$appendSql}) AS attendee
                  {$extraFieldNames}
            FROM batch b
            LEFT JOIN course c ON (c.course_id = b.course_id)
            LEFT JOIN (batch_teacher bt) ON (b.batch_id = bt.batch_id)
            LEFT JOIN (teacher t) ON (bt.teacher_id = t.teacher_id)
            {$extraTableNames}
            ";
        }

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'b';

        $course_id       = $fn->getReqParam('course_id');
        $teacher_id      = $fn->getReqParam('teacher_id');
        $batch_id        = $fn->getReqParam('batch_id');
        $status          = $fn->getReqParam('status');

        if ($batch_id != "") {
            $searchVar->sqlSearchVar[] = "b.batch_id = '{$batch_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "b.batch_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'b.batch_id');

            //if ($_SESSION['userGroupType'] == 'User') {
            if ($_SESSION['userGroupType'] == 'Teacher') {
                $searchVar->sqlSearchVar[] = "b.batch_id IN (
                    SELECT batch_id
                    FROM batch_teacher
                    WHERE teacher_id = {$_SESSION['cpTeacherID']}
                )";
            }

            if ($course_id != '' ) {
                $searchVar->sqlSearchVar[] = "c.course_id = {$course_id}";
            }

            if ($status != '' ) {
                $searchVar->sqlSearchVar[] = "b.status = '{$status}'";
            }

            if ($teacher_id != '' ) {
                $searchVar->sqlSearchVar[] = "t.teacher_id = {$teacher_id}";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       b.batch_code   LIKE '%{$tv['keyword']}%' OR
                       b.title        LIKE '%{$tv['keyword']}%' OR
                       c.title        LIKE '%{$tv['keyword']}%'
                )";
            }

            $searchVar->sortOrder = "b.sort_order";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the title');
        $validate->validateData('batch_code', 'Please enter the batch code');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fa['status'] = 'Open';
        $fa['sort_order'] = $fn->getNextSortOrder("batch");

        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the title');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'course_id');
        $fa = $fn->addToFieldsArray($fa, 'subject_id');
        $fa = $fn->addToFieldsArray($fa, 'class_id');
        $fa = $fn->addToFieldsArray($fa, 'batch_code');
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'venue');
        $fa = $fn->addToFieldsArray($fa, 'start_date');
        $fa = $fn->addToFieldsArray($fa, 'end_date');
        $fa = $fn->addToFieldsArray($fa, 'no_of_hours');
        $fa = $fn->addToFieldsArray($fa, 'start_time');
        $fa = $fn->addToFieldsArray($fa, 'end_time');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'min_enroll_count');
        $fa = $fn->addToFieldsArray($fa, 'max_enroll_count');

        //-----------------------------------------------------------------------//
        /*if($cpCfg['generateSEOUrl'] == 1 && ($tv['lang'] == "eng" || $tv['lang'] == "")){
            $fa['seo_title'] = strtolower( $fn->_prepare_url_text($fa[$titleLang]));
        }*/

        return $fa;
    }
    /**
     *
     */
    function getPmsBatchPmsContactLinkSQL($id) {
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $current_year    = date('Y');
        $enrollment_year = $fn->getReqParam('enrollment_year', $current_year);
        $site_id         = $fn->getSessionParam('cp_site_id');

        $appendSql = "";
        if ($enrollment_year != '') {
            $appendSql .= " AND cc.year_of_enrollment = '{$enrollment_year}'";
        }

        if ($site_id) {
            $appendSql .= " AND cc.site_id = {$site_id}";
        }

        $contactFld = ($formObj->mode == 'edit') ? 'c.contact_id' : 'c.first_name AS contact_name';
        //$batchFld = ($formObj->mode == 'edit') ? 'b.batch_id' : 'b.title AS batch_title';
        $linkStr = "<a href='index.php?module=ecommerce_order&_spAction=printOrder&order_id'>Print</a>";

        if($cpCfg['m.pms.batch.contactLinkPvt']){
            $SQL = "
            SELECT bh.batch_history_id
                  ,c.first_name
                  ,c.last_name
                  ,c.email
            FROM batch_history bh
            LEFT JOIN contact c ON (bh.contact_id = c.contact_id)
            WHERE bh.batch_id = '{$id}'
            ORDER BY c.registration_no
            ";
        } else {
            $SQL = "
            SELECT cc.course_contact_id
                  ,c.first_name
                  ,c.last_name
                  ,c.email
                  ,CASE WHEN cc.evaluate_status = 1 THEN 'COMPETENT'
                   WHEN cc.evaluate_status = 0 THEN 'NOT COMPETENT' END
            FROM course_contact cc
            LEFT JOIN contact c ON (c.contact_id = cc.contact_id)
            LEFT JOIN batch b ON (b.batch_id = cc.batch_id)
            WHERE cc.batch_id = '{$id}'
            AND c.status = 'Active'
            {$appendSql}
            ORDER BY cc.course_contact_id
            ";
        }

        return $SQL;
    }

    /**
     *
     */
    function getPmsBatchPmsAssessorLinkSQL($id) {
        $formObj = Zend_Registry::get('formObj');

        $SQL = "
        SELECT bt.batch_teacher_id
              ,t.first_name
              ,t.last_name
              ,t.email
        FROM batch_teacher bt
        JOIN teacher t ON (t.teacher_id = bt.teacher_id)
        LEFT JOIN batch b ON (b.batch_id = bt.batch_id)
        WHERE bt.batch_id = '{$id}'
          AND bt.record_type = 'Assessor'
        ORDER BY bt.batch_teacher_id
        ";

        return $SQL;
    }

    /**
     *
     */
    function getPmsBatchPmsTeacherLinkSQL($id) {
        $cpCfg = Zend_Registry::get('cpCfg');

        $sqlAppend = '';
        if($cpCfg['m.pms.batch.showTrainerOnly']){
            $sqlAppend = "AND bt.record_type = 'Trainer'";
        }

        $SQL = "
        SELECT bt.batch_teacher_id
              ,t.first_name
              ,t.last_name
              ,t.email
        FROM batch_teacher bt
        JOIN teacher t ON (t.teacher_id = bt.teacher_id)
        LEFT JOIN batch b ON (b.batch_id = bt.batch_id)
        WHERE bt.batch_id = '{$id}'
             {$sqlAppend}
        ORDER BY bt.batch_teacher_id
        ";

        return $SQL;
    }

    /**
     *
     */
    function getPmsBatchPmsTeacherLinkLinkedIdsSQL() {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $sqlAppend = '';
        if($cpCfg['m.pms.batch.showTrainerOnly']){
            $sqlAppend = "AND record_type = 'Trainer'";
        }

        $SQL = "
        SELECT (
            SELECT GROUP_CONCAT(teacher_id SEPARATOR '#')
            FROM batch_teacher
            WHERE batch_id = '{$tv['linkMasterTableID']}'
                  {$sqlAppend}
        )
        AS selectedIDs
        ";

        return $SQL;
    }

    /**
     *
     */
    function getPmsBatchPmsAssessorLinkLinkedIdsSQL() {
        $tv = Zend_Registry::get('tv');

        $SQL = "
        SELECT (
            SELECT GROUP_CONCAT(teacher_id SEPARATOR '#')
            FROM batch_teacher
            WHERE batch_id = '{$tv['linkMasterTableID']}'
            AND record_type = 'Assessor'
        )
        AS selectedIDs
        ";

        return $SQL;
    }

    /**
     *
     */
    function getBulkUpdateEvaluateSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');

        if (!$this->getBulkUpdateEvaluateValidate()){
            return $validate->getErrorMessageXML();
        }

        $assessment_remarks= $fn->getReqParam('assessment_remarks');
        $course_contact_arr  = $fn->getPostParam('course_contact_id', array());

        $count = count($course_contact_arr);
        for ($i= 0; $i< $count; $i++){
            $course_contact_id = $course_contact_arr[$i];
            $pfx = $course_contact_id . '_';
            $evaluate_status  = $fn->getPostParam("{$pfx}evaluate_status");
            $fa = array();
            $fa['evaluate_status']   = $evaluate_status;
            $fa['course_contact_id'] = $course_contact_id;
            $fa['assessment_remarks'] = $assessment_remarks;
            $fn->saveRecord($fa, 'course_contact', 'course_contact_id', $course_contact_id);
        }

        return $validate->getSuccessMessageXML();

    }
    /**
     *
     */
    function getBulkUpdateEvaluateValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        //==================================================================//
        $validate->resetErrorArray();
        //$validate->validateData("email"       , $ln->gd("cp.form.fld.email.err")      , "email");

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
    /**
     *
     */
    function getPrintVoucher() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');


        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        //include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new FPDF();
        $pdf->SetFont('Arial','B',14);

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $product_id  = $fn->getReqParam('id');
		$invoice_terms = '';
		$notes  = '';
        $total = '';

		$SQL = "
		SELECT pv.voucher_no
            ,pv.product_id
            ,p.title as product_title
		FROM product_voucher pv
		JOIN product p ON (pv.product_id = p.product_id)
		WHERE pv.product_id = {$product_id}
		ORDER BY pv.product_voucher_id
		";

        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
		if ($numRows == 0){
            $pdf->SetXY(60,30);
            $pdf->Cell(50, 20, "Please set the values for your Voucher and print the PDF");
			$pdf->Output();
			return;
		}
        $count = 0;
        $total = 0;
        $rows = "";

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){
                $pdf->Image('images/sgdealon_banner.jpg',0,0,210, 30);
                $pdf->SetY(32);
                $product_title = "Please find the Voucher Codes for the Product : " ;
                //$pdf->WordWrap($product_title, 200);
                $pdf->Write(5, $product_title);
                $pdf->Ln(8);
                $pdf->drawTextBox($row['product_title'], 195, 32, 'L', 'T', 0);
                $pdf->Ln(10);
            }
             //Table Content
            $voucher_no = $row['voucher_no'];
            $count++;
            //$pdf->Write(5, "Voucher No " . $count . ': ' . $voucher_no);
            $pdf->Cell(60, 5, "Voucher No " . $count . ': ' . $voucher_no, 1);
            if ($count % 3){
            }
            else{
                $pdf->Ln(10);
            }
        }
        //Final Values
        $pdf->Output();
    }

    /**
     *
     */
    function getValidateAttendanceDate() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $date= $fn->getReqParam('date');
        $batch_id = $fn->getReqParam('batch_id');

        $SQLAttendance = "
        SELECT date
              ,attendance_id
              ,batch_id
        FROM attendance
        WHERE date = '{$date}'
        AND batch_id = {$batch_id}
        ";
        $resultAttendance   = $db->sql_query($SQLAttendance);
        $numRows = $db->sql_numrows($resultAttendance);

        if ($numRows > 0) {
            return "Yes";
        } else {
            return "No";
        }
    }

    /**
     */
    function getTakeAttendanceSubmit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        if (!$this->getTakeAttendanceValidate()){
            return $validate->getErrorMessageXML();
        }

        if ($cpCfg['m.pms.batch.contactLinkPvt']) {
            $batch_history_arr  = $fn->getPostParam('batch_history_id', array());
            $count = count($batch_history_arr);
        } else {
            $course_contact_arr  = $fn->getPostParam('course_contact_id', array());
            $count = count($course_contact_arr);
        }

        for ($i= 0; $i< $count; $i++) {
            if($cpCfg['m.pms.batch.contactLinkPvt']){
                $batch_history_id = $batch_history_arr[$i];
                $pfx = $batch_history_id . '_' ;
                $row = $fn->getRecordRowByID('batch_history', 'batch_history_id', $batch_history_id);
            } else {
                $course_contact_id = $course_contact_arr[$i];
                $pfx = $course_contact_id . '_' ;
                $row = $fn->getRecordRowByID('course_contact', 'course_contact_id', $course_contact_id);
            }

            $status  = $fn->getPostParam("{$pfx}status");
            $remarks = $fn->getPostParam("{$pfx}remarks");
            $teacher_name = $fn->getPostParam('teacher_name');
            $date = $fn->getPostParam('date');
            $currentDate  = date("Y-m-d");

            $fa = array();
            $fa['contact_id'] = $row['contact_id'];
            $fa['batch_id']   = $row['batch_id'];
            $fa['status']     = $status;
            $fa['remarks']    = $remarks;
            $fa['date']       = $date;
            $fa['teacher_name'] = $teacher_name;
            $fn->addRecord($fa, 'attendance');
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getTakeAttendanceValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $checkDateAttendance = $this->getValidateAttendanceDate();

        //==================================================================//
        $validate->resetErrorArray();
        if ($checkDateAttendance == 'Yes') {
            $msg = 'The Attendance already taken for this date. Please choose another date.';
            $validate->validateData('error_box', $msg);
            
        }
        //$validate->validateData("status" , $ln->gd("cp.form.fld.status.err") , "status");

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getStudentGradeSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getStudentGradeValidate()){
            return $validate->getErrorMessageXML();
        }

        $batch_history_arr  = $fn->getPostParam('batch_history_id', array());
        $count = count($batch_history_arr);

        for ($i= 0; $i< $count; $i++){
            $batch_history_id = $batch_history_arr[$i];
            $pfx = $batch_history_id . '_' ;
            $marks          = $fn->getPostParam("{$pfx}marks");

            if ($cpCfg['cp.forAceIms']) {
                $grade          = $fn->getPostParam("{$pfx}grade");
                $student_result = $fn->getPostParam("{$pfx}student_result");

                $row = $fn->getRecordRowByID('batch_history', 'batch_history_id', $batch_history_id);
            } else {
                $remarks        = $fn->getPostParam("{$pfx}remarks");

                $row = $fn->getRecordRowByID('course_contact', 'course_contact_id', $batch_history_id);
            }

            $exam_type      = $fn->getPostParam("exam_type");
            $exam_date      = $fn->getPostParam("exam_date");
            $currentDate    = date("Y-m-d");

            $fa = array();
            $fa['contact_id']       = $row['contact_id'];
            $fa['batch_id']         = $row['batch_id'];
            $fa['marks']            = $marks;

            if ($cpCfg['cp.forAceIms']) {
                $fa['grade']            = $grade;
                $fa['student_result']   = $student_result;
            } else {
                $fa['remarks']          = $remarks;
            }

            $fa['exam_type']        = $exam_type;
            $fa['exam_date']        = $exam_date;
            $fa['creation_date']    = $currentDate;
            $fn->addRecord($fa, 'student_grade');
        }

        return $validate->getSuccessMessageXML();

    }

    /**
     *
     */
    function getStudentGradeValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        //==================================================================//
        $validate->resetErrorArray();
        $validate->validateData('exam_type', 'Please choose assessment type');
        $validate->validateData('exam_date', 'Please select the date');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getStudentFeedbackSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');

        if (!$this->getStudentFeedbackValidate()){
            return $validate->getErrorMessageXML();
        }

        $title = $fn->getPostParam("title");
        $batch_id= $fn->getReqParam('batch_id');

        $fa1 = array();
        $fa1['batch_id']   = $batch_id;
        $fa1['title']      = $title;
        $id = $fn->addRecord($fa1, 'batch_feedback');

        foreach($_POST AS $key => $val){
            if (substr($key, 0, 5) == 'marks'){
                $marks_arr = explode('_', $key);
                $student_id = $marks_arr[1];
                $feedback_id  = $marks_arr[2];
                $markVal = $val;

                if ($markVal) {
                    $fa = array();
                    $fa['contact_id'] = $student_id;
                    $fa['batch_id']   = $batch_id;
                    $fa['feedback_id']= $feedback_id;
                    $fa['marks']      = $markVal;
                    $fa['batch_feedback_id'] = $id;
                    $fn->addRecord($fa, 'student_feedback');
                }
            }
        }
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditStudentFeedbackSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');

        if (!$this->getStudentFeedbackValidate()){
            return $validate->getErrorMessageXML();
        }

        $title = $fn->getPostParam("title");
        $batch_id= $fn->getReqParam('batch_id');
        $batchFeedback = $fn->getRecordByCondition('batch_feedback', "batch_id = '{$batch_id}'");

        $fa1 = array();
        $fa1['title'] = $title;
        $id = $fn->saveRecord($fa1, 'batch_feedback', 'batch_feedback_id', $batchFeedback['batch_feedback_id']);

        foreach($_POST AS $key => $val){
            if (substr($key, 0, 5) == 'marks'){
                $marks_arr = explode('_', $key);
                $student_id = $marks_arr[1];
                $feedback_id  = $marks_arr[2];
                $student_feedback_id  = $marks_arr[3];
                $markVal = $val;

                if ($student_feedback_id != ''){
                    $fa = array();
                    $fa['marks']      = $markVal;
                    $fn->saveRecord($fa, 'student_feedback', 'student_feedback_id', $student_feedback_id);
                } else {
                    $fa = array();
                    $fa['contact_id'] = $student_id;
                    $fa['batch_id']   = $batch_id;
                    $fa['feedback_id']= $feedback_id;
                    $fa['marks']      = $markVal;
                    $fa['batch_feedback_id'] = $id;
                    $fn->addRecord($fa, 'student_feedback');
                }
             }
        }
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getStudentFeedbackValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        //==================================================================//
        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the title');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Updating Grade As Per The Mark
     */
    function getUpdateGrade(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $mark = $fn->getReqParam('mark');
        if ($mark >= 75){
            $grade = 'A1';
        } else if ($mark >= 70 && $mark <= 74){
            $grade = 'A2';
        } else if ($mark >= 65 && $mark <= 69){
            $grade = 'B3';
        } else if ($mark >= 60 && $mark <= 64){
            $grade = 'B4';
        } else if ($mark >= 55 && $mark <= 59){
            $grade = 'C5';
        } else if ($mark >= 50 && $mark <= 54){
            $grade = 'C6';
        } else if ($mark >= 45 && $mark <= 50){
            $grade = 'D7';
        } else if ($mark <= 45){
            $grade = 'F9';
        }

        return $grade;
    }

    /**
     * Updating Student Result as per Mark entered
     */
    function getUpdateStudentResult(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $mark = $fn->getReqParam('mark');
        if ($mark >= 50){
            $student_result = 'Pass';
        } else if ($mark < 50){
            $student_result = 'Fail';
        }

        return $student_result;
    }

    /**
     *
     */
    function getEditAttendanceSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');

        if (!$this->getTakeAttendanceValidate()){
            return $validate->getErrorMessageXML();
        }

        $teacher_name  = $fn->getPostParam('teacher_name');
        $attendance_id_arr  = $fn->getPostParam('attendance_id', array());
        $count = count($attendance_id_arr);

        for ($i= 0; $i< $count; $i++){
            $attendance_id = $attendance_id_arr[$i];
            $pfx = $attendance_id . '_' ;
            $status  = $fn->getPostParam("{$pfx}status");
            $remarks = $fn->getPostParam("{$pfx}remarks");

            $fa = array();
            $fa['teacher_name'] = $teacher_name;
            $fa['status']       = $status;
            $fa['remarks']      = $remarks;
            $fn->saveRecord($fa, 'attendance', 'attendance_id', $attendance_id);
        }

        return $validate->getSuccessMessageXML();

    }

    /**
     *
     */
    function getEditStudentGradeSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        /*if (!$this->getStudentGradeValidate()){
            return $validate->getErrorMessageXML();
        }*/

        $student_grade_id_arr  = $fn->getPostParam('student_grade_id', array());
        $count = count($student_grade_id_arr);

        for ($i= 0; $i< $count; $i++){
            $student_grade_id = $student_grade_id_arr[$i];
            $pfx = $student_grade_id . '_' ;

            $marks   = $fn->getPostParam("{$pfx}marks");

            $fa = array();
            $fa['marks']   = $marks;
            if ($cpCfg['cp.forAceIms']) {
                $grade          = $fn->getPostParam("{$pfx}grade");
                $student_result = $fn->getPostParam("{$pfx}student_result");

                $fa['grade']            = $grade;
                $fa['student_result']   = $student_result;
            } else {
                $remarks = $fn->getPostParam("{$pfx}remarks");
                $fa['remarks'] = $remarks;
            }

            $fn->saveRecord($fa, 'student_grade', 'student_grade_id', $student_grade_id);
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getDeleteAttendance() {
        $db      = Zend_Registry::get('db');
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $dbUtil  = Zend_Registry::get('dbUtil');

        $batch_id     = $fn->getReqParam('batch_id');
        $date = $fn->getReqParam('date');

        $SQLDeleteAttendance = "
        DELETE FROM attendance
        WHERE batch_id = '{$batch_id}'
        AND date = '{$date}'
        ";
        $resultDeleteAttendance  = $db->sql_query($SQLDeleteAttendance);
    }
}
