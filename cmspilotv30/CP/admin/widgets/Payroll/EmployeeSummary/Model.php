<?
class CP_Admin_Widgets_Payroll_EmployeeSummary_Model extends CP_Common_Lib_WidgetModelAbstract
{
    //==================================================================//
    function getTotalCountOfEmployees($pass_type) {
        $fn    = Zend_Registry::get('fn');
        $db    = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $appendSqlSite = "";
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT COUNT(*) AS total_count
        FROM employee
        WHERE citizen = '{$pass_type}'
          AND status = 'Current'
          {$appendSqlSite}
        ";
        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);
        
        return $row['total_count'];
    }
}