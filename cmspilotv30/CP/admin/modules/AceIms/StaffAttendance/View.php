<?
class CP_Admin_Modules_AceIms_StaffAttendance_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray) {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $listObj = Zend_Registry::get('listObj');

        $rowCounter = 0;
        $rows       = "";

        foreach ($dataArray as $row){
            $rows .="
    		{$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['staff_name'])}
            {$listObj->getListDateCell($row['record_date'])}
            {$listObj->getListDataCell($row['time_in'])}
            {$listObj->getListDataCell($row['leave_time'])}
            {$listObj->getListDateCell($fn->getYesNo($row['on_leave']), "center")}
            {$listObj->getListDataCell($row['staff_attendance_id'], 'center')}
    		{$listObj->getListRowEnd($row['staff_attendance_id'])}
			";

        	$rowCounter++;
		}

        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Staff', 'b.staff_id')}
        {$listObj->getListHeaderCell('Date', 'a.record_date')}
        {$listObj->getListHeaderCell('Time In', 'a.time_in')}
        {$listObj->getListHeaderCell('Time Out', 'a.leave_time')}
        {$listObj->getListHeaderCell('On Leave', 'a.on_leave', 'headerCenter')}
        {$listObj->getListHeaderCell('ID', 'a.staff_attendance_id', 'headerCenter')}
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
        WHERE (s.developer = 0 OR s.developer IS NULL)
        ORDER BY staff_name
        ";

        $fieldset = "
        {$formObj->getDDRowBySQL('Staff Name', 'staff_id', $sqlStaff, $_SESSION['staff_id'])}
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
        $formObj = Zend_Registry::get('formObj');

        $sqlStaff = "
        SELECT s.staff_id
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name 
        FROM staff s 
        WHERE (s.developer = 0 OR s.developer IS NULL)
        ORDER BY staff_name
        ";
        $expStaff = array('detailValue' => $row['staff_name']);

        $fieldset1  = "
        {$formObj->getDDRowBySQL('Name', 'staff_id', $sqlStaff, $row['staff_id'], $expStaff)}
        {$formObj->getDateRow('Date', 'record_date', $row['record_date'])}
        {$formObj->getTimeRow('Time in (HH:MM)', 'time_in', $row['time_in'])}
        {$formObj->getTimeRow('Time out (HH:MM)', 'leave_time', $row['leave_time'])}
        {$formObj->getYesNoRRow('On Leave', 'on_leave', $row['on_leave'])}
        {$formObj->getTARow('Notes', 'notes', $row['notes'])}
		";
        
        $fieldset2 = $formObj->getHTMLEditor('Description', 'description', $row['description']);
		
        $text = "
        {$formObj->getFieldSetWrapped('Details', $fieldset1)}
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

        if ($userGroupID == 1){
            $SQL = "
            SELECT staff_id
                  ,CONCAT_WS(' ', a.first_name, a.last_name) AS staff_name 
            FROM staff a 
            WHERE (a.developer = 0 OR a.developer IS NULL)
            ORDER BY staff_name
            ";
        } else if ($userGroupID == 2){
            $SQL = "
            SELECT staff_id
                  ,CONCAT_WS(' ', a.first_name, a.last_name) AS staff_name 
            FROM staff a 
            WHERE a.team ='{$_SESSION['staff_team']}'
              AND (a.developer = 0 OR a.developer IS NULL)
            ORDER BY staff_name
            ";
        } else if ($userGroupID == 3){
            $SQL = "
            SELECT staff_id
                  ,CONCAT_WS(' ', a.first_name, a.last_name) AS staff_name 
            FROM staff a 
            WHERE a.team ='{$_SESSION['staff_team']}' 
              AND a.staff_id = '{$_SESSION['staff_id']}'
              AND (a.developer = 0 OR a.developer IS NULL)
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
            <option value=''>Staff People</option>
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
}