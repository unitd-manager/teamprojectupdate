<?
class CP_Common_Modules_Ek_Class_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $SQL = "
        SELECT c.*
              ,CONCAT_WS(' ', s.first_name, s.last_name ) AS staff_name
              ,CONCAT_WS(' ', cl.first_name, cl.last_name ) AS class_leader
              ,(SELECT count(st.student_id) FROM student st WHERE st.class_id = c.class_id) as student_total 
        FROM class c
        LEFT JOIN (staff s)    ON (c.class_staff_id   = s.staff_id )
        LEFT JOIN (student cl) ON (c.class_leader_id  = cl.student_id )
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
        $searchVar->mainTableAlias = 'c';

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.class_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.class_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                   c.title LIKE '%{$tv['keyword']}%'
                )";
            }    
        }    
    }
}
