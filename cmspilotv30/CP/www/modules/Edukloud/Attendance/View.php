<?
class CP_Www_Modules_Edukloud_Attendance_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray) {
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        CP_Common_Lib_Registry::arrayMerge('jssKeys', array('jqForm-3.15'));

        $class_id = $fn->getReqParam('class_id');
        $date = $fn->getReqParam('date');
        $today = date('Y-m-d');
        $numRows = $db->sql_numrows($dataArray);

        if ($class_id == '') {
            $class_id = $this->getFirstClass();
        }
        
        if ($date == '') {
            $date = date('Y-m-d');
        }

        $attBtn = '';
        if ($_SESSION['cpLoginTypeWWW'] == 'edukloud_staff' && $date == $today) {
            $takeAttUrl = "/index.php?module=edukloud_attendance&_spAction=takeAttendance&class_id={$class_id}&date={$date}&showHTML=0";
            $attBtn = "
            <div class='floatbox'>
                <div class='retakeAttendanceBtnWrap'>
                    <a class='squarebutton' href='{$takeAttUrl}' id='takeAttendance'><span>{$ln->gd('retakeAttendance')}</span></a>
                </div>
            </div>    
            ";
        }

        $content = '';
        if ($numRows == 0){
            if ($_SESSION['cpLoginTypeWWW'] == 'edukloud_staff' && $date == $today && $tv['searchDone'] == 0) {
                $content = "
                <div class='floatbox'>
                    <div class='takeAttendanceBtnWrap button'>
                        <a dialogTitle='Take Attendance' href='javascript:void(0);' link='{$takeAttUrl}' 
                            id='takeAttendance' class_id='{$class_id}' date='{$date}'><span>{$ln->gd('takeAttendance')}</span>
                        </a>
                    </div>
                </div>
                ";
            } else {
                $content = "
                <strong><p class='txtCenter'>{$ln->gd('noRecords')}</p></strong>
                ";
            }    
        } else {
            $content = " 
    		<div id='studentAttendanceContainer'>
                {$this->getStudentList($result)}
    		</div>  
            {$attBtn}  
            ";
        }
            
        $text = "
        {$content}
        ";

        $fn->addLangKey(array('classDateError'));
        $fn->addLangKey(array('pleaseTagStudents'));
        
