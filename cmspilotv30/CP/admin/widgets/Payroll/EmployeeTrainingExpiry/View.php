<?
class CP_Admin_Widgets_Payroll_EmployeeTrainingExpiry_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $dateUtil = Zend_Registry::get('dateUtil');

        $current_date = date('Y-m-d');
        $after60days  = date('Y-m-d', strtotime("+60 days"));

        $rows = '';
        $sql = "
        SELECT e.first_name, ts.from_date, ts.to_date, t.title FROM training_staff ts
        LEFT JOIN (employee e) ON (ts.staff_id = e.employee_id)
        LEFT JOIN (training t) ON (ts.training_id = t.training_id)
        WHERE e.status = 'Current'
          AND ts.to_date BETWEEN '{$current_date}' AND '{$after60days}'
          AND ts.to_date != ''
        ORDER BY ts.to_date ASC
        ";
        $result  = $db->sql_query($sql);
        $numRows = $db->sql_numrows($result);
        if ($numRows) {
            $count = 1;
            while ($row = $db->sql_fetchrow($result)) {
                $rows .= "
                <tr>
                    <td>{$count}</td>
                    <td>{$row['first_name']}</td>
                    <td>{$row['title']}</td>
                    <td>{$dateUtil->formatDate($row['to_date'], 'DD MMM YYYY')}</td>
                </tr>
                ";
                $count++;
            }
        } else {
            $rows .= "
            <tr>
                <td colspan='4'>No employee has renewal for next 60 days.</td>
            </tr>
            ";
        }

        $text = "
        <h2>Training Expiry Reminders</h2>
        <div class='tableOuter'>
            <table class='thinList list' width='100%'>
                <tr>
                    <th width='10%' height='18px' class='iconCounter'></th>
                    <th width='40%' class='iconName'></th>
                    <th width='35%' class='certName'></th>
                    <th width='15%' class='iconDate'></th>
                </tr>
                {$rows}
            </table>
        </div>
        ";

        return $text;
    }
}