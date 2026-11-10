<?
class CP_Admin_Widgets_Hms_DutyRosterReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQLSite = "
        SELECT title
        FROM site
        ";
        $resultSite     = $db->sql_query($SQLSite);
        $LocationTitle = "";
        while ($rowSite = $db->sql_fetchrow($resultSite)) {
            $LocationTitle .= "<th>{$rowSite['title']}</th>";
        }

        $text = "
        <h2>Duty Roster Report</h2>
        <div class = 'tableOuter scroll-pane'>
        <table class='thinlist'>
            <thead>
                <tr>
                    <th>Dr Name</th>
                    {$LocationTitle}
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
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';
        $count = 1;

        foreach($this->model->dataArray as $row){

            $SQLSite = "
            SELECT site_id
            FROM site
            ";
            $resultSite     = $db->sql_query($SQLSite);
            $Location1 = "";
            while ($rowSite = $db->sql_fetchrow($resultSite)) {
                $SQLTimings ="
                SELECT s.site_id
                      ,CONCAT_WS(' - ', LOWER(DATE_FORMAT(r.work_from_time, '%l:%i %p'))
                      , LOWER(DATE_FORMAT(r.work_to_time, '%l:%i %p'))) AS work_time
                FROM site s
                LEFT JOIN (duty_roster r) ON (r.site_id = s.site_id)
                WHERE r.employment_id = {$row['employment_id']}
                AND r.site_id = {$rowSite['site_id']}
                GROUP BY r.work_from_time, r.work_to_time
                ";

                $resultTimings     = $db->sql_query($SQLTimings);

                $Location1 .= "<td>";
                while($rowTimings = $db->sql_fetchrow($resultTimings)){
                    $Location1 .= "{$rowTimings['work_time']}<br/>";
                }

                $Location1 .= "</td>";
            }


            $rows .= "
            <tr>
                <td>{$row['employee_name']}</td>
                {$Location1}
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