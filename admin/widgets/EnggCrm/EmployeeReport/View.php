<?
class CPL_Admin_Widgets_EnggCrm_EmployeeReport_View extends CP_Admin_Widgets_EnggCrm_EmployeeReport_View
{
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $status = $fn->getReqParam('status');

        $text = "
        <table class='thinlist summaryTable'>
            <thead>
                <th>Summary</th>
            </thead>
            <tr>
                <td>Status : {$status}</td>
            </tr>
        </table>
        <div class = 'tableOuter scroll-pane'>
        <table class='thinlist'>
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Employee Name</th>
                    <th>Passport No</th>
                    <th>FIN No</th>
                    <th>S Pass No</th>
                    <th>Date of birth</th>
                    <th>Date of expiry</th>
                    <th>Status</th>
                    <th>Salary</th>
                </tr>
            </thead>
            {$this->getRowsHTML()}
        </table>
        </div>
        ";
        return $text;
    }

    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $rows = '';
        $count = 1;

        foreach($this->model->dataArray as $row){

            $rows .= "
            <tbody class='employeeSummary'>
                <tr>
                    <td>{$count}</td>
                    <td>{$row['employee_name']}</a></td>
                    <td>{$row['passport']}</td>
                    <td>{$row['nric_no']}</td>
                    <td>{$row['spass_no']}</td>
                    <td>{$row['date_of_birth']}</td>
                    <td>{$row['date_of_expiry']}</td>
                    <td>{$row['status']}</td>
                    <td>{$row['salary']}</td>
                </tr>
            </tbody>
            ";                

            $count++;
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}