<?
class CP_Common_Modules_WebBasic_Career_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        
        $SQL = "
        SELECT c.*
        FROM career c
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar->mainTableAlias = 'c';

        $career_id = $fn->getReqParam('career_id');

        if (CP_SCOPE == 'www') {
            $searchVar->sqlSearchVar[] = "c.published = 1";
        }

        if ($career_id != '') {
            $searchVar->sqlSearchVar[] = "c.career_id = {$career_id}";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.career_id = {$tv['record_id']}";
        } else {
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "( 
                    c.name LIKE '%{$tv['keyword']}%'  
                )";
            }
        }
        
		$searchVar->sortOrder = "c.title ASC";
    }
}
