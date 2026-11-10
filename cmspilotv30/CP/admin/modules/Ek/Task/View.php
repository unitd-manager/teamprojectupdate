<?
class CP_Admin_Modules_Ek_Task_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['staff_name'])}
            {$listObj->getListDataCell($row['subject_title'])}
            {$listObj->getListDataCell($row['type'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListPublishedImage($row['published'], $row['task_id'])}
            {$listObj->getListRowEnd($row['task_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 't.title')}
        {$listObj->getListHeaderCell('Staff', 'staff_name')}
        {$listObj->getListHeaderCell('Subject', 'd.subject_id')}
        {$listObj->getListHeaderCell('Task type', 'type')}
        {$listObj->getListHeaderCell('Status', 'status')}
        {$listObj->getListHeaderCell('Published', 't.Published' , 'headerCenter')}
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
        $am = Zend_Registry::get('am');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $formObj->mode = $tv['action'];
        
        $sqlStaff = "
        SELECT  a.staff_id
               ,CONCAT_WS(' ', a.first_name, a.last_name ) AS staff_name 
        FROM staff a 
        ORDER BY staff_name
        ";
        $expStaff = array('detailValue' => $row['staff_name']);

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
        {$formObj->getDDRowBySQL('Staff', 'staff_id', $sqlStaff, $row['staff_id'], $expStaff)}
        {$formObj->getDDRowBySQL('Subject', 'subject_id', $sqlSubject, $row['subject_id'], $expSubject)}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
        {$formObj->getDDRowBySQL('Type', 'type', $sqlType, $row['type'], $expVl)}
        {$formObj->getDateRow('Due Date', 'due_date', $row['due_date'])}
        {$formObj->getDateRow('Launch Date', 'launch_date', $row['launch_date'])}
        {$formObj->getDateRow('Expiry Date', 'expiry_date', $row['expiry_date'])}
        {$formObj->getTARow('Description', 'description', $ln->gfv($row, 'description', '0'))}
        {$formObj->getTBRow('Links', 'links', $row['links'])}
        {$formObj->getTARow('Embed Code', 'embed_text', $row['embed_text'])}
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
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');

        $text ="
        {$media->getRightPanelMediaDisplay('Attachments', 'ek_task', 'attachment', $row)}
        {$media->getRightPanelMediaDisplay("Picture", "ek_task", "picture", $row)}
        {$displayLinkData->getLinkPortalMain("ek_task", "ek_studentLink", "Students Linked", $row)}
        {$displayLinkData->getLinkPortalMain("ek_task", "ek_classLink", "Classes Linked", $row)}
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
        $am = Zend_Registry::get('am');
        $fn = Zend_Registry::get('fn');

        $taskType     = $fn->getReqParam('type');
        $class_id     = $fn->getReqParam('class_id');
        $subject_id   = $fn->getReqParam('subject_id');
        $staff_id     = $fn->getReqParam('staff_id');
        
        $sql1 = "
        SELECT class_id, title 
        FROM class 
        ORDER BY title
        ";
        
        $SQLTaskType = "
        SELECT DISTINCT type
              ,type
        FROM task
        WHERE type != ''
        ORDER BY type
        ";
        
        $sqlCombo = "
        SELECT subject_id, title 
        FROM subject 
        ORDER BY title
        ";
        
        $sqlCombo1 = "
        SELECT  a.staff_id, 
          CONCAT_WS(' ', a.first_name, a.last_name ) AS staff_name 
        FROM staff a 
        ORDER BY staff_name
        ";
      
        $taskType = $dbUtil->getDropDownFromSQLCols2($db, $SQLTaskType, $taskType);
      
        $text = "
        <td>
            <select name='type'>
                <option value=''>Task Type</option>
                {$taskType}
            </select>
        </td>
        
        <td class='fieldValue'>
            <select name='subject_id'>
                <option value=''>Select Subject</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCombo, $subject_id)}
            </select>
        </td>
	    ";
        
        return $text;
    }
}
