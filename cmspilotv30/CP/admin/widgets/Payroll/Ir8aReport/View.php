<?
class CP_Admin_Widgets_Payroll_Ir8aReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');
        $c = &$this->controller;

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        $aisLink = "index.php?module=payroll_payrollManagement&_spAction=generateAisTxtFile&year={$cpCfg['cp.ir8aFormForYear']}&showHTML=0";

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist summaryTable'>
                <thead>
                    <th colspan='6' class='txtCenter'>
                        <div><a href='{$aisLink}' class='button float_right'>AIS File</a></div>
                        <div style='font-size:25px;' class='txtCenter'>IR8A Report for {$cpCfg['cp.ir8aFormForYear']}</div>
                    </th>
                </thead>
            </table>
    		<div class = 'tableOuter scroll-pane'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Employee Name</th>
                        <th>Status</th>
                        <th>Date of Birth</th>
                        <th>NRIC/ FIN</th>
                        <th>Payroll months</th>
                        <th class='txtRight'>Gross Salary</th>
                        <th class='txtRight'>Total Allowance</th>
                        <th class='txtRight'>Overall Income</th>
                        <th class='txtRight'>Gross Employee CPF</th>
                        <th class='txtCenter'>Print IR8A form</th>
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
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows = '';
        $counter = 1;
        foreach($this->model->dataArray as $row){
            $dob = $fn->getCPDate($row['dob'], 'd-m-Y');

            /*
            if ($row['citizen'] == 'SP' || $row['citizen'] == 'WP'){
                $id_no = $row['work_permit'];
            } else 
            */
            if ($row['citizen'] == 'PR' || $row['citizen'] == 'Citizen'){
                $id_no = $row['nric_no'];
            } else {
                $id_no = $row['fin_no'];
            }

            $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
            $appendSqlSite = "";
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSqlSite = "AND site_id = {$cpSiteIdSession}";
            }

            $sqlPmMonth = "
            SELECT payroll_month
                  ,basic_pay
                  ,cpf_employee
                  ,ot_amount
                  ,allowance1
                  ,allowance2
                  ,allowance3
                  ,allowance4
                  ,allowance5
            FROM payroll_management
            WHERE payroll_year = '{$cpCfg['cp.ir8aFormForYear']}'
              AND employee_id = '{$row['employee_id']}'
              AND (status = 'Generated' OR status = 'Approved' OR status = 'Paid')
              {$appendSqlSite}
            ORDER BY payroll_month
            ";
            $resultPmMonth = $db->sql_query($sqlPmMonth);
            $numRowsPmMonth = $db->sql_numrows($resultPmMonth);
            $countPmMonth = 1;
            $total_amount = 0;
            $total_allowance = 0;
            $total_cpf_amount = 0;
            $payroll_months_display = '';
            while ($rowPmMonth = $db->sql_fetchrow($resultPmMonth)) {
                if ($countPmMonth == $numRowsPmMonth) {
                    $payroll_months_display .= $rowPmMonth['payroll_month'];
                } else {
                    $payroll_months_display .= $rowPmMonth['payroll_month'] . ', ';
                }
                $countPmMonth++;
                $total_amount += $rowPmMonth['basic_pay'] + $rowPmMonth['ot_amount'];
                $total_allowance += $rowPmMonth['allowance1'] + $rowPmMonth['allowance2'] + $rowPmMonth['allowance3'] + $rowPmMonth['allowance4'] + $rowPmMonth['allowance5'];
                $total_cpf_amount += $rowPmMonth['cpf_employee'];
            }

            $total_amount_formatted = number_format($total_amount, 2);
            $total_allowance_formatted = number_format($total_allowance, 2);
            $total_cpf_amount_formatted = number_format($total_cpf_amount, 2);
            $overall_amount = $total_amount + $total_allowance;
            $overall_amount_formatted = number_format($overall_amount, 2);

            //$export = "index.php?module=payroll_payrollManagement&_spAction=printIr8aForm&employee_id={$row['employee_id']}&year={$cpCfg['cp.ir8aFormForYear']}&showHTML=0";
            $print = "index.php?module=payroll_payrollManagement&_spAction=printIr8aFormInPdf&employee_id={$row['employee_id']}&year={$cpCfg['cp.ir8aFormForYear']}&showHTML=0";

            $bgColor = '';
            if ($row['status'] == 'Archive' && $row['ir21_filed'] == 1){
                $bgColor = 'background-color:grey';
            }

            $rows .= "
            <tr style='{$bgColor}'>
                <td>{$counter}</td>
                <td>{$row['employee_name']}</td>
                <td>{$row['status']}</td>
                <td>{$dob}</td>
                <td>{$id_no}</td>
                <td>{$payroll_months_display}</td>
                <td class='txtRight'>{$total_amount_formatted}</td>
                <td class='txtRight'>{$total_allowance_formatted}</td>
                <td class='txtRight'>{$overall_amount_formatted}</td>
                <td class='txtRight'>{$total_cpf_amount_formatted}</td>
                <td class='txtCenter'><a href='{$print}' target='_blank'>Print</a></td>
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