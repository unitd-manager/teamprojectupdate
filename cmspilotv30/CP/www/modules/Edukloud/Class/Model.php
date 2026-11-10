<?
class CP_Www_Modules_Edukloud_Class_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */    
    function getSQL() {
        $SQL = "
        SELECT c.*
        FROM class c
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
