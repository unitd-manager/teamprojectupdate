<?
class CP_Www_Modules_LawNews_Correspondent_Model extends CP_Common_Lib_ModuleModelAbstract
{

    /**
     *
     */
    function getSQL() {
        $SQL = "
        SELECT c.*, j.title AS jurisdiction_title
        FROM correspondent c
        LEFT JOIN (jurisdiction j) ON (c.jurisdiction_id = j.jurisdiction_id)
        ";
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = Zend_Registry::get('searchVar');
        
        $searchVar->sqlSearchVar[] = "c.published = 1";
        $searchVar->sqlSearchVar[] = "c.active = 1";

        if ($cpCfg['cp.hasMultiSites']){
            $searchVar->sqlSearchVar[] = "
            c.correspondent_id IN (
                SELECT record_id
                FROM site_link
                WHERE module = 'lawNews_correspondent'
                  AND site_id = {$cpCfg['cp.site_id']}
                  AND published = 1
            )
            ";
        }         
        
        if ($tv['record_id'] != ''){
            $searchVar->sqlSearchVar[] = "c.correspondent_id  = {$tv['record_id']}";
        }  else {
            
        }
        
        $searchVar->sortOrder = "c.sort_order ASC";     
    }
    
}