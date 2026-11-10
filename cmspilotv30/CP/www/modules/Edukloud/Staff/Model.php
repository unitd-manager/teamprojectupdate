<?
class CP_Www_Modules_Edukloud_Staff_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT s.*
              ,CONCAT_WS(' ', s.first_name, s.last_name ) AS staff_name
              ,gc.name AS country
        FROM staff s
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

        $staff_id     = $fn->getReqParam('staff_id');
        $subject_id   = $fn->getReqParam('subject_id');

        if ($staff_id != "") {
            $searchVar->sqlSearchVar[] = "s.staff_id = '{$staff_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "s.staff_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'st.staff_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    s.first_name LIKE '%{$tv['keyword']}%' OR 
                    s.last_name LIKE '%{$tv['keyword']}%'
                )";
            }
        }        
    }
    
}
