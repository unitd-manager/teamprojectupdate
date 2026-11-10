<?
class CP_Www_Modules_Forex_RateBoard_Model extends CP_Common_Lib_ModuleModelAbstract
{

    /**
     *
     */
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');

        $tagsSQL = "";

        $SQL = "
        SELECT c.*
        FROM currency c
        ";
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $searchVar->sqlSearchVar[] = "c.published = 1";

        if ($tv['record_id'] != '' ) {
            $searchVar->sqlSearchVar[] = "c.currency_id = '{$tv['record_id']}'";
        } else {
            $searchVar->sortOrder = "c.sort_order ASC";
        }
    }

}