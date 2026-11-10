<?
class CP_Admin_Widgets_Payroll_ForeignWorkers_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $fn    = Zend_Registry::get('fn');
        $db    = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $appendSqlSite = "";
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND site_id = {$cpSiteIdSession}";
        }

        $rows = '';
        $sql = "
        SELECT first_name
              ,fin_no
              ,citizen
        FROM employee
        WHERE status = 'Current'
          AND (citizen = 'EP' OR citizen = 'SP' OR citizen = 'WP' OR citizen = 'DP')
          {$appendSqlSite}
        ORDER BY citizen = 'WP', citizen = 'SP', citizen = 'DP', citizen = 'EP', first_name ASC
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
                    <td>{$row['fin_no']}</td>
                    <td>{$row['citizen']}</td>
                </tr>
                ";
                $count++;
            }
        } else {
            $rows .= "
            <tr>
                <td colspan='3'>No employee found</td>
            </tr>
            ";
        }

        $text = "
        <h2>List of Foreign workers</h2>
        <div class='tableOuter'>
            <table class='thinList list'>
                <tr>
                    <th>S.No</th>
                    <th>Employee Name</th>
                    <th>NRIC No</th>
                    <th>Pass Type</th>
                </tr>
                {$rows}
            </table>
        </div>
        ";

        return $text;
    }
}