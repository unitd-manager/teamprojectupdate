<?
class CP_Common_Modules_Directory_State_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL = "
        SELECT s.*
        	  ,c.title AS country_title
        FROM state s
        LEFT JOIN country c ON s.country_id = c.country_id
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
        $searchVar->mainTableAlias = 's';

        $country_id = $fn->getSessionParam('cp_country_id');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "s.state_id = {$tv['record_id']}";
        }
		
        if ($country_id != '' ) {
            $searchVar->sqlSearchVar[] = "s.country_id = '{$country_id}'";
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(s.title   LIKE '%{$tv['keyword']}%'  
                                          )";
        }

		$searchVar->sortOrder = "s.title";
    }
}
