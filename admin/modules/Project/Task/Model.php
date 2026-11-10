<?
class CPL_Admin_Modules_Project_Task_Model extends CP_Admin_Modules_Project_Task_Model
{
    /**
     *
     */
    function getTaskJsonByProId() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        
        $project_id = $fn->getReqParam('project_id', '', true);

        $json  = array();
        
        if ($project_id == ''){
            $json[] = array('value' => '', 'caption' => 'Please Select');
            return json_encode($json);
        }

        $SQL = $this->getTaskByProjectSQL($project_id);
        $result = $db->sql_query($SQL);  

        $json[] = array('value' => '', 'caption' => 'Please Select');
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['task_id'], "caption" => $row['task_title']);
        }
        
        return json_encode($json);
    }

    /**
     *
     */
    function getTaskByProjectSQL($project_id = 0) {

        $append = "WHERE t.project_id = {$project_id}";

        $sql = "
        SELECT t.task_id
              ,t.title AS task_title
        FROM task t
        {$append}
        ORDER BY task_title
        ";
        return $sql;
    }
}
