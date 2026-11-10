<?
class CP_Admin_Widgets_Project_ProjectReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');


        $text = "
        <h2>Project Report</h2>
        <div class = 'tableOuter scroll-pane'>
        <table class='thinlist'>
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Project Name</th>
                    <th>Client Name</th>
                    <th>Amount</th>
                    <th>Status</th>
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
        
            $amount = $row['add_hourly_rate'] * $row['employee_hours'];
            
                $rows .= "
                <tr>
                    <td>{$count}</td>
                    <td>{$row['Project_name']}</td>
                    <td>{$row['client_name']}</td>
                    <td align='right'>{$amount}</td>
                    <td>{$row['status']}</td>
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