<?
class CP_Common_Modules_Ek_Teacher_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT st.*
              ,gc.name AS country
        FROM staff st
        LEFT JOIN geo_country gc ON (st.country_code = gc.country_code)
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
        $searchVar->mainTableAlias = 'st';

        $staff_id     = $fn->getReqParam('staff_id');
        $subject_id   = $fn->getReqParam('subject_id');

        /*if ($subject_id != "") {
            $searchVar->sqlSearchVar[] = "st.subject_id = '{$subject_id}'";
        }*/
        if ($staff_id != "") {
            $searchVar->sqlSearchVar[] = "st.staff_id = '{$staff_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "st.staff_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'st.staff_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                   st.first_name LIKE '%{$tv['keyword']}%'
                OR st.last_name LIKE '%{$tv['keyword']}%'
                )";
            }
        }        
    }
}
