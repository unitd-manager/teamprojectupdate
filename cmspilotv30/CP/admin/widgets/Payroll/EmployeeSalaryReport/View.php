<?
class CP_Admin_Widgets_Payroll_EmployeeSalaryReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $fn       = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');

        $c = &$this->controller;

        $rowsHTML = $this->getRowsHTML();
        $text = '';


        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist summaryTable'>
                <thead>
                    <th colspan='11'>Employee Salary Report</th>
                </thead>
            </table>
    		<div class = 'tableOuter scroll-pane'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Employee Name</th>
                        <th>NRIC</th>
                        <th>DOB</th>
                        <th>Age</th>
                        <th>Designation</th>
                        <th>Department</th>
                        <th>Basic Pay</th>
                        <th>Total Allowances</th>
                        <th>Total Deductions</th>
                        <th>Net Pay</th>
                    </tr>
                </thead>
                <tbody>
                    {$rowsHTML}
                </tbody>
            </table>
            </div>
            ";
        }

        return $text;
    }
    
    /**
     *
     */
    function getRowsHTML() {
        $fn    = Zend_Registry::get('fn');
        $db    = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows = '';
        $counter = 1;
        foreach($this->model->dataArray as $row){
            $date_of_birth = $fn->getCPDate($row['dob'], 'd M Y');

            if ($row['citizen'] == 'Citizen' || $row['citizen'] == 'PR') {
                $ic_no = $row['nric_no'];
            } else {
                $ic_no = $row['fin_no'];
            }

            $age = '';
            if ($row['dob']) {
                $dob = $fn->getCPDate($row['dob'], 'Y');
                $age = date('Y')- $dob;
            }

            if ($row['status'] == 'Current') {
                $sqlAppend = "j.status = 'Current'";                
            } else {
                $sqlAppend = "j.status != 'Current'";
            }

            $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
            $appendSqlSite = "";
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSqlSite = "AND j.site_id = {$cpSiteIdSession}";
            }

            $sqlJobInfo = "
            SELECT j.designation
                  ,j.department
                  ,j.basic_pay
                  ,j.allowance1
                  ,j.allowance2
                  ,j.allowance3
                  ,j.allowance4
                  ,j.allowance5
                  ,j.deduction1
                  ,j.deduction2
                  ,j.deduction3
            FROM job_information j
            WHERE j.employee_id = {$row['employee_id']}
              AND {$sqlAppend}
              {$appendSqlSite}
            ORDER BY j.job_information_id DESC
            LIMIT 0,1
            ";
            $resultJobInfo = $db->sql_query($sqlJobInfo);
            $rowJi = $db->sql_fetchrow($resultJobInfo);

            $basic_pay_formatted = number_format($rowJi['basic_pay'], 2);
            $total_allowance = $rowJi['allowance1'] + $rowJi['allowance2'] + $rowJi['allowance3'] + $rowJi['allowance4'] + $rowJi['allowance5'];
            $total_allowance_formatted = number_format($total_allowance, 2);
            $total_deduction = $rowJi['deduction1'] + $rowJi['deduction2'] + $rowJi['deduction3'];
            $total_deduction_formatted = number_format($total_deduction, 2);
            $net_pay = number_format(($rowJi['basic_pay'] + $total_allowance - $total_deduction),2);

            $rows .= "
            <tr>
                <td>{$counter}</td>
                <td>{$row['employee_name']}</td>
                <td>{$ic_no}</td>
                <td>{$date_of_birth}</td>
                <td>{$age}</td>
                <td>{$rowJi['designation']}</td>
                <td>{$rowJi['department']}</td>
                <td>{$basic_pay_formatted}</td>
                <td>{$total_allowance_formatted}</td>
                <td>{$total_deduction_formatted}</td>
                <td>{$net_pay}</td>
            </tr>
            ";
            $counter++;
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}