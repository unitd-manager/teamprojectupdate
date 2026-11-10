<?
class CP_Admin_Widgets_Common_MultiCountry_Model extends CP_Common_Lib_WidgetModelAbstract
{
    function getCountrySQL() {
        $db = Zend_Registry::get('db');
        $db = Zend_Registry::get('db');

        $SQL = "
        SELECT c.country_id
              ,c.title
        FROM country c
        ORDER BY c.title
        ";
        
        return $SQL;

    }

    function setDefaultCountry() {
        $fn = Zend_Registry::get('fn');
        $cp_country_id = $fn->getSessionParam('cp_country_id');
        $fn->setSessionParam('cp_country_id', $cp_country_id);
    }

    function getChangeCountry() {
        $fn = Zend_Registry::get('fn');
        $cp_country_id = $fn->getReqParam('cp_country_id');
        $fn->setSessionParam('cp_country_id', $cp_country_id);
    }
}
