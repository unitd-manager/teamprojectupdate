<?
class CP_Admin_Modules_Project_TaskLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        if ($tv['srcRoom'] == "project_opportunity" || $tv['srcRoom'] == "manPower_opportunity") {
            $sqlStaff = "
            SELECT a.staff_id
                  ,CONCAT_WS(' ', a.first_name, a.last_name) AS staff_name
            FROM staff a
                ,opportunity_staff b
            WHERE a.staff_id = b.staff_id
              AND b.opportunity_id = {$tv['srcRoomId']}
            ORDER BY staff_name
            ";

        } else if ($tv['srcRoom'] == "project_project") {
            $sqlStaff = "
            SELECT a.staff_id
                  ,CONCAT_WS(' ', a.first_name, a.last_name) AS staff_name
            FROM staff a,
                 project_staff b
            WHERE a.staff_id = b.staff_id
              AND b.project_id = {$tv['srcRoomId']}
            ORDER BY staff_name
            ";
        }

        $sqlStatus = $fn->getValueListSQL('taskStatus');
        $sqlCat = $fn->getValueListSQL('taskCategory');
        $chargeable = ($tv['module'] == 'project') ? 1 : 0;

        $sqlPM = $fn->getDDSql('core_staff', array('condn' => "staff_type = 'Project Manager' AND status = 'Current'"));

        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $exp = array('sqlType' => 'OneField');

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Title', 'title')}
                {$formObj->getCheckBoxArrRowBySQL($cpCfg['m.project.staffFieldLabel'].' Linked', 'staff_ids[]', $sqlStaff)}
                {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, '', $exp)}
                {$formObj->getDDRowBySQL('Category', 'category', $sqlCat, 'Project Inclusive', $exp)}
                {$formObj->getYesNoRRow('Chargeable', 'chargeable', $chargeable)}
                {$formObj->getYesNoRRow('Alert '. $cpCfg['m.project.staffFieldLabel'].' by email', 'staff_alert')}
                {$formObj->getYesNoRRow('Alert Project Manager when complete', 'project_manager_alert')}
                {$formObj->getDateRow('Due Date', 'due_date')}
                {$formObj->getTBRow('Estimated Hours', 'estimated_hours')}
                {$formObj->getDDRowBySQL('Project Manager', 'project_manager_id', $sqlPM)}
                {$formObj->getTARow('Description', 'description')}
            </fieldset>
            <input type='hidden' name='{$fn->getSrcRoomKeyFldName()}' value='{$tv['srcRoomId']}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('task', 'task_id', $id);
        
        if ($row['project_id'] != '') {
            $sqlStaff = "
            SELECT a.staff_id
                  ,CONCAT_WS(' ', a.first_name, a.last_name) AS staff_name
            FROM staff a,
                 project_staff b
            WHERE a.staff_id = b.staff_id
              AND b.project_id = {$row['project_id']}
            ORDER BY staff_name
            ";
        } else if ($row['opportunity_id'] != ''){
            $sqlStaff = "
            SELECT a.staff_id
                  ,CONCAT_WS(' ', a.first_name, a.last_name) AS staff_name
            FROM staff a
                ,opportunity_staff b
            WHERE a.staff_id = b.staff_id
              AND b.opportunity_id = {$row['opportunity_id']}
            ORDER BY staff_name
            ";
        }

        $sql = "
        SELECT staff_id
        FROM task_staff
        WHERE task_id = {$id}
        ";
        $result = $db->sql_query($sql);
        $staffArray = $dbUtil->getResultsetAsArrayForForm($result);

        $sqlStatus = $fn->getValueListSQL('taskStatus');
        $sqlCat = $fn->getValueListSQL('taskCategory');

        $sqlPM = $fn->getDDSql('core_staff', array('condn' => "staff_type = 'Project Manager' AND status = 'Current'"));
        
        $formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $exp = array('sqlType' => 'OneField');

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Title', 'title', $row['title'])}
                {$formObj->getCheckBoxArrRowBySQL($cpCfg['m.project.staffFieldLabel'].' Linked', 'staff_ids[]', $sqlStaff, $staffArray)}
                {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $exp)}
                {$formObj->getDDRowBySQL('Category', 'category', $sqlCat, $row['category'], $exp)}
                {$formObj->getYesNoRRow('Chargeable', 'chargeable', $row['chargeable'])}
                {$formObj->getYesNoRRow('Alert '. $cpCfg['m.project.staffFieldLabel'].' by email', 'staff_alert', $row['chargeable'])}
                {$formObj->getYesNoRRow('Alert Project Manager when complete', 'project_manager_alert', $row['project_manager_alert'])}
                {$formObj->getDateRow('Due Date', 'due_date', $row['due_date'])}
                {$formObj->getTBRow('Estimated Hours', 'estimated_hours', $row['estimated_hours'])}
                {$formObj->getDDRowBySQL('Project Manager', 'project_manager_id', $sqlPM, $row['project_manager_id'])}
                {$formObj->getTARow('Description', 'description', $row['description'])}
            </fieldset>
            <input type='hidden' name='task_id' value='{$id}' />
        </form>
        ";
        return $text;
    }

    /**
     *
     */
    function getNewPortalFromDashboard(){
        $formObj = Zend_Registry::get('formObj');
        $tv = Zend_Registry::get('tv');

        $formAction = "index.php?_spAction=addPortalFromDashboard&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $exp = array('sqlType' => 'OneField');

        $sqlProj = $fn->getDDSql('project_project', array('condn' => "status = 'WIP'"));

        $sqlOpp = "
        SELECT o.opportunity_id
              ,o.title
        FROM opportunity o
        WHERE LOWER(o.status) != 'cancelled' 
          AND LOWER(o.status) != 'win' 
          AND LOWER(o.status) != 'lost'
        ORDER BY o.title
        ";
        
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Title', 'title')}
                {$formObj->getDDRowBySQL('Project', 'project_id', $sqlProj)}
                {$formObj->getDDRowBySQL('Opportunity', 'opportunity_id', $sqlOpp)}
            </fieldset>
        </form>
        ";

        return $text;
    }
}
