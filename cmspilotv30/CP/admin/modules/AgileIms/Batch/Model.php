<?
class CP_Admin_Modules_AgileIms_Batch_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $fn    = Zend_Registry::get('fn');

        $SQL = "
        SELECT b.*
              ,c.title AS course_title
              ,CONCAT_WS(' ', t.first_name, t.last_name ) AS teacher_name
              ,(SELECT COUNT(*)
                FROM course_contact
                WHERE batch_id = b.batch_id) AS attendee
              ,s.title AS subject_title
        FROM batch b
        LEFT JOIN course c ON (c.course_id = b.course_id)
        LEFT JOIN teacher t ON (t.teacher_id = b.teacher_id)
        LEFT JOIN subject s ON (s.subject_id = b.subject_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv        = Zend_Registry::get('tv');
        $fn        = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');

        $searchVar->mainTableAlias = 'b';

        $course_id  = $fn->getReqParam('course_id');
        $teacher_id = $fn->getReqParam('teacher_id');
        $batch_id   = $fn->getReqParam('batch_id');
        $status     = $fn->getReqParam('status');
        $venue      = $fn->getReqParam('venue');

        if ($batch_id != "") {
            $searchVar->sqlSearchVar[] = "b.batch_id = '{$batch_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "b.batch_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'b.batch_id');

            if ($_SESSION['userGroupType'] == 'User') {
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

            if ($venue != '' ) {
                $searchVar->sqlSearchVar[] = "b.venue = '{$venue}'";
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
        $fn       = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa               = $this->getFields();
        $fa['status']     = 'Open';
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
        $fn       = Zend_Registry::get('fn');
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
        $fn = Zend_Registry::get('fn');

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

        return $fa;
    }

    /**
     *
     */
    function getAgileImsBatchAgileImsAssessorLinkSQL($id) {

        $SQL = "
        SELECT bt.batch_teacher_id
              ,t.first_name
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
    function getAgileImsBatchAgileImsTeacherLinkSQL($id) {
        $cpCfg = Zend_Registry::get('cpCfg');

        $sqlAppend = '';
        if($cpCfg['m.agileIms.batch.showTrainerOnly']){
            $sqlAppend = "AND bt.record_type = 'Trainer'";
        }

        $SQL = "
        SELECT bt.batch_teacher_id
              ,t.first_name
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
    function getAgileImsBatchAgileImsTeacherLinkLinkedIdsSQL() {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $sqlAppend = '';
        if($cpCfg['m.agileIms.batch.showTrainerOnly']){
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
    function getAgileImsBatchAgileImsAssessorLinkLinkedIdsSQL() {
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
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getBulkUpdateEvaluateValidate()){
            return $validate->getErrorMessageXML();
        }

        $assessment_remarks  = $fn->getReqParam('assessment_remarks');
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
        $validate = Zend_Registry::get('validate');

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
     * Updating Grade As Per The Mark
     */
    function getUpdateGrade(){
        $fn = Zend_Registry::get('fn');

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

        $mark = $fn->getReqParam('mark');

        if ($mark >= 50){
            $student_result = 'Pass';
        } else if ($mark < 50){
            $student_result = 'Fail';
        }

        return $student_result;
    }

    /**
     */
    function getAlertBatchChangesFormSubmit() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $batch_id = $fn->getReqParam('batch_id');
        $subject  = $fn->getPostParam('subject');
        $message  = $fn->getPostParam('message');

        if (!$this->getAlertBatchChangesFormValidate()) {
            return $validate->getErrorMessageXML();
        }

        $sqlBatch = "
        SELECT c.first_name
              ,c.email
        FROM contact c
        LEFT JOIN (batch_history bh) ON (c.contact_id = bh.contact_id)
        WHERE bh.batch_id = {$batch_id}
        ";
        $resultBatch = $db->sql_query($sqlBatch);
        $count = 1;
        while ($rowBatch = $db->sql_fetchrow($resultBatch)) {
            $toName  = $rowBatch['first_name'];
            $toEmail = $rowBatch['email'];

            $fromName  = $cpCfg['cp.companyName'];
            $fromEmail = $cpCfg['cp.adminEmail'];

            $smtp  = includeCPClass('Lib', 'smtp', 'CPSMTP');
            if ($count == 1) {
                $staffRec = $fn->getRecordRowByID('staff', 'staff_id', $fn->getSessionParam('staff_id'));
                $ccEmail  = $staffRec['email'];
                $error    = $smtp->sendEmail($toName, $toEmail, $fromName, $fromEmail, $subject, $message, $ccEmail);
            } else {
                $error = $smtp->sendEmail($toName, $toEmail, $fromName, $fromEmail, $subject, $message);
            }
            $count++;
        }
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAlertBatchChangesFormValidate() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $batch_id = $fn->getReqParam('batch_id');

        $sqlBatch = "
        SELECT COUNT(*)
        FROM batch_history
        WHERE batch_id = {$batch_id}
        ";
        $resultBatch = $db->sql_query($sqlBatch);
        $numRows  = $db->sql_numrows($resultBatch);

        $validate->resetErrorArray();

        if ($numRows == 0) {
            $msg = 'No Student is assigned for this batch';
            $validate->validateData('error_box', $msg);
        }

        $validate->validateData('subject' , 'Please enter the Subject');
        $validate->validateData('message' , 'Please enter the Message');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getPrintEducationalInformation() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');

        //-----------------------------------------------------------------//
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/tbs_class.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_opentbs.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_html.php');

        $TBS = new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);

        $batch_id  = $fn->getReqParam('id');
        $template  = 'Educational_Confirm.xlsx';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $TBS->LoadTemplate($templatePath);
        $rnd_no = mt_rand();
        $file_name = 'Educational_Confirm_' . $batch_id . '_' . $rnd_no . '.xlsx';
        $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);

        $path = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        $file_name_save = $path . '\\' . $file_name;
        $sourceFilePath = $file_name_save;
        $today =  date('d/m/Y');

        $SQL = "
        SELECT DISTINCT cont.contact_id
              ,b.*
              ,c.title AS course_title
              ,s.title AS subject_title
              ,c.course_code
              ,cont.first_name AS student_name
              ,cont.phone
              ,cont.registration_no
              ,cont.id_card_no
              ,cc.course_contact_id
        FROM batch b
        LEFT JOIN course c ON (c.course_id = b.course_id)
        LEFT JOIN course_contact cc ON (cc.batch_id = b.batch_id)
        LEFT JOIN contact cont ON (cc.contact_id = cont.contact_id)
        LEFT JOIN subject s ON (b.subject_id = s.subject_id)
        WHERE b.batch_id = {$batch_id}
        ORDER BY cont.registration_no
        ";
        $result = $db->sql_query($SQL);

        $serialNo    = 1;
        $arr         = array();
        $blkMain     = array();
        $blkStd      = array();
        $blkRegNo    = array();
        $blkSerialNo = array();

        while ($row = $db->sql_fetchrow($result)) {
            $arr1 = array('student_name' => $row['student_name']);
            $blkStd[] = $arr1;

            $arr2 = array('id_card_no' => $row['id_card_no']);
            $blkRegNo[] = $arr2;

            $arr3 = array('serial_no' => $serialNo);
            $blkSerialNo[] = $arr3;

            $arr['course_code']   = $row['course_code'];
            $arr['start_time']    = $row['start_time'];
            $arr['end_time']      = $row['end_time'];
            $arr['course_title']  = $row['course_title'];
            $arr['subject_title'] = $row['subject_title'];
            $arr['batch_code']    = $row['batch_code'];
            $blkMain[] = $arr;

            $serialNo++;
        }

        $TBS->MergeBlock('blkMain', $blkMain);
        $TBS->MergeBlock('blkStd', $blkStd);
        $TBS->MergeBlock('blkRegNo', $blkRegNo);
        $TBS->MergeBlock('blkSerialNo', $blkSerialNo);
        $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
    }

    /**
     *
     */
    function getPrintAssessmentSummary() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');

        //-----------------------------------------------------------------//
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/tbs_class.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_opentbs.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_html.php');

        $TBS = new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);

        $batch_id  = $fn->getReqParam('id');
        $template  = 'Assessment Summary.xlsx';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $TBS->LoadTemplate($templatePath);
        $rnd_no = mt_rand();
        $file_name = 'Assessment_Summary_' . $batch_id . '_' . $rnd_no . '.xlsx';
        $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);

        $path = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        $file_name_save = $path . '\\' . $file_name;
        $sourceFilePath = $file_name_save;
        $today =  date('d/m/Y');

        $SQL = "
        SELECT DISTINCT cont.contact_id
              ,b.*
              ,c.title AS course_title
              ,s.title AS subject_title
              ,c.course_code
              ,cont.first_name AS student_name
              ,cont.phone
              ,cont.registration_no
              ,cont.id_card_no
              ,cc.course_contact_id
        FROM batch b
        LEFT JOIN course c ON (c.course_id = b.course_id)
        LEFT JOIN course_contact cc ON (cc.batch_id = b.batch_id)
        LEFT JOIN contact cont ON (cc.contact_id = cont.contact_id)
        LEFT JOIN subject s ON (b.subject_id = s.subject_id)
        WHERE b.batch_id = {$batch_id}
        ORDER BY cont.registration_no
        ";
        $result = $db->sql_query($SQL);

        $serialNo    = 1;
        $arr         = array();
        $blkMain     = array();
        $blkStd      = array();
        $blkRegNo    = array();
        $blkSerialNo = array();

        while ($row = $db->sql_fetchrow($result)) {
            $arr1 = array('student_name' => $row['student_name']);
            $blkStd[] = $arr1;

            $arr2 = array('id_card_no' => $row['id_card_no']);
            $blkRegNo[] = $arr2;

            $arr3 = array('serial_no' => $serialNo);
            $blkSerialNo[] = $arr3;

            $arr['course_code']   = $row['course_code'];
            $arr['start_time']    = $row['start_time'];
            $arr['end_time']      = $row['end_time'];
            $arr['course_title']  = $row['course_title'];
            $arr['subject_title'] = $row['subject_title'];
            $arr['batch_code']    = $row['batch_code'];
            $blkMain[] = $arr;

            $serialNo++;
        }

        $TBS->MergeBlock('blkMain', $blkMain);
        $TBS->MergeBlock('blkStd', $blkStd);
        $TBS->MergeBlock('blkRegNo', $blkRegNo);
        $TBS->MergeBlock('blkSerialNo', $blkSerialNo);
        $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
    }

    /**
    *
    */
    function getPrintMom() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $text =  '';

        $batch_id = $fn->getReqParam('id');

        //$leftContent = 'WTF Hello this is not working';
        $text .= $this->getTextFileForDBS($batch_id);
        $template = 'mom_upload.txt';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $templatefile = fopen("{$templatePath}","w");
        fwrite($templatefile, $text);
        //fclose($templatefile);
        header ("Content-Type: application/download");
        header ("Content-Disposition: attachment; filename=$templatePath");
        header("Content-Length: " . filesize("$templatePath"));
        $fp = fopen("$templatePath", "r");
        fpassthru($fp);
        //header("Location: $templatePath");
    }

    /**
    *
    */
    function getTextFileForDBS($batch_id) {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = '';
        $text .= "<score>";
        $text .=   "\n";

        $SQL = "
        SELECT b.*
              ,c.course_code
              ,o.order_date
              ,co.first_name
              ,co.id_card_no
              ,co.date_of_birth
              ,co.nationality
        FROM batch b
        LEFT JOIN course_contact cc ON (cc.batch_id = b.batch_id)
        LEFT JOIN course c ON (c.course_id = b.course_id)
        LEFT JOIN `order` o ON (o.order_id = cc.order_id)
        LEFT JOIN contact co ON (co.contact_id = cc.contact_id)
        WHERE b.batch_id = {$batch_id}
        ";
        $result   = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);
        while ($row = $db->sql_fetchrow($result)) {
            $text .= "008-012-00046;";
            $text .= "{$row['course_code']};";
            $text .= "Language Code;";
            $text .= "{$row['order_date']};";
            $text .= "{$row['start_date']};";
            $text .= "{$row['end_date']};";
            $text .= "{$row['first_name']};";
            $text .= "WP No;";
            $text .= "{$row['id_card_no']};";
            $text .= "{$row['date_of_birth']};";
            $text .= "{$row['nationality']};";
            $text .= ";";
            $text .=   "\n";
        }

        //footer code comes here.
        $text .= "</Score>";

        return $text;
    }

    /**
     *
     */
    function getStudentGrade($batch_id, $contact_id){
        $db = Zend_Registry::get('db');

        $sqlSg = "
        SELECT student_grade_id
        FROM student_grade
        WHERE batch_id = {$batch_id}
        ";
        $resultSg  = $db->sql_query($sqlSg);
        $numRowsSg = $db->sql_numrows($resultSg);

        $sql = "
        SELECT student_grade_id
              ,marks
              ,grade
              ,exam_type
              ,exam_date
        FROM student_grade
        WHERE batch_id = {$batch_id}
          AND student_result = 'Pass'
          AND contact_id = {$contact_id}
        ";
        $result  = $db->sql_query($sql);
        $numRows = $db->sql_numrows($result);
        
        if ($numRowsSg > 0 && $numRows > 0) {
            $rowSg = $db->sql_fetchrow($result);
            $text = $rowSg;
        } else {
            $text = 'Not Passed';
        }

        return $text;
    }
}
