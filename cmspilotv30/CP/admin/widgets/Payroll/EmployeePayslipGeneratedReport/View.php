<?
class CP_Admin_Widgets_Payroll_EmployeePayslipGeneratedReport_View extends CP_Common_Lib_WidgetViewAbstract
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
                    <th colspan='11'>Employee Payslip Generated Report</th>
                </thead>
            </table>
    		<div class = 'tableOuter scroll-pane'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Employee Name</th>
                        <th>Pass Type</th>
                        <th>Employee Status</th>
                        <th>NRIC/FIN No</th>
                        <th>Basic Pay</th>
                        <th>OT Amount</th>
                        <th>CPF(Employer)</th>
                        <th>CPF(Employee)</th>
                        <th>Allowance</th>
                        <th>Reimbursement</th>
                        <th>Deductions</th>
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
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $rows = '';
        $counter = 1;
        foreach($this->model->dataArray as $row){
            if ($row['citizen'] == 'Citizen' || $row['citizen'] == 'PR') {
                $ic_no = $row['nric_no'];
            } else {
                $ic_no = $row['fin_no'];
            }

            $OT  = $row['ot_hours'] * $row['overtime_pay_rate'];
            $gross_pay = $row['basic_pay'] + $row['ot_amount'] + $row['commission'] + $row['allowance1'] + $row['allowance2'] + $row['allowance3'] + $row['allowance4'] + $row['allowance5'];

            $total_allowance = $row['allowance1'] + $row['allowance2'] + $row['allowance3'] + $row['allowance4'] + $row['allowance5'];
            $total_allowance_display = number_format($total_allowance, 2);

            $total_deduction = round($row['cpf_employee'], 2) + $row['sdl'] + $row['loan_amount'] + $row['income_tax_amount'] + $row['pay_cdac'] + $row['pay_sinda'] + $row['pay_mbmf'] + $row['pay_eucf'] + $row['deduction1'] + $row['deduction2'] + $row['deduction3'] + $row['loan_deduction'];
            $total_deduction_display = number_format($total_deduction, 2);
            
            $net_total = $gross_pay + $row['reimbursement'] - $total_deduction;
            $net_total_display = number_format($net_total, 2);

            $cpf_employer = 0.00;
            if ($row['cpf_employer']) {
                $cpf_employer = $row['cpf_employer'];
            }

            $cpf_employee = 0.00;
            if ($row['cpf_employee']) {
                $cpf_employee = $row['cpf_employee'];
            }

            $rows .= "
            <tr>
                <td>{$counter}</td>
                <td>{$row['employee_name']}</td>
                <td>{$row['citizen']}</td>
                <td>{$row['employee_status']}</td>
                <td>{$ic_no}</td>
                <td>{$row['basic_pay']}</td>
                <td>{$OT}</td>
                <td>{$cpf_employer}</td>
                <td>{$cpf_employee}</td>
                <td>{$total_allowance_display}</td>
                <td>{$row['reimbursement']}</td>
                <td>{$total_deduction_display}</td>
                <td>{$net_total_display}</td>
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