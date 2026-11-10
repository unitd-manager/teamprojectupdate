<?
class CP_Admin_Widgets_Payroll_CsocExpiry_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $dateUtil = Zend_Registry::get('dateUtil');

        $current_date = date('Y-m-d');
        $after60days  = date('Y-m-d', strtotime("+60 days"));

        $rows = '';
        $sql = "
        SELECT first_name, work_permit_expiry_date FROM employee
        WHERE status = 'Current'
          AND work_permit_expiry_date BETWEEN '{$current_date}' AND '{$after60days}'
          AND work_permit_expiry_date != ''
        ORDER BY work_permit_expiry_date ASC
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
                    <td>{$dateUtil->formatDate($row['work_permit_expiry_date'], 'DD MMM YYYY')}</td>
                </tr>
                ";
                $count++;
            }
        } else {
            $rows .= "
            <tr>
                <td colspan='3'>No employee has renewal for next 60 days.</td>
            </tr>
            ";
        }

        $text = "
        <h2>CSOC Expiry Reminders</h2>
        <div class='tableOuter'>
            <table class='thinList list'>
                <tr>
                    <th width='10%' height='18px' class='iconCounter'></th>
                    <th width='75%' class='iconName'></th>
                    <th width='15%' class='iconDate'></th>
                </tr>
                {$rows}
            </table>
        </div>
        ";

        return $text;
    }
}