<?
class CP_Admin_Modules_Directory_Reports_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $arr = $cpCfg['cp.dashboardArr'];
        
        $text = "
        <div id='reports' class='subcolumns'>
            {$this->getFRSummaryReport()}
        </div>
        ";
        
        return $text;
    }

    function getFRSummaryReport() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        $text = '';

        $SQL = "
        SELECT s.*
        FROM staff s
        JOIN user_group ug ON ug.user_group_id = s.user_group_id
        WHERE ug.title = 'DGD-FR'
        ORDER BY s.first_name
                ,s.last_name
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows();

        $dataArr = array();
        $days = 10;
        while ($rowS = $db->sql_fetchrow($result)) {
            $frName = $rowS['first_name'] . ' ' . $rowS['last_name'];
            $dataArr[$frName] = array();
            $dataArr[$frName]['days'] = array();
            for ($day = 0; $day <= $days; $day++) {
                $currDate = strtotime("-{$day} days");
                $dateStart = date('Y-m-d 00:00:00', $currDate);
                $dateEnd   = date('Y-m-d 23:59:59', $currDate);
                
                $SQL = "
                SELECT COUNT(*) AS count
                FROM business b
                WHERE b.dgd_modified_by = '" . escapeForDb($frName) . "'
                  AND b.dgd_modification_date BETWEEN '{$dateStart}' AND '{$dateEnd}'
                ";
                $resultC = $db->sql_query($SQL);
                $rowC = $db->sql_fetchrow($resultC);
                $dataArr[$frName]['days'][$day] = $rowC['count'];
            }
        }
        
        $trs = '';
        $tds = '';
        $ths = "
        <th class='name'>FR Name</th>
        <th class='count'>Average</th>
        ";
        for ($day = 0; $day <= $days; $day++) {
            $dayText = $day;
            if ($day == 0) {
                $dayText = 'Today';
            } else if ($day == 1) {
                $dayText = 'Yesterday';
            }
            $ths .= "
            <th class='day'>{$dayText}</th>
            ";
        }
        
        foreach ($dataArr as $frName => $arr) {
            $tds = '';
            $total = 0;
            foreach ($arr['days'] as $day => $count) {
                $total += $count;
                $tds .= "
                <td class='count'>{$count}</td>
                ";
            }
            $avg = round($total / ($days + 1), 2);
            $tds = "
            <td class='name'>{$frName}</td>
            <td class='count'>{$avg}</td>
            {$tds}
            ";
            
            $trs .= "
            <tr>
            {$tds}
            </tr>
            ";
            
        }
        $text = "
        <div id='frSummary'>
        <table class='thinlist'>
        {$ths}
        {$trs}
        </table>
        </div>
        ";
        
        return $text;
    }

}