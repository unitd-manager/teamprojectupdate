<?
class CP_Www_Modules_Edukloud_Parent_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT p.*
              ,CONCAT_WS(' ', p.first_name, p.last_name ) AS parent_name
              ,gc.name AS country
        FROM `parent` p
        LEFT JOIN geo_country gc ON (p.address_country_code = gc.country_code)
        ";
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'p';

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "p.parent_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'p.parent_id');
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
               p.first_name LIKE '%{$tv['keyword']}%'
            OR p.last_name LIKE '%{$tv['keyword']}%'
            )";
        }        
    }
    
}
