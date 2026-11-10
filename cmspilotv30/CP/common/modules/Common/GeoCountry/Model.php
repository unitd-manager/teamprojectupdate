<?
class CP_Common_Modules_Common_GeoCountry_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $stockistAppendSQL = '';
        $stockistJoinSQL = '';
        if ($cpCfg['m.common.geoCountry.hasStockist']) {
            $stockistAppendSQL = ",s.company_name AS stockist_name";
            $stockistJoinSQL = "LEFT JOIN (stockist s) ON (gc.stockist_id = s.stockist_id)";
        }        
        
        $SQL   = "
        SELECT gc.*
        {$stockistAppendSQL}
        FROM geo_country gc
        {$stockistJoinSQL}
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $searchVar->mainTableAlias = 'gc';

        if (CP_SCOPE == 'www') {
            $searchVar->sqlSearchVar[] = "gc.published = 1";
        }

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "gc.geo_country_id = {$tv['record_id']}";

        } else {
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    gc.country_code LIKE '%{$tv['keyword']}%'
                    OR gc.name LIKE '%{$tv['keyword']}%'  
                )";
            }
        }
    }

    /**
     *
     */
    function getCountryDDSQL() {
        $append = (CP_SCOPE == 'www') ? 'WHERE gc.published = 1' : '';
        
        $SQL = "
        SELECT gc.country_code
              ,gc.name
        FROM geo_country gc
        {$append}
        ORDER BY gc.name
        ";

        return $SQL;
    }

    /**
     *
     */
    function getCodeByName($name) {
        $db = Zend_Registry::get('db');

        if ($name == '') {
            return;
        }

        $SQL    = "
        SELECT country_code
        FROM geo_country
        WHERE name = '{$name}'
        ";

        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows > 0) {
            $row = $db->sql_fetchrow($result);
            return $row['country_code'];
        }
    }
}
