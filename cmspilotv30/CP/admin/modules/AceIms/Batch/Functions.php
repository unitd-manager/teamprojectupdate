<?
class CP_Admin_Modules_AceIms_Batch_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('aceIms_batch');
        $modules->registerModule($modObj, array(
            'title'         => 'Batch'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('aceIms_batch', 'picture', 'image');
        $mediaObj = $mediaArr->getMediaObj('aceIms_batch', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
 
   /**
    *
    */
    function setLinksArray($inst) {
        $cpCfg = Zend_Registry::get('cpCfg');

        $linkObj = $inst->getLinksArrayObj('aceIms_batch', 'aceIms_contactLink');        
        $inst->registerLinksArray($linkObj, array(
              'historyTableName'          => 'batch_history'
             ,'linkingType'               => 'modal'
             ,'historyTableKeyField'      => 'batch_history_id'
             ,'showLinkPanelInEdit'       => 1
             ,'hasPortalEdit'             => 0
             ,'hasPortalDelete'           => 0
             ,'hasModalChoose'            => true
             ,'fieldlabel'                => array('Name', 'Email')
             ,'showAnchorInLinkPortal'    => false
             ,'additionalFieldsArray' => array(
                 'c.first_name'
                 ,'c.last_name'
                 ,'c.email'
             )
             ,'hasGridEdit'               => 0
        ));
	   //------------------------------------------------------------------------------//

        $linkObj = $inst->getLinksArrayObj('aceIms_batch', 'aceIms_teacherLink');        
        $inst->registerLinksArray($linkObj, array(
            'recordTypeForHistory'  => 'Trainer'
           ,'historyTableName'      => 'batch_teacher'
           ,'historyTableKeyField'  => 'batch_teacher_id'
           ,'fieldlabel'            => array('Name', 'Email')
           ,'linkingType'           => 'modal'
           ,'showLinkPanelInEdit'   => 1
        ));

	   //------------------------------------------------------------------------------//

       $linkObj = $inst->getLinksArrayObj('aceIms_batch', 'aceIms_assessorLink');
       $inst->registerLinksArray($linkObj, array(
             'recordTypeForHistory'      => 'Assessor'
            ,'historyTableName'          => 'batch_teacher'
            ,'linkingType'               => 'modal'
            ,'historyTableKeyField'      => 'batch_teacher_id'
            ,'showLinkPanelInEdit'       => 1
            ,'hasPortalEdit'             => 0
            ,'hasPortalDelete'           => 0
            ,'hasModalChoose'            => true
            ,'fieldlabel'                => array('Name', 'Email')
            ,'showAnchorInLinkPortal'    => false
            ,'hasGridEdit'               => 0
       ));

        //------------------------------------------------------------------------------//
        
        $linkObj = $inst->getLinksArrayObj('aceIms_batch', 'aceIms_attendance');               
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
        
        $linkObj = $inst->getLinksArrayObj('aceIms_batch', 'aceIms_feedback');                
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
    function getAceImsBatchAceImsContactLinkAddLinkCallback($course_contact_id, $row) {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');
        
        $batch_id  = $row['batch_id'];
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
            $fa['module']          = 'aceIms_course';
            $fa['order_status']    = 'Due';
            $fa['order_date']      =  date('Y-m-d');
            $fa['contact_module']  = 'aceIms_contact';
            $order_id = $fn->addRecord($fa, 'order');

            $fa = array();
            $fa['order_id']   = $order_id;
            $fa['module']     = 'aceIms_course';
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
    function getAceImsBatchAceImsContactLinkDeleteLinkCallback($batch_id, $contact_id) {
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
    function getAceImsBatchAceImsContactLinkAddAllLinkCallback($batch_id, $newly_created_contact_ids) {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        $contactIds = explode(',', $newly_created_contact_ids);
        foreach($contactIds AS $contact_id){
        }
    }
    
    /**
     *
     */
    function getAceImsBatchAceImsContactLinkRemoveAllLinkCallback($batch_id, $removed_contact_ids) {
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
        $file_name = 'Attendance_' . $batch_id . '_' . $rnd_no . '.xlsx';
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
              ,CONCAT_WS(' ', cont.first_name, cont.last_name ) AS student_name
              ,(SELECT t.first_name FROM teacher t
                LEFT JOIN batch_teacher bt ON (t.teacher_id = bt.teacher_id)
                WHERE bt.batch_id = {$batch_id}
                  AND t.trainer_type = 'Trainer'
                  AND bt.record_type = 'Trainer'
                LIMIT 0,1
               ) AS teacher_name
              ,cont.phone
              ,cont.registration_no
              ,bh.batch_history_id
        FROM batch b
        LEFT JOIN course c ON (c.course_id = b.course_id)
        LEFT JOIN batch_history bh ON (bh.batch_id = b.batch_id)
        LEFT JOIN contact cont ON (bh.contact_id = cont.contact_id)
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
}