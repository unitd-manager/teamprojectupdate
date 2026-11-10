<?
class CP_Admin_Widgets_Payroll_CPFSummaryReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $fn = Zend_Registry::get('fn');
        $c = &$this->controller;

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist summaryTable'>
                <thead>
                    <th colspan='6'>CPF Summary Report</th>
                </thead>
            </table>
    		<div class = 'tableOuter scroll-pane'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Employee Name</th>
                        <th>NRIC</th>
                        <th align='right'>CPF by Employer</th>
                        <th align='right'>CPF By Employee</th>
                        <th align='right'>Total CPF</th>
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
        $overall_cpf_employer = 0;
        $overall_cpf_employee = 0;
        $overall_total_cpf = 0;
        foreach ($this->model->dataArray as $row) {
            $cpf_employer_formatted = number_format($row['cpf_employer'],2);
            $cpf_employee_formatted = number_format($row['cpf_employee'],2);
            $total_cpf_formatted = number_format($row['total_cpf_contribution'],2);
            
            $overall_cpf_employer += $row['cpf_employer'];
            $overall_cpf_employee += $row['cpf_employee'];
            $overall_total_cpf += $row['total_cpf_contribution'];
            
            $rows .= "
            <tr>
                <td>{$counter}</td>
                <td>{$row['employee_name']}</td>
                <td>{$row['nric_no']}</td>
                <td align='right'>{$cpf_employer_formatted}</td>
                <td align='right'>{$cpf_employee_formatted}</td>
                <td align='right'>{$total_cpf_formatted}</td>
            </tr>
            ";
            $counter++;
        }
        
        $overall_cpf_employer_formatted = number_format($overall_cpf_employer,2);
        $overall_cpf_employee_formatted = number_format($overall_cpf_employee,2);
        $overall_total_cpf_formatted = number_format($overall_total_cpf,2);

        $text = "
        {$rows}
        <tr>
            <td colspan='3' align='right'><strong>TOTAL</strong></td>
            <td align='right'><strong>{$overall_cpf_employer_formatted}</strong></td>
            <td align='right'><strong>{$overall_cpf_employee_formatted}</strong></td>
            <td align='right'><strong>{$overall_total_cpf_formatted}</strong></td>
        </tr>
        ";

        return $text;
    }
}