<?
class CPL_Admin_Widgets_Project_OperationalFinancialReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');
        $c = &$this->controller;

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');

        $current_date = date('d-m-Y');
        if ($start_date != '' && $end_date == '') {
            $start_date = $dateUtil->formatDate($start_date, 'DD-MM-YYYY');
            $end_date = $current_date;
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $current_date;
            $end_date = $dateUtil->formatDate($end_date, 'DD-MM-YYYY');
        } else if ($start_date != '' && $end_date != '') {
            $start_date = $dateUtil->formatDate($start_date, 'DD-MM-YYYY');
            $end_date = $dateUtil->formatDate($end_date, 'DD-MM-YYYY');
        } else {
            $start_date = $current_date;
            $end_date = $current_date;
        }

        $text = "
        <table class='thinlist summaryTable'>
            <thead>
                <th colspan='2' class='txtCenter'>Summary</th>
            </thead>
            <tr>
                <td><b>Start Date :</b> {$start_date}</td>
                <td><b>End Date :</b> {$end_date}</td>
            </tr>
        </table>
		<div class = 'tableOuter scroll-pane'>
        <table class='thinlist'>
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Employee Name</th>
                    <th>Fin No.</th>
                    <th class='txtRight'>Client</th>
                    <th class='txtRight'>Levy</th>
                    <th class='txtRight'>Normal Rate</th>
                    <th class='txtRight'>OT Rate</th>
                    <th class='txtRight'>Dorm</th>
                    <th class='txtRight'>Total</th>
                </tr>
            </thead>
            <tbody>
                {$rowsHTML}
            </tbody>
        </table>
        </div>
        ";

        return $text;
    }
    
    /**
     *
     */
    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows = '';
        $counter = 1;
        $overallTotal = 0;
        foreach($this->model->dataArray as $row){
            $start_date = $fn->getReqParam('start_date');
            $end_date   = $fn->getReqParam('end_date');
            $current_date = date('Y-m-d');

            if ($row['citizen'] == 'Citizen' || $row['citizen'] == 'PR') {
                $ic_no = $row['nric_no'];
            } else {
                $ic_no = $row['fin_no'];
            }

            if ($start_date != '' && $end_date == '') {
                $end_date = $current_date;
            } else if ($start_date == '' && $end_date != ''){
                $start_date = $current_date;
            } else if ($start_date != '' && $end_date != '') {
            } else {
                $start_date = $current_date;
                $end_date = $current_date;
            }

              //AND (j.act_join_date >= '{$start_date}' AND j.termination_date <= '{$end_date}')
            $sqlJobInfo = "
            SELECT j.*
            FROM job_information j
            WHERE j.employee_id = {$row['employee_id']}
              AND (j.termination_date <= '{$end_date}')
            ORDER BY j.job_information_id DESC
            ";
            $resultJobInfo = $db->sql_query($sqlJobInfo);
            $numRows = $db->sql_numrows($resultJobInfo);
            if($numRows == 0){
                $sqlJobInfo = "
                SELECT j.*
                FROM job_information j
                WHERE j.employee_id = {$row['employee_id']}
                  AND (j.termination_date = '' OR j.termination_date IS NULL)
                ORDER BY j.job_information_id DESC
                ";
                $resultJobInfo = $db->sql_query($sqlJobInfo);                
            }
            $rowJi = $db->sql_fetchrow($resultJobInfo);

            //$basic_pay_formatted = number_format($rowJi['basic_pay'], 2);
            $SQLTimesheet = "
            SELECT *
            FROM employee_timesheet
            WHERE employee_id = {$row['employee_id']}
            AND (date >= '{$start_date}' AND date <= '{$end_date}')
            ";
            $resultTimesheet = $db->sql_query($SQLTimesheet);
            $client = 0;
            $nr_rate = 0;
            $ot_rate = 0;
            while ($rowTimesheet = $db->sql_fetchrow($resultTimesheet)) {
                $nrRate = $rowTimesheet['employee_hours'] * $rowTimesheet['hourly_rate'];
                $otRate = $rowTimesheet['employee_ot_hours'] * $rowTimesheet['ot_hourly_rate'];
                $phRate = $rowTimesheet['employee_ph_hours'] * $rowTimesheet['ph_hourly_rate'];
                $client += $nrRate + $otRate + $phRate;
                $nr_rate += $rowTimesheet['employee_hours'];
                $ot_rate += $rowTimesheet['employee_ot_hours'] + $rowTimesheet['employee_ph_hours'];
            }

            $normalRate   = round((($rowJi['basic_pay'] / 30) / 8),2) * $nr_rate;
            $overtimeRate = (round((($rowJi['basic_pay'] / 30) / 8),2) * $rowJi['over_time_rate']) * $ot_rate;

            $datetime1  = date_create($start_date); 
            $datetime2  = date_create($end_date); 
            $interval   = date_diff($datetime1, $datetime2); 
            $no_of_days = $interval->format('%a') + 1;

            $levy_amount = round(($rowJi['levy_amount'] / 30),2) * $no_of_days;
            $deduction1 = (($rowJi['deduction1'] + 30) / 30) * $no_of_days;

            $total = $client - ($levy_amount + $normalRate + $overtimeRate + $deduction1);
            $overallTotal += $total;

            $client = number_format($client, 2);
            $normalRate = number_format($normalRate, 2);
            $overtimeRate = number_format($overtimeRate, 2);
            $levy_amount = number_format($levy_amount, 2);
            $deduction1 = number_format($deduction1, 2);
            $total = number_format($total, 2);

            $rows .= "
            <tr>
                <td>{$counter}</td>
                <td>{$row['employee_name']}</td>
                <td>{$ic_no}</td>
                <td class='txtRight'>{$client}</td>
                <td class='txtRight'>{$levy_amount} (" . round(($rowJi['levy_amount'] / 30),2) . "*". $no_of_days .")</td>
                <td class='txtRight'>{$normalRate} (" . round((($rowJi['basic_pay'] / 30) / 8),2) . "*". $nr_rate .")</td>
                <td class='txtRight'>{$overtimeRate} (" . (round((($rowJi['basic_pay'] / 30) / 8),2) * $rowJi['over_time_rate']) . "*". $ot_rate .")</td>
                <td class='txtRight'>{$deduction1}</td>
                <td class='txtRight'>{$total}</td>
            </tr>
            ";
            $counter++;
        }
        $overallTotal = number_format($overallTotal, 2);
        
        $text = "
        {$rows}
        <tr bgcolor=\"#A9A9A9\">
            <th colspan = '8' class='txtRight'>TOTAL</th>
            <th class='lastRowBgColor txtRight'>{$overallTotal}</th>
        </tr>
        ";

        return $text;
    }
}