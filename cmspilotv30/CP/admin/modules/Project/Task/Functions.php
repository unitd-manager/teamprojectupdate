<?
class CP_Admin_Modules_Project_Task_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('project_task');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new', 'search', 'export', 'printListPDF', 'reportsMenu')
           ,'relatedTables' => array('media', 'task_staff')
        ));
    }

    /**
     *
     */
    function setLocalArrayValues(){
        $tv = Zend_Registry::get('tv');

        array_push($tv['protSiteSpActionExceptions'], 'sendTaskUpdatesToPM');
    }

    /**
     *
     */
    function setReportsArray($repInst) {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $report = $fn->getReqParam('report');

        $repInst->setReportArrayObj('task', "taskList");
        $arr = &$repInst->reportsArray['task']['taskList'];
        $arr['jasperFileName'] = 'task_list.jasper';
        $arr['sendRecIds']     = true;
        $arr['sendSortOrder']  = true;
        $arr['outputFileName'] = $cpCfg['cp.companyName'] . '-Tasks-' . date('Ymd');

        $repInst->setReportArrayObj('task', "taskSummaryList");
        $arr = &$repInst->reportsArray['task']['taskSummaryList'];
        $arr['jasperFileName'] = 'task_summary_list.jasper';
        $arr['sendRecIds']     = true;
        $arr['outputFileName'] = $cpCfg['cp.companyName'] . '-Tasks-Summary-' . date('Ymd');

        if ($report == 'taskSummaryList') {
            $due_date_1 = $fn->getReqParam('due_date_1');
            $due_date_2 = $fn->getReqParam('due_date_2');
            $staff_id    = $fn->getReqParam('staff_id');
            $company_id  = $fn->getReqParam('company_id');
            $search_criteria_display = '';

            $due_date_disp = '';
            $staff_disp      = '';
            $company_disp    = '';

            if ($staff_id) {
                $staffRec = $fn->getRecordRowByID('staff', 'staff_id', $staff_id);
                $staff_disp = "Staff: {$staffRec['first_name']} {$staffRec['last_name']}\n";
            }
            if ($company_id) {
                $companyRec = $fn->getRecordRowByID('company', 'company_id', $company_id);
                $company_disp = "Company: {$companyRec['company_name']}\n";
            }


            $where_condition_staff = '';
            if ($staff_id != '') {
                $where_condition_staff = " AND s.staff_id = {$staff_id} ";
            }

            if ($due_date_1 != '' && $due_date_2 != '') {
                $due_date_disp = "{$due_date_1} to {$due_date_2}";
            } else if ($due_date_1 != '') {
                $due_date_disp = "{$due_date_1}";
            }

            if ($due_date_disp != '') {
                $due_date_disp = "Due date: {$due_date_disp}";
            }


            $search_criteria_display = $company_disp
                                     . $staff_disp
                                     . $due_date_disp
                                     ;
            if ($search_criteria_display == '') {
                $search_criteria_display = "all";
            }
            $search_criteria_display = "Search criteria:\n"
                                     . $search_criteria_display;

            $arr['extraParams']['search_criteria_display'] = $search_criteria_display;
            $arr['extraParams']['where_condition_staff']   = $where_condition_staff;
        }
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('project_task', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('project_task', 'project_timesheetLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'timesheet'
           ,'linkingType'           => 'portal'
           ,'displayTitleFieldName' => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'hasPortalEdit'         => 1
           ,'hasPortalDelete'       => 1
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('project_task', 'core_staffLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'task_staff'
           ,'displayTitleFieldName'  => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'showAnchorInLinkPortal' => 0
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('project_task', 'project_taskHistoryLink');

        $sqlStatus = $fn->getValueListSQL('taskHistoryStatus');
        $result = $db->sql_query($sqlStatus);
        $statusArr = $dbUtil->getResultsetAsArrayForForm($result);

        $inst->registerLinksArray($linkObj, array(
             'historyTableName'    => 'task_history'
            ,'linkingType'         => 'grid'
            ,'showLinkPanelInNew'  => 0
            ,'historyTableKeyField'=> 'task_history_id'
            ,'showLinkPanelInEdit' => 1
            ,'hasPortalEdit'       => 0
            ,'hasPortalDelete'     => 1
            ,'fieldlabel'          => array('Title', 'Status', 'Percentage', 'Sort', 'Comments')
            ,'fieldClassArray'     => array('w100', 'w100', 'w50 txtCenter', 'w50 txtCenter')
            ,'gridFieldTypeArray'  => array(
                 array('type' => 'textbox')
                ,array('type' => 'dropdown', 'ddArr' => $statusArr, 'useKey' => 0)
            )
            ,'showAnchorInLinkPortal' => false
        ));
    }

    /**
     *
     */
    function sendNotificationToAllTaskStaff($task_id, $isPortal = 0) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $domain = $_SERVER['HTTP_HOST'];

        $pageLink = "http://{$domain}/admin/index.php?_topRm=project&module=task&_action=detail&task_id={$task_id}";

        if ($isPortal == 0) {
            $this->setFields();
        } else {
            $this->setFields1();
        }

        $record_id = isset($_REQUEST['record_id'])        ? $_REQUEST['record_id']            : "";

        $SQL = "
        SELECT a.*,
        IF (a.opportunity_id IS NOT NULL AND a.opportunity_id != '', b.title, c.title) AS project_opp_title,
        IF (a.opportunity_id IS NOT NULL AND a.opportunity_id != '', j.company_name, g.company_name) AS project_opp_company,
        IF (a.opportunity_id IS NOT NULL AND a.opportunity_id != '', b.opportunity_code, c.project_code) AS project_opp_code,
        IF (a.opportunity_id IS NOT NULL AND a.opportunity_id != '', 'Opportunity', 'Project') AS project_or_opp,
        CONCAT_WS(' ', f.first_name, f.last_name) AS project_manager_name
        FROM
        task a
        LEFT JOIN (opportunity b) ON (a.opportunity_id = b.opportunity_id )
        LEFT JOIN (project c) ON (a.project_id         = c.project_id     )
        LEFT JOIN (staff f)   ON (a.project_manager_id = f.staff_id       )
        LEFT JOIN (company g) ON (c.company_id         = g.company_id     )
        LEFT JOIN (company j) ON (b.company_id         = j.company_id     )
        WHERE a.task_id = {$task_id}
        ";

        $result = $db->sql_query($SQL);
        $rowProject = $db->sql_fetchrow($result);

        $project_code = $rowProject['project_opp_code'];
        $company_name = $rowProject['project_opp_company'];

        $status = $this->fieldsArray['status'];
        $title = $this->fieldsArray['title'] ;
        $description = $this->fieldsArray['description'] ;
        $due_date = $this->fieldsArray['due_date'] ;
        $assigned_to = $this->getStaffNamesByTask($task_id) ;

        $subject = "Task: " . $this->fieldsArray['title'];

        $message = "<table cellpadding='5' width='500'>";
        $message .= $fn->getEmailRow("{$rowProject['project_or_opp']} Name", $rowProject['project_opp_title']);
        $message .= $fn->getEmailRow("{$rowProject['project_or_opp']} Code", $project_code);
        $message .= $fn->getEmailRow("Company Name", $company_name);
        $message .= $fn->getEmailRow("Task ID", $task_id);
        $message .= $fn->getEmailRow("Due date", $due_date);
        $message .= $fn->getEmailRow("Title", $title);
        $message .= $fn->getEmailRow("Description", $description);
        $message .= $fn->getEmailRow("Assigned To", $assigned_to);
        $message .= "</table>";
        $message .= "<br clear='all'><div style='font-style:italic;font-size:11px;'>Page Link : <a href='{$pageLink}'>{$pageLink}</a></div>";

        $SQL = "SELECT staff_id FROM task_staff WHERE task_id = {$task_id}";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $this->sendMailToStaff($row['staff_id'], $subject, $message);
        }
    }

    /**
     *
     */
    function getStaffDetailByID($staff_id) {
        $db = Zend_Registry::get('db');

        $SQL = "
        SELECT CONCAT_WS(' ', first_name, last_name) AS staff_name
              ,email
        FROM staff
        WHERE staff_id = {$staff_id}
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows > 0) {
            $row = $db->sql_fetchrow($result);
            return $row;
        }
    }

    /**
     *
     */
    function sendMailToStaff($staff_id, $subject, $message) {
        $fn = Zend_Registry::get('fn');

        $staff_detail = $this->getStaffDetailByID($staff_id);

        if (isset($staff_detail['email'])) {

            $toName = $staff_detail['staff_name'];
            $toEmail = $staff_detail['email'];

            $fromName  = $fn->getSessionParam('userFullName');
            $fromEmail = $fn->getSessionParam('email');

            $smtp = includeCPClass('Lib', 'smtp', 'CPSMTP');
            $error = $smtp->sendEmail($toName, $toEmail, $fromName, $fromEmail, $subject, $message);
        }
    }

    /**
     *
     */
    function sendNotificationToProjectManager($task_id, $status_prev, $isPortal = 0) {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $pageLink = "{$cpCfg['cp.siteUrl']}admin/index.php?_topRm=project&module=task&_action=detail&task_id={$task_id}";

        if ($isPortal == 0) {
            $this->setFields();
        }
        else {
            $this->setFields1();
        }

        $status = $this->fieldsArray['status'];
        $project_code = $this->fieldsArray['project_code'] ;
        $company_name = $this->fieldsArray['company_name'] ;
        $title = $this->fieldsArray['title'] ;
        $description = $this->fieldsArray['description'] ;
        $assigned_to = $this->getStaffNamesByTask($task_id) ;
        $project_manager_id = $this->fieldsArray['project_manager_id'];

        if ($project_manager_id > 0 && $status_prev != "Complete" && $status == "Complete") {

            $subject = "Task: " . $this->fieldsArray['title'];

            $message = "<table cellpadding='0' width='400'>";
            $message .= $fn->getEmailRow("Project Code", $project_code);
            $message .= $fn->getEmailRow("Company Name", $company_name);
            $message .= $fn->getEmailRow("Title", $title);
            $message .= $fn->getEmailRow("Description", $description);
            $message .= $fn->getEmailRow("Assigned To", $assigned_to);
            $message .= $fn->getEmailRow("Status", $status);
            $message .= "</table>";
            $message .= "<br clear='all'><div style='font-style:italic;font-size:11px;'>Page Link : <a href='{$pageLink}'>{$pageLink}</a></div>";

            $this->sendMailToStaff($project_manager_id, $subject, $message);
        }
    }
}