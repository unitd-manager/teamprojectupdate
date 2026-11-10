<?
class CP_Admin_Widgets_Payroll_PayslipByEmployeeReport_View extends CP_Common_Lib_WidgetViewAbstract
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

        $employee_id     = $fn->getReqParam('employee_id');
        $year            = $fn->getReqParam('year');
        $employee_status = $fn->getReqParam('employee_status');

        $employeeRec = $fn->getRecordRowById('employee', 'employee_id', $employee_id);
        if ($employeeRec['citizen'] == 'Citizen' || $employeeRec['citizen'] == 'PR') {
            $ic_no = $employeeRec['nric_no'];
        } else {
            $ic_no = $employeeRec['fin_no'];
        }
        $address = $employeeRec['address_area'] . ' ' . $employeeRec['address_street'] . ' Singapore ' . $employeeRec['address_po_code'];
        $dob = $fn->getCPDate($employeeRec['date_of_birth'], 'd M Y');

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist summaryTable'>
                <thead>
                    <th colspan='6' class='txtCenter'>SUMMARY</th>
                </thead>
                <tr>
                    <td>Year : {$year}</td>
                    <td>Status : {$employee_status}</td>
                    <td>Employee Name : {$employeeRec['first_name']}</td>
                    <td>NRIC/FIN No : {$ic_no}</td>
                    <td>Address : {$address}</td>
                    <td>Date of Birth : {$dob}</td>
                </tr>
            </table>
    		<div class = 'tableOuter scroll-pane'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Month</th>
                        <th>Status</th>
                        <th class='txtRight'>Basic Pay</th>
                        <th class='txtRight'>OT Amount</th>
                        <th class='txtRight'>Director Fee</th>
                        <th class='txtRight'>CPF(Employer)</th>
                        <th class='txtRight'>CPF(Employee)</th>
                        <th class='txtRight'>Allowance</th>
                        <th class='txtRight'>Reimbursement</th>
                        <th class='txtRight'>Deductions</th>
                        <th class='txtRight'>Net Pay</th>
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
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows = '';
        $counter = 1;
        $total_basic_pay = 0;
        $total_ot_amount = 0;
        $total_director_amount = 0;
        $total_cpf_employer = 0;
        $total_cpf_employee = 0;
        $total_allowance = 0;
        $total_reimbursement = 0;
        $total_deduction = 0;
        $total_net_pay = 0;

        foreach($this->model->dataArray as $row){
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

            $month = $dateUtil->getLongMonthName($row['payroll_month']);

            $rows .= "
            <tr>
                <td>{$counter}</td>
                <td>{$month}</td>
                <td>{$row['status']}</td>
                <td class='txtRight'>{$row['basic_pay']}</td>
                <td class='txtRight'>{$OT}</td>
                <td class='txtRight'>{$row['director_fee']}</td>
                <td class='txtRight'>{$cpf_employer}</td>
                <td class='txtRight'>{$cpf_employee}</td>
                <td class='txtRight'>{$total_allowance_display}</td>
                <td class='txtRight'>{$row['reimbursement']}</td>
                <td class='txtRight'>{$total_deduction_display}</td>
                <td class='txtRight'>{$net_total_display}</td>
            </tr>
            ";
            $counter++;

            $total_basic_pay += $row['basic_pay'];
            $total_ot_amount += $OT;
            $total_director_amount += $row['director_fee'];
            $total_cpf_employer += $cpf_employer;
            $total_cpf_employee += $cpf_employee;
            $total_allowance += $total_allowance;
            $total_reimbursement += $row['reimbursement'];
            $total_deduction += $total_deduction;
            $total_net_pay += $net_total;
        }

        $total_basic_pay_formatted = number_format($total_basic_pay, 2);
        $total_ot_amount_formatted = number_format($total_ot_amount, 2);
        $total_director_amount_formatted = number_format($total_director_amount, 2);
        $total_cpf_employer_formatted = number_format($total_cpf_employer, 2);
        $total_cpf_employee_formatted = number_format($total_cpf_employee, 2);
        $total_allowance_formatted = number_format($total_allowance, 2);
        $total_reimbursement_formatted = number_format($total_reimbursement, 2);
        $total_deduction_formatted = number_format($total_deduction, 2);
        $total_net_pay_formatted = number_format($total_net_pay, 2);
        
        $text = "
        {$rows}
        <tr style='font-weight:bold;'>
            <td colspan='3' class='txtRight'>TOTAL</td>
            <td class='txtRight'>{$total_basic_pay_formatted}</td>
            <td class='txtRight'>{$total_ot_amount_formatted}</td>
            <td class='txtRight'>{$total_director_amount_formatted}</td>
            <td class='txtRight'>{$total_cpf_employer_formatted}</td>
            <td class='txtRight'>{$total_cpf_employee_formatted}</td>
            <td class='txtRight'>{$total_allowance_formatted}</td>
            <td class='txtRight'>{$total_reimbursement_formatted}</td>
            <td class='txtRight'>{$total_deduction_formatted}</td>
            <td class='txtRight'>{$total_net_pay_formatted}</td>
        </tr>
        ";

        return $text;
    }
}