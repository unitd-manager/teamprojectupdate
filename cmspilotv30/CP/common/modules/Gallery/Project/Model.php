<?
class CP_Common_Modules_Gallery_Project_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        
        $SQL = "
        SELECT p.*
              ,c.title AS category_title
              ,sc.title AS sub_category_title
        FROM project p
        LEFT JOIN (category c) ON (p.category_id = c.category_id)
        LEFT JOIN (sub_category sc)ON (p.sub_category_id = sc.sub_category_id)
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
        $searchVar->mainTableAlias = 'p';

        $project_id = $fn->getReqParam('project_id');

        if (CP_SCOPE == 'www') {
            $searchVar->sqlSearchVar[] = "p.published = 1";
        }

        if ($project_id != '') {
            $searchVar->sqlSearchVar[] = "p.project_id = {$project_id}";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "p.project_id = {$tv['record_id']}";
        } else {
            if ($tv['catType'] == 'Latest' ) {
                $searchVar->sqlSearchVar[] = "p.latest = 1";

            } else {
                if ($tv['category_id'] != '') {
                    $searchVar->sqlSearchVar[] = "p.category_id = {$tv['category_id']}";
                }
        
                if ($tv['sub_category_id'] != '') {
                    $searchVar->sqlSearchVar[] = "p.sub_category_id = {$tv['sub_category_id']}";
                }

                if ($tv['subRoom'] != '' ) {
                    $searchVar->sqlSearchVar[] = "p.category_id = '{$tv['subRoom']}'";
                }

                if ($tv['subCat'] != '' ) {
                    $searchVar->sqlSearchVar[] = "p.sub_category_id = '{$tv['subCat']}'";
                }
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "( 
                    p.title                 LIKE '%{$tv['keyword']}%'  
                    OR p.chi_title          LIKE '%{$tv['keyword']}%'
                    OR p.description        LIKE '%{$tv['keyword']}%'
                    OR p.chi_description    LIKE '%{$tv['keyword']}%'
                )";
            }
        }
        
		$searchVar->sortOrder = "p.sort_order ASC";
    }
}
