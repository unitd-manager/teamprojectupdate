<?
class CP_Www_Modules_Forex_RateBoardAcc_Model extends CP_Common_Lib_ModuleModelAbstract
{

    /**
     *
     */
    function getSQL() {
        $SQL = "
        SELECT cc.*
        	  ,c.currency_id
        	  ,c.title AS country
        	  ,c.code AS currency_code
        	  ,c2.title AS to_currency
        	  ,c2.code AS to_currency_code
        FROM currency_convert cc
        LEFT JOIN currency c ON c.currency_id = cc.from_currency_id
        LEFT JOIN currency c2 ON c2.currency_id = cc.to_currency_id
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

        //$searchVar->sqlSearchVar[] = "c.published = 1";

        if ($tv['record_id'] != '' ) {
            $searchVar->sqlSearchVar[] = "c.currency_id = '{$tv['record_id']}'";
        }
        $searchVar->sortOrder = "cc.sort_order ASC";
    }
}