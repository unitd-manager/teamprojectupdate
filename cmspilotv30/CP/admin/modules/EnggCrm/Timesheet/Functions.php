<?
class CP_Admin_Modules_EnggCrm_Timesheet_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('enggCrm_timesheet');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new', 'search', 'export', 'printListPDF', 'reportsMenu')
        ));
    }

    /**
     *
     */
    function setReportsArray($repInst) {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $report = $fn->getReqParam('report');

        $repInst->setReportArrayObj('timesheet', "timesheetList");
        $arr = &$repInst->reportsArray['timesheet']['timesheetList'];
        $arr['jasperFileName'] = 'timesheet_list.jasper';
        $arr['sendRecIds']     = true;
        $arr['sendSortOrder']  = true;
        $arr['outputFileName'] = $cpCfg['cp.companyName'] . '-Timesheet-' . date('Ymd');

        $repInst->setReportArrayObj('timesheet', "timesheetSummaryList");
        $arr = &$repInst->reportsArray['timesheet']['timesheetSummaryList'];
        $arr['jasperFileName'] = 'timesheet_summary_list.jasper';
        $arr['sendRecIds']     = true;
        $arr['outputFileName'] = $cpCfg['cp.companyName'] . '-Timesheet-Summary-' . date('Ymd');

        $repInst->setReportArrayObj('timesheet', "timesheetSummaryListByDay");
        $arr = &$repInst->reportsArray['timesheet']['timesheetSummaryListByDay'];
        $arr['jasperFileName'] = 'timesheet_summary_list_by_day.jasper';
        $arr['sendRecIds']     = true;
        $arr['outputFileName'] = $cpCfg['cp.companyName'] . '-Timesheet-Summary-By-Day-' . date('Ymd');

        if ($report == 'timesheetSummaryList' || $report == 'timesheetSummaryListByDay') {
            $entry_date1 = $fn->getReqParam('entry_date_1');
            $entry_date2 = $fn->getReqParam('entry_date_2');
            $staff_id    = $fn->getReqParam('staff_id');
            $company_id  = $fn->getReqParam('company_id');
            $search_criteria_display = '';

            $entry_date_disp = '';
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

            if ($entry_date1 != '' && $entry_date2 != '') {
                $entry_date_disp = "{$entry_date1} to {$entry_date2}";
            } else if ($entry_date1 != '') {
                $entry_date_disp = "{$entry_date1}";
            }

            if ($entry_date_disp != '') {
                $entry_date_disp = "Entry date: {$entry_date_disp}";
            }

            $search_criteria_display = $company_disp
                                     . $staff_disp
                                     . $entry_date_disp
                                     ;
            if ($search_criteria_display == '') {
                $search_criteria_display = "all";
            }
            $search_criteria_display = "<font size='7'><b>Search criteria:</b></font>\n"
                                     . $search_criteria_display;

            $arr = &$repInst->reportsArray['timesheet'][$report];
            $arr['extraParams']['search_criteria_display'] = $search_criteria_display;
            $arr['extraParams']['where_condition_staff']   = $where_condition_staff;
        }
    }

    /**
     *
     */
    function refreshValuesBasedOnTimeRecord($timesheet_id, $excludeCurRec = 0){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $rec = $fn->getRecordRowByID('timesheet', 'timesheet_id', $timesheet_id);
        
        if (!is_array($rec)){
            return;
        }
        
        /**** record excluded if it is going to be deleted ********/
        $append = ($excludeCurRec == 1) ? "AND ts.timesheet_id != {$timesheet_id}" : '';
        
        if ($rec['project_id'] > 0) {

            $SQL = "
            UPDATE project p SET p.used_inhouse = 
                (
                SELECT SUM(total_cost) AS total_cost 
                FROM timesheet ts 
                WHERE ts.project_id = p.project_id
                      {$append}
                ) 
            WHERE p.project_id = {$rec['project_id']}
            ";
            $db->sql_query($SQL);

        } else if ($rec['opportunity_id'] > 0) {
            $SQL = "
            SELECT SUM(total_cost) as total_cost
            FROM timesheet
            WHERE opportunity_id = {$rec['opportunity_id']}
            ";
            $result = $db->sql_query($SQL);
            $row = $db->sql_fetchrow($result);
    
            $SQL = "
            UPDATE opportunity o SET o.opportunity_cost = 
                (
                SELECT SUM(total_cost) AS total_cost 
                FROM timesheet ts 
                WHERE ts.opportunity_id = o.opportunity_id
                      {$append}
                ) 
            WHERE o.opportunity_id = {$rec['opportunity_id']}
            ";
            $db->sql_query($SQL);
        }
    }

    /**
     *
     */
    function beforeDeleteHandler($timesheet_id){
        $this->refreshValuesBasedOnTimeRecord($timesheet_id, 1);
    }
}