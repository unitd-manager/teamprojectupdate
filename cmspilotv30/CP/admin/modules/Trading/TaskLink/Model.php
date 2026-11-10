<?
class CP_Admin_Modules_Trading_TaskLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{

    /**
     *
     */
    function getNewPortal(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($tv['module'] == "opportunity") {
            $sqlStaff = "
            SELECT a.staff_id
                  ,CONCAT_WS(' ', a.first_name, a.last_name) AS staff_name
            FROM staff a
                ,opportunity_staff b
            WHERE a.staff_id = b.staff_id
              AND b.opportunity_id = {$tv['srcRoomId']}
            ORDER BY staff_name
            ";

        } else if ($tv['module'] == "project") {
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

        $sqlPM = "
        SELECT staff_id
              ,CONCAT_WS(' ', a.first_name, a.last_name) AS staff_name
        FROM staff a
        WHERE a.staff_type = 'Project Manager'
          AND a.status = 'Current'
        ORDER BY staff_name
        ";
        
        $formAction = "index.php?_spAction=addPortal&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $exp = array('sqlType' => 'OneField');

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Title', 'title')}
                {$formObj->getCheckBoxArrRowBySQL($cpCfg['staffFieldLabel'].' Linked', 'staff_ids[]', $sqlStaff)}
                {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, '', $exp)}
                {$formObj->getDDRowBySQL('Category', 'category', $sqlCat, 'Project Inclusive', $exp)}
                {$formObj->getYesNoRRow('Chargeable', 'chargeable', $chargeable)}
                {$formObj->getYesNoRRow('Alert '. $cpCfg['staffFieldLabel'].' by email', 'staff_alert')}
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
    function getEditPortal(){
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

        $sqlPM = "
        SELECT staff_id
              ,CONCAT_WS(' ', a.first_name, a.last_name) AS staff_name
        FROM staff a
        WHERE a.staff_type = 'Project Manager'
          AND a.status = 'Current'
        ORDER BY staff_name
        ";
        
        $formAction = "index.php?_spAction=savePortal&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $exp = array('sqlType' => 'OneField');

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Title', 'title', $row['title'])}
                {$formObj->getCheckBoxArrRowBySQL($cpCfg['staffFieldLabel'].' Linked', 'staff_ids[]', $sqlStaff, $staffArray)}
                {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $exp)}
                {$formObj->getDDRowBySQL('Category', 'category', $sqlCat, $row['category'], $exp)}
                {$formObj->getYesNoRRow('Chargeable', 'chargeable', $row['chargeable'])}
                {$formObj->getYesNoRRow('Alert '. $cpCfg['staffFieldLabel'].' by email', 'staff_alert', $row['chargeable'])}
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
    function getFields(){
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'due_date');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'chargeable');
        $fa = $fn->addToFieldsArray($fa, 'staff_alert');
        $fa = $fn->addToFieldsArray($fa, 'project_manager_alert');
        $fa = $fn->addToFieldsArray($fa, 'estimated_hours');
        $fa = $fn->addToFieldsArray($fa, 'project_manager_id');
        $fa = $fn->addToFieldsArray($fa, 'opportunity_id');
        $fa = $fn->addToFieldsArray($fa, 'project_id');
        $fa = $fn->addToFieldsArray($fa, 'description');
        
        return $fa;
    }

    //==================================================================//
    function getSaveFromList() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $text = "";

        $task_id  = $fn->getPostParam('task_id');
        $status   = $fn->getPostParam('status');
        $due_date = $fn->getPostParam('due_date');

        $SQL = "UPDATE task SET status = '{$status}', due_date = '{$due_date}' WHERE task_id = '{$task_id}'";
        $result = $db->sql_query($SQL);

        //*********************************************************//
        $text .= "
        <script>
            window.opener.UtilDocument.refreshPage();
            window.close();
        </script>
        ";

        return $text;
    }
}
