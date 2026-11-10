<?
class CP_Admin_Modules_Edukloud_Batch_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('edukloud_batch');
        $modules->registerModule($modObj, array(
            'title'         => 'Batch'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('edukloud_batch', 'picture', 'image');
        $mediaObj = $mediaArr->getMediaObj('edukloud_batch', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
 
   /**
    *
    */
    function setLinksArray($inst) {
        $cpCfg = Zend_Registry::get('cpCfg');

        $linkObj = $inst->getLinksArrayObj('edukloud_batch', 'edukloud_contactLink');        
        $inst->registerLinksArray($linkObj, array(
              'historyTableName'          => 'course_contact'
             ,'linkingType'               => 'modal'
             ,'historyTableKeyField'      => 'course_contact_id'
             ,'portalSearchFunction'      => 1
             ,'showLinkPanelInEdit'       => 1
             ,'hasPortalEdit'             => 0
             ,'hasPortalDelete'           => 0
             ,'hasModalChoose'            => false
             ,'fieldlabel'                => array('First Name', 'Last Name', 'Email', 'Evalaute Status', 'Print Certificate')
             ,'showAnchorInLinkPortal'    => false
             ,'additionalFieldsArray' => array(
                 'c.first_name'
                 ,'c.last_name'
                 ,'c.email'
                 ,'cc.evalauate_status'
             )
             ,'hasGridEdit'               => 0
        ));
	   //------------------------------------------------------------------------------//

        $linkObj = $inst->getLinksArrayObj('edukloud_batch', 'edukloud_teacherLink');        
        $inst->registerLinksArray($linkObj, array(
            'recordTypeForHistory'  => 'Trainer'
           ,'historyTableName'      => 'batch_teacher'
           ,'historyTableKeyField'  => 'batch_teacher_id'
           ,'fieldlabel'            => array('First Name', 'Last Name', 'Email')
           ,'linkingType'           => 'modal'
           ,'showLinkPanelInEdit'   => 1
        ));

	   //------------------------------------------------------------------------------//

       $linkObj = $inst->getLinksArrayObj('edukloud_batch', 'edukloud_assessorLink');
       $inst->registerLinksArray($linkObj, array(
             'recordTypeForHistory'      => 'Assessor'
            ,'historyTableName'          => 'batch_teacher'
            ,'linkingType'               => 'modal'
            ,'historyTableKeyField'      => 'batch_teacher_id'
            ,'showLinkPanelInEdit'       => 1
            ,'hasPortalEdit'             => 0
            ,'hasPortalDelete'           => 0
            ,'hasModalChoose'            => true
            ,'fieldlabel'                => array('First Name', 'Last Name', 'Email')
            ,'showAnchorInLinkPortal'    => false
            ,'hasGridEdit'               => 0
       ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('edukloud_batch', 'edukloud_attendance');               
        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'attendance'
           ,'displayTitleFieldName' => "a.date"
           ,'historyTableKeyField'  => 'attendance_id'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'linkingType'           => 'portal'
           ,'hasPortalEdit'         => 1
           ,'hasPortalDelete'       => 1
           ,'portalDialogWidth'     => 650
           ,'portalDialogHeight'    => 350
           ,'fieldlabel'            => array('Date')
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('edukloud_batch', 'edukloud_feedback');        
        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'student_feedback'
           ,'displayTitleFieldName' => "a.title"
           ,'historyTableKeyField'  => 'student_feedback_id'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'linkingType'           => 'portal'
           ,'hasPortalEdit'         => 1
           ,'hasPortalDelete'       => 1
           ,'portalDialogWidth'     => 650
           ,'portalDialogHeight'    => 350
           ,'fieldlabel'            => array('Title')
        ));
   }    
   
   /**
    *
    */
    function getEdukloudBatchEdukloudContactLinkAddLinkCallback($course_contact_id, $row) {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');
        
        $batch_id = $row['batch_id'];
        $batchRec = $fn->getRecordRowByID('batch', 'batch_id', $batch_id);

        $fa = array();
        $fa['course_id'] = $batchRec['course_id'];

        $whereCondition = "
        WHERE course_contact_id = {$course_contact_id}
        ";

        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'course_contact', $whereCondition);
        $db->sql_query($SQL);

        // To create order, order_item record after linking the trainee to the batch.
        if ($row['order_id'] == ''){
            $courseRec = $fn->getRecordRowByID('course', 'course_id', $batchRec['course_id']);

            $fa = array();
            $fa['contact_id']      = $row['contact_id'];
            $fa['payment_method']  = '';
            $fa['module']          = 'edukloud_course';
            $fa['order_status']    = 'Due';
            $fa['order_date']      =  date('Y-m-d');
            $fa['contact_module']  = 'edukloud_contact';
            $order_id = $fn->addRecord($fa, 'order');

            $fa = array();
            $fa['order_id']   = $order_id;
            $fa['module']     = 'edukloud_course';
            $fa['record_id']  = $batchRec['course_id'];
            $fa['qty']        = 1;
            $fa['item_title'] = $courseRec['title'];
            $fa['unit_price'] = $courseRec['price'];
            $fn->addRecord($fa, 'order_item');

            //Updating order id in course_contact table.
            $fa = array();
            $fa['order_id'] = $order_id;

            $whereCondition = "
            WHERE course_contact_id = {$course_contact_id}
            ";

            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'course_contact', $whereCondition);
            $db->sql_query($SQL);
        }
    }
   /**
    *
    */
    function getEdukloudBatchEdukloudContactLinkDeleteLinkCallback($batch_id, $contact_id) {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        //To cancel the order for the respective contact
        $expBatch = array('condn' => " AND contact_id = {$contact_id}");
        $courseContactRec = $fn->getRecordRowByID('course_contact', 'batch_id', $batch_id, $expBatch);

        $fa = array();
        $fa['order_status'] = 'Cancelled';

        $whereCondition = "
        WHERE order_id = {$courseContactRec['order_id']}
        ";
        
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'order', $whereCondition);
        $db->sql_query($SQL);
    }
   /**
    *
    */
    function getEdukloudBatchEdukloudContactLinkAddAllLinkCallback($batch_id, $newly_created_contact_ids) {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        $contactIds = explode(',', $newly_created_contact_ids);
        foreach($contactIds AS $contact_id){
        }
    }
   /**
    *
    */
    function getEdukloudBatchEdukloudContactLinkRemoveAllLinkCallback($batch_id, $removed_contact_ids) {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        $contactIds = explode(',', $removed_contact_ids);
        foreach($contactIds AS $contact_id){
            print $contact_id . '<br>';
        }
    }    

    /**
     *
     */
        function getPrintAttendance() {
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
        $template = 'Attendance1.docx';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $TBS->LoadTemplate($templatePath);
        $rnd_no = mt_rand();
        $file_name = 'Attendance_' . $batch_id . '_' . $rnd_no . '.docx';
        $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);
        
        $path = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        $file_name_save = $path . '\\' . $file_name;
        $sourceFilePath = $file_name_save;
        $today =  date('d/m/Y');
        
        $SQL = "
        SELECT b.*
              ,c.title AS course_title
              ,c.course_code
              ,CONCAT_WS(' ', cont.first_name, cont.last_name ) AS student_name
              ,CONCAT_WS(' ', t.first_name, t.last_name ) AS teacher_name
              ,cont.phone
              ,cont.registration_no
        FROM batch b
        LEFT JOIN course c ON (c.course_id = b.course_id)
        LEFT JOIN course_contact cc ON (cc.batch_id = b.batch_id)
        LEFT JOIN contact cont ON (cc.contact_id = cont.contact_id)
        LEFT JOIN teacher t ON (b.teacher_id = t.teacher_id)
        WHERE b.batch_id = {$batch_id}
        ORDER BY cont.registration_no
        ";
        $result = $db->sql_query($SQL);
    
        $serialNo    = 1;
        $arr         = array();
        $blkMain     = array();       
        $blkStd      = array();
        $blkPhone    = array();
        $blkRegNo    = array();
        $blkSerialNo = array();
        
        while ($row = $db->sql_fetchrow($result)) {
            $arr1 = array('student_name' => $row['student_name']);
            $blkStd[] = $arr1;
    
            $arr2 = array('registration_no' => $row['registration_no']);
            $blkRegNo[] = $arr2;
    
            $arr3 = array('serial_no' => $serialNo);
            $blkSerialNo[] = $arr3;
    
            $arr4 = array('phone' => $row['phone']);
            $blkPhone[] = $arr4;
    
            $arr['course_code']  = $row['course_code'];
            $arr['teacher_name'] = $row['teacher_name'];
            $arr['start_date']   = $row['start_date'];
            $arr['end_date']     = $row['end_date'];
            $arr['course_title'] = $row['course_title'];
            $blkMain[] = $arr;
            
            $serialNo++;
        }
    
        $TBS->MergeBlock('blkMain', $blkMain);         
        $TBS->MergeBlock('blkStd', $blkStd);         
        $TBS->MergeBlock('blkRegNo', $blkRegNo);         
        $TBS->MergeBlock('blkSerialNo', $blkSerialNo);         
        $TBS->MergeBlock('blkPhone', $blkPhone);         
        $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
        
    }

    /**
     *
     */
        function getPrintAttendanceExcell() {
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
        $template = 'Mass-Attendance.xlsx';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $TBS->LoadTemplate($templatePath);
        $rnd_no = mt_rand();
        $file_name = 'Mass-Attendance_' . $batch_id . '_' . $rnd_no . '.xlsx';
        $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);
        
        $path = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        $file_name_save = $path . '\\' . $file_name;
        $sourceFilePath = $file_name_save;
        $today =  date('d/m/Y');
        
        $SQL = "
        SELECT b.*
              ,c.title AS course_title
              ,s.title AS subject_title
              ,c.course_code
              ,CONCAT_WS(' ', cont.first_name, cont.last_name ) AS student_name
              ,CONCAT_WS(' ', t.first_name, t.last_name ) AS teacher_name
              ,cont.phone
              ,cont.registration_no
              ,bh.batch_history_id
        FROM batch b
        LEFT JOIN course c ON (c.course_id = b.course_id)
        LEFT JOIN batch_history bh ON (bh.batch_id = b.batch_id)
        LEFT JOIN contact cont ON (bh.contact_id = cont.contact_id)
        LEFT JOIN batch_teacher bt ON (b.batch_id = bt.batch_id)
        LEFT JOIN teacher t ON (t.teacher_id = bt.teacher_id)
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

            $arr2 = array('registration_no' => $row['registration_no']);
            $blkRegNo[] = $arr2;

            $arr3 = array('serial_no' => $serialNo);
            $blkSerialNo[] = $arr3;

            $arr['course_code']   = $row['course_code'];
            $arr['teacher_name']  = $row['teacher_name'];
            $arr['start_time']    = $row['start_time'];
            $arr['end_time']      = $row['end_time'];
            $arr['course_title']  = $row['course_title'];
            $arr['subject_title'] = $row['subject_title'];
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
    function getEdukloudBatchEdukloudContactLinkPortalSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $current_year = date('Y');
        $sqlYear = "SELECT DISTINCT cc.year_of_enrollment FROM course_contact cc";

        $text = "
        <select name='enrollment_year' class='float_right m5'>
            <option value=''>Enrollment Year</option>
            {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $current_year)}
        </select>
        ";

        return $text;
    }
}