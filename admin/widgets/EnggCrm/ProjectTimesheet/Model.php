<?
class CPL_Admin_Widgets_EnggCrm_ProjectTimesheet_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "";

        return $SQL;
    }
    
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;        
    }

    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enggCrm_projectQuote');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }

    /**
     * 
     */
    function getUpdateDetailsProjectTimeSheetDetails() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $project_id        = $fn->getReqParam('project_id');
        $employee_id       = $fn->getReqParam('employee_id');
        $admin_charges     = $fn->getReqParam('admin_charges');
        $transport_charges = $fn->getReqParam('transport_charges');
        $ratePerHR         = $fn->getReqParam('ratePerHR');
        $oTRatePerHR       = $fn->getReqParam('oTRatePerHR');
        $pHRatePerHR       = $fn->getReqParam('pHRatePerHR');
        $timesheet_type    = $fn->getReqParam('timesheet_type');
        $year              = $fn->getReqParam('year');
        $month             = $fn->getReqParam('month');

        $start_date = $year.'-'.$month.'-01';
        $end_date   = $year.'-'.$month.'-31';

        if($ratePerHR != "") {
            $sqlUpdate = "
            UPDATE employee_timesheet SET hourly_rate = '{$ratePerHR}'
            WHERE employee_id = '{$employee_id}'
              AND (date BETWEEN '{$start_date}' AND '{$end_date}')
              AND project_id = '{$project_id}'
            ";
            $resultUpdate = $db->sql_query($sqlUpdate);
        }

        if($oTRatePerHR != "") {
            $sqlUpdate = "
            UPDATE employee_timesheet SET ot_hourly_rate = '{$oTRatePerHR}'
            WHERE employee_id = '{$employee_id}'
              AND (date BETWEEN '{$start_date}' AND '{$end_date}')
              AND project_id = '{$project_id}'
            ";
            $resultUpdate = $db->sql_query($sqlUpdate);
        }

        if($ratePerHR != "") {
            $sqlUpdate = "
            UPDATE employee_timesheet SET ph_hourly_rate = '{$ratePerHR}'
            WHERE employee_id = '{$employee_id}'
              AND (date BETWEEN '{$start_date}' AND '{$end_date}')
              AND project_id = '{$project_id}'
            ";
            $resultUpdate = $db->sql_query($sqlUpdate);
        }

        if($admin_charges != "") {
            $sqlUpdate = "
            UPDATE employee_timesheet SET admin_charges = '{$admin_charges}'
            WHERE employee_id = '{$employee_id}'
              AND (date BETWEEN '{$start_date}' AND '{$end_date}')
              AND project_id = '{$project_id}'
            ";
            $resultUpdate = $db->sql_query($sqlUpdate);
        }

        if($transport_charges != "") {
            $sqlUpdate = "
            UPDATE employee_timesheet SET transport_charges = '{$transport_charges}'
            WHERE employee_id = '{$employee_id}'
              AND (date BETWEEN '{$start_date}' AND '{$end_date}')
              AND project_id = '{$project_id}'
            ";
            $resultUpdate = $db->sql_query($sqlUpdate);
        }
    }

    /**
     * 
     */
    function getUpdateTimeSheetSignStaff() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $project_id      = $fn->getReqParam('project_id');
        $month           = $fn->getReqParam('month');
        $year            = $fn->getReqParam('year');
        $timesheet_type  = $fn->getReqParam('timesheet_type');
        $sign_staff_id_1 = $fn->getReqParam('sign_staff_id_1');
        $sign_staff_id_2 = $fn->getReqParam('sign_staff_id_2');
        $sign_staff_id   = $fn->getReqParam('sign_staff_id');

        // Update Staff id in employee timesheet table
        if ($timesheet_type == 'Fortnightly') {
            $start_date1 = $year.'-'.$month.'-01';
            $end_date1   = $year.'-'.$month.'-15';

            $start_date2 = $year.'-'.$month.'-16';
            $end_date2   = $year.'-'.$month.'-31';

            $sqlUpdate1 = "
            UPDATE employee_timesheet SET sign_staff_id = {$sign_staff_id_1}
            WHERE (date BETWEEN '{$start_date1}' AND '{$end_date1}')
            AND project_id = '{$project_id}'
            ";
            $resultUpdate1 = $db->sql_query($sqlUpdate1);

            $sqlUpdate2 = "
            UPDATE employee_timesheet SET sign_staff_id = {$sign_staff_id_2}
            WHERE (date BETWEEN '{$start_date2}' AND '{$end_date2}')
            AND project_id = '{$project_id}'
            ";
            $resultUpdate2 = $db->sql_query($sqlUpdate2);
            
        } else {
            $start_date = $year.'-'.$month.'-01';
            $end_date   = $year.'-'.$month.'-31';

            $sqlUpdate = "
            UPDATE employee_timesheet SET sign_staff_id = {$sign_staff_id}
            WHERE (date BETWEEN '{$start_date}' AND '{$end_date}')
            AND project_id = '{$project_id}'
            ";
            $resultUpdate = $db->sql_query($sqlUpdate);
        }
    }

    /**
     * 
     */
    function getCreateUpdateEmployeeTimesheetRecordEdit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');

        $normalHours           = $fn->getReqParam('normalHours');
        $oTHours               = $fn->getReqParam('oTHours');
        $pHHours               = $fn->getReqParam('pHHours');
        $employee_id           = $fn->getReqParam('employee_id');
        $project_id            = $fn->getReqParam('project_id');
        $timeSheetDate         = $fn->getReqParam('timeSheetDate');
        $month                 = $fn->getReqParam('month');
        $year                  = $fn->getReqParam('year');
        $ratePerHR             = $fn->getReqParam('ratePerHR');
        $oTRatePerHR           = $fn->getReqParam('oTRatePerHR');
        $pHRatePerHR           = $fn->getReqParam('pHRatePerHR');
        $admin_charges         = $fn->getReqParam('admin_charges');
        $transport_charges     = $fn->getReqParam('transport_charges');
        $employee_timesheet_id = $fn->getReqParam('employee_timesheet_id');
        $timesheet_type        = $fn->getReqParam('timesheet_type');
        $sign_staff_id_1       = $fn->getReqParam('sign_staff_id_1');
        $sign_staff_id_2       = $fn->getReqParam('sign_staff_id_2');
        $sign_staff_id         = $fn->getReqParam('sign_staff_id');
        $payroll_management_id = $fn->getReqParam('payroll_management_id');

        $fa = array();
        $fa['project_id']        = $project_id;
        $fa['employee_id']       = $employee_id;
        $fa['employee_hours']    = $normalHours;
        $fa['employee_ot_hours'] = $oTHours;        
        $fa['employee_ph_hours'] = $pHHours;
        $fa['date']              = $timeSheetDate;
        $fa['hourly_rate']       = $ratePerHR;
        $fa['ot_hourly_rate']    = $oTRatePerHR;
        $fa['ph_hourly_rate']    = $pHRatePerHR;
        $fa['month']             = $month;
        $fa['year']              = $year;
        $fa['admin_charges']     = $admin_charges; 
        $fa['transport_charges'] = $transport_charges;

        if($employee_timesheet_id != "") {
            $fa['modification_date'] = date('Y-m-d H:i:s');
            $whereCondition = "WHERE employee_timesheet_id = '{$employee_timesheet_id}'";
            $updateSQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'employee_timesheet', $whereCondition);
            $resultSQL = $db->sql_query($updateSQL);
        } else {
            $sqlEmpTimeSheet = "
            SELECT employee_timesheet_id 
            FROM employee_timesheet
            WHERE project_id  = '{$project_id}'
              AND employee_id = '{$employee_id}'
              AND `date`      = '{$timeSheetDate}'
            ";
            $resultEmpTimeSheet  = $db->sql_query($sqlEmpTimeSheet);
            $numRowsEmpTimeSheet = $db->sql_numrows($resultEmpTimeSheet);
            if($numRowsEmpTimeSheet > 0) {
                $rowEmpTimeSheet    = $db->sql_fetchrow($resultEmpTimeSheet);
                $employee_timesheet_id   = $rowEmpTimeSheet['employee_timesheet_id'];
                $fa['modification_date'] = date('Y-m-d H:i:s');
                $whereCondition = "WHERE employee_timesheet_id = '{$employee_timesheet_id}'";
                $updateSQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'employee_timesheet', $whereCondition);
                $resultSQL = $db->sql_query($updateSQL);    
            } else {
                $fa['creation_date'] = date('Y-m-d H:i:s');
                $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'employee_timesheet');
                $result = $db->sql_query($insert);
                $employee_timesheet_id = $db->sql_nextid();
            }
        }

        // Update Staff id in employee timesheet table
        /*
        if ($timesheet_type == 'Fortnightly') {
            $start_date1 = $year.'-'.$month.'-01';
            $end_date1   = $year.'-'.$month.'-15';

            $start_date2 = $year.'-'.$month.'-16';
            $end_date2   = $year.'-'.$month.'-31';

            $sqlUpdate1 = "
            UPDATE employee_timesheet SET sign_staff_id = {$sign_staff_id_1}
            WHERE (date BETWEEN '{$start_date1}' AND '{$end_date1}')
            AND project_id = '{$project_id}'
            ";
            $resultUpdate1 = $db->sql_query($sqlUpdate1);

            $sqlUpdate2 = "
            UPDATE employee_timesheet SET sign_staff_id = {$sign_staff_id_2}
            WHERE (date BETWEEN '{$start_date2}' AND '{$end_date2}')
            AND project_id = '{$project_id}'
            ";
            $resultUpdate2 = $db->sql_query($sqlUpdate2);
        } else {
            $start_date = $year.'-'.$month.'-01';
            $end_date   = $year.'-'.$month.'-31';

            $sqlUpdate = "
            UPDATE employee_timesheet SET sign_staff_id = {$sign_staff_id}
            WHERE (date BETWEEN '{$start_date}' AND '{$end_date}')
            AND project_id = '{$project_id}'
            ";
            $resultUpdate = $db->sql_query($sqlUpdate);
        }
        */

        if ($payroll_management_id > 0) {
            $monthCheck = $month;
            if($month < 10) {
                $monthCheck = '0'.$month;
            }

            $SQLEmpTmS = "
            SELECT SUM(employee_hours)As employee_total_hrs
                  ,SUM(employee_ot_hours) AS totalOTHours
                  ,SUM(employee_ph_hours) AS totalPHHours
            FROM employee_timesheet             
            WHERE employee_id = {$employee_id}
            AND DATE_FORMAT(date, '%Y-%m') = '{$year}-{$monthCheck}'
            ";
            $resultEmpTmS = $db->sql_query($SQLEmpTmS);
            $rowEmpTmS    = $db->sql_fetchrow($resultEmpTmS);

            $rowPm = $fn->getRecordRowByID('payroll_management', 'payroll_management_id', $payroll_management_id);
            $faPm = array();
            $faPm['flag']               = 1;
            $faPm['total_normal_hours'] = $rowEmpTmS['employee_total_hrs'];
            $faPm['total_ot_hours']     = $rowEmpTmS['totalOTHours'];
            $faPm['total_ph_hours']     = $rowEmpTmS['totalPHHours'];
            
            //A Checking if Payment method is Hourly for Employee and updating Basic pay in Payroll Management table
            $appendSqlSite = "";
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSqlSite = " AND j.site_id = {$cpSiteIdSession}";
            }
            $sqlJi = "
            SELECT j.payment_type FROM job_information j
            LEFT JOIN (employee e) ON (j.employee_id = e.employee_id)
            WHERE j.employee_id = {$rowPm['employee_id']}
              AND e.status = 'Current'
              AND j.status = 'Current'
              AND j.termination_date IS NULL
              {$appendSqlSite}
            ";
            $resultJi = $db->sql_query($sqlJi);
            $rowJi    = $db->sql_fetchrow($resultJi);

            if ($rowJi['payment_type'] == 'Hourly') {
                $faPm['basic_pay'] = $rowEmpTmS['employee_total_hrs'] * $rowPm['hourly_pay_rate'];
            }

            $whereCondition = "WHERE payroll_management_id = {$payroll_management_id}";
            $sqlPmUpdate    = $dbUtil->getUpdateSQLStringFromArray($faPm, "payroll_management", $whereCondition);
            $resultPmUpdate = $db->sql_query($sqlPmUpdate);
        }

        return $employee_timesheet_id;
    }
}