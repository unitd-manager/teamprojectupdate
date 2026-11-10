<?
class CP_Admin_Modules_EnterpriseIms_TeacherAttendance_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $rows  = "";
        $rowCounter = 0;
        
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){            
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
			{$listObj->getGoToDetailText($rowCounter, $row['teacher_name'])}
            {$listObj->getListDataCell($row['time_in'])}
            {$listObj->getListDataCell($row['time_out'])}
            {$listObj->getListDateCell($row['date'])}
            {$listObj->getListDataCell($row['hours'])}
            {$listObj->getListDataCell($row['teacher_attendance_id'], 'center')}
            {$listObj->getListRowEnd($row['teacher_attendance_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Staff Name', 'teacher_name')}
        {$listObj->getListHeaderCell('Time In', 'time_in')}
        {$listObj->getListHeaderCell('Time Out', 'time_out')}
        {$listObj->getListHeaderCell('Date', 'date')}
        {$listObj->getListHeaderCell('Hours', 'hours')}
        {$listObj->getListHeaderCell('Teacher Attendance Id', 'teacher_attendance_id' , 'headerCenter')}
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
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

   		$sqlTeacher = getCPModuleObj('enterpriseIms_teacher')->model->getTeacherSQL();
        
        $fieldset = "
        {$formObj->getDDRowBySQL('Teacher Name', 'teacher_id', $sqlTeacher)}
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

   		$sqlTeacher = getCPModuleObj('enterpriseIms_teacher')->model->getTeacherSQL();
        $expTeach = array('detailValue' => $row['teacher_name']);
        
        $fielset1 = "
        {$formObj->getDDRowBySQL('Teacher Name', 'teacher_id', $sqlTeacher, $row['teacher_id'], $expTeach)}
        {$formObj->getDateRow('Date', 'date', $row['date'])}
        {$formObj->getTimeRow('Time In', 'time_in', $row['time_in'])}
        {$formObj->getTimeRow('Time Out', 'time_out', $row['time_out'])}
        {$formObj->getTBRow('No. of Hours', 'hours', $row['hours'])}
		";
		
        $text = "
        {$formObj->getFieldSetWrapped('Teacher Attendance Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $media = Zend_Registry::get('media');

        $text ="
        {$media->getRightPanelMediaDisplay('Attachments', 'enterpriseIms_teacherAttendance', 'attachment', $row)}
        ";
        
        return $text;
    }
    
    /**
     *
     */
    function getQuickSearch() {
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');

        $text = "";        
        
        return $text;
    }
}