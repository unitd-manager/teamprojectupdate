<?
class CP_Www_Widgets_Edukite_CalendarDisplay_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $currentYear = date("Y");

        $SQL = "
        SELECT c.title AS course_title
              ,b.title AS batch_title
              ,b.batch_id
              ,b.venue
              ,b.start_date
              ,b.end_date
              ,b.start_time
              ,b.end_time
              ,t.first_name as trainer_name
	    	  ,(SELECT COUNT(*)
	    		FROM course_contact cc
	    		WHERE cc.batch_id = b.batch_id
                AND b.status = 'Current'
	    		) AS attendee_count
        FROM batch b
        JOIN course c ON (c.course_id = b.course_id)
        JOIN teacher t ON (t.teacher_id = b.teacher_id)
        ";

        $SQL = "
        SELECT n.*
              ,CONCAT_WS(' ', t.first_name, t.last_name) AS teacher_name
        FROM notice n
        LEFT JOIN (teacher t) ON (t.teacher_id = n.teacher_id)
        LEFT JOIN (notice_student ns) ON (ns.notice_id = n.notice_id)
        WHERE n.template = 'Daily Diary'
          AND n.status = 'Active'
          AND n.launch_now = 1
          AND ns.student_id = 3715
          GROUP BY ns.notice_id
          ORDER BY n.notice_id DESC
        ";

        return $SQL;
    }
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');

        /*
        $searchVar->sqlSearchVar[] = "b.status = 'Current'";

        if ($start_date != ''){
            $searchVar->sqlSearchVar[] = "b.start_date >= '{$start_date}'";
        }
        if ($end_date != ''){
            $searchVar->sqlSearchVar[] = "b.end_date <= '{$end_date}'";
        }

        $searchVar->sortOrder = 'course_title';
        */
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'edukite_calendarDisplay');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }
    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getEventDetails(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $teacherKite     = $fn->getReqParam('teacherKite');
        $archive = $_SESSION['student_status'];
        $student_status = '';

        $jsonArray = array();
        $currentYear = date("Y");
        //this condition is to check student id , in case if the teacher is viewing the student kite calendar.
        $student_id_from_controller = $fn->getSessionParam('cpTempContactId');
        $student_id = ($tv['sitePfxId'] != '') ? $tv['sitePfxId'] :  $_SESSION['cpContactId'];
        $student_id = ($student_id_from_controller  != '') ? $student_id_from_controller  :  $_SESSION['cpContactId'];
        $teacherKiteId = $fn->getSessionParam('teacherKiteId');
         //$student_id = 3694;
        $tv['siteType'] == 'kite';
        // For Parent Login we need to get the student id , below code is for the same
        if($_SESSION['cpLoginTypeWWW'] == 'edukite_parent'){
            $histRec    = $fn->getRecordRowByID('student_parent', 'parent_id', $_SESSION['cpContactId']);

            $sqlParentStudent = "
            SELECT s.student_id
                  ,CONCAT_WS(' ', s.first_name, s.last_name ) AS student_name
            FROM student_parent sp
            LEFT JOIN (student s) ON (s.student_id = sp.student_id)
            WHERE sp.parent_id = {$_SESSION['cpContactId']}
            ORDER BY s.status
            ";
            $result      = $db->sql_query($sqlParentStudent);
            $numRows = $db->sql_numrows($result);
            $row = $db->sql_fetchrow($result);

            $session_student_id = $_SESSION['student_id'];
            if($session_student_id != ''){
                $studRec    = $fn->getRecordRowByID('student', 'student_id', $_SESSION['student_id'] );
            } else {
                $studRec    = $fn->getRecordRowByID('student', 'student_id', $row['student_id'] );
            }
            $student_id = $studRec['student_id'] ;
            $student_status = $studRec['status'] ;
        }

        if($teacherKiteId == 1){
            $kite_id = "AND ns.teacher_id = {$student_id}";
        } else {
            $kite_id = "AND ns.student_id = {$student_id}";
        }

        $SQLNoticeCurYear = "
        SELECT s.student_id
              ,n.notice_id
        FROM student s
        LEFT JOIN (notice_student ns) ON (ns.student_id = s.student_id)
        LEFT JOIN (notice n) ON (n.notice_id = ns.notice_id)
        WHERE s.status = 'Archive'
          AND n.launch_now = 1
          AND s.student_id = {$student_id}
          AND n.academic_year = '{$currentYear}'
          ORDER BY n.notice_id DESC
        ";
        $resultNoticeCurYear  = $db->sql_query($SQLNoticeCurYear);
        $numRowsNoticeCurYear = $db->sql_numrows($resultNoticeCurYear);

        if(($student_status == 'Archive' && $numRowsNoticeCurYear > 0) || ($archive == 'Archive' && $numRowsNoticeCurYear > 0)){
            $status = "AND n.status IN ('Active', 'Archive')";
        }else if($student_status == 'Archive' || $archive == 'Archive'){
            $status = "AND n.status = 'Archive'";
        } else {
            $status = "AND n.status = 'Active'";
        }

        $SQL = "
        SELECT n.*
              ,CONCAT_WS(' ', t.first_name, t.last_name) AS teacher_name
        FROM notice n
        LEFT JOIN (teacher t) ON (t.teacher_id = n.teacher_id)
        LEFT JOIN (notice_student ns) ON (ns.notice_id = n.notice_id)
        WHERE n.template = 'Daily Diary'
          {$status}
          AND n.launch_now = 1
          {$kite_id}
          GROUP BY ns.notice_id
          ORDER BY n.notice_id DESC
        ";
        $result  = $db->sql_query($SQL);
        $activity_date = '';

        while ($row = $db->sql_fetchrow($result)) {
            $title = ' ';
            $eventStartdate = $row['activity_date'];
            $eventEnddate   = $row['activity_date'] ;
            //$eventdate = '2012-05-01T13:15:30';
            //$eventStartdate = '2013 11 20';
            //$eventEnddate = '2013 11 20';
            //$Link = 'index.php?widget=edukite_calendarDisplay&_spAction=noticeDetails&showHTML=0&launch_date=' . $row['launch_date'] . '&student_id=' . $student_id;
            $Link = "/index.php?module=webBasic_home&_spAction=dailyDairy&showHTML=0&activity_date={$row['activity_date']}&student_id={$student_id}&status={$archive}&siteType=kite";
            $buildjson = array(
             'title'  => $title
            ,'start'  => $eventStartdate
            ,'end'    => $eventEnddate
            ,'allDay' => false
            ,'url'    => $Link
            );

            // Adds each array into the container array
            if($row['activity_date'] != $activity_date){
                array_push($jsonArray, $buildjson);
            }

                $activity_date = $row['activity_date'];
        }

        echo json_encode($jsonArray);
    }
}