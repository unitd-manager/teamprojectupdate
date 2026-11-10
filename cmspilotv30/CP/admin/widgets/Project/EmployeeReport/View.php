<?
class CP_Admin_Widgets_Project_EmployeeReport_View extends CP_Common_Lib_WidgetViewAbstract
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
                    <th>Project Name</th>
                    <th>Employee Name</th>
                    <th>Part Time / Full Time</th>
                    <th>Hours Worked</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                {$this->getRowsHTML()}
            </tbody>
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

            $url = "index.php?_topRm=project&module=enggCrm_project&_action=edit&project_id={$row['project_id']}";
            $amount = $row['add_hourly_rate'] * $row['employee_hours'];
            
                $rows .= "
                <tr>
                    <td>{$count}</td>
                    <td><a href='{$url}'>{$row['Project_name']}</a></td>
                    <td>{$row['employee_name']}</td>
                    <td>{$row['employee_work_type']}</td>
                    <td>{$row['employee_hours']}</td>
                    <td align='right'>{$amount}</td>
                </tr>
                ";                

                $count++;
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}