        return $text;
    }

    /**
     *
     */
    function getTakeAttendance() {
        checkLoggedIn();
        $fn = Zend_Registry::get('fn');

        $text = '';

        $class_id = $fn->getReqParam('class_id', '', true);
        $date     = $fn->getReqParam('date', '', true);

        if ($class_id == ''){
            return;
        }

        $this->getCreateAttendanceHistory($class_id, $date);

        $text = "
        {$this->getStudentLinkDialog($class_id, $date)}
        ";

        return $text;
    }

    /**
     *
     */
    function getCreateAttendanceHistory($class_id = '', $date= '') {
        checkLoggedIn();
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $staff_id  = $fn->getSessionParam('cpContactId', '', true);
        
        $date = $fn->getReqParam('date' , '', true);

        if ($class_id == ''){
            $class_id = $fn->getReqParam('class_id', '', true);
        }
                
        if ($_SESSION['cpLoginTypeWWW'] == 'Admin') {            
            $SQL = "
            SELECT staff_id
            FROM staff
            ";
            $result  = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);
            
            while ($row = $db->sql_fetchrow($result)) {
                $SQL2 = "
                SELECT staff_id
                FROM staff_attendance
                WHERE staff_id = {$row['staff_id']}
                  AND record_date = '{$date}'
                ";
                $result2  = $db->sql_query($SQL2);
                $numRows2 = $db->sql_numrows($result2);
                
                if ($numRows2 == 0){
                    $fa = array();
                    $fa['staff_id']          = $row['staff_id'];
                    $fa['record_date']       = $date;
                    $fa['user_id']           = $fn->getSessionParam('cpContactId');
                    $fa['status']            = '';
                    $fa['creation_date']     = date('Y-m-d H:i:s');
                    $fa['modification_date'] = date('Y-m-d H:i:s');
                
                    $SQL = $dbUtilCommon->getInsertSQLStringFromArray($fa, 'staff_attendance');
                    $db->sql_query($SQL);
                }
            } 
        } else {
            $SQL = "
            SELECT student_id
                  ,class_id
            FROM student
            WHERE class_id = {$class_id}
            ";
            $result  = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);
            
            while ($row = $db->sql_fetchrow($result)) {
                $SQL2 = "
                SELECT student_id
                FROM student_attendance
                WHERE class_id   = {$class_id}
                  AND student_id = {$row['student_id']}
                  AND record_date = '{$date}'
                ";
                $result2  = $db->sql_query($SQL2);
                $numRows2 = $db->sql_numrows($result2);
                
                if ($numRows2 == 0){
                    $fa = array();
                    $fa['student_id']        = $row['student_id'];
                    $fa['class_id']          = $row['class_id'];
                    $fa['record_date']       = $date;
                    $fa['staff_id']          = $fn->getSessionParam('cpContactId');
                    $fa['status']            = '';
                    $fa['creation_date']     = date('Y-m-d H:i:s');
                    $fa['modification_date'] = date('Y-m-d H:i:s');
                
                    $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'student_attendance');
                    $db->sql_query($SQL);
                }
            } 
        }
    }

    /**
     *
     */
    function getStudentLinkDialog($class_id = '', $date = '') {
        checkLoggedIn();
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $ln = Zend_Registry::get('ln');

        $staff_id  = $fn->getSessionParam('cpContactId', '', true);
        
        if ($class_id == ''){
            $class_id = $fn->getReqParam('class_id', '', true);
            $date     = $fn->getReqParam('date' , '', true);
        }

        if ($class_id == ''){
            return;
        }

        $rows = '';

        $SQL = "
        SELECT sa.*, CONCAT_WS(' ', s.first_name, s.last_name ) AS student_name
              ,c.title AS class_title
              ,s.student_id
        FROM student_attendance sa
        JOIN student s ON (s.student_id = sa.student_id)
        JOIN class c ON (s.class_id = c.class_id)
        WHERE sa.class_id = {$class_id}
          AND sa.record_date = '{$date}'
        ORDER BY student_name
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $radArr = array('Present', 'Absent');
            
            $fld_name = 'status__' . $row['student_attendance_id'];
            
            $rows .= "
            <tr id='{$row['student_attendance_id']}'>
                <td>{$row['student_name']}</td>
                <td>{$row['class_title']}</td>
                <td>
                    {$cpUtil->getRadioFromArray($radArr, $fld_name, $row['status'], array('fieldLabelCls' => 'float_left'))}
                </td>
                <td>
                    <textarea name='comment' value='{$row['comment']}'>{$row['comment']}</textarea>
                </td>
            </tr>
            ";
        }

        $bulkUpdateUrl = "/index.php?_room=attendance&_spAction=updateAttendanceStatusBulk&showHTML=0&status=";
        $formAction = "/index.php?module=edukloud_attendance&_spAction=takeAttendanceSubmit&showHTML=0";

        $text ="
        <form id='portalForm' class='yform columnar cpJqForm' method='post' action='{$formAction}'>
        <div class='floatbox'>
            <div class='float_right ml5 button'>
                <a class='' href='javascript:void(0);' link='{$bulkUpdateUrl}Absent' value='Absent' id='allAbsent'><span>{$ln->gd('allAbsent')}</span></a>
            </div>
            <div class='float_right button'>
                <a class='' href='javascript:void(0);' link='{$bulkUpdateUrl}Present' value='Present' id='allPresent'><span>{$ln->gd('allPresent')}</span></a>
            </div>
        </div>
        <table class='linkStudent list' id='editAttendanceList'>
            <tr>
                <th>{$ln->gd('studentName')}</th>
                <th>{$ln->gd('class')}</th>
                <th>{$ln->gd('status')}</th>
                <th>{$ln->gd('comment')}</th>
            </tr>
            {$rows}
        </table>
        </form>
        ";

        return $text;

    }

    /**
     *
     */
    function getFirstClass(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        
        $staff_id = $fn->getSessionParam('cpContactId');
        $SQL = "
        SELECT c.class_id
        FROM class c
        WHERE c.class_id 
        IN (
            SELECT sc.class_id 
            FROM staff_class sc 
            WHERE sc.staff_id = {$staff_id}
        )
        ORDER BY c.class_id ASC
        LIMIT 0, 1
        ";
        
        $result = $db->sql_query($SQL);  
        $numRows = $db->sql_numrows($result);
        $row = $db->sql_fetchrow($result);
        
        if ($numRows > 0) {
            return $row['class_id'];            
        }    
    }

    /**
     *
     */
    function getStudentList($result) {
        checkLoggedIn();
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $rows = '';
        $buttons = '';
        $topItems = '';
        
        $currUrl   = $_SERVER['REQUEST_URI'];
        $currUrl  .= (strpos($currUrl, '?') === false) ? '?' : '';
        $printUrl  = "{$currUrl}&print=1&_action=printList";
        $exportUrl = "{$currUrl}&export=1&_action=exportData&showHTML=0";
        $topItems = '';

        //$urlAtt = $fn->getUrlByType('attendance');
        //$urlNewArr['action_name'] = $arrayMaster->getSEOValueByKey('exportList');
        //$exportUrl = $fn->make_seo_url($urlNewArr, 0, $urlAtt);

        if ($tv['action']!= 'printList' && $_SESSION['userType'] == 'Staff'){
            $topItems ="
            <div class='floatbox'>
                <div class='float_right'>
                    <a class='squarebutton' href='{$exportUrl}' id='exportData'><span>{$ln->gd('export')}</span></a>
                </div>
                <div class='float_right'>
                    <a class='squarebutton printBtn' href='{$printUrl}'><span>{$ln->gd('print')}</a>
                </div>
            </div>
            ";
        }

        while ($row = $db->sql_fetchrow($result)) {
	    $date = ($row['record_date'] != '') ? date('d-m-Y', strtotime($row['record_date'])) : '';
            $student_id = $row['student_id'];
            
            //Enable Edit function in comment on click
            $fieldValueOrig = $row['comment'];
            $fieldValue    = substr(strip_tags($fieldValueOrig) , 0, 100) . '';
            $fieldValueHid = htmlspecialchars($fieldValueOrig);
            $rec_id = $row['student_attendance_id'];
            $buttons = "
            <td class='comment' rec_id='{$row['student_attendance_id']}'>
                <div class='display'>{$fieldValue}</div>
                <div class='btns'>
                    <button type='button' class='saveComment'>Save</button>
                    <button type='button' class='cancelComment'>Cancel</button>
                </div>
                <input type='hidden' id='prev__{$rec_id}' value='{$fieldValueHid}' />
            </td>
            ";

            $studNameTxt = '';
            $btnCmt = '';
            if ($_SESSION['userType'] == 'Staff') {
                $studNameTxt = "
                <td>{$row['student_name']}</td>
                ";

                $btnCmt = "
                <a class='ic-edit editAttComment'></a>
                ";
            }
            
        	$rows .="
            <tr>
                {$studNameTxt}
                <td>{$row['class_title']}</td>
                <td>{$date}</td>
                <td>{$row['status']}</td>
                {$buttons}
                <td>{$btnCmt}</td>
            </tr>
            ";
        }
        
        $studNameTxt = '';
        
        if ($_SESSION['userType'] == 'Staff') {
            $studNameTxt = "
            <th class='w150'>{$ln->gd('name')}</th>
            ";
        }

        $text = "
        {$topItems}
        <table class='thinList'>
            <tr>
                {$studNameTxt}
                <th class='w100'>{$ln->gd('class')}</th>
                <th class='w100'>{$ln->gd('date')}</th>
                <th class='w75'>{$ln->gd('status')}</th>
                <th  class='txtCenter'>{$ln->gd('comment')}</th>
                <th>&nbsp;</th>
            </tr>
            {$rows}
        </table>
        <script>
            Comment.enableEditFunctions();
        </script>
        ";

        return $text;
    }
}
