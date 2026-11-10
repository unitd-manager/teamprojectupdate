<?
class CPL_Admin_Widgets_Common_MultiUniqueSite_Model extends CP_Admin_Widgets_Common_MultiUniqueSite_Model
{
    function getSQL() {
        $dbUtil = Zend_Registry::get('dbUtil');

        $condn = '';
        if($dbUtil->getColumnExists('site', 'sort_order')){
            $condn = "ORDER BY sort_order";
        }

        $SQL = "
        SELECT site_id, title
        FROM site
        WHERE published = 1
        {$condn}
        ";
        return $SQL;
    }

    function getSqlCondn($mainTblPrefix = '') {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');

        if ($mainTblPrefix != '') {
            $mainTblPrefix = $mainTblPrefix . '.';
        }

        $whereCond = '';
        $cp_site_id = $fn->getSessionParam('cp_site_id');
        if ($cpCfg['cp.hasMultiUniqueSites'] == 1 && $tv['module'] != 'webBasic_content'){
            $whereCond = "{$mainTblPrefix}site_id = {$cp_site_id}";
        }
        return $whereCond;
    }

}
