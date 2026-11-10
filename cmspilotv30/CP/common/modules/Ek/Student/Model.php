<?
class CP_Common_Modules_Ek_Student_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT s.*
              ,c.title AS class_title
              ,gc.name AS country
        FROM `student` s
        LEFT JOIN (class c)      ON (s.class_id     = c.class_id)
        LEFT JOIN geo_country gc ON (s.country_code = gc.country_code)
        ";
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $searchVar = Zend_Registry::get('searchVar');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $searchVar->mainTableAlias = 's';

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "s.student_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 's.student_id');
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
               a.first_name LIKE '%{$tv['keyword']}%'
            OR a.last_name LIKE '%{$tv['keyword']}%'
            )";
        } 
    }
}
