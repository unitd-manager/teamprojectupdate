<?
class CP_Admin_Widgets_Pms_CalendarDisplay_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($cpCfg['w.pms.calendarDisplay.hasPvt']) {
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
		    		FROM batch_history bh
		    		WHERE bh.batch_id = b.batch_id
                    AND b.status = 'Open'
		    		) AS attendee_count
            FROM batch b
            LEFT JOIN course c ON (c.course_id = b.course_id)
            LEFT JOIN teacher t ON (t.teacher_id = b.teacher_id)
            ";
        } else {
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
        }

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
        $searchVar->mainTableAlias = 'b';

        $searchVar->sqlSearchVar[] = "b.status = 'Current'";

        if ($start_date != ''){
            $searchVar->sqlSearchVar[] = "b.start_date >= '{$start_date}'";
        }
        if ($end_date != ''){
            $searchVar->sqlSearchVar[] = "b.end_date <= '{$end_date}'";
        }

        $searchVar->sortOrder = 'course_title';
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'pms_calendarDisplay');

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

        $jsonArray = array();
        
        $SQL = $this->getSQL();
        $result  = $db->sql_query($SQL);  
        
        while ($row = $db->sql_fetchrow($result)) {
             $title = $row['course_title'] .' - ' . $row['attendee_count'];
             $eventStartdate = $row['start_date'] .' ' . $row['start_time'];  
             $eventEnddate = $row['end_date'] .' ' . $row['end_time'];  
             //$eventdate = '2012-05-01T13:15:30'; 
             //$eventStartdate = '2012 05 01 09:30:00'; 
             //$eventEnddate = '2012 05 22 17:00:00'; 
             $batchLink = 'index.php?widget=pms_calendarDisplay&_spAction=batchDetails&showHTML=0&batch_id='. $row['batch_id'];
             // Stores each database record to an array
             $buildjson = array(
              'title'  => $title
             ,'start'  => $eventStartdate
             ,'end'    => $eventEnddate
             ,'allDay' => false
             ,'url'    => $batchLink
             );

             // Adds each array into the container array
             array_push($jsonArray, $buildjson);
        }
        
        echo json_encode($jsonArray);
    }
    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getBatchDetails(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $text = '';    
        
        $batch_id = $fn->getReqParam('batch_id');
        $batchLink = 'index.php?_topRm=main&module=pms_batch&_action=edit&batch_id=' . $batch_id;

        $wTraineeByBatch = getCPWidgetObj('pms_traineeByBatch');
        $text = $wTraineeByBatch->getWidget(array('searcVarCondn'=>"batch_id={$batch_id}"
        ));

        $text .= "
        <div class='mt10'>
        <a href='{$batchLink}' target='_blank'><u>Click to Goto Batch</u></a>
        </div>
        ";
        
        return $text;
    }
}