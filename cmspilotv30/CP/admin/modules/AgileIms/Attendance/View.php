<?
class CP_Admin_Modules_AgileIms_Attendance_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row) {

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['contact_name'])}
            {$listObj->getListDataCell($row['batch_title'])}
            {$listObj->getListDateCell($row['date'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($row['attendance_id'], 'center')}
            {$listObj->getListRowEnd($row['attendance_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Name', 'contact_name')}
        {$listObj->getListHeaderCell('Batch', 'batch_title')}
        {$listObj->getListHeaderCell('Date', 'a.date')}
        {$listObj->getListHeaderCell('Status', 'a.status')}
        {$listObj->getListHeaderCell('ID', 'a.attendance_id' , 'headerCenter')}
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
        {$formObj->getDateRow('Date', 'date')}
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
        $fn      = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $statusArr = array(1 => 'Present', 0 => 'Absent');
        
        $sqlCont = $fn->getDDSql('agileIms_contact');
        $expCont = array('detailValue' => $row['contact_name']);

        $sqlBatch = $fn->getDDSql('agileIms_batch');
        $expBatch = array('detailValue' => $row['batch_title']);

        $fielset1 = "
        {$formObj->getDDRowBySQL('Name', 'contact_id', $sqlCont, $row['contact_id'], $expCont)}
        {$formObj->getDDRowBySQL('Batch', 'batch_id', $sqlBatch, $row['batch_id'], $expBatch)}
        {$formObj->getDateRow('Date', 'date', $row['date'])}
        {$formObj->getRadioArrRow('Status', 'status', $row['status'], $statusArr, '')}
        ";
		
        $text = "
        {$formObj->getFieldSetWrapped('Attendance Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){

        $text = "";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {

        $text = "";        
        
        return $text;
    }

    /**
     */
    function getTakeAttendance() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');
        $db = Zend_Registry::get('db');
               
        $batch_id = $fn->getReqParam('id');
        $rows = '';
        $formAction = "index.php?module=agileIms_attendance&_spAction=takeAttendanceSubmit&showHTML=0";
        $currentDate  = date("Y-m-d");
        
        $SQLAttendance = "
        SELECT date
              ,attendance_id
              ,batch_id
        FROM attendance
        WHERE date = '{$currentDate}'
        AND batch_id = {$batch_id}
        ";
        $resultAttendance = $db->sql_query($SQLAttendance);  
        $numRows          = $db->sql_numrows($resultAttendance);
        
        if ($numRows > 0) {
            return "<strong>Attendance already taken</strong>";
        }

        $SQL = "
        SELECT c.first_name
              ,cc.course_contact_id
        FROM course_contact cc
        LEFT JOIN (contact c) ON (c.contact_id = cc.contact_id)
        WHERE cc.batch_id = {$batch_id}
        ";
        $result = $db->sql_query($SQL);
        
        while ($row = $db->sql_fetchrow($result)) {
            $rows .= $this->getAttendanceRecords($row['course_contact_id'], $row['first_name']);
        }
        
        $headers ="
        <strong>Date : {$currentDate}</strong>
        <th>Trainee Name</th>
        <th>
            <a href='#' class='allPresent'>All Present / </a>
            <a href='#' class='allAbsent'>All Absent</a>
        </th>
        ";
        
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
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
     * history_table_id - May be batch_history_id or course_contact_id according to the condition re-directed from
     */
    function getAttendanceRecords($history_table_id, $trainer_name){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $row = $fn->getRecordRowByID('course_contact', 'course_contact_id', $history_table_id);
        $pfx = $history_table_id . '_' ;
        $table_history_id = 'course_contact_id[]';
        
        $arr = array('Present' => 'Present', 'Absent' => 'Absent');
        
        $text = "
        <tr>
            <td>{$trainer_name}</td>
            <td>{$formObj->getRadioArrRow(' ', "{$pfx}status", '', $arr, '')}</td>
            <input type='hidden' name='{$table_history_id}' 
            value='{$history_table_id}' />
        </tr>
        ";

        return $text;
    }

    /**
     *
     */
     function getEditAttendance() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');
        $db = Zend_Registry::get('db');
               
        $batch_id = $fn->getReqParam('id');
        $attendance_date = $fn->getReqParam('attendance_date');
        $currentDate  = date("Y-m-d");
        
        $rows = '';
        $formAction = "index.php?module=agileIms_attendance&_spAction=editAttendanceSubmit&showHTML=0";

        $SQL = "
        SELECT a.*
              ,c.first_name
        FROM attendance a
        LEFT JOIN (contact c) ON (a.contact_id = c.contact_id)
        WHERE a.batch_id = '{$batch_id}'
        AND a.date = '{$attendance_date}'
        ";
        
        $result = $db->sql_query($SQL);
        
        while ($row = $db->sql_fetchrow($result)) {
            $rows .= $this->getEditAttendanceRecords($row['attendance_id'], $row['first_name']);
        }
        
        $headers ="
        <th>Trainer Name</th>
        <th>Attendance Status</th>
        ";
        
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
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
    function getEditAttendanceRecords($attendance_id, $trainer_name){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $row = $fn->getRecordRowByID('attendance', 'attendance_id', $attendance_id);
        $pfx = $attendance_id . '_' ;
        $arr = array('Present' => 'Present', 'Absent' => 'Absent');
        $text = "
        <tr>
            <td>{$trainer_name}</td>
            <td>{$formObj->getRadioArrRow(' ', "{$pfx}status", $row['status'], $arr, '')}</td>
            <input type='hidden' name='attendance_id[]' 
            value='{$attendance_id}' />
        </tr>
        
        ";
        return $text;
    }
}