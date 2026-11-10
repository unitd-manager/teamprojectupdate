<?
class CP_Common_Modules_Ek_School_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT s.*
              ,gc.name AS country
        FROM `school` s
        LEFT JOIN geo_country gc ON (s.address_country_code = gc.country_code)
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
        $searchVar->mainTableAlias = 's';

        $school_type  = $fn->getReqParam('school_type');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "s.school_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 's.school_id');

            if ($school_type != "") {
                $searchVar->sqlSearchVar[] = "s.school_type = '{$school_type}'";
            }
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
               s.title LIKE '%{$tv['keyword']}%'
            OR s.school_type LIKE '%{$tv['keyword']}%'
            )";
        }        
    }

}
