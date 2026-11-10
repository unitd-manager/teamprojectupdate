<?
class CP_Admin_Modules_EnggCrm_Schedule_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL = "
        SELECT a.*,
        b.title AS project_title
        FROM schedule a
        LEFT JOIN (project b) ON (a.project_id  = b.project_id)
        ";

        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');

        $project_id = $fn->getReqParam('project_id');
        $start_date = $fn->getReqParam('start_date');

        if ($project_id != "") {
            $searchVar->sqlSearchVar[] = "a.project_id   = '{$project_id}'";
        }

        if ($start_date != "") {
            $searchVar->sqlSearchVar[] = "a.start_date   = '{$start_date}'";
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
                                    a.title       LIKE '%{$tv['keyword']}%' OR
                                    b.title       LIKE '%{$tv['keyword']}%' OR
                                    a.description LIKE '%{$tv['keyword']}%'
                                   )";
        }


        //------------------------------------------------------------------------//
        if ($tv['special_search'] == "Flagged") {
            $searchVar->sqlSearchVar[] = "a.flag = 1";
        }

        if ($tv['special_search'] == "Not-Flagged") {
            $searchVar->sqlSearchVar[] = "(a.flag != 1 OR a.flag IS null)";
        }

        //------------------------------------------------------------------------//
        $searchVar->sortOrder = "a.start_date DESC";
    }
}
