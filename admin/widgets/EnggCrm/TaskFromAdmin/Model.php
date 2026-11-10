<?
class CPL_Admin_Widgets_EnggCrm_TaskFromAdmin_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT o.*
        FROM opportunity_project_history o
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'o';

        $SQLStaff = "
        SELECT e.employee_id
        FROM staff s
        LEFT JOIN employee e ON (e.employee_id = s.employee_id)
        WHERE s.staff_id = {$_SESSION['staff_id']}
        ";
        
        $resultStaff  = $db->sql_query($SQLStaff);
        $rowStaff = $db->sql_fetchrow($resultStaff);

        $searchVar->sqlSearchVar[] = "o.date != '' ";
        $searchVar->sqlSearchVar[] = "(o.alert_status IS NULL OR o.alert_status = '' OR o.alert_status = 0)";
        if ($_SESSION['userGroupName'] != 'Super Administrator' && $_SESSION['userGroupName'] != 'SATHISH' && $_SESSION['userGroupName'] != 'SHANKAR') {
            $searchVar->sqlSearchVar[] = "{$rowStaff['employee_id']} IN (o.staff_ids, o.staff_id_created)";
        }
        $searchVar->sqlSearchVar[] = "o.type = 'Task' ";
        $searchVar->sortOrder = "o.opportunity_project_history_id DESC";
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enggCrm_taskFromAdmin');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

    /**
     *
     */
    function getUpdateIsRead() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $date = date('Y-m-d H:i:s');
        $opportunity_project_history_id     = $fn->getReqParam('opportunity_project_history_id');

        $sqlUpdate = "
        UPDATE `opportunity_project_history` SET alert_status = 1, modification_date = '{$date}', modified_by = '{$_SESSION['userFullName']}'
        WHERE opportunity_project_history_id = {$opportunity_project_history_id}
        ";
        $resultUpdate = $db->sql_query($sqlUpdate);
    }

    /**
     *
     */
    function getUpdateNotRead() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $opportunity_project_history_id     = $fn->getReqParam('opportunity_project_history_id');

        $sqlUpdate = "
        UPDATE `opportunity_project_history` SET alert_status = '', emp_task_status = ''
        WHERE opportunity_project_history_id = {$opportunity_project_history_id}
        ";
        $resultUpdate = $db->sql_query($sqlUpdate);
    }

    /**
     *
     */
    function getUpdateEmpStatusIsRead() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $date = date('Y-m-d H:i:s');
        $opportunity_project_history_id = $fn->getReqParam('opportunity_project_history_id');

        $sqlUpdate = "
        UPDATE `opportunity_project_history` SET emp_task_status = 1, modification_date = '{$date}', modified_by = '{$_SESSION['userFullName']}'
        WHERE opportunity_project_history_id = {$opportunity_project_history_id}
        ";
        $resultUpdate = $db->sql_query($sqlUpdate);
    }

    /**
     *
     */
    function getNotificationSubmit() {
        $fn       = Zend_Registry::get('fn');
        $ln       = Zend_Registry::get('ln');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');
        $cpUtil   = Zend_Registry::get('cpUtil');
        
        $title = $fn->getPostParam('title');
        $due_date = $fn->getPostParam('due_date');
        $staff_id = $fn->getPostParam('staff_id');
        $link = $fn->getPostParam('link');

        if (!$this->getNotificationValidate()){
            return $validate->getErrorMessageXML();
        }

        $SQLStaff = "
        SELECT e.employee_id
        FROM staff s
        LEFT JOIN employee e ON (e.employee_id = s.employee_id)
        WHERE s.staff_id = {$_SESSION['staff_id']}
        ";
        
        $resultStaff  = $db->sql_query($SQLStaff);
        $rowStaff = $db->sql_fetchrow($resultStaff);

        $fa = array();
        $fa['date']               = date('Y-m-d');
        $fa['title']              = $title;
        $fa['link']               = $link;
        $fa['due_date']           = $due_date;
        $fa['staff_ids']          = $staff_id;
        $fa['staff_id_created']   = $rowStaff['employee_id'];
        $fa['type']               = 'Task';
        $fa['creation_date']      = date('Y-m-d H:i:s');
        $fa['created_by']         = $_SESSION['userFullName'];

        $fn->addRecord($fa, 'opportunity_project_history');           

        return $validate->getSuccessMessageXML();
    }
    /**
     *
     */
    function getNotificationValidate() {
        $validate = Zend_Registry::get('validate');
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the description');
        $validate->validateData('staff_id', 'Please select the employee');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
}