<?
class CP_Admin_Modules_ManPower_TaskLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $sqlStaff = "
        SELECT a.staff_id
              ,CONCAT_WS(' ', a.first_name, a.last_name) AS staff_name
        FROM staff a
            ,opportunity_staff b
        WHERE a.staff_id = b.staff_id
          AND b.opportunity_id = {$tv['srcRoomId']}
        ORDER BY staff_name
        ";
        $current_date  = $cpUtil->getISODateStr();
        $due_date  = strftime("%Y-%m-%d", strtotime("$current_date +7 day"));

        $sqlStatus = $fn->getValueListSQL('taskStatus');
        $sqlCat    = $fn->getValueListSQL('taskCategory');
        $chargeable = ($tv['module'] == 'project') ? 1 : 0;

        $sqlPM = $fn->getDDSql('manPower_staff', array('condn' => "staff_type = 'Project Manager' AND status = 'Current'"));

        $projectRec = $fn->getRecordRowByID('project', 'project_id', $tv['srcRoomId']);

        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $exp = array('sqlType' => 'OneField');

                /*{$formObj->getDDRowBySQL('Category', 'category', $sqlCat, 'Project Inclusive', $exp)}
                {$formObj->getYesNoRRow('Chargeable', 'chargeable', $chargeable)}*/
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Title', 'title')}
                {$formObj->getCheckBoxArrRowBySQL($cpCfg['m.project.staffFieldLabel'].' Linked', 'staff_ids[]', $sqlStaff)}
                {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, 'Due', $exp)}
                {$formObj->getYesNoRRow('Alert '. $cpCfg['m.project.staffFieldLabel'].' by email', 'staff_alert')}
                {$formObj->getDateRow('From Date', 'from_date',$current_date)}
                {$formObj->getDateRow('Due Date', 'due_date',$due_date)}
                {$formObj->getTBRow('Estimated Hours', 'estimated_hours')}
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
        $sqlCat    = $fn->getValueListSQL('taskCategory');

        $sqlPM = $fn->getDDSql('manPower_staff', array('condn' => "staff_type = 'Project Manager' AND status = 'Current'"));
        
        $formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $exp = array('sqlType' => 'OneField');

                /*{$formObj->getDDRowBySQL('Category', 'category', $sqlCat, $row['category'], $exp)}
                {$formObj->getYesNoRRow('Chargeable', 'chargeable', $row['chargeable'])}*/

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Title', 'title', $row['title'])}
                {$formObj->getCheckBoxArrRowBySQL($cpCfg['m.project.staffFieldLabel'].' Linked', 'staff_ids[]', $sqlStaff, $staffArray)}
                {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $exp)}
                {$formObj->getYesNoRRow('Alert '. $cpCfg['m.project.staffFieldLabel'].' by email', 'staff_alert', $row['chargeable'])}
                {$formObj->getDateRow('From Date', 'from_date', $row['from_date'])}
                {$formObj->getDateRow('Due Date', 'due_date', $row['due_date'])}
                {$formObj->getTBRow('Estimated Hours', 'estimated_hours', $row['estimated_hours'])}
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
