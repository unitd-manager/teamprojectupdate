<?
class CP_Common_Modules_Edukite_Interest_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL = "
        SELECT i.*
        FROM interest i
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
        $searchVar->mainTableAlias = 'i';


        if ($tv['record_id'] != '' ) {
            $searchVar->sqlSearchVar[] = "i.interest_id  = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'i.interest_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    i.title LIKE '%{$tv['keyword']}%'
                )";
            }
        }
    }
}
