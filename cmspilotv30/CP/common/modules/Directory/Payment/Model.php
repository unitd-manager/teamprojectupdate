<?
class CP_Common_Modules_Directory_Payment_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $SQL = "
        SELECT p.*
        	  ,c.title AS country_title
        FROM payment p
        LEFT JOIN country c ON c.country_id = p.country_id
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

        if ($tv['record_id'] != '' && $linkRecType == '') {
            $searchVar->sqlSearchVar[] = "p.payment_id = {$tv['record_id']}";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'p.payment_id');
    		
            $country_id = $fn->getSessionParam('cp_country_id');
            if ($country_id != '' ) {
                $searchVar->sqlSearchVar[] = "(p.country_id = '{$country_id}' OR p.country_id IS NULL)";
            }            
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    p.title LIKE '%{$tv['keyword']}%'  
                )";
            }
    
    		$searchVar->sortOrder = "p.title";
    	}
    }

    /**
     *
     */
    function getDataByBusiness($business_id) {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $searchVar = Zend_Registry::get('searchVar');
        
        $linkedIds = $fn->getLinkedIDs('directory_business', 'directory_paymentLink', $business_id);
        Zend_Registry::set('linkedIds', $linkedIds);
        $SQL = $this->getSQL();
        $SQL .= $searchVar->getSearchVar($this->controller, 1, 'linked');
        $result = $db->sql_query($SQL);
        $dataArray = $dbUtil->getResultsetAsArray($result);
        Zend_Registry::set('linkedIds', $linkedIds);

        return $text;
    }

    /**
     *
     */
    function getPayementSQL() {
        $fn = Zend_Registry::get('fn');
        $country_id = $fn->getSessionParam('cp_country_id');
        $appendSQL = ($country_id != '') ? "WHERE country_id = '{$country_id}' OR country_id IS NULL" : '';

        $sql = "
        SELECT payment_id
              ,title
        FROM payment
        {$appendSQL}
        ORDER BY title
        ";
        return $sql;
    }    
}
