<?
class CP_Admin_Widgets_Common_MultiUniqueSite_Model extends CP_Common_Lib_WidgetModelAbstract
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

    function getSitesArray() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $dbUtil = Zend_Registry::get('dbUtil');
        $SQL = $this->getSQL();
        $arr = $dbUtil->getArrayFromSQLForVL($SQL);

        return $arr;

    }

    function setDefaultSite() {
        $fn = Zend_Registry::get('fn');

        $cp_site_id = $fn->getSessionParam('cp_site_id');

        if ($cp_site_id == '') {
            $SQL = $this->getSQL();
            $rowSite = $fn->getRecordBySQL($SQL);

            $cp_site_id = $rowSite['site_id'];
            $fn->setSessionParam('cp_site_id', $cp_site_id);
        }
    }

    function getChangeSite() {
        $fn = Zend_Registry::get('fn');
        $cp_site_id = $fn->getReqParam('cp_site_id');
        $fn->setSessionParam('cp_site_id', $cp_site_id);
    }

    function getSqlCondn($mainTblPrefix = '') {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($mainTblPrefix != '') {
            $mainTblPrefix = $mainTblPrefix . '.';
        }

        $whereCond = '';
        $cp_site_id = $fn->getSessionParam('cp_site_id');
        if ($cpCfg['cp.hasMultiUniqueSites']){
            $whereCond = "{$mainTblPrefix}site_id = {$cp_site_id}";
        }
        return $whereCond;
    }

    function getSiteRecord() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $row = $fn->getRecordRowByID('site', 'site_id', $cpCfg['cp.site_id']);

        return $row;
    }
}
