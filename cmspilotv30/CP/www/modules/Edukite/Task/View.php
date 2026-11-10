<?
class CP_Www_Modules_Edukite_Task_View extends CP_Common_Modules_Edukite_Task_View
{
    //==================================================================//
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        $mediaArray = Zend_Registry::get('mediaArray');

        $rows  = "";
        $rowCounter = 0;
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter, '', $row)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'], '', '', $row)}
            {$listObj->getListDataCell($row['teacher_name'])}
            {$listObj->getListDataCell($row['subject_title'])}
            {$listObj->getListPublishedImage($row['published'], $row['task_id'])}
            {$listObj->getListRowEnd($row['task_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 't.title')}
        {$listObj->getListHeaderCell('Staff', 'teacher_name')}
        {$listObj->getListHeaderCell('Subject', 'subject_title')}
        {$listObj->getListHeaderCell('Published', 't.published' , 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    //==================================================================//
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

    //==================================================================//
    function getEdit($row) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        $theme = Zend_Registry::get('currentTheme');

        $formObj->mode = $tv['action'];
        
        $sqlStaff = "
        SELECT  a.staff_id
               ,CONCAT_WS(' ', a.first_name, a.last_name ) AS staff_name 
        FROM staff a 
        ORDER BY staff_name
        ";
        $expStaff = array('detailValue' => $row['teacher_name']);

        $sqlSubject = "
        SELECT subject_id
              ,title as subject_title 
        FROM subject a 
        ORDER BY subject_id
        ";
        $expSubject = array('detailValue' => $row['subject_title']);

        $expVl   = array('sqlType' => 'OneField');
        $sqlStatus = $fn->getValueListSQL('status');

        $expVl   = array('sqlType' => 'OneField');
        $sqlType = $fn->getValueListSQL('taskType');
        
        $fielset1 = "
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getDDRowBySQL('Teacher', 'teacher_id', $sqlStaff, $row['teacher_id'], $expStaff)}
        {$formObj->getDDRowBySQL('Subject', 'subject_id', $sqlSubject, $row['subject_id'], $expSubject)}
        {$formObj->getDateRow('Start Date', 'start_date', $row['start_date'])}
        {$formObj->getDateRow('Due Date', 'due_date', $row['due_date'])}
        {$formObj->getTARow('Description', 'description', $ln->gfv($row, 'description', '0'))}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fielset1)}
        ";

        return $text;
    }

    //==================================================================//
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');

        $text ="
        {$media->getRightPanelMediaDisplay('Picture', 'edukite_task', 'picture', $row)}
        {$media->getRightPanelMediaDisplay('Attachments', 'edukite_task', 'attachment', $row)}
        ";
        return $text;
    }

    //==================================================================//
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $subject_id   = $fn->getReqParam('subject_id');
        $teacher_id     = $fn->getReqParam('teacher');
        
        $sqlCombo = "
        SELECT subject_id, title 
        FROM subject 
        ORDER BY title
        ";
        
        $text = "
        <div>
            <select name='subject_id'>
                <option value=''>Select Subject</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCombo, $subject_id)}
            </select>
        </div>
	    ";
        
        return $text;
    }
}
