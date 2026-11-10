<?
class CP_Admin_Widgets_Payroll_LeaveReport_View extends CP_Common_Lib_WidgetViewAbstract
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

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist summaryTable'>
                <thead>
                    <th colspan='6'>Leave Report</th>
                </thead>
            </table>
    		<div class = 'tableOuter scroll-pane'>
            {$rowsHTML}
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

        $year = $fn->getReqParam('year');

        $rows = '';
        $leave_type = '';
        $counter = 1;
        $employee_id = '';
        foreach($this->model->dataArray as $row){

            if ($row['citizen'] == 'Citizen' || $row['citizen'] == 'PR') {
                $ic_no = $row['nric_no'];
            } else {
                $ic_no = $row['fin_no'];
            }

            if ($leave_type != $row['leave_type']) {
                $rows .= "
                <tr>
                    <td colspan='9' align='center'><strong>{$row['leave_type']}</strong></td>
                </tr>
                ";
            }

            $rows .= "
            <tr>
                <td>{$counter}</td>
                <td>{$row['employee_name']}</td>
                <td>{$ic_no}</td>
                <td class='txtCenter'>{$dateUtil->formatDate($row['from_date'], 'DD MMM YYYY')}</td>
                <td class='txtCenter'>{$dateUtil->formatDate($row['to_date'], 'DD MMM YYYY')}</td>
                <td class='txtCenter'>{$row['no_of_days']}</td>
                <td class='txtCenter'>{$row['no_of_days_next_month']}</td>
                <td>{$row['reason']}</td>
            </tr>
            ";
            $leave_type = $row['leave_type'];
            $employee_id = $row['employee_id'];
            $counter++;
        }

        if ($employee_id == '') {
            return "<div class='txtCenter mt10'><strong>No records found</strong></div>";
        }

        $text = "
        <table class='thinlist'>
            <thead>
                <tr> 
                    <th>S.No</th>
                    <th>Employee Name</th>
                    <th>NRIC/FIN</th>
                    <th class='txtCenter'>Leave from date</th>
                    <th class='txtCenter'>Leave to date</th>
                    <th class='txtCenter'>No Of Days (Current Month)</th>
                    <th class='txtCenter'>No Of Days (Next Month)</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                {$rows}
                <tr>
                    <td colspan='8' class='txtCenter'><strong>SUMMARY</strong></td>
                </tr>
                <tr>
                    <td colspan='2' class='txtCenter'><strong>Total Absent Leave: {$this->model->getTotalLeaveDays($employee_id, 'Absent', $year)}</strong></td>
                    <td colspan='2' class='txtCenter'><strong>Total Annual Leave: {$this->model->getTotalLeaveDays($employee_id, 'Annual Leave', $year)}</strong></td>
                    <td colspan='2' class='txtCenter'><strong>Total Hospitalization Leave: {$this->model->getTotalLeaveDays($employee_id, 'Hospitalization Leave', $year)}</strong></td>
                    <td colspan='2' class='txtCenter'><strong>Total Sick Leave: {$this->model->getTotalLeaveDays($employee_id, 'Sick Leave', $year)}</strong></td>
                </tr>
            </tbody>
        </table>
        ";

        return $text;
    }
}