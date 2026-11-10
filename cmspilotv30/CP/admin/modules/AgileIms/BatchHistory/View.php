<?
class CP_Admin_Modules_AgileIms_BatchHistory_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getListDataCell($row['course_title'])}
            {$listObj->getListDataCell($row['batch_title'])}
            {$listObj->getListDataCell($row['teacher_name'])}
            {$listObj->getListYesNo($row['evaluate_status'])}
            {$listObj->getListDataCell($row['certificate_status'])}
            <td><div align='left'><a href='mailto:{$row['email']}'>{$row['email']}</a></div></td>
            {$listObj->getListDataCell($row['phone'])}
            {$listObj->getListDataCell($row['course_contact_id'], 'center')}
            {$listObj->getListRowEnd($row['course_contact_id'])}
            ";
            $rowCounter++ ;
        }
        
        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Full Name', 'ct.first_name')}
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
        
        $sqlCourse  = $fn->getDDSql('agileIms_course');
        $expCourse  = array('detailValue' => $row['course_title']);
        $expVl = array('sqlType' => 'OneField');
        $exp = array('isEditable' => 0);
        
        //$exp = array('titleFld' => "CONCAT_WS(' ', first_name, last_name )");
        $sqlTeacher  = $fn->getDDSql('agileIms_teacher', $exp);
        $expTeach  = array('detailValue' => $row['teacher_name']);
        $sqlCertStatus = $fn->getValueListSQL('certificateStatus');
        
        $fielset1 = "
        {$formObj->getTBRow('Full Name', 'first_name', $row['first_name'], $exp)}
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
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        
        $ccRec = $fn->getRecordRowById('course_contact', 'course_contact_id', $row['course_contact_id']);

        $modObj = getCPModuleObj('agileIms_batch');
        $student_result = $modObj->model->getStudentGrade($ccRec['batch_id'], $ccRec['contact_id']);

        $urlPrintCertificateDoc = "index.php?module=agileIms_batchHistory&_spAction=printCertificateDoc&course_contact_id={$row['course_contact_id']}&showHTML=0";

        
        if ($student_result == 'Not Passed') {
            $print_cert = "
            ";
        } else {
            $print_cert = "
            <div class='floatbox actionBtnsDetail'>
                <div class='float_right button mb5'>
                    <a href='{$urlPrintCertificateDoc}'>Print Certificate</a>
                </div> 
                <div class='float_right mb5'>
                    <h3>PRINT</h3>
                </div> 
            </div>
            ";
        }

        $text = "
        {$print_cert}
        {$media->getRightPanelMediaDisplay('Attach Certificate', 'agileIms_batchHistory', 'attachment', $row)}
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

    /**
     *This Function is used to print the certificate Document for Batch History.
     *
     */
    function getPrintCertificateDoc() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');
        $dateUtil = Zend_Registry::get('dateUtil');

        //-----------------------------------------------------------------//
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/tbs_class.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_opentbs.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_html.php');

        $TBS = new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);

        $course_contact_id = $fn->getReqParam('course_contact_id');
        $template    = 'Print_Certificate.docx';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $TBS->LoadTemplate($templatePath);
        $rnd_no = mt_rand();
        $file_name = 'Print_Certificate' . $course_contact_id . '_' . $rnd_no . '.docx';
        $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);

        $path = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        $file_name_save = $path . '\\' . $file_name;
        $sourceFilePath = $file_name_save;
        $today =  date('d/m/Y');


        $SQL = "
        SELECT DISTINCT cc.course_contact_id
              ,c.title AS course_title
              ,cc.contact_id
              ,ct.first_name 
              ,ct.id_card_no
        FROM course_contact cc 
        JOIN course c ON (c.course_id = cc.course_id)
        JOIN contact ct ON (ct.contact_id = cc.contact_id)
        LEFT JOIN batch b ON (b.batch_id = cc.batch_id)
        LEFT JOIN teacher t ON (t.teacher_id = b.teacher_id)
        WHERE cc.course_contact_id = '{$course_contact_id}'
        ORDER BY cc.course_contact_id
        ";

        $result  = $db->sql_query($SQL);
        $row     = $db->sql_fetchrow($result);
        $blkMain     = array();

        $arr = array();
        $arr['first_name']   = strtoupper($row['first_name']);
        $arr['id_card_no']   = $row['id_card_no'];
        $arr['course_title'] = strtoupper($row['course_title']);
        $arr['date']                 = $today;

        $blkMain[] = $arr;

        $TBS->MergeBlock('blkMain', $blkMain);
        $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
    }

}