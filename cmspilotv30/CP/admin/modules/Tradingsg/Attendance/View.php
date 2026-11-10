<?
class CP_Admin_Modules_Tradingsg_Attendance_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray) {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $listObj = Zend_Registry::get('listObj');

        $rowCounter = 0;
        $rows       = "";

        foreach ($dataArray as $row){
            $time_in = substr($row['time_in'], 0, 5);
            $leave_time = substr($row['leave_time'], 0, 5);
            
            if ($cpCfg['m.tradingsg.attendance.hasMultipleSessions'] == 1) {
                
                $timeInOutData = "
                {$listObj->getListDataCell($row['time_in_morning'])}
                {$listObj->getListDataCell($row['leave_time_morning'])}
                {$listObj->getListDataCell($row['time_in_evening'])}
                {$listObj->getListDataCell($row['leave_time_evening'])}
                ";

                /*$timeIn = '';
                if ($row['time_in_morning'] == '' || $row['time_in_morning'] == '00:00:00') {
                    $timeIn = $listObj->getListDataCell($row['time_in_evening']);
                } else {
                    $timeIn = $listObj->getListDataCell($row['time_in_morning']);
                }
                
                $timeOut = '';
                if ($row['leave_time_evening'] == '' || $row['leave_time_evening'] == '00:00:00') {
                    $timeOut = $listObj->getListDataCell($row['leave_time_morning']);
                } else {
                    $timeOut = $listObj->getListDataCell($row['leave_time_evening']);
                }
                
                $timeInOutData = "
                {$timeIn}
                {$timeOut}
                ";*/
                
            } else {
                $timeInOutData = "
                {$listObj->getListDataCell($row['time_in'])}
                {$listObj->getListDataCell($row['leave_time'])}
                ";
            }
            
            $rows .="
    		{$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['staff_name'])}
            {$listObj->getListDateCell($row['record_date'])}
            {$timeInOutData}
            {$listObj->getListDateCell($fn->getYesNo($row['on_leave']), "center")}
            {$listObj->getListDataCell($row['attendance_id'], 'center')}
    		{$listObj->getListRowEnd($row['attendance_id'])}
			";

