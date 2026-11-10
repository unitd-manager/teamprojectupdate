<?
class CP_Admin_Widgets_EnggCrm_EmployeeReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');


        $text = "
        <h2>Employee Report</h2>
        <div class = 'tableOuter scroll-pane'>
        <table class='thinlist'>
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Employee Name</th>
                    <th>Project Code</th>
                    <th>Project Title</th>
                    <th>Start Date</th>
                    <th>End Date</th>
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

        $employee_details = $this->getemployeeDetails($row['employee_id']);
            //$url = "index.php?_topRm=project&module=enggCrm_project&_action=edit&project_id={$row['project_id']}";
            //$amount = $row['add_hourly_rate'] * $row['employee_hours'];
            
                $rows .= "
                <tbody class='employeeSummary'>
                    <tr>
                        <td>{$count}</td>
                        <td class='employeeVal'>{$row['employee_name']}</a></td>
                        <td>{$row['project_code']}</td>
                        <td>{$row['title']}</td>
                        <td>{$row['start_date']}</td>
                        <td>{$row['actual_finish_date']}</td>
                    </tr>
                    <tr>
                        <td class='employeeDetailsMain' colspan='6'>{$employee_details}</td>
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
    /**
     * 
     */
    function getemployeeDetails($employee_id) {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows  = '';
        $count = 1;


        $sqlemployee = "
        SELECT month
              ,DATE_FORMAT(date, '%M') AS Month
              ,project_id
        FROM `employee_timesheet` et
        LEFT JOIN employee e ON (e.employee_id = et.employee_id)
        ";

        $resultemployee = $db->sql_query($sqlemployee);
        while ($rowemployee = $db->sql_fetchrow($resultemployee)) {

        $employeehrs_details = $this->getemployeehrsDetails($rowemployee['project_id']);

            $rows .= "
            <tbody class='employeehrsSummary'>
                <tr>
                    <td>{$count}</td>
                    <td class='employeehrsVal'>{$rowemployee['Month']}</td>
                <tr>
                <tr>
                    <td class='employeehrsDetailsMain' colspan='6'>{$employeehrs_details}</td>
                </tr>
            </tbody>
            ";
            $count++;
        }

        $text = "
        <div class='employeeDetails mt5'>
            <table class='paymentDetails'>
            <tr>
                <td>S.No</td>
                <td>Month</td>
            </tr>
            {$rows}
            </table>
        </div>
        ";
        
        return $text;
    }

    /**
     * 
     */
    function getemployeehrsDetails($project_id) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows  = '';
        $count = 1;


        $sqlemployeehrs = "
        SELECT et.*
        FROM `employee_timesheet` et
        ORDER BY et.date ASC
        ";

        $resultemployeehrs = $db->sql_query($sqlemployeehrs);
        while ($rowemployeehrs = $db->sql_fetchrow($resultemployeehrs)) {

        $employee_date   = $fn->getCPDate($rowemployeehrs['date'], 'd-m-Y');


            $rows .= "
            <tr>
                <td>{$count}</td>
                <td>{$employee_date}</td>
                <td>{$rowemployeehrs['employee_hours']}</td>
            <tr>
            ";
            $count++;
        }

        $text = "
        <div class='employeehrsDetails mt5'>
            <table class='paymentDetails'>
            <tr>
                <td>S.No</td>
                <td>Date</td>
                <td>Hours</td>
            </tr>
            {$rows}
            </table>
        </div>
        ";
        
        return $text;
    }
}