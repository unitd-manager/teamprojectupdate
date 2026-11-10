<?
class CP_Admin_Widgets_Project_TaskSummary_Model extends CP_Common_Lib_WidgetModelAbstract
{
    //==================================================================//
    function getTotalTasksRaisedToday() {
        $db = Zend_Registry::get('db');
        
        $today = date('Y-m-d');
        
        $SQL = "
        SELECT count(*) AS total
        FROM task
        WHERE creation_date = '{$today}' 
        ";
        $result  = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        return $row['total'];
    }

    //==================================================================//
    function getTotalTasksRaisedThisWeek() {
        $db = Zend_Registry::get('db');
        $dateUtil = Zend_Registry::get('dateUtil');
        
        $today = date('Y-m-d');
        list($start_date, $end_date) = $dateUtil->getWeekRange($today);

        $SQL = "
        SELECT count(*) AS total
        FROM task
        WHERE creation_date BETWEEN '{$start_date} 00:00:00' AND '{$end_date} 23:59:59'
        ";
        $result  = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        return $row['total'];
    }

    //==================================================================//
    function getTotalHrsRaisedToday() {
        $db = Zend_Registry::get('db');

        $today = date('Y-m-d');

        $SQL = "
        SELECT FORMAT(SUM(hours), 2) AS total
        FROM timesheet
        WHERE entry_date = '{$today}' 
        ";
        $result  = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        return $row['total'];
    }

    //==================================================================//
    function getTotalHrsRaisedThisWeek() {
        $db = Zend_Registry::get('db');
        $dateUtil = Zend_Registry::get('dateUtil');

        $today = date('Y-m-d');
        list($start_date, $end_date) = $dateUtil->getWeekRange($today);

        $SQL = "
        SELECT  FORMAT(SUM(hours), 2) AS total
        FROM timesheet
        WHERE entry_date BETWEEN '{$start_date}' AND '{$end_date}'
        ";
        $result  = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        return $row['total'];
    }
}