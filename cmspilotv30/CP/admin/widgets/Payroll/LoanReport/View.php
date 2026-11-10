<?
class CP_Admin_Widgets_Payroll_LoanReport_View extends CP_Common_Lib_WidgetViewAbstract
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
                    <th colspan='6'>Loan Report</th>
                </thead>
            </table>
    		<div class = 'tableOuter scroll-pane'>
            <table class='thinlist'>
                <thead>
                    <tr> 
                        <th>S.No</th>
                        <th>Emplloyee Name</th>
                        <th>NRIC</th>
                        <th>Designation</th>
                        <th>Department</th>
                        <th>Loan Duration</th>
                        <th>Loan Amount</th>
                        <th>Amount Returned</th>
                        <th>Balance to be Paid</th>
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
        foreach($this->model->dataArray as $row){

            $rows .= "
            <tr>
                <td>{$counter}</td>
                <td>{$row['employee_name']}</td>
                <td>{$row['nric_no']}</td>
                <td>{$row['position']}</td>
                <td>{$row['department']}</td>
                <td>{$row['no_of_months']}</td>
                <td>{$row['amount']}</td>
                <td>{$row['due_date']}</td>
                <td>{$row['loan_closing_date']}</td>
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