<?
class CP_Common_Modules_Party_Testimonial_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL = "
        SELECT t.*
        FROM testimonial t
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

        if (CP_SCOPE == 'www') {
            $searchVar->sqlSearchVar[] = "t.published = 1";
        }

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "t.testimonial_id = {$tv['record_id']}";

        } else {
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    t.title LIKE '%{$tv['keyword']}%'
                    OR t.name LIKE '%{$tv['keyword']}%'  
                    OR t.description LIKE '%{$tv['keyword']}%'  
                )";
            }
        }       
    }
}
