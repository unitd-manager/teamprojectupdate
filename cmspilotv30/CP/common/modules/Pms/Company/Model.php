<?
class CP_Common_Modules_Pms_Company_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT c.* 
              ,gc.name AS country_name
        FROM company c
        LEFT JOIN geo_country gc ON (c.address_country_code = gc.country_code)
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

        $category     = $fn->getReqParam('category');
        $status       = $fn->getReqParam('status');
        $company_id   = $fn->getReqParam('company_id');
        $title = $fn->getReqParam('title');

        if ($company_id != "") {
            $searchVar->sqlSearchVar[] = "c.company_id = '{$company_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.company_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.company_id');
    
            if ($status != "") {
                $searchVar->sqlSearchVar[] = "c.status = '{$status}'";
            }
    
            if ($category != "") {
                $searchVar->sqlSearchVar[] = "c.category = '{$category}'";
            }
    
            if ($title != "") {
                $searchVar->sqlSearchVar[] = "c.title LIKE '%{$title}%'";
            }
    
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    c.title  LIKE '%{$tv['keyword']}%'
                    OR c.group_name LIKE '%{$tv['keyword']}%'  
                    OR c.email      LIKE '%{$tv['keyword']}%'
                )";
            }
    
            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "c.flag = 1";
            }
    
            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(c.flag != 1 OR c.flag IS null)";
            }
    
            $searchVar->sortOrder = "c.title";
        }
    }
}
