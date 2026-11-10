<?
class CP_Common_Modules_Ecommerce_PromoCode_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL   = "
        SELECT pc.* 
              ,c.title AS country_name
              ,cy.title AS city_name
        FROM promo_code pc
        LEFT JOIN (country c) ON (pc.country_id = c.country_id)
        LEFT JOIN (city cy) ON (pc.city_id = cy.city_id)
        ";
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'pc';


        if ($tv['record_id'] != '' ){
           $searchVar->sqlSearchVar[] = "pc.promo_code_id  = {$tv['record_id']}";
        }

        $searchVar->sortOrder = 'pc.promo_code_id';        
    }
}
