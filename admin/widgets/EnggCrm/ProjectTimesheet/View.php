<?
class CPL_Admin_Widgets_EnggCrm_ProjectTimesheet_View extends CP_Common_Lib_WidgetViewAbstract {
    /**
     *
    */
    function getWidget() {
        $text = '';
        return $text;
    }

    /**
     *
     */
    function getEmploymentTimeSheetPopupView($project_id) {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT employee_id
        FROM `project_employee`
        WHERE project_id = {$project_id}
        ";

        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $text = "";

        if($numRows > 0){
            $text = "
            <div class='button mb5'>
                <a href='#' class='addTimesheetForProjectEmployee' project_id='{$project_id}'>New Timesheet</a>
            </div>
            <div class='timesheetData'>{$this->getEmploymentTimeSheetNewAllView($project_id)}</div>
            ";
        }

        return $text;
    }

    /**
       *
    */
    function getEmploymentTimeSheetNewAllView($project_id = '') {
        $db     = Zend_Registry::get('db');
        $fn     = Zend_Registry::get('fn');
        $cpCfg  = Zend_Registry::get('cpCfg');

        if ($project_id == '') {
            $project_id = $fn->getReqParam('project_id');
        }

        $quoteRec = $fn->getRecordRowByID('quote', 'project_id', $project_id);

        $SQL = "
        SELECT DATE_FORMAT(et.date, '%Y-%m') AS dateMonth
              ,DATE_FORMAT(et.date, '%M') AS Month
              ,DATE_FORMAT(et.date, '%m') AS month_req
              ,DATE_FORMAT(et.date, '%Y') AS year_req
              ,DATE_FORMAT(et.date, '%Y-%m') AS year_Months
              ,SUM(et.employee_hours) AS totalHours
              ,SUM(et.employee_ot_hours) AS totalOTHours
              ,SUM(et.employee_ph_hours) AS totalPHHours
              ,et.project_id
        FROM employee_timesheet et
        WHERE et.project_id = {$project_id}
        GROUP BY DATE_FORMAT(et.date, '%Y-%m')
        ORDER BY et.date DESC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows = '';
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
                $editLink = "<a project_id={$project_id} year={$row['year_req']} month={$row['month_req']} class='editTimesheetForProjectEmployee' title='Edit'><img src='../admin/images/edit.png' class='icon'></a>";

                $SQLAmount = "
                SELECT CAST(SUM(et.employee_hours * et.hourly_rate) AS Decimal (18,2)) AS totalAmount
                      ,CAST(SUM(et.employee_ot_hours * et.ot_hourly_rate) AS Decimal (18,2)) AS totalOTAmount
                      ,CAST(SUM(et.employee_ph_hours * et.ph_hourly_rate) AS Decimal (18,2)) AS totalPHAmount
                FROM employee_timesheet et
                WHERE et.project_id = '{$row['project_id']}'
                  AND DATE_FORMAT(et.date, '%Y-%m') = '{$row['dateMonth']}'
                GROUP BY et.employee_id
                ";
                $resultAmount = $db->sql_query($SQLAmount);
                $totalAmount   = 0;
                $totalOTAmount = 0;
                $totalPHAmount = 0;
                while ($rowAmount = $db->sql_fetchrow($resultAmount)) {
                    $totalAmount   += $rowAmount['totalAmount'];
                    $totalOTAmount += $rowAmount['totalOTAmount'];
                    $totalPHAmount += $rowAmount['totalPHAmount'];
                }

                $SQL2 = "
                SELECT e.employee_name
                      ,e.employee_id
                      ,et.admin_charges
                      ,et.transport_charges
                FROM employee_timesheet et
                LEFT JOIN employee e ON(e.employee_id = et.employee_id)
                WHERE et.project_id = {$project_id}
                AND DATE_FORMAT(date, '%Y-%m') = '{$row['year_Months']}'
                GROUP BY et.employee_id
                ";

                $result2 = $db->sql_query($SQL2);
                $rows2 = '';
                $admin_charges     = 0;
                $transport_charges = 0;
                while ($row2 = $db->sql_fetchrow($result2)) {
                  $SQLArchiveCheck = "
                  SELECT SUM( et.employee_hours ) AS totalHrs
                       , e.status
                       , pe.active_in_project
                  FROM `employee_timesheet` et
                  LEFT JOIN project_employee pe ON (pe.project_id = et.project_id)
                  LEFT JOIN employee e ON (e.employee_id = pe.employee_id)
                  WHERE et.employee_id = {$row2['employee_id']}
                  AND pe.employee_id   = {$row2['employee_id']}
                  AND et.project_id    = {$project_id}
                  AND DATE_FORMAT(et.date, '%Y-%m') = '{$row['year_Months']}'
                  ";
                  $resultArchiveCheck = $db->sql_query($SQLArchiveCheck);
                  $rowArchiveCheck    = $db->sql_fetchrow($resultArchiveCheck);
                  if($rowArchiveCheck['totalHrs'] == '' && $rowArchiveCheck['status'] == 'Archive'){
                  }else if($rowArchiveCheck['totalHrs'] == '' && $rowArchiveCheck['active_in_project'] == 0){
                  }else{
                    $rows2 .= "{$this->getTimeSheetByEmployee($project_id, $row2['employee_id'], $row['year_Months'])}";
                    $urlprintTimeSheetPdf = "index.php?widget=enggCrm_projectTimesheet&_spAction=printTimeSheetPdf&project_id={$project_id}&year={$row['year_req']}&month={$row['month_req']}&showHTML=0";

                    $urlprintSummaryPdf = "index.php?widget=enggCrm_projectTimesheet&_spAction=printSummaryPdf&project_id={$project_id}&year={$row['year_req']}&month={$row['month_req']}&showHTML=0";

                    $urlprintTimeSheet1Pdf = "index.php?_topRm=project&module=enggCrm_project&_spAction=printTimeSheet1Pdf&project_id={$project_id}&year={$row['year_req']}&month={$row['month_req']}&showHTML=0";

                    $urlprintTimeSheet2Pdf = "index.php?_topRm=project&module=enggCrm_project&_spAction=printTimeSheet2Pdf&project_id={$project_id}&year={$row['year_req']}&month={$row['month_req']}&showHTML=0";

                    $admin_charges     += $row2['admin_charges'];
                    $transport_charges += $row2['transport_charges'];
                  }
                }
                
                $urlPrintquotecolumnLinkPdf  = "index.php?_topRm=project&module=enggCrm_project&_spAction=printQuoteDisplayPdf&project_id={$project_id}&year={$row['year_req']}&month={$row['month_req']}&showHTML=0";

                if ($quoteRec['timesheet_type'] != 'Monthly') {
                    $timesheetPrint = "
                    <div class='float_left printTimeSheetPdf'>
                        <a href='{$urlprintTimeSheetPdf}' target='_blank' title='PrintTimesheet'><img src='../admin/images/icon-print.ico' class='icon'></a>
                    </div>
                    <div class='float_left printSummaryPdf'>
                        <a href='{$urlprintSummaryPdf}' target='_blank' title='Print Summary'><img src='../admin/images/icon-print.ico' class='icon'></a>
                    </div>
                    ";
                } else {
                    $urlprintTimeSheet1Pdf = "index.php?_topRm=project&module=enggCrm_project&_spAction=printTimeSheet1Pdf&project_id={$project_id}&year={$row['year_req']}&month={$row['month_req']}&showHTML=0";
                    $urlprintSummary1Pdf = "index.php?_topRm=project&module=enggCrm_project&_spAction=printSummary1Pdf&project_id={$project_id}&year={$row['year_req']}&month={$row['month_req']}&showHTML=0";
                    $urlprintTimeSheet2Pdf = "index.php?_topRm=project&module=enggCrm_project&_spAction=printTimeSheet2Pdf&project_id={$project_id}&year={$row['year_req']}&month={$row['month_req']}&showHTML=0";
                    $urlprintSummary2Pdf = "index.php?_topRm=project&module=enggCrm_project&_spAction=printSummary2Pdf&project_id={$project_id}&year={$row['year_req']}&month={$row['month_req']}&showHTML=0";

                    $timesheetPrint = "
                    <div class = 'floatbox'>
                        <div class='float_left printTimeSheetPdf'>
                            <a href='{$urlprintTimeSheet1Pdf}' target='_blank' title='Print 1st Half Timesheet'><img src='../admin/images/icon-print.ico' class='icon'></a>
                        </div>
                        <div class='float_left printSummaryPdf'>
                            <a href='{$urlprintSummary1Pdf}' target='_blank' title='Print 1st Half Summary'><img src='../admin/images/icon-print.ico' class='icon'></a>
                        </div>
                    </div>
                    <div class = 'floatbox'>
                        <div class='float_left printTimeSheetPdf'>
                            <a href='{$urlprintTimeSheet2Pdf}' target='_blank' title='Print 2nd Half Timesheet'><img src='../admin/images/icon-print.ico' class='icon'></a>
                        </div>
                        <div class='float_left printSummaryPdf'>
                            <a href='{$urlprintSummary2Pdf}' target='_blank' title='Print 2nd Half Summary'><img src='../admin/images/icon-print.ico' class='icon'></a>
                        </div>
                    </div>
                    ";
                }

                $urlStaffForMonthDetails = "index.php?widget=enggCrm_projectTimesheet&_spAction=employeeForMonthDetails&project_id={$project_id}&year_month={$row['year_Months']}&showHTML=0";

                $addEmployeeLineItemView = $timesheetPrint . "
                <!--<div class='float_left'><u><a class='employeeListShow' href='#'>View Staff</a></u>
                </div>-->
                <div class='float_left'>
                    <a class='viewStaffForMonthDetails jqui-dialog' href='{$urlStaffForMonthDetails}'><u>Details</u></a>
                </div>
                <!--<div class='float_left printLink'>
                    <a href='{$urlPrintquotecolumnLinkPdf}' target='_blank'>Manpower display pdf</a>
                </div>-->
                ";

                $overallTotalAmount = $totalAmount + $totalOTAmount + $totalPHAmount + $admin_charges + $transport_charges;
                $overallTotalAmount = number_format($overallTotalAmount, 2);

                if ($quoteRec['timesheet_type'] == 'Fortnightly') {
                    $sqlFirstHalfMonth = "
                    SELECT SUM(et.employee_hours) AS totalHours
                          ,SUM(et.employee_ot_hours) AS totalOTHours
                          ,SUM(et.employee_ph_hours) AS totalPHHours
                          ,et.project_id
                    FROM employee_timesheet et
                    WHERE et.project_id = {$project_id}
                      AND et.date BETWEEN '{$row['year_req']}-{$row['month_req']}-01' AND '{$row['year_req']}-{$row['month_req']}-15'
                    ";
                    $resultFirstHalfMonth = $db->sql_query($sqlFirstHalfMonth);
                    $rowFirstHalfMonth    = $db->sql_fetchrow($resultFirstHalfMonth);

                    $totalHoursNormal1  = number_format($rowFirstHalfMonth['totalHours'],2);
                    $totalOTHours1      = number_format($rowFirstHalfMonth['totalOTHours'],2);
                    $totalPHHours1      = number_format($rowFirstHalfMonth['totalPHHours'],2);

                    $sqlFirstHalfMonthAmount = "
                    SELECT CAST(SUM(et.employee_hours * et.hourly_rate) AS Decimal (18,2)) AS totalAmount1
                          ,CAST(SUM(et.employee_ot_hours * et.ot_hourly_rate) AS Decimal (18,2)) AS totalOTAmount1
                          ,CAST(SUM(et.employee_ph_hours * et.ph_hourly_rate) AS Decimal (18,2)) AS totalPHAmount1
                    FROM employee_timesheet et
                    WHERE et.project_id = '{$row['project_id']}'
                      AND et.date BETWEEN '{$row['year_req']}-{$row['month_req']}-01' AND '{$row['year_req']}-{$row['month_req']}-15'
                    GROUP BY et.employee_id
                    ";
                    $resultFirstHalfMonthAmount = $db->sql_query($sqlFirstHalfMonthAmount);
                    $totalAmount1   = 0;
                    $totalOTAmount1 = 0;
                    $totalPHAmount1 = 0;
                    while ($rowFirstHalfMonthAmount = $db->sql_fetchrow($resultFirstHalfMonthAmount)) {
                        $totalAmount1   += $rowFirstHalfMonthAmount['totalAmount1'];
                        $totalOTAmount1 += $rowFirstHalfMonthAmount['totalOTAmount1'];
                        $totalPHAmount1 += $rowFirstHalfMonthAmount['totalPHAmount1'];
                    }
                    $overallTotalAmount1 = $totalAmount1 + $totalOTAmount1 + $totalPHAmount1 + $admin_charges + $transport_charges;
                    $overallTotalAmount1 = number_format($overallTotalAmount1, 2);

                    // SECOND HALF DATA STARTS
                    $sqlSecondHalfMonth = "
                    SELECT SUM(et.employee_hours) AS totalHours
                          ,SUM(et.employee_ot_hours) AS totalOTHours
                          ,SUM(et.employee_ph_hours) AS totalPHHours
                          ,et.project_id
                    FROM employee_timesheet et
                    WHERE et.project_id = {$project_id}
                      AND et.date BETWEEN '{$row['year_req']}-{$row['month_req']}-16' AND '{$row['year_req']}-{$row['month_req']}-31'
                    ";
                    $resultSecondHalfMonth = $db->sql_query($sqlSecondHalfMonth);
                    $rowSecondHalfMonth    = $db->sql_fetchrow($resultSecondHalfMonth);

                    $totalHoursNormal2 = number_format($rowSecondHalfMonth['totalHours'],2);
                    $totalOTHours2 = number_format($rowSecondHalfMonth['totalOTHours'],2);
                    $totalPHHours2 = number_format($rowSecondHalfMonth['totalPHHours'],2);

                    $sqlSecondHalfMonthAmount = "
                    SELECT CAST(SUM(et.employee_hours * et.hourly_rate) AS Decimal (18,2)) AS totalAmount2
                          ,CAST(SUM(et.employee_ot_hours * et.ot_hourly_rate) AS Decimal (18,2)) AS totalOTAmount2
                          ,CAST(SUM(et.employee_ph_hours * et.ph_hourly_rate) AS Decimal (18,2)) AS totalPHAmount2
                    FROM employee_timesheet et
                    WHERE et.project_id = '{$row['project_id']}'
                      AND et.date BETWEEN '{$row['year_req']}-{$row['month_req']}-16' AND '{$row['year_req']}-{$row['month_req']}-31'
                    GROUP BY et.employee_id
                    ";
                    $resultSecondHalfMonthAmount = $db->sql_query($sqlSecondHalfMonthAmount);
                    $totalAmount2   = 0;
                    $totalOTAmount2 = 0;
                    $totalPHAmount2 = 0;
                    while ($rowSecondHalfMonthAmount = $db->sql_fetchrow($resultSecondHalfMonthAmount)) {
                        $totalAmount2   += $rowSecondHalfMonthAmount['totalAmount2'];
                        $totalOTAmount2 += $rowSecondHalfMonthAmount['totalOTAmount2'];
                        $totalPHAmount2 += $rowSecondHalfMonthAmount['totalPHAmount2'];
                    }
                    $overallTotalAmount2 = $totalAmount2 + $totalOTAmount2 + $totalPHAmount2 + $admin_charges + $transport_charges;
                    $overallTotalAmount2 = number_format($overallTotalAmount2, 2);
                    // SECOND HALF DATA ENDS

                    $month = "1st Half {$row['Month']}<br/><br/>2nd Half {$row['Month']}";
                    $totalHoursNormal = $totalHoursNormal1 . "<br/><br/>" . $totalHoursNormal2;
                    $totalOTHours = $totalOTHours1 . "<br/><br/>" . $totalOTHours2;
                    $totalPHHours = $totalPHHours1 . "<br/><br/>" . $totalPHHours2;
                    $overallTotalAmount = $overallTotalAmount1 . "<br/><br/>" . $overallTotalAmount2;
                } else {
                    $sqlWholeMonth = "
                    SELECT SUM(et.employee_hours) AS totalHours
                          ,SUM(et.employee_ot_hours) AS totalOTHours
                          ,SUM(et.employee_ph_hours) AS totalPHHours
                          ,et.project_id
                    FROM employee_timesheet et
                    WHERE et.project_id = {$project_id}
                    GROUP BY DATE_FORMAT(et.date, '%Y-%m')
                    ";
                    $resultWholeMonth = $db->sql_query($sqlWholeMonth);
                    $rowWholeMonth    = $db->sql_fetchrow($resultWholeMonth);

                    $month = $row['Month'];
                    $totalHoursNormal = number_format($rowWholeMonth['totalHours'],2);
                    $totalOTHours = number_format($rowWholeMonth['totalOTHours'],2);
                    $totalPHHours = number_format($rowWholeMonth['totalPHHours'],2);
                    $overallTotalAmount = $overallTotalAmount;
                }

                $reminder = $numRows % 2 ;

                $addClass = 'portal-row2';
                if ($reminder == 0) {
                    $addClass = 'portal-row1';
                }

                $rows .= "
                <tbody class='employeeMonthRow'>
                    <tr class='addEmployeeRow1 {$addClass}'>
                        <td>{$month}</td>
                        <td class='txtCenter'>{$totalHoursNormal}</td>
                        <td class='txtCenter'>{$totalOTHours}</td>
                        <td class='txtCenter'>{$totalPHHours}</td>
                        <td class='txtRight'>{$overallTotalAmount}</td>
                        <td>
                            <div class='floatbox'>
                                <div class='float_left'><u>{$editLink}</u></div>
                                {$addEmployeeLineItemView}
                            </div>
                        </td>
                    </tr>
                </tbody>
                ";
                $count++;
            }