        	$rowCounter++;
		}

        if ($cpCfg['m.tradingsg.attendance.hasMultipleSessions'] == 1) {
            $timeInOutHeader = "
            {$listObj->getListHeaderCell('Time In (Morning)', 'a.time_in_morning')}
            {$listObj->getListHeaderCell('Time Out (Morning)', 'a.leave_time_morning')}
            {$listObj->getListHeaderCell('Time In (Evening)', 'a.time_in_evening')}
            {$listObj->getListHeaderCell('Time Out (Evening)', 'a.leave_time_evening')}
            ";

            /*$timeInOutHeader = "
            {$listObj->getListHeaderCell('Time In', 'a.time_in_morning')}
            {$listObj->getListHeaderCell('Time Out', 'a.leave_time_evening')}
            ";*/
        } else {
            $timeInOutHeader = "
            {$listObj->getListHeaderCell('Time In', 'a.time_in')}
            {$listObj->getListHeaderCell('Time Out', 'a.leave_time')}
            ";
        }

        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell($cpCfg['m.tradingsg.staffFieldLabel'], 'b.staff_id')}
        {$listObj->getListHeaderCell('Date', 'a.record_date')}
        {$timeInOutHeader}
        {$listObj->getListHeaderCell('On Leave', 'a.on_leave', 'headerCenter')}
        {$listObj->getListHeaderCell('ID', 'a.attendance_id', 'headerCenter')}
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
        $cpCfg = Zend_Registry::get('cpCfg');

        $sqlStaff = "
        SELECT s.staff_id
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name 
        FROM staff s 
        ORDER BY staff_name
        ";

        $fieldset = "
        {$formObj->getDDRowBySQL($cpCfg['m.tradingsg.staffFieldLabel'], 'staff_id', $sqlStaff, $_SESSION['staff_id'])}
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $sqlStaff = "
        SELECT s.staff_id
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name 
        FROM staff s 
        ORDER BY staff_name
        ";

        $expStf = array('detailValue' => $row['staff_name']);

        if ($cpCfg['m.tradingsg.attendance.hasMultipleSessions'] == 1){
            $inOutMorn = "
            {$formObj->getTimeRow('Time in (HH:MM)', 'time_in_morning', $row['time_in_morning'])}
            {$formObj->getTimeRow('Time out (HH:MM)', 'leave_time_morning', $row['leave_time_morning'])}
            ";

            $inOutEve = "
            {$formObj->getTimeRow('Time in (HH:MM)', 'time_in_evening', $row['time_in_evening'])}
            {$formObj->getTimeRow('Time out (HH:MM)', 'leave_time_evening', $row['leave_time_evening'])}
            ";
            
            $inOut = "
            {$formObj->getFieldSetWrapped('Morning Session', $inOutMorn)}
            {$formObj->getFieldSetWrapped('Evening Session', $inOutEve)}
            ";
        } else {
            $time = "
            {$formObj->getTimeRow('Time in (HH:MM)', 'time_in', $row['time_in'])}
            {$formObj->getTimeRow('Time out (HH:MM)', 'leave_time', $row['leave_time'])}
            ";

            $inOut = "
            {$formObj->getFieldSetWrapped('Timing', $time)}
            ";
        }

        $sqlTypeOfLeave = $fn->getValueListSQL('typeOfLeave');
        $expVl     = array('sqlType' => 'OneField');

        $fielset1  = "
        {$formObj->getDDRowBySQL($cpCfg['m.tradingsg.staffFieldLabel'],  'staff_id', $sqlStaff, $row['staff_id'], $expStf)}
        {$formObj->getDateRow('Date', 'record_date', $row['record_date'])}
        {$formObj->getYesNoRRow('On Leave', 'on_leave', $row['on_leave'])}
        {$formObj->getDDRowBySQL('Type Of Leave', 'type_of_leave', $sqlTypeOfLeave, $row['type_of_leave'], $expVl)}
        {$formObj->getTARow('Notes', 'notes', $row['notes'])}
		";
        $fieldset2 = $formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'));
		
        $text = "
        {$formObj->getFieldSetWrapped('Details', $fielset1)}
        {$inOut}
        {$formObj->getFieldSetWrapped('Description', $fieldset2)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        $userGroupID = $fn->getSessionParam('userGroupID');
        $special_search = $fn->getReqParam('special_search');

        if ($userGroupID == 1 ){
            $SQL = "
            SELECT staff_id
                  ,CONCAT_WS(' ', a.first_name, a.last_name) AS staff_name 
            FROM staff a 
            ORDER BY staff_name
            ";
        } else if ($userGroupID == 2 ){
            $SQL = "
            SELECT staff_id
                  ,CONCAT_WS(' ', a.first_name, a.last_name) AS staff_name 
            FROM staff a 
            WHERE a.team ='{$_SESSION['staff_team']}' 
            ORDER BY staff_name
            ";
        } else if ($userGroupID == 3 ){
            $SQL = "
            SELECT staff_id
                  ,CONCAT_WS(' ', a.first_name, a.last_name) AS staff_name 
            FROM staff a 
            WHERE a.team ='{$_SESSION['staff_team']}' 
              AND a.staff_id = '{$_SESSION['staff_id']}' 
            ORDER BY staff_name
            ";
        }

        $olArray = array(
              "Yes"
             ,"No"
        );

        $text = "
        <td>
        <select name='staff_id'>
            <option value=''>Step People</option>
            {$dbUtil->getDropDownFromSQLCols2($db, $SQL, $tv['staff_id'])}
        </select>
        </td>

        <td>
            <select name='special_search'>
                <option value=''>On Leave</option>
                {$cpUtil->getDropDown1($olArray, $tv['special_search'])}
            </select>
        </td>
        ";

        
        return $text;
    }

    /**
     *
     */
   function getSendAttendanceReportToPM() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $appendSQL = '';
        $yesterday = date("Y-m-d", strtotime("yesterday"));
        //$yesterday = "2011-08-14";
        $rowCounter = 0;

        $SQL = "
        SELECT s.staff_id
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
              ,s.name AS display_name
              ,s.email
        FROM staff s
        WHERE s.staff_id NOT IN
              (SELECT a.staff_id
                FROM attendance a
                WHERE a.staff_id = s.staff_id 
                AND a.record_date = '{$yesterday}'
              )
        AND s.position IN ('Programmer SG', 'Programmer HK', 'Programmer', 'Trainee')
        AND s.published = 1
        ORDER BY staff_name
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $staff_name1 = '';
        $rows = '';
        $comma = '';

        $data = array();
        while ($row = $db->sql_fetchrow($result, MYSQL_ASSOC)) {
            
            $staff_name   = $row['display_name'];

            if ($staff_name != $staff_name1){
                $data[$staff_name] = array();
                $staff_name1 = $staff_name;
            }

            $data[$staff_name][] = $row;
        }

        $staff = "";
        $link = "";
        
        foreach ($data as $staff_name => $rows){

        if($rowCounter > 0){
            $comma = ",";
        }
            $staff_name_disp = "";
            foreach ($rows as $key => $row ){
                $staff_name_disp   = $row['display_name'];
                $staff_id   = $row['staff_id'];
            }
        
            $staff .= "{$comma} {$staff_name_disp}";
            $link .= "
            <p>
                <p><a href='http://studiouss.usoftsolutions.com/admin/index.php?_topRm=admin&module=tradingsg_attendance&_spAction=list&searchDone=1&staff_id={$staff_id}'>{$staff_name_disp}</a></p>
            </p>
            ";

            if($numRows > 0) {
               $fa['creation_date'] = date("Y-m-d H:i:s");
               $fa['staff_id']      = $row['staff_id'];
               $fa['record_date']   = $yesterday;
               $fa['on_leave']      = 1;
               $fa['leave_time']    = '00:00:00';
               $fa['time_in']       = '00:00:00';
               $fa['type_of_leave'] = 'Personal Leave';
               $SQL                 = $dbUtil->getInsertSQLStringFromArray($fa, "attendance");
               $result              = $db->sql_query($SQL);
            }
            $rowCounter++;

        }

        $s = '';
        if ($rowCounter > 1){
            $s = 's';
        }

        $text = "
        <table border='0'>
            <tbody>
            <p>Dear Syed / Moin</p>
            <p>The below mentioned staff{$s} seems to be not present yesterday / Not marked the attendance. </p>
            <p>They have marked as not present. </p>
            <p>Please update the attendance if any changes</p>
            {$link}
            <p>Thanks for your help.</p>
            <p>Regards<br>
            Admin</p>
            </tbody>
        </table>
        ";

        $message     = $text;

        $subject     = "USS Attendance" ." - " . $staff . " - " . $yesterday;
        $fromName    = "Admin";
        $fromEmail   = "usstech@usoftsolutions.com";
        
        $toName      = "Syed, Moin";
        $toEmail     = "shafeeq@usoftsolutions.com";
        $toEmail1    = "";

        $args = array(
             'toName'    => $toName
            ,'toEmail'   => $toEmail
            ,'toEmail1'  => $toEmail1
            ,'subject'   => $subject
            ,'message'   => $message
            ,'fromName'  => $fromName
            ,'fromEmail' => $fromEmail
        );

        if ($rowCounter > 0){
            $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
            $emailMsg->sendEmail();
        }

/*        if ($rowCounter > 0){
            $smtp  = includeCPClass('libLocal', 'smtp', 'CPSMTP');
            $error = $smtp->sendEmail($toName, $toEmail, $fromName, $fromEmail, $subject, $message);
            $error = $smtp->sendEmail($toName, $toEmail1, $fromName, $fromEmail, $subject, $message);
        } */
      
        return $text;
    }
}