<?
class CP_Common_Modules_Edukite_Teacher_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT t.*
              ,CONCAT_WS(' ', t.first_name, t.last_name ) AS teacher_name
              ,gc.name AS country
        FROM teacher t
        LEFT JOIN geo_country gc ON (t.address_country_code = gc.country_code)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 't';

        $teacher_id     = $fn->getReqParam('teacher_id');
        $subject_id   = $fn->getReqParam('subject_id');

        if ($teacher_id != "") {
            $searchVar->sqlSearchVar[] = "t.teacher_id = '{$teacher_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "t.teacher_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'st.teacher_id');

            $searchVar->sqlSearchVar[] = "t.status = 'Active'";

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    t.first_name LIKE '%{$tv['keyword']}%' OR
                    t.last_name LIKE '%{$tv['keyword']}%'
                )";
            }
        }
    }
}