            $text = '';

            $urlOverAllPrintEmployeePdf  = "index.php?_topRm=project&module=enggCrm_project&_spAction=printOverAllEmployeeTimesheetForPdf&project_id={$project_id}&employee_id={$row['employee_id']}&showHTML=0";

            $overAllTimeSheetPdf = "
            <div class='float_right printTimeSheetPdf'>
                <a href='{$urlOverAllPrintEmployeePdf}' target='_blank'>Over All Print Timesheet</a>
             </div>
             ";

            if ($numRows > 0)  {
            $text = "
            <div id='employeePortal' class='linkPortalWrapper'>
                <table class='list'>
                    <thead>
                        <tr>
                            <th colspan='6' align='left'>Employee Time Sheet</th>
                        </tr>
                        <tr>
                            <th width='20%'>Month</th>
                            <th width='12%' class='txtCenter'>Total Hours</th>
                            <th width='12%' class='txtCenter'>Total OT Hours</th>
                            <th width='12%' class='txtCenter'>Total PH Hours</th>
                            <th width='12%' class='txtRight'>Amount</th>
                            <th width='32%'>Action</th>
                        </tr>
                    </thead>
                    {$rows}
                </table>
            </div>
            ";

            return $text;
        }
    }

    /**
     *
    */
    function getTimeSheetByEmployee($project_id, $employee_id, $year_Months) {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT e.employee_id
              ,SUM(employee_hours)As employee_total_hrs
              ,SUM(employee_ot_hours) AS totalOTHours
              ,SUM(employee_ph_hours) AS totalPHHours
              ,SUM(employee_ot_hours*ot_hourly_rate) AS totalOTAmount
              ,SUM(employee_ph_hours*ph_hourly_rate) AS totalPHAmount
              ,e.first_name
              ,e.employee_work_type
              ,et.admin_charges
              ,et.transport_charges
              ,et.hourly_rate AS add_hourly_rate
        FROM employee_timesheet et
        LEFT JOIN (employee e) ON (e.employee_id = et.employee_id)
        WHERE et.project_id = {$project_id}
        AND et.employee_id = {$employee_id}
        AND DATE_FORMAT(date, '%Y-%m') = '{$year_Months}'
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $row = $db->sql_fetchrow($result);

        $urlPrintEmployeePdf = "index.php?_topRm=project&module=enggCrm_project&_spAction=printEmployeeTimesheetForPdf&project_id={$project_id}&employee_id={$row['employee_id']}&showHTML=0";

        $addEmployeeLineItemView = '';
        if($row['employee_total_hrs'] > 0 ) {
            $urlHourDetails = "index.php?widget=enggCrm_projectTimesheet&_spAction=employeeAddTimeHoursNewListView&project_id={$project_id}&employee_id={$row['employee_id']}&year_Months={$year_Months}&showHTML=0";

            $addEmployeeLineItemView ="
            <div class='float_right'>
                <a class='viewStaffHourDetails jqui-dialog' href='{$urlHourDetails}'><u>More</u></a>
            </div>
            ";
        }

        $amount = ($row['employee_total_hrs'] * $row['add_hourly_rate']);
        $amount = $amount + $row['totalOTAmount'] + $row['totalPHAmount'] + $row['admin_charges'] + $row['transport_charges'];
        $amount = number_format($amount ,2);

        $rows = "
        <tr>
            <td>{$row['first_name']}</td>
            <td class='txtCenter'>{$row['employee_total_hrs']}</td>
            <td class='txtCenter'>{$row['totalOTHours']}</td>
            <td class='txtCenter'>{$row['totalPHHours']}</td>
            <td class='txtRight'>{$amount}</td>
            <td class='viewRowWidth txtRight'>{$addEmployeeLineItemView}</td>
        </tr>
        ";

        $text = '';

        if ($numRows > 0)  {
            $text = "
            {$rows}
            ";

           return $text;
        }
    }

    /**
     *
    */
    function getEmployeeAddTimeHoursNewListView() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $project_id  = $fn->getReqParam('project_id');
        $employee_id = $fn->getReqParam('employee_id');
        $year_Months = $fn->getReqParam('year_Months');

        $employeeRec = $fn->getRecordRowByID('employee', 'employee_id', $employee_id);
        $SQL = "
        SELECT et.*
        FROM employee_timesheet et
        WHERE et.project_id = {$project_id}
        AND et.employee_id = {$employee_id}
        AND DATE_FORMAT(et.date, '%Y-%m') = '{$year_Months}'
        ORDER BY et.date ASC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows = '';
        $text = '';
        $total_normal_hours = 0;
        $total_ot_hours = 0;
        $total_sunPh_hours = 0;

        while ($row = $db->sql_fetchrow($result)) {
            $employee_date   = $fn->getCPDate($row['date'], 'd-m-Y');

            $editEmployeeView = "index.php?_topRm=project&module=enggCrm_project&_spAction=editEmploymentViewItem&project_id={$project_id}&employee_id={$row['employee_id']}&employee_timesheet_id={$row['employee_timesheet_id']}&showHTML=0";

            $editEmployeeView = "
            <div class='float_left'>
                <a class='editForEmployeeItemView' href='{$editEmployeeView}'>Edit</a>
            </div>
            ";

            $deleteEmployeeView = "
            <div class='float_right'>
                <a  class='deleteForEmployeeItemView' project_id='{$row['project_id']}' employee_id= '{$row['employee_id']}' employee_timesheet_id={$row['employee_timesheet_id']}>Delete</a></td>
            </div>
            ";

            $rows .= "
            <tr class = 'employeeItemBackgroundSecond'>
                <td>{$employee_date}</td>
                <td class='txtCenter'>{$row['employee_hours']}</td>
                <td class='txtCenter'>{$row['employee_ot_hours']}</td>
                <td class='txtCenter'>{$row['employee_ph_hours']}</td>
            </tr>
            ";

            $total_normal_hours += $row['employee_hours'];
            $total_ot_hours += $row['employee_ot_hours'];
            $total_sunPh_hours += $row['employee_ph_hours'];
        }

        $formatted_total_normal_hours = number_format($total_normal_hours, 2);
        $formatted_total_ot_hours = number_format($total_ot_hours, 2);
        $formatted_total_sunPh_hours = number_format($total_sunPh_hours, 2);

        if ($numRows > 0)  {
            $employee_name = strtoupper($employeeRec['first_name']);
            $text = "
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th colspan='4' class='txtCenter' style='background-color:#17375E !important; color: #fff !important;'><b>{$employee_name}</b></th>
                    </tr>
                    <tr class='employeeTrTh'>
                        <th width='25%'>Date</th>
                        <th width='25%' class='txtCenter'>Hours</th>
                        <th width='25%' class='txtCenter'>OT Hours</th>
                        <th width='25%' class='txtCenter'>PH Hours</th>
                    </tr>
                </thead>
                {$rows}
                <tr style='font-weight:bold;text-align:center;'>
                    <td class='txtRight'>Total Hours</td>
                    <td>{$formatted_total_normal_hours}</td>
                    <td>{$formatted_total_ot_hours}</td>
                    <td>{$formatted_total_sunPh_hours}</td>
                </tr>
            </table>
            ";

            return $text;
        }
    }

    /**
     *
    */
    function getAddHoursProjectEmployee($project_id = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        if($project_id == ''){
            $project_id = $fn->getReqParam('project_id');
        }
        $quoteRec   = $fn->getRecordRowByID('quote', 'project_id', $project_id);
        $sqlStaff   = "SELECT employee_id ,CONCAT_WS(' ', first_name, last_name) AS title FROM employee WHERE admin_staff = 1 ORDER BY CONCAT_WS(' ', first_name, last_name);";

        if($quoteRec['timesheet_type'] == 'Fortnightly') {
            $sign_staff_id = "
            <div class='float_left timesheetStaff'>
                <div class='float_left'>{$formObj->getDDRowBySQL('Timesheet 1 Sign *', 'sign_staff_id_1', $sqlStaff)}</div>
                <div class='clearfix'>{$formObj->getDDRowBySQL('Timesheet 2 Sign *', 'sign_staff_id_2', $sqlStaff)}</div>
            </div>
            ";
        } else {
            $sign_staff_id = "<div class='float_left'>{$formObj->getDDRowBySQL('Timesheet Sign *', 'sign_staff_id', $sqlStaff, '')}</div>";
        }

        $currentYear  = date("Y");
        $currentMonth = date("n");

        $yearArray = array( date("Y") - 1
                          , $currentYear
                          , date("Y") + 1
                     );

        $exp = array(
            'hideFirstOption' => true
        );

        $expmonth = array(
            'hideFirstOption' => true,
            'useKey' => true
        );

        $monthArray = array(
                         1 => 'January'
                        ,2 => 'February'
                        ,3 => 'March'
                        ,4 => 'April'
                        ,5 => 'May'
                        ,6 => 'June'
                        ,7 => 'July'
                        ,8 => 'August'
                        ,9 => 'September'
                        ,10 => 'October'
                        ,11 => 'November'
                        ,12 => 'December'
                      );

        $SQLCheckMonth = "
        SELECT month
              ,DATE_FORMAT(date, '%c') AS Month
        FROM `employee_timesheet`
        WHERE project_id = {$project_id}
        GROUP BY month,project_id
        ";
        $resultCheckMonth    = $db->sql_query($SQLCheckMonth);
        $dataArrayCheckMonth = $dbUtil->getResultsetAsArrayForForm($resultCheckMonth);

        $monthResultArray = array_diff_key($monthArray, $dataArrayCheckMonth);

        $yearRow  = "{$formObj->getDropDownByArray('Year', 'project_time_year', $yearArray, $currentYear, $exp)}";
        $MonthRow = "{$formObj->getDropDownByArray('Month', 'project_time_month', $monthArray, $currentMonth, $expmonth)}";

        $formAction = "index.php?_topRm=project&module=enggCrm_project&_spAction=addMultipleTimesheetRecordsSubmit&project_id={$project_id}&showHTML=0";
        $expEdit = array('isEditable' => 0);

        $text = "
        <form id='addMultipleHoursEmployeeForm' class='addMultipleHoursEmployeeForm' method='post' action='{$formAction}'>
            <div class='float_box'>
                <div class='float_left'>
                    <label>Year: </label>{$yearRow}
                    <label class='monthlabelfilter'>Month: </label>{$MonthRow}
                </div>
                {$sign_staff_id}
                
                <div class='float_right validationDivforAdd'>{$formObj->getTBRow('', "error_box", '', $expEdit)}</div>
            </div>

            <div class= 'float_box chargesDivEmployeeForm'>
            </div>

            <div class= 'timesheetTableProj'>
                {$this->getAddDaysRowHeadTimesheet($project_id, $currentMonth, $currentYear)}
            </div>
            <input type='hidden' name='project_id'         value='{$project_id}' />
            <input type='hidden' name='project_time_month' value='{$currentMonth}' />
            <input type='hidden' name='project_time_year'  value='{$currentYear}' />
            <input type='hidden' name='timesheet_type'     value='{$quoteRec['timesheet_type']}' />
        </form>
        ";

        return $text;
    }

    /**
     *
    */
    function getAddDaysRowHeadTimesheet($project_id= '', $currentMonth= '', $currentYear= ''){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        if ($project_id == '') {
            $project_id = $fn->getReqParam('project_id');
        }

        if ($currentMonth == '') {
            $currentMonth = $fn->getReqParam('selected_month');
        }

        if ($currentYear == '') {
            $currentYear = $fn->getReqParam('selected_year');
        }

        $text = "";
        $rows = "";
        $header = "";

        $SQL = "
        SELECT pe.employee_id
             ,e.first_name
             ,pe.category_type
        FROM `project_employee` pe
        LEFT JOIN employee e ON (e.employee_id = pe.employee_id)
        WHERE pe.project_id = {$project_id}
          AND e.status = 'Current'
          AND pe.active_in_project = 1
        ORDER BY e.first_name ASC
        ";

        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $count = 1;

        while ($row = $db->sql_fetchrow($result)) {
            $dayContRow      = "";
            $count2          = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
            $dayCount        = 1;
            $totalHoursSheet = 0;
            $rowSplitCount   = 1;

            for ($j= 0; $j < $count2; $j++) {
                $dayContHeader = "";
                $dayNameRow = "";
                $timeSheetDate =  $currentYear.'-'.$currentMonth.'-'.$dayCount;

                $SQLTimesheetDays = "
                SELECT employee_hours
                      ,employee_timesheet_id
                FROM `employee_timesheet`
                WHERE project_id = {$project_id}
                AND  employee_id = {$row['employee_id']}
                AND date = '{$timeSheetDate}'
                ";
                $resultTimesheetDays = $db->sql_query($SQLTimesheetDays);
                $rowTimesheetDays    = $db->sql_fetchrow($resultTimesheetDays);

                if($rowSplitCount > 16){
                    $dayContRow .= "</tr><tr>";
                    $rowSplitCount = 1;
                }

                $rowSplitCount++;

                $dayNameDate = $fn->getCPDate($timeSheetDate, 'D');
                $dayNameDate = strtoupper($dayNameDate);

                $style = '';
                if ($dayNameDate == 'SAT') {
                    $style = 'style="background-color: #fed8b1;"';
                } else if ($dayNameDate == 'SUN') {
                    $style = 'style="background-color: #90ee90;"';
                }

                $dayContRow .= "
                <th class='addFormTimeSheetRightPanelPopupTh timesheetDaysTd txtCenter' {$style}>
                    {$dayNameDate}
                    <br/>
                    {$dayCount}&nbsp;
                    <input type='text' value=''  id='timeSheetDays_{$row['employee_id']}_{$dayCount}' employee_id='{$row['employee_id']}' totalDays='{$count2}' currentInputNo={$dayCount}  class='text timeSheetDaysNormalInput txtRight' name='TimesheetDaysProject{$dayCount}[]'>
                    <br/><br/>
                    <input type='text' value=''  id='timeSheetOTDays_{$row['employee_id']}_{$dayCount}' employee_id='{$row['employee_id']}' totalDays='{$count2}' currentInputNo={$dayCount}  class='text timeSheetDaysOTInput txtRight' name='TimesheetDaysProjectOT{$dayCount}[]'>
                    <br/><br/>
                    <input type='text' value=''  id='timeSheetPHDays_{$row['employee_id']}_{$dayCount}' employee_id='{$row['employee_id']}' totalDays='{$count2}' currentInputNo={$dayCount}  class='text timeSheetDaysPHInput txtRight' name='TimesheetDaysProjectPH{$dayCount}[]'>
                    <input type='hidden' name='projectTimeSheetHiddenValues' class='projectTimeSheetHiddenValues' employee_id='{$row['employee_id']}' project_id='{$project_id}' employee_timesheet_id='{$rowTimesheetDays['employee_timesheet_id']}' timeSheetDate='{$timeSheetDate}' year='{$currentYear}' month='{$currentMonth}'>
                </th>";
                $dayCount++;

                $totalHoursSheet += $rowTimesheetDays['employee_hours'];
            }

            $yearMonthSelected = $currentYear.'-'.sprintf("%02d", $currentMonth);

            $SQLTimesheet = "
            SELECT hourly_rate
            FROM `employee_timesheet`
            WHERE project_id = {$project_id}
            AND  employee_id = {$row['employee_id']}
            AND DATE_FORMAT(date, '%Y-%m') = '{$yearMonthSelected}'
            GROUP BY employee_id
            ";
            $resultTimesheet = $db->sql_query($SQLTimesheet);
            $rowTimesheet    = $db->sql_fetchrow($resultTimesheet);

            $totalHoursSheet = number_format($totalHoursSheet, 2, '.', '');

            $daysRow = "<input type='hidden'name='TimesheetEmployee_id[]' value='{$row['employee_id']}' />
                        {$dayContRow}
                    ";

            $dayContHeader = "";
            $dayNameRow = "";
            $dayHeaderCount = 1;
            $count2 = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
            for ($j= 0; $j < 10; $j++) {
                $dateTimesheet =  $currentYear.'-'.$currentMonth.'-'.$dayHeaderCount;
                $dayNameDate = $fn->getCPDate($dateTimesheet, 'D');
                $dayNameDate = strtoupper($dayNameDate);
                $dayNameRow .= "<th class='timesheetDaysTd txtCenter'></th>";
                $dayHeaderCount++;
            }

            $hrlyRate = '';
            $SQLQuote = "
            SELECT qi.quantity
            FROM quote q
            LEFT JOIN quote_items qi ON (qi.quote_id = q.quote_id)
            WHERE q.project_id = {$project_id}
            AND (q.quote_status = 'Confirmed' OR q.quote_status = 'Order Raised')
            ";
            $resultQuote = $db->sql_query($SQLQuote);
            $QuoteRec    = $db->sql_fetchrow($resultQuote);

            if ($QuoteRec['quantity'] != ''){
                $projRec = $fn->getRecordRowByID('project', 'project_id', $project_id);
                if($projRec['category'] == 'Hourly Charge'){
                    $hrlyRate = $QuoteRec['quantity'];
                }
            }

            if ($row['category_type'] != "") {
                $qiRec = $fn->getRecordByCondition('quote_items', "title = '{$row['category_type']}' AND project_id = {$project_id}");
                if ($qiRec) {
                    $recQT = $fn->getRecordByCondition('quote_timesheet', "quote_id = '{$qiRec['quote_id']}'");
                    if($recQT['category_1_shift_2_charges'] != ''){
                      $recQT['category_1_shift_2_charges'] = $recQT['category_1_shift_2_charges'];
                    } else {
                      $recQT['category_1_shift_2_charges'] = 1;
                    }
                    if($recQT['category_4_shift_1_charges'] != ''){
                      $recQT['category_4_shift_1_charges'] = $recQT['category_4_shift_1_charges'];
                    } else {
                      $recQT['category_4_shift_1_charges'] = 1;
                    }
                    $hrlyRate = $qiRec['amount'];
                    $ot_rate  = number_format($qiRec['amount'] * $recQT['category_1_shift_2_charges'], 2);
                    $ph_rate  = number_format($qiRec['amount'] * $recQT['category_4_shift_1_charges'], 2);          
                }
            } else {
              $SQLEc = "
              SELECT *
              FROM employee_category
              WHERE employee_id = {$row['employee_id']}
              ";
              $resultEc = $db->sql_query($SQLEc);
              $hrlyRate = '';
              $ot_rate  = '';
              $ph_rate  = '';
              while ($rowEc = $db->sql_fetchrow($resultEc)) {
                $qiRec = $fn->getRecordByCondition('quote_items', "title = '{$rowEc['category']}' AND project_id = {$project_id}");
                $recQT = $fn->getRecordByCondition('quote_timesheet', "quote_id = '{$qiRec['quote_id']}'");
                if ($qiRec) {
                    if($recQT['category_1_shift_2_charges'] != ''){
                      $recQT['category_1_shift_2_charges'] = $recQT['category_1_shift_2_charges'];
                    } else {
                      $recQT['category_1_shift_2_charges'] = 1;
                    }
                    if($recQT['category_4_shift_1_charges'] != ''){
                      $recQT['category_4_shift_1_charges'] = $recQT['category_4_shift_1_charges'];
                    } else {
                      $recQT['category_4_shift_1_charges'] = 1;
                    }
                    $hrlyRate = $qiRec['amount'];
                    $ot_rate  = number_format($qiRec['amount'] * $recQT['category_1_shift_2_charges'], 2);
                    $ph_rate  = number_format($qiRec['amount'] * $recQT['category_4_shift_1_charges'], 2);          
                }
              }
            }

            $rows .= "
                <table class='thinlist timesheetTableProjReltab'>
                    <thead>
                        <tr>
                            <th colspan='2' class='timesheetFirstRow'>S.No: {$count}</th>
                            <th colspan='14' class='timesheetFirstRow'>
                                <div class='float_left'>Employee Name:
                                    <div class = 'employee_name_timesheet float_right'>
                                        {$row['first_name']}
                                    </div>
                                </div>
                            </th>
                        </tr>
                        <tr>
                            <th class='timesheetDaysTdRate txtCenter' colspan='2'>Normal Rate / HR:
                                <input type='text' value='{$hrlyRate}' id='timeSheetRatePerHr_{$row['employee_id']}' employee_id='{$row['employee_id']}' project_id='{$project_id}' year='{$currentYear}' month='{$currentMonth}' class='text timeSheetDaysRatePerHr txtRight' name='TimesheetRatePerHr[]' />
                            </th>
                            <th class='timesheetDaysTdRate txtCenter' colspan='2'>OT Rate / HR:
                                <input type='text' value='{$ot_rate}' id='timeSheetOTRatePerHr_{$row['employee_id']}' employee_id='{$row['employee_id']}' project_id='{$project_id}' year='{$currentYear}' month='{$currentMonth}' class='text timeSheetDaysOTRatePerHr txtRight' name='TimesheetOTRatePerHr[]' />
                            </th>
                            <th class='timesheetDaysTdRate txtCenter' colspan='2'>PH Rate / HR:
                                <input type='text' value='{$ph_rate}' id='timeSheetPHRatePerHr_{$row['employee_id']}' employee_id='{$row['employee_id']}' project_id='{$project_id}' year='{$currentYear}' month='{$currentMonth}' class='text timeSheetDaysPHRatePerHr txtRight' name='TimesheetPHRatePerHr[]' />
                            </th>
                            <th class='txtRight timesheetDaysTd' colspan='2'>Total Normal HRS:
                                <input type='text' value='' id='timeSheetTotalHours_{$row['employee_id']}' class='text timeSheetTotalHours txtRight' name='TimesheetTotalHours[]' disabled=1>
                            </th>
                            <th class='txtRight timesheetDaysTd' colspan='2'>Total OT HRS:
                                <input type='text' value='' id='timeSheetOTTotalHours_{$row['employee_id']}' class='text timeSheetTotalHours txtRight' name='TimesheetTotalHours[]' disabled=1>
                            </th>
                            <th class='txtRight timesheetDaysTd' colspan='2'>Total PH HRS:
                                <input type='text' value='' id='timeSheetPHTotalHours_{$row['employee_id']}' class='text timeSheetTotalHours txtRight' name='TimesheetTotalHours[]' disabled=1>
                            </th>
                            <th class='txtCenter'>Normal Rate<br/> Row 1</th>
                            <th class='txtCenter'>OT Rate<br/> Row 2</th>
                            <th class='txtCenter'>PH Rate<br/> Row 3</th>
                            <th>
                                <a class='btn btn-success saveTimeSheetTimeRecordPopupBtn mt10'>Save</a>
                            </th>
                        </tr>
                        <tr>
                            <!--<th class='txtRight timesheetDaysTd txtCenter' colspan='4'>Admin Charges:<br/>
                                <input type='text' value='' id='admin_charges_{$row['employee_id']}' employee_id='{$row['employee_id']}' project_id='{$project_id}' year='{$currentYear}' month='{$currentMonth}' class='text adminChargesEmployee txtRight' name='adminChargesEmployee[]'>
                            </th>
                            <th class='txtRight timesheetDaysTd txtCenter' colspan='4'>Transport Charges:<br/>
                                <input type='text' value='' id='transport_charges_{$row['employee_id']}' employee_id='{$row['employee_id']}' project_id='{$project_id}' year='{$currentYear}' month='{$currentMonth}' class='text transportChargesEmployee txtRight' name='transportChargesEmployee[]'>
                            </th>-->
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            {$daysRow}
                        </tr>
                    </tbody>
                </table>
            <br/>
            <br/>
            ";

            $count++;
        }

        if ($numRows == 0) {
            $rows = 'Please choose atleast one employee as active';
        }

        $yearMonthSelected = $currentYear.'-'.sprintf("%02d", $currentMonth);

        $SQLProjectTimeSheet ="
        SELECT * FROM `employee_timesheet`
        WHERE project_id = {$project_id}
        AND DATE_FORMAT(date, '%Y-%m') = '{$yearMonthSelected}'
        ";
        $resultProjectTimeSheet  = $db->sql_query($SQLProjectTimeSheet);
        $numRowsProjectTimeSheet = $db->sql_numrows($resultProjectTimeSheet);

        if($numRowsProjectTimeSheet > 0){
            $text = "
            <div class= 'float_box timesheetTableProjRel'>
                <p class='ValidationForTimesheetRecord'> Record already created for this month. </p>
            </div>
            ";
        } else {
            $text = "
            <div class= 'float_box timesheetTableProjRel'>
                {$rows}
            </div>
            ";
        }

        return $text;
    }

    /**
     *
    */
    function getEditHoursProjectEmployee() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $formObj  = Zend_Registry::get('formObj');
        $dateUtil = Zend_Registry::get('dateUtil');

        $project_id = $fn->getReqParam('project_id');
        $month      = $fn->getReqParam('month');
        $year       = $fn->getReqParam('year');
        $month      = ltrim($month,"0");

        $quoteRec = $fn->getRecordRowByID('quote', 'project_id', $project_id);

        $PreviousYear = date("Y") - 1;
        $currentYear  = date("Y");
        $nextYear     = date("Y") + 1;
        $currentMonth = date("m");

        $yearArray = array( $PreviousYear
                          , $currentYear
                          , $nextYear
                     );

        $exp = array(
              'hideFirstOption' => true
             ,'disabled'  => true
        );

        $expmonth = array(
            'hideFirstOption' => true
            ,'useKey' => true
            ,'disabled'  => true
        );

        switch ($month) {
            case 1: $monthVal = 'January';
            break;
            case 2: $monthVal = 'February';
            break;
            case 3: $monthVal = 'March';
            break;
            case 4: $monthVal = 'April';
            break;
            case 5: $monthVal = 'May';
            break;
            case 6: $monthVal = 'June';
            break;
            case 7: $monthVal = 'July';
            break;
            case 8: $monthVal = 'August';
            break;
            case 9: $monthVal = 'September';
            break;
            case 10: $monthVal = 'October';
            break;
            case 11: $monthVal = 'November';
            break;
            case 12: $monthVal = 'December';
            break;
        }

        $SQLTimesheetDays = "
        SELECT admin_charges
              ,transport_charges
        FROM `employee_timesheet`
        WHERE project_id = {$project_id}
        ";
        $resultTimesheetDays = $db->sql_query($SQLTimesheetDays);
        $rowTimesheetDays    = $db->sql_fetchrow($resultTimesheetDays);

        $formAction = "index.php?_topRm=project&module=enggCrm_project&_spAction=addMultipleTimesheetRecordsSubmit&project_id={$project_id}&year={$year}&month={$month}&type=edit&showHTML=0";

        $SQLInvoiceCheck = "
        SELECT i.start_date
              ,i.end_date
        FROM `invoice` i
        LEFT JOIN `order` o ON(o.project_id = {$project_id})
        WHERE i.status != 'Cancelled'
        AND i.order_id = o.order_id
        AND DATE_FORMAT(i.start_date, '%Y-%m') = '{$year}-{$month}'
        AND DATE_FORMAT(i.end_date, '%Y-%m') = '{$year}-{$month}'
        ";
        $resultInvoiceCheck   = $db->sql_query($SQLInvoiceCheck);
        $numRowsInvoiceCheck  = $db->sql_numrows($resultInvoiceCheck);
        $msg = '';
        if($numRowsInvoiceCheck > 0){
            $msg = "<div class='msgforInvoiceCreated'><font>Please cancel the related invoice to edit the below records.</font></div>";
        }

        $expEdit = array('isEditable' => 0);

        //if ($row['sign_staff_id']) {
        $sqlStaff = "SELECT employee_id ,CONCAT_WS(' ', first_name, last_name) AS title FROM employee WHERE admin_staff = 1 ORDER BY CONCAT_WS(' ', first_name, last_name);";
        //} else {
            //$sqlStaff = $fn->getDDSql('payroll_employee', array('condn' => "status = 'Current'"));
        //}

        if($quoteRec['timesheet_type'] == 'Fortnightly') {
            $start_date1 = $year.'-'.$month.'-01';
            $end_date1   = $year.'-'.$month.'-15';

            $start_date2 = $year.'-'.$month.'-16';
            $end_date2   = $year.'-'.$month.'-31';

            $staff1 = $fn->getRecordByCondition('employee_timesheet', "date BETWEEN '{$start_date1}' AND '{$end_date1}'");
            $staff2 = $fn->getRecordByCondition('employee_timesheet', "date BETWEEN '{$start_date2}' AND '{$end_date2}'");

            $sign_staff_id = "
            <div class='float_box'>
                <div class='float_left'>{$formObj->getDDRowBySQL('Timesheet 1 Sign *', 'sign_staff_id_1', $sqlStaff, $staff1['sign_staff_id'])}</div>
                <div class='clearfix'>{$formObj->getDDRowBySQL('Timesheet 2 Sign *', 'sign_staff_id_2', $sqlStaff, $staff2['sign_staff_id'])}</div>
            </div>
            ";
        } else {
            $start_date = $year.'-'.$month.'-01';
            $end_date   = $year.'-'.$month.'-31';

            $staff = $fn->getRecordByCondition('employee_timesheet', "date BETWEEN '{$start_date}' AND '{$end_date}'");
            $sign_staff_id = "<div class='float_left'>{$formObj->getDDRowBySQL('Timesheet Sign *', 'sign_staff_id', $sqlStaff, $staff['sign_staff_id'])}</div>";
        }

        $text = "
        <form id='addMultipleHoursEmployeeForm' class='addMultipleHoursEmployeeForm' method='post' action='{$formAction}'>
            <div class= 'float_box'>
                <div class= 'float_box'>
                  <label>Year: </label>&nbsp;{$year}
                  <label class='monthlabelfilter'>Month: </label>&nbsp;{$monthVal}
                </div>
                {$sign_staff_id}

                <div class='float_right validationDivforEdit'>{$formObj->getTBRow('', "error_box", '', $expEdit)}</div>
                {$msg}
            </div>
            <div class='timesheetTableProj'>
                {$this->getEditDaysRowHeadTimesheet($project_id, $month, $year)}
            </div>
            <input type='hidden' name='project_id' value='{$project_id}' />
            <input type='hidden' name='project_time_year' value='{$year}' />
            <input type='hidden' name='project_time_month' value='{$month}' />
            <input type='hidden' name='timesheet_type' value='{$quoteRec['timesheet_type']}' />
        </form>
        ";

        return $text;
    }

    /**
     *
    */
    function getEditDaysRowHeadTimesheet($project_id= '', $month= '', $year= ''){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        if($project_id == ''){
            $project_id = $fn->getReqParam('project_id');
        }

        if($year == ''){
            $year = $fn->getReqParam('year');
        }

        if($month == ''){
            $month = $fn->getReqParam('month');
        }

        $text = "";
        $rows = "";
        $header = "";

        $yearMonthSelected = $year.'-'.sprintf("%02d", $month);

        $SQL = "
        SELECT e.first_name
              ,e.employee_id
              ,et.category_type
        FROM project_employee et
        LEFT JOIN employee e ON(e.employee_id = et.employee_id)
        WHERE et.project_id = {$project_id}
        GROUP BY et.employee_id
        ORDER BY e.first_name ASC
        ";

        $result = $db->sql_query($SQL);
        $count  = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $dayContRow        = "";
            $count2            = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $dayCount          = 1;
            $totalHoursSheet   = 0;
            $totalOTHoursSheet = 0;
            $totalPHHoursSheet = 0;
            $rowSplitCount     = 1;
            for ($j= 0; $j < $count2; $j++) {
                $dayContHeader = "";
                $dayNameRow    = "";
                $timeSheetDate = $year.'-'.$month.'-'.$dayCount;

                $SQLTimesheetDays = "
                SELECT employee_hours
                      ,employee_ot_hours
                      ,employee_ph_hours
                      ,employee_timesheet_id
                FROM `employee_timesheet`
                WHERE project_id = {$project_id}
                AND  employee_id = {$row['employee_id']}
                AND date = '{$timeSheetDate}'
                ";
                $resultTimesheetDays = $db->sql_query($SQLTimesheetDays);
                $rowTimesheetDays    = $db->sql_fetchrow($resultTimesheetDays);

                $SQLInvoice = "
                SELECT i.start_date
                      ,i.end_date
                FROM `invoice` i
                LEFT JOIN `order` o ON(o.project_id = {$project_id})
                WHERE i.status != 'Cancelled'
                AND i.order_id = o.order_id
                AND '{$timeSheetDate}' BETWEEN i.start_date AND i.end_date
                ";
                $resultInvoice   = $db->sql_query($SQLInvoice);
                $numRowsInvoice  = $db->sql_numrows($resultInvoice);

                $monthCheck = $month;
                if($month < 10) {
                    $monthCheck = '0'.$month;
                }

                //A Allowing Timesheet edit for Current and Previous Months only
                $disabledInput = '';
                if ($monthCheck == date('m') || $monthCheck == (date('m') - 1)) {
                } else {
                    if ($numRowsInvoice == 0) {
                    } else {
                        $disabledInput = "disabled=1";
                    }
                }

                if($rowSplitCount > 16){
                    $dayContRow .= "</tr><tr>";
                    $rowSplitCount = 1;
                }
                $rowSplitCount++;

                $dayNameDate = $fn->getCPDate($timeSheetDate, 'D');
                $dayNameDate = strtoupper($dayNameDate);

                $style = '';
                if ($dayNameDate == 'SAT') {
                    $style = 'style="background-color: #fed8b1;"';
                } else if ($dayNameDate == 'SUN') {
                    $style = 'style="background-color: #90ee90;"';
                }

                $sqlPayroll = "
                SELECT payroll_management_id FROM payroll_management
                WHERE employee_id = {$row['employee_id']}
                  AND payroll_month = '{$monthCheck}'
                  AND payroll_year  = '{$year}'
                ";
                $resultPayroll  = $db->sql_query($sqlPayroll);
                $rowPayroll     = $db->sql_fetchrow($resultPayroll);

                $dayContRow .= "
                <th class='timesheetDaysTd editFormTimeSheetRightPanelPopupTh txtCenter' {$style}>
                      {$dayNameDate}
                      <br>
                      {$dayCount}&nbsp;
                      <input type='text' value='{$rowTimesheetDays['employee_hours']}'     id='timeSheetDays_{$row['employee_id']}_{$dayCount}' employee_id='{$row['employee_id']}' totalDays='{$count2}' currentInputNo={$dayCount}  class='text timeSheetDaysNormalInput txtRight' name='TimesheetDaysProject{$dayCount}[]' {$disabledInput}>
                      <br><br>
                      <input type='text' value='{$rowTimesheetDays['employee_ot_hours']}'  id='timeSheetOTDays_{$row['employee_id']}_{$dayCount}' employee_id='{$row['employee_id']}' totalDays='{$count2}' currentInputNo={$dayCount}  class='text timeSheetDaysOTInput txtRight' name='TimesheetDaysProjectOT{$dayCount}[]' {$disabledInput}>
                      <br><br>
                      <input type='text' value='{$rowTimesheetDays['employee_ph_hours']}'  id='timeSheetPHDays_{$row['employee_id']}_{$dayCount}' employee_id='{$row['employee_id']}' totalDays='{$count2}' currentInputNo={$dayCount}  class='text timeSheetDaysPHInput txtRight' name='TimesheetDaysProjectPH{$dayCount}[]' {$disabledInput}>
                      <input type='hidden' name='projectTimeSheetHiddenValues' class='projectTimeSheetHiddenValues' employee_id='{$row['employee_id']}' project_id='{$project_id}' employee_timesheet_id='{$rowTimesheetDays['employee_timesheet_id']}' timeSheetDate='{$timeSheetDate}' year='{$year}' month='{$month}' payroll_management_id='{$rowPayroll['payroll_management_id']}'>
                </th>";
                $dayCount++;

                $totalHoursSheet   += $rowTimesheetDays['employee_hours'];
                $totalOTHoursSheet += $rowTimesheetDays['employee_ot_hours'];
                $totalPHHoursSheet += $rowTimesheetDays['employee_ph_hours'];
            }

            $yearMonthSelected = $year.'-'.sprintf("%02d", $month);

            $SQLTimesheet = "
            SELECT hourly_rate
                  ,ot_hourly_rate
                  ,ph_hourly_rate
                  ,admin_charges
                  ,transport_charges
            FROM `employee_timesheet`
            WHERE project_id = '{$project_id}'
            AND  employee_id = '{$row['employee_id']}'
            AND DATE_FORMAT(date, '%Y-%m') = '{$yearMonthSelected}'
            GROUP BY employee_id
            ";
            $resultTimesheet = $db->sql_query($SQLTimesheet);
            $rowTimesheet    = $db->sql_fetchrow($resultTimesheet);

            if($rowTimesheet['hourly_rate'] == ''){
                /*CALCULATING HRLY RATE*/
                $hrlyRate = '';
                $SQLQuote = "
                SELECT qi.quantity
                FROM quote q
                LEFT JOIN quote_items qi ON (qi.quote_id = q.quote_id)
                WHERE q.project_id = {$project_id}
                AND (q.quote_status = 'Confirmed' OR q.quote_status = 'Order Raised')
                ";
                $resultQuote = $db->sql_query($SQLQuote);
                $QuoteRec    = $db->sql_fetchrow($resultQuote);

                if ($QuoteRec['quantity'] != ''){
                    $projRec = $fn->getRecordRowByID('project', 'project_id', $project_id);
                    if($projRec['category'] == 'Hourly Charge'){
                        $hrlyRate = $QuoteRec['quantity'];
                    }
                }

                if ($row['category_type'] != "") {
                    /*
                    $qiRec = $fn->getRecordByCondition('quote_items', "title = '{$row['category_type']}' AND project_id = {$project_id}");
                    if ($qiRec) {
                        $hrlyRate = $qiRec['amount'];
                        $ot_rate  = $qiRec['ot_rate'];
                        $ph_rate  = $qiRec['ph_rate'];
                    }
                    */
                    $qiRec = $fn->getRecordByCondition('quote_items', "title = '{$row['category_type']}' AND project_id = {$project_id}");
                    if ($qiRec) {
                        $recQT = $fn->getRecordByCondition('quote_timesheet', "quote_id = '{$qiRec['quote_id']}'");
                        if($recQT['category_1_shift_2_charges'] != ''){
                          $recQT['category_1_shift_2_charges'] = $recQT['category_1_shift_2_charges'];
                        } else {
                          $recQT['category_1_shift_2_charges'] = 1;
                        }
                        if($recQT['category_4_shift_1_charges'] != ''){
                          $recQT['category_4_shift_1_charges'] = $recQT['category_4_shift_1_charges'];
                        } else {
                          $recQT['category_4_shift_1_charges'] = 1;
                        }
                        $hrlyRate = $qiRec['amount'];
                        $ot_rate  = number_format($qiRec['amount'] * $recQT['category_1_shift_2_charges'], 2);
                        $ph_rate  = number_format($qiRec['amount'] * $recQT['category_4_shift_1_charges'], 2);          
                    }
                } else {
                    $SQLEc = "
                    SELECT *
                    FROM employee_category
                    WHERE employee_id = {$row['employee_id']}
                    ";
                    $resultEc = $db->sql_query($SQLEc);
                    $hrlyRate = '';
                    $ot_rate  = '';
                    $ph_rate  = '';
                    while ($rowEc = $db->sql_fetchrow($resultEc)) {
                        /*
                        $qiRec = $fn->getRecordByCondition('quote_items', "title    '{$rowEc['category']}' AND project_id = {$project_id}");
                        if ($qiRec) {
                            $hrlyRate = $qiRec['amount'];
                            $ot_rate  = $qiRec['ot_rate'];
                            $ph_rate  = $qiRec['ph_rate'];                    
                        }
                        */

                        $qiRec = $fn->getRecordByCondition('quote_items', "title = '{$rowEc['category']}' AND project_id = {$project_id}");
                        $recQT = $fn->getRecordByCondition('quote_timesheet', "quote_id = '{$qiRec['quote_id']}'");
                        if ($qiRec) {
                            if($recQT['category_1_shift_2_charges'] != ''){
                              $recQT['category_1_shift_2_charges'] = $recQT['category_1_shift_2_charges'];
                            } else {
                              $recQT['category_1_shift_2_charges'] = 1;
                            }
                            if($recQT['category_4_shift_1_charges'] != ''){
                              $recQT['category_4_shift_1_charges'] = $recQT['category_4_shift_1_charges'];
                            } else {
                              $recQT['category_4_shift_1_charges'] = 1;
                            }
                            $hrlyRate = $qiRec['amount'];
                            $ot_rate  = number_format($qiRec['amount'] * $recQT['category_1_shift_2_charges'], 2);
                            $ph_rate  = number_format($qiRec['amount'] * $recQT['category_4_shift_1_charges'], 2);          
                        }
                    }
                }
                /*CALCULATING HRLY RATE ENDS*/
            } else{
                $hrlyRate = $rowTimesheet['hourly_rate'];
                $ot_rate  = $rowTimesheet['ot_hourly_rate'];
                $ph_rate  = $rowTimesheet['ph_hourly_rate'];                                  
            }    
            $totalHoursSheet   = number_format($totalHoursSheet, 2, '.', '');
            $totalOTHoursSheet = number_format($totalOTHoursSheet, 2, '.', '');
            $totalPHHoursSheet = number_format($totalPHHoursSheet, 2, '.', '');

            $daysRow = "<input type='hidden'name='TimesheetEmployee_id[]' value='{$row['employee_id']}' />
                        {$dayContRow}
                      ";

            $dayContHeader  = "";
            $dayNameRow     = "";
            $dayHeaderCount = 1;
            $count2 = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            for ($j= 0; $j < 10; $j++) {
                $dateTimesheet =  $year.'-'.$month.'-'.$dayHeaderCount;
                $dayNameDate = $fn->getCPDate($dateTimesheet, 'D');
                $dayNameDate = strtoupper($dayNameDate);
                $dayNameRow .= "<th class='timesheetDaysTd txtCenter'></th>";
                $dayHeaderCount++;
            }

            $hiddenHrlyRate = '';
            $SQLInvoiceCheck = "
            SELECT i.start_date
                  ,i.end_date
            FROM `invoice` i
            LEFT JOIN `order` o ON(o.project_id = {$project_id})
            WHERE i.status != 'Cancelled'
            AND i.order_id = o.order_id
            AND DATE_FORMAT(i.start_date, '%Y-%m') = '{$year}-{$month}'
            AND DATE_FORMAT(i.end_date, '%Y-%m') = '{$year}-{$month}'
            ";
            $resultInvoiceCheck   = $db->sql_query($SQLInvoiceCheck);
            $numRowsInvoiceCheck  = $db->sql_numrows($resultInvoiceCheck);
            $disabledInputHrly = '';
            $hiddenOTHrlyRate  = '';
            $hiddenPHHrlyRate  = '';
            if($numRowsInvoiceCheck > 0){
                $disabledInputHrly = "disabled=1";
                $hiddenHrlyRate    = "<input type='hidden' value='{$hrlyRate}' name='TimesheetRatePerHr[]'>";
                $hiddenOTHrlyRate  = "<input type='hidden' value='{$ot_rate}' name='TimesheetOTRatePerHr[]'>";
                $hiddenPHHrlyRate  = "<input type='hidden' value='{$ph_rate}' name='TimesheetPHRatePerHr[]'>";
            }

            $SQLArchiveCheck = "
            SELECT SUM( et.employee_hours ) AS totalHrs
                 , e.status
            FROM `employee_timesheet` et
            LEFT JOIN employee e ON (e.employee_id = et.employee_id)
            WHERE et.employee_id = {$row['employee_id']}
              AND et.project_id  = {$project_id}
              AND DATE_FORMAT(et.date, '%Y-%m') = '{$yearMonthSelected}'
            ";
            $resultArchiveCheck = $db->sql_query($SQLArchiveCheck);
            $rowArchiveCheck    = $db->sql_fetchrow($resultArchiveCheck);

            $projEmployee = $fn->getRecordByCondition('project_employee', "project_id = {$project_id} AND employee_id = {$row['employee_id']}");

            $noteTxt = '';
            if($rowArchiveCheck['totalHrs'] == '' && $rowArchiveCheck['status'] == 'Archive'){
            } else if($rowArchiveCheck['totalHrs'] == '' && $projEmployee['active_in_project'] == 0){
            } else {
                /*if($numRowsInvoice > 0) {
                    $noteTxt = "NOTE: Please note that Invoice is already generated for the month. Goto related Order record and cancel the Invoice for the month to make further changes for timesheet.";
                }*/

                $rows .= "
                {$noteTxt}
                  <table class='thinlist timesheetTableProjReltab timeSheetEmployeeTableDetailsMain_{$row['employee_id']} mt10'>
                      <thead>
                          <tr>
                              <th colspan='2' class='timesheetFirstRow'>S.No: {$count}</th>
                              <th colspan='14' class='timesheetFirstRow'>
                                  <div class = 'float_left'>Employee Name:
                                      <div class = 'employee_name_timesheet float_right'>
                                          {$row['first_name']}
                                      </div>
                                  </div>
                              </th>
                          </tr>
                          <tr>
                              <th class='timesheetDaysTdRate txtCenter' colspan='2'>Normal Rate / HR:
                                  <input type='text' {$disabledInput} value='{$hrlyRate}' id='timeSheetRatePerHr_{$row['employee_id']}' employee_id='{$row['employee_id']}' project_id='{$project_id}' year='{$year}' month='{$month}' class='text timeSheetDaysRatePerHr txtRight' name='TimesheetRatePerHr[]' {$disabledInputHrly}>
                                  {$hiddenHrlyRate}
                              </th>
                              <th class='timesheetDaysTdRate txtCenter' colspan='2'>OT Rate / HR:
                                  <input type='text' {$disabledInput} value='{$ot_rate}' id='timeSheetOTRatePerHr_{$row['employee_id']}' employee_id='{$row['employee_id']}' project_id='{$project_id}' year='{$year}' month='{$month}' class='text timeSheetDaysOTRatePerHr txtRight' name='TimesheetOTRatePerHr[]' {$disabledInputHrly}/>
                                  {$hiddenOTHrlyRate}
                              </th>
                              <th class='timesheetDaysTdRate txtCenter' colspan='2'>PH Rate / HR:
                                  <input type='text' {$disabledInput} value='{$ph_rate}' id='timeSheetPHRatePerHr_{$row['employee_id']}' employee_id='{$row['employee_id']}' project_id='{$project_id}' year='{$year}' month='{$month}' class='text timeSheetDaysPHRatePerHr txtRight' name='TimesheetPHRatePerHr[]' {$disabledInputHrly}/>
                                  {$hiddenPHHrlyRate}
                              </th>
                              <th class='txtRight timesheetDaysTd' colspan='2'>Total Normal HRS:
                                  <input type='text' value='{$totalHoursSheet}' id='timeSheetTotalHours_{$row['employee_id']}' class='text timeSheetTotalHours txtRight' name='TimesheetTotalHours[]' disabled=1>
                              </th>
                              <th class='txtRight timesheetDaysTd' colspan='2'>Total OT HRS:
                                  <input type='text' value='{$totalOTHoursSheet}' id='timeSheetOTTotalHours_{$row['employee_id']}' class='text timeSheetTotalHours txtRight' name='TimesheetTotalHours[]' disabled=1>
                              </th>
                              <th class='txtRight timesheetDaysTd' colspan='2'>Total PH HRS:
                                  <input type='text' value='{$totalPHHoursSheet}' id='timeSheetPHTotalHours_{$row['employee_id']}' class='text timeSheetTotalHours txtRight' name='TimesheetTotalHours[]' disabled=1>
                              </th>
                              <th class='txtCenter'>Normal Rate<br/> Row 1</th>
                              <th class='txtCenter'>OT Rate<br/> Row 2</th>
                              <th class='txtCenter'>PH Rate<br/> Row 3</th>
                              <th>
                                <a class='btn btn-success saveTimeSheetTimeRecordPopupBtn mt10'>
                                    Save
                                </a>
                              </th>
                          </tr>
                          <tr>
                              <!--<th class='txtRight timesheetDaysTd txtCenter' colspan='4'>Admin Charges:<br/>
                                  <input type='text' {$disabledInput} value='{$rowTimesheet['admin_charges']}' id='admin_charges_{$row['employee_id']}' employee_id='{$row['employee_id']}' project_id='{$project_id}' year='{$year}' month='{$month}' class='text adminChargesEmployee txtRight' name='adminChargesEmployee[]'>
                              </th>
                              <th class='txtRight timesheetDaysTd txtCenter' colspan='4'>Transport Charges:<br/>
                                  <input type='text' {$disabledInput} value='{$rowTimesheet['transport_charges']}' id='transport_charges_{$row['employee_id']}' employee_id='{$row['employee_id']}' project_id='{$project_id}' year='{$year}' month='{$month}' class='text transportChargesEmployee txtRight' name='transportChargesEmployee[]'>
                              </th>-->
                          </tr>
                      </thead>
                      <tbody>
                          <tr>
                              {$daysRow}
                          </tr>
                      </tbody>
                  </table>
              <br/>
              ";

            $count++;
          }
        }

        $text = "
        <div class= 'float_box timesheetTableProjRel'>
            {$rows}
        </div>
        ";

        return $text;
    }

    /**
     *
    */
    function getPrintTimeSheetPdf() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot1Landscape.php');

        $pdf = new MYPDF_Local('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS CRM');
        $pdf->SetSubject('Print Timesheet');
        $pdf->SetTitle('Print Timesheet');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(5, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        //$pdf->SetAutoPageBreak(TRUE, 62);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->SetFont('arialblack','B',10);
        $pdf->AddPage();

        $employee_id = $fn->getReqParam('employee_id');
        $project_id = $fn->getReqParam('project_id');
        $month = $fn->getReqParam('month');
        $year = $fn->getReqParam('year');

        switch ($month) {
            case 1: $month_name = 'January';
            break;
            case 2: $month_name = 'February';
            break;
            case 3: $month_name = 'March';
            break;
            case 4: $month_name = 'April';
            break;
            case 5: $month_name = 'May';
            break;
            case 6: $month_name = 'June';
            break;
            case 7: $month_name = 'July';
            break;
            case 8: $month_name = 'August';
            break;
            case 9: $month_name = 'September';
            break;
            case 10: $month_name = 'October';
            break;
            case 11: $month_name = 'November';
            break;
            case 12: $month_name = 'December';
            break;
        }

        // Finding total number of Trainees for the Project for the month for footer margin ARIF
        $sqlCountForFooter = "
        SELECT DISTINCT et.employee_id FROM employee_timesheet et
        WHERE et.project_id = {$project_id}
          AND (et.employee_hours    != '' 
            OR et.employee_ot_hours != ''
            OR et.employee_ph_hours != ''
            OR et.employee_hours    = '0.00' 
            OR et.employee_ot_hours = '0.00'
            OR et.employee_ph_hours = '0.00')
        AND DATE_FORMAT(et.date, '%Y-%m') = '{$year}-{$month}'
        ";
        $resultCountForFooter = $db->sql_query($sqlCountForFooter);
        $numRowsCountForFooter = $db->sql_numrows($resultCountForFooter);

        $SQL = "
        SELECT et.*
             ,emp.first_name
             ,p.project_code
             ,p.title AS project_title
             ,c.company_name
             ,et.hourly_rate
             ,et.admin_charges
             ,et.transport_charges
             ,DATE_FORMAT(MIN(et.date), '%d') AS min_date
             ,DATE_FORMAT(MAX(et.date), '%d') AS max_date
        FROM employee_timesheet et
        LEFT JOIN (employee emp) ON (emp.employee_id = et.employee_id)
        LEFT JOIN (project p) ON (et.project_id = p.project_id)
        LEFT JOIN (company c) ON (p.company_id = c.company_id)
        WHERE et.project_id = {$project_id}
          AND (et.employee_hours    != '' 
            OR et.employee_ot_hours != ''
            OR et.employee_ph_hours != ''
            OR et.employee_hours    = '0.00' 
            OR et.employee_ot_hours = '0.00'
            OR et.employee_ph_hours = '0.00')
        AND DATE_FORMAT(et.date, '%Y-%m') = '{$year}-{$month}'
        ";
        $resultProject = $db->sql_query($SQL);
        $rowProject    = $db->sql_fetchrow($resultProject);

        // Used to find list of holidays
        $sqlValuelist = "
        SELECT value FROM valuelist WHERE code = '{$year}' AND key_text = 'singaporeHolidays'";
        $resultValuelist = $db->sql_query($sqlValuelist);
        $rowValuelist    = $db->sql_fetchrow($resultValuelist);
        $arrValuelist    = explode(',', $rowValuelist['value']);
        $current_date    = date('d-m-Y');

        $pdf->SetFont('times');

        $dayContHeader = "";
        $dateContHeader = "";
        $dayHeaderCount = 1;
        $overall_total_of_all_employees = 0;
        $count2 = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        for ($j=0; $j < $count2; $j++) {
            if ($dayHeaderCount <= 9) {
                $dayHeaderCountCheck = '0'.$dayHeaderCount;
            } else {
                $dayHeaderCountCheck = $dayHeaderCount;
            }

            $dateTimesheet =  $year.'-'.$month.'-'.$dayHeaderCount;
            $dayNameDate   = $fn->getCPDate($dateTimesheet, 'D');
            $dateTimesheetCheck =  $year.'-'.$month.'-'.$dayHeaderCountCheck;

            if (in_array($dateTimesheetCheck, $arrValuelist)) {
                $dayContHeader .= '<th width="2.4%" style="background-color:#92D050; line-height:300%; border:1px solid #000;"><b>'. $dayHeaderCount .'</b></th>';
                $dateContHeader .= '<th width="2.4%" style="background-color:#92D050; line-height:200%; border:1px solid #000;"><b>'. strtoupper($dayNameDate) .'</b></th>';
            } else if ($dayNameDate == 'Sun') {
                    $dayContHeader .= '<th width="2.4%" style="background-color:#ADD8E6; line-height:300%; border:1px solid #000;"><b>'. $dayHeaderCount .'</b></th>';
                    $dateContHeader .= '<th width="2.4%" style="background-color:#ADD8E6; line-height:200%; border:1px solid #000;"><b>'. strtoupper($dayNameDate) .'</b></th>';
            } else {
                $dayContHeader .= '<th width="2.4%" style="line-height:300%; border:1px solid #000;"><b>'. $dayHeaderCount .'</b></th>';
                $dateContHeader .= '<th width="2.4%" style="line-height:200%; border:1px solid #000;"><b>'. strtoupper($dayNameDate) .'</b></th>';
            }

            $dayHeaderCount++;
        }

        $tbl1 = '
        <table border="0" width="100%">
            <tr>
                <td align="left"><b>Project : '. strtoupper($rowProject['project_title']) .'</b></td>
            </tr>
            <tr>
                <td align="left"><b>Direct Workers Timesheet : '. $rowProject['min_date'] . '-' . $month . '-' . $year. ' - ' . $rowProject['max_date'] . '-' . $month . '-' . $year.'</b></td>
            </tr>
        </table>
        ';

        // In-line border: 1px solid #000 written in order to get border and fixing footer height ARIF
        $tbl2 ='
        <table border="0" cellpadding="3" width="100%" style="font-size:7px;">
            <thead>
                <tr align="center">
                    <th width="3%" rowspan="2" style="border:1px solid #000;"><b>S.NO</b></th>
                    <th width="15%" style="line-height:300%; border:1px solid #000;" rowspan="2"><b>NAME</b></th>
                    <th width="4%" style="border:1px solid #000;">DAY</th>'.
                    $dayContHeader . '
                    <th width="7%" style="line-height:300%; border:1px solid #000;" rowspan="2"><b>TOTAL HRS</b></th>
                </tr>
                <tr align="center">
                    <td width="4%" style="line-height:200%; border:1px solid #000;">DATE</td>'.
                    $dateContHeader . '
                </tr>
            </thead>
        ';

        $serialNo = 1;
        $totalValue = 0;
        $total_claim_for_employee = 0;
        $total_hours_of_all_employees = 0; // Total hours of all employees for summary
        $total_amount_of_all_employees = 0; // Total claim (amount) of all employees for summary

        $sqlTimesheet = "
        SELECT pe.employee_id
              ,e.first_name
              ,e.nric_no
              ,e.fin_no
              ,e.citizen
              ,pe.category_type
        FROM project_employee pe
        LEFT JOIN (employee e) ON (pe.employee_id = e.employee_id)
        WHERE pe.project_id = {$project_id}
        ORDER BY e.first_name ASC
        ";
        $resultTimesheet  = $db->sql_query($sqlTimesheet);
        while ($row = $db->sql_fetchrow($resultTimesheet)) {
            if ($row['citizen'] == 'Citizen' || $row['citizen'] == 'PR') {
                $ic_no = $row['nric_no'];
            } else {
                $ic_no = $row['fin_no'];
            }

            $employee_rate     = 0;
            $employee_ot_rate  = 0;
            $employee_ph_rate  = 0;
            $admin_charges     = 0;
            $transport_charges = 0;

            $dayContRow   = "";
            $dayContOTRow = "";
            $dayContPHRow = "";
            $count2 = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $dayCount = 1;
            $totalHoursSheet = 0;
            $totalOTHoursSheet = 0;
            $totalPHHoursSheet = 0;
            for ($j= 0; $j < $count2; $j++) {
                $timeSheetDate =  $year.'-'.$month.'-'.$dayCount;
                $SQLTimesheetDays = "
                SELECT employee_hours
                      ,employee_ot_hours
                      ,employee_ph_hours
                      ,hourly_rate
                      ,ot_hourly_rate
                      ,ph_hourly_rate
                      ,admin_charges
                      ,transport_charges
                FROM employee_timesheet
                WHERE project_id = {$project_id}
                AND  employee_id = {$row['employee_id']}
                AND date = '{$timeSheetDate}'
                ";
                $resultTimesheetDays = $db->sql_query($SQLTimesheetDays);
                $rowTimesheetDays    = $db->sql_fetchrow($resultTimesheetDays);

                $timesheet_days    = '';
                $timesheet_OT_days = '';
                $timesheet_PH_days = ''; 
                if ($rowTimesheetDays['employee_hours']){
                    $str_arr = explode('.',$rowTimesheetDays['employee_hours']);
                    $after_decimal = $str_arr[1]; // After decimal value
                    if ($after_decimal > 0) {
                        $timesheet_days    = $rowTimesheetDays['employee_hours'];
                    } else {
                        $timesheet_days    = $str_arr[0]; // Before decimal value
                    }
                }

                if ($rowTimesheetDays['employee_ot_hours']){
                    $str_arr = explode('.',$rowTimesheetDays['employee_ot_hours']);
                    $after_decimal = $str_arr[1]; // After decimal value
                    if ($after_decimal > 0) {
                        $timesheet_OT_days = $rowTimesheetDays['employee_ot_hours'];
                    } else {
                        $timesheet_OT_days = $str_arr[0];
                    }
                }

                if ($rowTimesheetDays['employee_ph_hours']){
                    $str_arr = explode('.',$rowTimesheetDays['employee_ph_hours']);
                    $after_decimal = $str_arr[1]; // After decimal value
                    if ($after_decimal > 0) {
                        $timesheet_PH_days = $rowTimesheetDays['employee_ph_hours'];
                    } else {
                        $timesheet_PH_days = $str_arr[0];
                    }
                }

                $dayContRow   .= '<td width="2.4%" align="center" style="line-height:200%; border: 1px solid #000;">'. $timesheet_days . '</td>';
                $dayContOTRow .= '<td width="2.4%" align="center" style="line-height:200%; border: 1px solid #000;">'. $timesheet_OT_days . '</td>';
                $dayContPHRow .= '<td width="2.4%" align="center" style="line-height:200%; border: 1px solid #000;">'. $timesheet_PH_days . '</td>';
                
                $dayCount++;

                $totalHoursSheet   += $rowTimesheetDays['employee_hours'];
                $totalOTHoursSheet += $rowTimesheetDays['employee_ot_hours'];
                $totalPHHoursSheet += $rowTimesheetDays['employee_ph_hours'];

                if($rowTimesheetDays['hourly_rate'] != ''){
                  $employee_rate    = $rowTimesheetDays['hourly_rate'];
                }

                if($rowTimesheetDays['ot_hourly_rate'] != ''){
                  $employee_ot_rate = $rowTimesheetDays['ot_hourly_rate'];
                }

                if($rowTimesheetDays['ph_hourly_rate'] != ''){
                  $employee_ph_rate = $rowTimesheetDays['ph_hourly_rate'];
                }

                if($rowTimesheetDays['admin_charges'] != ''){
                  $admin_charges    = $rowTimesheetDays['admin_charges'];
                }

                if($rowTimesheetDays['transport_charges'] != ''){
                  $transport_charges = $rowTimesheetDays['transport_charges'];
                }
            }

            $total_claim_for_employee = $employee_rate * $totalHoursSheet;
            $total_claim_for_employee_formatted = number_format($total_claim_for_employee,2);

            $total_claim_for_employee_ot = $employee_ot_rate * $totalOTHoursSheet;
            $total_claim_for_employee_ot_formatted = '-';
            if ($total_claim_for_employee_ot > 0) {
                $total_claim_for_employee_ot_formatted = number_format($total_claim_for_employee_ot,2);
            }

            $total_claim_for_employee_ph = $employee_ph_rate * $totalPHHoursSheet;
            $total_claim_for_employee_ph_formatted = '-';
            if ($total_claim_for_employee_ph > 0) {
                $total_claim_for_employee_ph_formatted = number_format($total_claim_for_employee_ph,2);
            }

            $overall_claim_amount = round($total_claim_for_employee + $total_claim_for_employee_ot + $total_claim_for_employee_ph, 2);
            $total_allowance = $admin_charges + $transport_charges;
            $total_amount_per_employee = $overall_claim_amount + $total_allowance;
            $overall_total_of_all_employees += $total_amount_per_employee;

            $SQLArchiveCheck = "
            SELECT SUM( et.employee_hours ) AS totalHrs
                 , e.status
                 , pe.active_in_project
            FROM `employee_timesheet` et
            LEFT JOIN project_employee pe ON (pe.project_id = et.project_id)
            LEFT JOIN employee e ON (e.employee_id = pe.employee_id)
            WHERE et.employee_id = {$row['employee_id']}
            AND pe.employee_id   = {$row['employee_id']}
            AND et.project_id    = {$project_id}
            AND DATE_FORMAT(et.date, '%Y-%m') = '{$year}-{$month}'
            ";
            $resultArchiveCheck = $db->sql_query($SQLArchiveCheck);
            $rowArchiveCheck    = $db->sql_fetchrow($resultArchiveCheck);
            if($rowArchiveCheck['totalHrs'] == '' && $rowArchiveCheck['status'] == 'Archive'){
            }else if($rowArchiveCheck['totalHrs'] == '' && $rowArchiveCheck['active_in_project'] == 0){
            }else{
                $totalHoursSheetFormatted = '';
                if ($totalHoursSheet > 0) {
                    $totalHoursSheetFormatted = number_format($totalHoursSheet, 2);
                }
                $total_allowance_formatted = '';
                if ($total_allowance > 0) {
                    $total_allowance_formatted = number_format($total_allowance, 2);
                }
                $totalOTHoursSheetFormatted = '-';
                if ($totalOTHoursSheet > 0) {
                    $totalOTHoursSheetFormatted = number_format($totalOTHoursSheet, 2);
                }
                $totalPHHoursSheetFormatted = '-';
                if ($totalPHHoursSheet > 0) {
                    $totalPHHoursSheetFormatted = number_format($totalPHHoursSheet, 2);
                }

                $tbl2 = $tbl2.'
                <tr align="center">
                    <td rowspan="3" width="3%"  style="line-height:800%; border: 1px solid #000;">'.$serialNo.'</td>
                    <td rowspan="3" width="15%"  style="border: 1px solid #000;">'.$row['first_name'].'<br/><br/>' . $ic_no .'<br/><br/>' . strtoupper($row['category_type']) .'</td>
                    <td width="4%" style="line-height:200%; border: 1px solid #000;">NH</td>'.
                    $dayContRow.'
                    <td width="7%" align="right" style="line-height:200%; border: 1px solid #000;">'.$totalHoursSheetFormatted.'</td>
                </tr>
                <tr>
                    <td width="4%" height="20px" align="center" style="line-height:200%; border: 1px solid #000;">OT</td>'.
                      $dayContOTRow.'
                    <td width="7%" align="right" style="line-height:200%; border: 1px solid #000;">'.$totalOTHoursSheetFormatted.'</td>
                </tr>
                <tr>
                    <td width="4%" height="20px" align="center" style="line-height:200%; border: 1px solid #000;">PH</td>'.
                      $dayContPHRow.'
                    <td width="7%" align="right" style="line-height:200%; border: 1px solid #000;">'.$totalPHHoursSheetFormatted.'</td>
                </tr>
                ';
                $total_hours_of_all_employees  += $totalHoursSheet + $totalOTHoursSheet + $totalPHHoursSheet; // Total hours of all employees for summary
                $total_amount_of_all_employees += $total_claim_for_employee + $total_claim_for_employee_ot + $total_claim_for_employee_ph + $admin_charges + $transport_charges; // Total claim (amount) of all employees for summary

                // Adding extra row for footer alignment ARIF
                if ($numRowsCountForFooter != $serialNo) {
                    $reminder = $serialNo % 4;
                    $quotient = $serialNo / 4;
                    if ($reminder == 0 && $quotient == 1) {
                        $colspan = $dayContRow + 4;
                        $tbl2 = $tbl2.'
                        <tr>
                            <td width="101%" style="line-height:2100%" colspan='. $colspan .'></td>
                        </tr>
                        ';
                    } else if ($reminder == 0 && $quotient > 1) {
                        $colspan = $dayContRow + 4;
                        $tbl2 = $tbl2.'
                        <tr>
                            <td width="101%" style="line-height:2500%" colspan='. $colspan .'></td>
                        </tr>
                        ';
                    }
                }
                $serialNo++;
            }
        }

        $colspan = $dayCount + 3;

        /* Calculate GST for the total amount */
        //if ($cpCfg['hasGST'])
        $gst_amount = 0;
        $gst_amount = (($total_amount_of_all_employees*7)/100);
        $total_amount_for_invoice = $total_amount_of_all_employees + $gst_amount;

        $total_amount_of_all_employees_formatted = number_format($total_amount_of_all_employees,2);
        $gst_amount_formatted = number_format($gst_amount, 2);
        $total_amount_for_invoice_formatted = number_format($total_amount_for_invoice, 2);

        $colspanCount = $count2 + 3;

        $tbl2 = $tbl2 . '
            <tr>
                <td colspan="'.$colspanCount.'" style="border:1px solid #000;"></td>
                <td align="right" style="border:1px solid #000;"><b>' . number_format($total_hours_of_all_employees, 2).'</b></td>
            </tr>
        </table>';

        $tbl3 = '
        <table border="0" width="100%">
            <tr>
                <td width="53%"></td>
                <td width="20%" style="border-bottom: 2px solid #000;"></td>
                <td width="10%"></td>
                <td width="20%" style="border-bottom: 2px solid #000;"></td>
            </tr>
            <tr>
                <td width="53%"></td>
                <td width="20%" align="center">PREPARED BY</td>
                <td width="10%"></td>
                <td width="20%" align="center">CHECKED BY</td>
            </tr>
        </table>
        ';

        if ($rowProject['sign_staff_id']) {
            $SQLMedia = "
            SELECT file_name, record_type FROM media
            WHERE record_id = '{$rowProject['sign_staff_id']}'
              AND room_name   = 'payroll_employee'
              AND record_type = 'digitalSign'
            ";
            $resultMedia  = $db->sql_query($SQLMedia);
            $numRowsMedia = $db->sql_numrows($resultMedia);

            if ($numRowsMedia) {
                $rowMedia = $db->sql_fetchrow($resultMedia);

                $path = realpath($cpCfg['cp.mediaFolder']) . '/normal';
                $file_name_save = $path . '/' . $rowMedia['file_name'];

                $sign = '<td width="40%"><img src="' .$file_name_save. '" height="100px"></td>';
            } else {
                $sign = '<td width="40%" style="line-height:103px;">&nbsp;</td>';
            }
        } else {
            $sign = '<td width="40%" style="line-height:103px;">&nbsp;</td>';
        }

        $tbl3 = '
        <table border="0" width="101%">
            <tr>
                <td width="60%" height="50%"></td>
                '. $sign .'
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $rowProject['project_code'] . '-Employee-Timesheet.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
    */
    function getPrintSummaryPdf() {
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot1Landscape.php');

        $pdf = new MYPDF_Local('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS CRM');
        $pdf->SetSubject('Print Timesheet');
        $pdf->SetTitle('Print Timesheet');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(5, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->SetFont('arialblack','B',10);
        $pdf->AddPage();

        $employee_id = $fn->getReqParam('employee_id');
        $project_id = $fn->getReqParam('project_id');
        $month = $fn->getReqParam('month');
        $year = $fn->getReqParam('year');

        switch ($month) {
            case 1: $month_name = 'January';
            break;
            case 2: $month_name = 'February';
            break;
            case 3: $month_name = 'March';
            break;
            case 4: $month_name = 'April';
            break;
            case 5: $month_name = 'May';
            break;
            case 6: $month_name = 'June';
            break;
            case 7: $month_name = 'July';
            break;
            case 8: $month_name = 'August';
            break;
            case 9: $month_name = 'September';
            break;
            case 10: $month_name = 'October';
            break;
            case 11: $month_name = 'November';
            break;
            case 12: $month_name = 'December';
            break;
        }

        // Finding total number of Trainees for the Project for the month for footer margin ARIF
        $sqlCountForFooter = "
        SELECT DISTINCT et.employee_id FROM employee_timesheet et
        WHERE et.project_id = {$project_id}
          AND (et.employee_hours    != '' 
            OR et.employee_ot_hours != ''
            OR et.employee_ph_hours != ''
            OR et.employee_hours    = '0.00' 
            OR et.employee_ot_hours = '0.00'
            OR et.employee_ph_hours = '0.00')
        AND DATE_FORMAT(et.date, '%Y-%m') = '{$year}-{$month}'
        ";
        $resultCountForFooter = $db->sql_query($sqlCountForFooter);
        $numRowsCountForFooter = $db->sql_numrows($resultCountForFooter);

        $SQL = "
        SELECT et.*
             ,emp.first_name
             ,p.project_code
             ,p.title AS project_title
             ,c.company_name
             ,et.hourly_rate
             ,et.admin_charges
             ,et.transport_charges
             ,DATE_FORMAT(MIN(et.date), '%d') AS min_date
             ,DATE_FORMAT(MAX(et.date), '%d') AS max_date
        FROM employee_timesheet et
        LEFT JOIN (employee emp) ON (emp.employee_id = et.employee_id)
        LEFT JOIN (project p) ON (et.project_id = p.project_id)
        LEFT JOIN (company c) ON (p.company_id = c.company_id)
        WHERE et.project_id = {$project_id}
          AND (et.employee_hours    != '' 
            OR et.employee_ot_hours != ''
            OR et.employee_ph_hours != ''
            OR et.employee_hours    = '0.00' 
            OR et.employee_ot_hours = '0.00'
            OR et.employee_ph_hours = '0.00')
        AND DATE_FORMAT(et.date, '%Y-%m') = '{$year}-{$month}'
        ";
        $resultProject = $db->sql_query($SQL);
        $rowProject    = $db->sql_fetchrow($resultProject);

        // Used to find list of holidays
        $sqlValuelist = "
        SELECT value FROM valuelist WHERE code = '{$year}' AND key_text = 'singaporeHolidays'";
        $resultValuelist = $db->sql_query($sqlValuelist);
        $rowValuelist    = $db->sql_fetchrow($resultValuelist);
        $arrValuelist    = explode(',', $rowValuelist['value']);
        $current_date    = date('d-m-Y');

        $pdf->SetFont('times');

        $dayContHeader = "";
        $dayHeaderCount = 1;
        $overall_total_of_all_employees = 0;
        $count2 = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $count2 = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        for ($j=0; $j < $count2; $j++) {
            if ($dayHeaderCount <= 9) {
                $dayHeaderCountCheck = '0'.$dayHeaderCount;
            } else {
                $dayHeaderCountCheck = $dayHeaderCount;
            }

            $dateTimesheet =  $year.'-'.$month.'-'.$dayHeaderCount;
            $dayNameDate   = $fn->getCPDate($dateTimesheet, 'D');
            $dateTimesheetCheck =  $year.'-'.$month.'-'.$dayHeaderCountCheck;

            if (in_array($dateTimesheetCheck, $arrValuelist)) {
                $dayContHeader .= '<th width="2.50%" style="background-color:#92D050; line-height:300%"><b>'. $dayHeaderCount .'</b></th>';
            } else if ($dayNameDate == 'Sun') {
                    $dayContHeader .= '<th width="2.50%" style="background-color:#ADD8E6; line-height:300%"><b>'. $dayHeaderCount .'</b></th>';
            } else {
                $dayContHeader .= '<th width="2.50%" style="line-height:300%"><b>'. $dayHeaderCount .'</b></th>';
            }

            $dayHeaderCount++;
        }

        // Setting to show values for empty min and max dates
        $min_date = $dateUtil->formatDate($rowProject['min_date'], 'DD-MM-YYYY');
        $max_date = $dateUtil->formatDate($rowProject['max_date'], 'DD-MM-YYYY');
        $tbl1 = '
        <table border="0" width="100%">
            <tr>
                <td align="left"><b>'. $rowProject['company_name'] .'</b></td>
                <td align="right"><b>PROJECT: '. strtoupper($rowProject['project_title']) .'</b></td>
            </tr>
            <tr>
                <td width="45%" align="left"><b>'. $rowProject['min_date'] . '-' . $month . '-' . $year. ' - ' . $rowProject['max_date'] . '-' . $month . '-' . $year.'</b></td>
                <td width="55%" align="left"><b>SUMMARY SHEET</b></td>
            </tr>
        </table>
        ';

        // In-line border: 1px solid #000 written in order to get border and fixing footer height ARIF
        $tbl2 ='
        <table border="0" cellpadding="3" width="100%" style="font-size:9px; font-weight:bold;">
            <thead>
                <tr align="center">
                    <th width="4%" rowspan="2" style="border:1px solid #000;"><br/><br/><br/>S.NO</th>
                    <th width="20%" rowspan="2" style="border:1px solid #000;"><br/><br/><br/>EMPLOYEE NAME</th>
                    <th width="11%" rowspan="2" style="border:1px solid #000;"><br/><br/><br/>DESIGNATION</th>
                    <th width="20%" colspan="3" style="line-height:300%; border:1px solid #000;">NORMAL WORKING HOURS</th>
                    <th width="20%" colspan="3" style="line-height:300%; border:1px solid #000;">OVERTIME HOURS</th>
                    <th width="20%" colspan="3" style="line-height:300%; border:1px solid #000;">SUN/PH HOURS</th>
                    <th width="9%" rowspan="2" align="right" style="border:1px solid #000;"><br/><br/><br/>SUB TOTAL (S$)</th>
                </tr>
                <tr align="center">
                    <th width="5%" style="border:1px solid #000;">HOURS</th>
                    <th width="8%" style="border:1px solid #000;">UNIT PRICE (S$)</th>
                    <th width="7%" style="border:1px solid #000;">AMOUNT (S$)</th>
                    <th width="5%" style="border:1px solid #000;">HOURS</th>
                    <th width="8%" style="border:1px solid #000;">UNIT PRICE (S$)</th>
                    <th width="7%" style="border:1px solid #000;">AMOUNT (S$)</th>
                    <th width="5%" style="border:1px solid #000;">HOURS</th>
                    <th width="8%" style="border:1px solid #000;">UNIT PRICE (S$)</th>
                    <th width="7%" style="border:1px solid #000;">AMOUNT (S$)</th>
                </tr>
            </thead>
        ';

        $serialNo                       = 1;
        $totalValue                     = 0;
        $total_claim_for_employee       = 0;
        $total_hours_of_all_employees   = 0; // Total hours of all employees for summary
        $total_amount_of_all_employees  = 0; // Total claim (amount) of all employees for summary
        $overallHours                   = 0; //A overall normal hours in summary
        $overallOTHours                 = 0; //A overall OT hours in summary
        $overallPHHours                 = 0; //A overall Sun/PH hours in summary

        $sqlTimesheet = "
        SELECT pe.employee_id
              ,e.first_name
              ,e.nric_no
              ,e.fin_no
              ,e.citizen
              ,pe.category_type
        FROM project_employee pe
        LEFT JOIN (employee e) ON (pe.employee_id = e.employee_id)
        WHERE pe.project_id = {$project_id}
        ORDER BY e.first_name ASC
        ";
        $resultTimesheet       = $db->sql_query($sqlTimesheet);
        while ($row = $db->sql_fetchrow($resultTimesheet)) {
            if ($row['citizen'] == 'Citizen' || $row['citizen'] == 'PR') {
                $ic_no = $row['nric_no'];
            } else {
                $ic_no = $row['fin_no'];
            }

            $employee_rate     = 0;
            $employee_ot_rate  = 0;
            $employee_ph_rate  = 0;
            $admin_charges     = 0;
            $transport_charges = 0;

            $dayContRow   = "";
            $dayContOTRow = "";
            $dayContPHRow = "";
            $count2 = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $dayCount = 1;
            $totalHoursSheet = 0;
            $totalOTHoursSheet = 0;
            $totalPHHoursSheet = 0;
            for ($j= 0; $j < $count2; $j++) {
                $timeSheetDate =  $year.'-'.$month.'-'.$dayCount;
                $SQLTimesheetDays = "
                SELECT employee_hours
                      ,employee_ot_hours
                      ,employee_ph_hours
                      ,hourly_rate
                      ,ot_hourly_rate
                      ,ph_hourly_rate
                      ,admin_charges
                      ,transport_charges
                FROM employee_timesheet
                WHERE project_id = {$project_id}
                AND  employee_id = {$row['employee_id']}
                AND date = '{$timeSheetDate}'
                ";
                $resultTimesheetDays = $db->sql_query($SQLTimesheetDays);
                $rowTimesheetDays    = $db->sql_fetchrow($resultTimesheetDays);

                $timesheet_days    = '';
                $timesheet_OT_days = '';
                $timesheet_PH_days = ''; 
                if ($rowTimesheetDays['employee_hours']){
                    $str_arr = explode('.',$rowTimesheetDays['employee_hours']);
                    $after_decimal = $str_arr[1]; // After decimal value
                    if ($after_decimal > 0) {
                        $timesheet_days    = $rowTimesheetDays['employee_hours'];
                    } else {
                        $timesheet_days    = $str_arr[0]; // Before decimal value
                    }
                }

                if ($rowTimesheetDays['employee_ot_hours']){
                    $str_arr = explode('.',$rowTimesheetDays['employee_ot_hours']);
                    $after_decimal = $str_arr[1]; // After decimal value
                    if ($after_decimal > 0) {
                        $timesheet_OT_days = $rowTimesheetDays['employee_ot_hours'];
                    } else {
                        $timesheet_OT_days = $str_arr[0];
                    }
                }

                if ($rowTimesheetDays['employee_ph_hours']){
                    $str_arr = explode('.',$rowTimesheetDays['employee_ph_hours']);
                    $after_decimal = $str_arr[1]; // After decimal value
                    if ($after_decimal > 0) {
                        $timesheet_PH_days = $rowTimesheetDays['employee_ph_hours'];
                    } else {
                        $timesheet_PH_days = $str_arr[0];
                    }
                }

                $dayContRow   .= '<td width="2.50%" align="center" style="line-height:200%">'. $timesheet_days . '</td>';
                $dayContOTRow .= '<td width="2.50%" align="center" style="line-height:200%">'. $timesheet_OT_days . '</td>';
                $dayContPHRow .= '<td width="2.50%" align="center" style="line-height:200%">'. $timesheet_PH_days . '</td>';
                
                $dayCount++;

                $totalHoursSheet   += $rowTimesheetDays['employee_hours'];
                $totalOTHoursSheet += $rowTimesheetDays['employee_ot_hours'];
                $totalPHHoursSheet += $rowTimesheetDays['employee_ph_hours'];

                if($rowTimesheetDays['hourly_rate'] != ''){
                  $employee_rate    = $rowTimesheetDays['hourly_rate'];
                }

                if($rowTimesheetDays['ot_hourly_rate'] != ''){
                  $employee_ot_rate = $rowTimesheetDays['ot_hourly_rate'];
                }

                if($rowTimesheetDays['ph_hourly_rate'] != ''){
                  $employee_ph_rate = $rowTimesheetDays['ph_hourly_rate'];
                }

                if($rowTimesheetDays['admin_charges'] != ''){
                  $admin_charges    = $rowTimesheetDays['admin_charges'];
                }

                if($rowTimesheetDays['transport_charges'] != ''){
                  $transport_charges = $rowTimesheetDays['transport_charges'];
                }
            }

            $total_claim_for_employee = $employee_rate * $totalHoursSheet;
            $total_claim_for_employee_formatted = number_format($total_claim_for_employee,2);

            $total_claim_for_employee_ot = $employee_ot_rate * $totalOTHoursSheet;
            $total_claim_for_employee_ot_formatted = '-';
            if ($total_claim_for_employee_ot > 0) {
                $total_claim_for_employee_ot_formatted = number_format($total_claim_for_employee_ot,2);
            }

            $total_claim_for_employee_ph = $employee_ph_rate * $totalPHHoursSheet;
            $total_claim_for_employee_ph_formatted = '-';
            if ($total_claim_for_employee_ph > 0) {
                $total_claim_for_employee_ph_formatted = number_format($total_claim_for_employee_ph,2);
            }

            $overall_claim_amount           = round(($total_claim_for_employee + $total_claim_for_employee_ot + $total_claim_for_employee_ph), 2);
            $total_allowance                = $admin_charges + $transport_charges;
            $total_amount_per_employee      = $overall_claim_amount;
            $overall_total_of_all_employees += $total_amount_per_employee;

            $SQLArchiveCheck = "
            SELECT SUM( et.employee_hours ) AS totalHrs
                 , e.status
                 , pe.active_in_project
            FROM `employee_timesheet` et
            LEFT JOIN project_employee pe ON (pe.project_id = et.project_id)
            LEFT JOIN employee e ON (e.employee_id = pe.employee_id)
            WHERE et.employee_id = {$row['employee_id']}
            AND pe.employee_id   = {$row['employee_id']}
            AND et.project_id    = {$project_id}
            AND DATE_FORMAT(et.date, '%Y-%m') = '{$year}-{$month}'
            ";
            $resultArchiveCheck = $db->sql_query($SQLArchiveCheck);
            $rowArchiveCheck    = $db->sql_fetchrow($resultArchiveCheck);
            if($rowArchiveCheck['totalHrs'] == '' && $rowArchiveCheck['status'] == 'Archive'){
            }else if($rowArchiveCheck['totalHrs'] == '' && $rowArchiveCheck['active_in_project'] == 0){
            }else{
                $totalHoursSheetFormatted = '';
                if ($totalHoursSheet > 0) {
                    $totalHoursSheetFormatted = number_format($totalHoursSheet, 1);
                }
                $total_allowance_formatted = '';
                if ($total_allowance > 0) {
                    $total_allowance_formatted = number_format($total_allowance, 2);
                }
                $totalOTHoursSheetFormatted = '-';
                if ($totalOTHoursSheet > 0) {
                    $totalOTHoursSheetFormatted = number_format($totalOTHoursSheet, 2);
                }
                $totalPHHoursSheetFormatted = '-';
                if ($totalPHHoursSheet > 0) {
                    $totalPHHoursSheetFormatted = number_format($totalPHHoursSheet, 2);
                }

                $tbl2 = $tbl2.'
                <tr align="center">
                    <td width="4%" style="line-height:300%; border: 1px solid #000;">'.$serialNo.'</td>
                    <td width="20%" style="line-height:300%; border: 1px solid #000;">'.$row['first_name'].'</td>
                    <td width="11%" style="line-height:300%; border: 1px solid #000;">' . strtoupper($row['category_type']) .'</td>
                    <td width="5%" align="right" style="line-height:300%; border: 1px solid #000;">'.$totalHoursSheetFormatted.'</td>
                    <td width="8%" align="right" style="line-height:300%; border: 1px solid #000;">'.$employee_rate.'</td>
                    <td width="7%" align="right" style="line-height:300%; border: 1px solid #000;">'.$total_claim_for_employee_formatted.'</td>
                    <td width="5%" align="right" style="line-height:300%; border: 1px solid #000;">'.$totalOTHoursSheetFormatted.'</td>
                    <td width="8%" align="right" style="line-height:300%; border: 1px solid #000;">'.$employee_ot_rate.'</td>
                    <td width="7%" align="right" style="line-height:300%; border: 1px solid #000;">'.$total_claim_for_employee_ot_formatted.'</td>
                    <td width="5%" align="right" style="line-height:300%; border: 1px solid #000;">'.$totalPHHoursSheetFormatted.'</td>
                    <td width="8%" align="right" style="line-height:300%; border: 1px solid #000;">'.$employee_ph_rate.'</td>
                    <td width="7%" align="right" style="line-height:300%; border: 1px solid #000;">'.$total_claim_for_employee_ph_formatted.'</td>
                    <td width="9%" align="right" style="line-height:300%; border: 1px solid #000;">'. number_format($total_amount_per_employee, 2).'</td>
                </tr>
                ';
                $total_hours_of_all_employees  += $totalHoursSheet + $totalOTHoursSheet + $totalPHHoursSheet; // Total hours of all employees for summary
                $total_amount_of_all_employees += $total_claim_for_employee + $total_claim_for_employee_ot + $total_claim_for_employee_ph + $admin_charges + $transport_charges; // Total claim (amount) of all employees for summary

                //A Adding of total individual hours of employees for Summary
                $overallHours   += $totalHoursSheet;
                $overallOTHours += $totalOTHoursSheet;
                $overallPHHours += $totalPHHoursSheet;

                // Adding extra row for footer alignment ARIF
                if ($numRowsCountForFooter != $serialNo) {
                    $reminder = $serialNo % 6;
                    $quotient = $serialNo / 6;
                    if ($reminder == 0 && $quotient == 1) {
                        $colspan = $dayContRow + 4;
                        $tbl2 = $tbl2.'
                        <tr>
                            <td width="101%" style="line-height:2250%" colspan='. $colspan .'></td>
                        </tr>
                        ';
                    } else if ($reminder == 0 && $quotient > 1) {
                        $colspan = $dayContRow + 4;
                        $tbl2 = $tbl2.'
                        <tr>
                            <td width="101%" style="line-height:2500%" colspan='. $colspan .'></td>
                        </tr>
                        ';
                    }
                }

                $serialNo++;
            }
        }

        $colspan = $dayCount + 3;

        /* Calculate GST for the total amount */
        //if ($cpCfg['hasGST'])
        $gst_amount = 0;
        $gst_amount = (($overall_total_of_all_employees*7)/100);
        $total_amount_for_invoice = $overall_total_of_all_employees + $gst_amount;

        $total_amount_of_all_employees_formatted = number_format($total_amount_of_all_employees,2);
        $gst_amount_formatted = number_format($gst_amount, 2);
        $total_amount_for_invoice_formatted = $total_amount_for_invoice;

        $tbl2 = $tbl2 . '
            <tr>
                <td align="right" colspan="3" style="border:1px solid #000;">Total Hours</td>
                <td align="right" style="border:1px solid #000;">'. number_format($overallHours, 2).'</td>
                <td colspan="2" style="border:1px solid #000;"></td>
                <td align="right" style="border:1px solid #000;">'. number_format($overallOTHours, 2).'</td>
                <td colspan="2" style="border:1px solid #000;"></td>
                <td align="right" style="border:1px solid #000;">'. number_format($overallPHHours, 2).'</td>
                <td colspan="2" align="right" style="border:1px solid #000;">Amount</td>
                <td align="right" style="border:1px solid #000;"><b>' . number_format($overall_total_of_all_employees, 2).'</b></td>
            </tr>
            <tr>
                <td colspan="12" align="right" style="border:1px solid #000;">GST 7%</td>
                <td align="right" style="border:1px solid #000;"><b>' . $gst_amount_formatted.'</b></td>
            </tr>
            <tr>
                <td colspan="12" align="right" style="border:1px solid #000;">Total Amount</td>
                <td align="right" style="border:1px solid #000;"><b>' . number_format($total_amount_for_invoice_formatted, 2).'</b></td>
            </tr>
        </table>';

        if ($rowProject['sign_staff_id']) {
            $SQLMedia = "
            SELECT file_name, record_type FROM media
            WHERE record_id = '{$rowProject['sign_staff_id']}'
              AND room_name   = 'payroll_employee'
              AND record_type = 'digitalSign'
            ";
            $resultMedia  = $db->sql_query($SQLMedia);
            $numRowsMedia = $db->sql_numrows($resultMedia);

            if ($numRowsMedia) {
                $rowMedia = $db->sql_fetchrow($resultMedia);

                $path = realpath($cpCfg['cp.mediaFolder']) . '/normal';
                $file_name_save = $path . '/' . $rowMedia['file_name'];

                $sign = '<td width="40%"><img src="' .$file_name_save. '" height="100px"></td>';
            } else {
                $sign = '<td width="40%" style="line-height:103px;">&nbsp;</td>';
            }
        } else {
            $sign = '<td width="40%" style="line-height:103px;">&nbsp;</td>';
        }

        $tbl3 = '
        <table border="0" width="100%">
            <tr>
                <td width="60%"></td>
                '. $sign .'
            </tr>
        </table>
        ';
        
        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $rowProject['project_code'] . '-Employee-Summary.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
    */
    function getEmployeeForMonthDetails() {
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');

        $year_month = $fn->getReqParam('year_month');
        $project_id = $fn->getReqParam('project_id');

        $SQL2 = "
        SELECT e.employee_name
              ,e.employee_id
              ,et.admin_charges
              ,et.transport_charges
        FROM employee_timesheet et
        LEFT JOIN employee e ON(e.employee_id = et.employee_id)
        WHERE et.project_id = {$project_id}
        AND DATE_FORMAT(date, '%Y-%m') = '{$year_month}'
        GROUP BY et.employee_id
        ";
        $result2 = $db->sql_query($SQL2);
        $rows2 = '';
        $admin_charges     = 0;
        $transport_charges = 0;
        while ($row2 = $db->sql_fetchrow($result2)) {
            $SQLArchiveCheck = "
            SELECT SUM( et.employee_hours ) AS totalHrs
                 , e.status
                 , pe.active_in_project
            FROM `employee_timesheet` et
            LEFT JOIN project_employee pe ON (pe.project_id = et.project_id)
            LEFT JOIN employee e ON (e.employee_id = pe.employee_id)
            WHERE et.employee_id = {$row2['employee_id']}
            AND pe.employee_id   = {$row2['employee_id']}
            AND et.project_id    = {$project_id}
            AND DATE_FORMAT(et.date, '%Y-%m') = '{$year_month}'
            ";
            $resultArchiveCheck = $db->sql_query($SQLArchiveCheck);
            $rowArchiveCheck    = $db->sql_fetchrow($resultArchiveCheck);
            if($rowArchiveCheck['totalHrs'] == '' && $rowArchiveCheck['status'] == 'Archive'){
            }else if($rowArchiveCheck['totalHrs'] == '' && $rowArchiveCheck['active_in_project'] == 0){
            }else{
                $rows2 .= "{$this->getTimeSheetByEmployee($project_id, $row2['employee_id'], $year_month)}";

                $admin_charges     += $row2['admin_charges'];
                $transport_charges += $row2['transport_charges'];
            }
        }

        $month_year_arr = explode('-', $year_month);
        $monthVal = '';

        switch ($month_year_arr[1]) {
            case 1: $monthVal = 'January';
            break;
            case 2: $monthVal = 'February';
            break;
            case 3: $monthVal = 'March';
            break;
            case 4: $monthVal = 'April';
            break;
            case 5: $monthVal = 'May';
            break;
            case 6: $monthVal = 'June';
            break;
            case 7: $monthVal = 'July';
            break;
            case 8: $monthVal = 'August';
            break;
            case 9: $monthVal = 'September';
            break;
            case 10: $monthVal = 'October';
            break;
            case 11: $monthVal = 'November';
            break;
            case 12: $monthVal = 'December';
            break;
        }

        $text = "
        <table class='thinlist'>
            <thead>
                <tr>
                    <th colspan='6' class='txtCenter' style='background-color:#17375E !important; color: #fff !important;'>Employees for the month {$monthVal} and year {$month_year_arr['0']}</th>
                </tr>
                <tr>
                    <th>Name</th>
                    <th class='txtCenter'>Hours</th>
                    <th class='txtCenter'>OT Hours</th>
                    <th class='txtCenter'>PH Hours</th>
                    <th class='txtRight'>Amount</th>
                    <th class='txtRight'>Action</th>
                </tr>
            </thead>
            {$rows2}
        </table>
        ";

        return $text;
    }
}