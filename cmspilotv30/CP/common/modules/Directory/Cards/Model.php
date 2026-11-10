<?
class CP_Common_Modules_Directory_Cards_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $SQL = "
        SELECT c.*
        	  ,co.title AS country_title
        FROM cards c
        LEFT JOIN country co ON co.country_id = c.country_id
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType) {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.card_id = {$tv['record_id']}";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.card_id');
            
            $country_id = $fn->getSessionParam('cp_country_id');
            if ($country_id != '') {
                $searchVar->sqlSearchVar[] = "(c.country_id = '{$country_id}' OR c.country_id IS NULL)";
            }    		
            
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    c.title   LIKE '%{$tv['keyword']}%'  
                )";
            }
    
    		$searchVar->sortOrder = "c.title";
    	}
    }

    /**
     *
     */
    function getCardSQL() {
        $fn = Zend_Registry::get('fn');
        $country_id = $fn->getSessionParam('cp_country_id');
        $appendSQL = ($country_id != '') ? "WHERE c.country_id = '{$country_id}' OR c.country_id IS NULL" : '';

        $sql = "
        SELECT c.card_id
              ,c.title
        FROM cards c
        {$appendSQL}
        ORDER BY c.title
        ";
        return $sql;
    }
}
