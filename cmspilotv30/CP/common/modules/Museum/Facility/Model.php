<?
class CP_Common_Modules_Museum_Facility_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $SQL = "
        SELECT f.* 
              ,s.title AS section_title
              ,s.section_type
              ,ca.title AS category_title
              ,ca.category_type
              ,sc.title AS sub_category_title
              ,sc.sub_category_type
        FROM facility f
        LEFT JOIN (section s) ON (f.section_id = s.section_id)
        LEFT JOIN (category ca) ON (f.category_id = ca.category_id)
        LEFT JOIN (sub_category sc)ON (f.sub_category_id  = sc.sub_category_id)        
        ";
     
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar->mainTableAlias = 'f';

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "f.facility_id = {$tv['record_id']}";
        } else {
            if ($tv['section_id'] != '') {
                $searchVar->sqlSearchVar[] = "f.section_id  = {$tv['section_id']}";
            }

            if ($tv['category_id'] != '') {
                $searchVar->sqlSearchVar[] = "f.category_id = {$tv['category_id']}";
            }

            if ($tv['sub_category_id'] != '') {
                $searchVar->sqlSearchVar[] = "f.sub_category_id = {$tv['sub_category_id']}";
            }
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "( f.title   LIKE '%{$tv['keyword']}%')";
        }

		$searchVar->sortOrder = "f.facility_id";
    }

}
