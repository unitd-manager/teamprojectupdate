<?
class CP_Www_Modules_Web2_Feed_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $extraTableNames = "";
        
        $SQL = "
        SELECT f.*
        FROM feed f
        ";
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'f';

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "f.feed_id = '{$tv['record_id']}'";
        } else {

            $searchVar->sortOrder = "f.sort_order ASC, f.content_date DESC";
        }
        
    }
}
