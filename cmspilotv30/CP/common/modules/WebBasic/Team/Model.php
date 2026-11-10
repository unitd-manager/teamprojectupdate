<?
class CP_Common_Modules_WebBasic_Team_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        
        $SQL = "
        SELECT t.*
        FROM team t
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
        $searchVar->mainTableAlias = 't';

        $team_id = $fn->getReqParam('team_id');

        if (CP_SCOPE == 'www') {
            $searchVar->sqlSearchVar[] = "t.published = 1";
        }

        if ($team_id != '') {
            $searchVar->sqlSearchVar[] = "t.team_id = {$team_id}";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "t.team_id = {$tv['record_id']}";
        } else {
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "( 
                    t.name LIKE '%{$tv['keyword']}%'  
                )";
            }
        }
        
		$searchVar->sortOrder = "t.title ASC";
    }
}
