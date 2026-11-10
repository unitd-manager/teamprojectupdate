<?
class CP_Www_Modules_Event_Event_Model extends CP_Common_Modules_Event_Event_Model
{
    function getSQL() {
        $SQL = "
        SELECT e.*
              ,ca.title AS category_title
              ,ca.category_type
              ,sc.title AS sub_category_title
              ,sc.sub_category_type
        FROM event e
        LEFT JOIN category ca     ON (e.category_id     = ca.category_id)
        LEFT JOIN sub_category sc ON (e.sub_category_id = sc.sub_category_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = Zend_Registry::get('searchVar');

        $searchVar->sqlSearchVar['published'] = "e.published = 1";
        if ($tv['record_id'] != ''){
            $searchVar->sqlSearchVar['event_id'] = "e.event_id  = {$tv['record_id']}";
        }

        if ($tv['record_id'] == ''){
            if ($cpCfg['m.event.event.showEventType']){
                $searchVar->sqlSearchVar['content_type'] = "e.content_type = 'Event'";
            }

            if ($cpCfg['m.event.event.hasSection'] && $tv['section_id'] != '') {
                $searchVar->sqlSearchVar[] = "e.section_id  = {$tv['section_id']}";    
            }                      
            
            if ($tv['subRoom'] != '' && is_numeric($tv['subRoom']) ){
                $searchVar->sqlSearchVar['category_id'] = "e.category_id  = {$tv['subRoom']}";
            }

            if ($tv['sub_category_id'] != '') {
                $searchVar->sqlSearchVar['sub_category_id'] = "e.sub_category_id  = {$tv['sub_category_id']}";
            }

            if ($tv['module'] != '' && $tv['subRoom'] == '') {
                $searchVar->sqlSearchVar['category_id_2'] = "(e.category_id IS NULL OR e.category_id ='')";
            }

            if ($tv['keyword'] != ""){
                $searchVar->sqlSearchVar['keyword'] = "(
                    e.title        LIKE '%{$tv['keyword']}%' OR
                    e.description  LIKE '%{$tv['keyword']}%'
                )";
            }
        }

        if ($cpCfg['cp.hasMultiSites']){
            $searchVar->sqlSearchVar['site_link'] = "
            e.event_id IN (
                SELECT record_id
                FROM site_link
                WHERE module = 'event_event'
                  AND site_id = {$cpCfg['cp.site_id']}
                  AND published = 1
            )
            ";
        }

        if (!isLoggedInWWW()){
            $searchVar->sqlSearchVar['member_only'] = "(e.member_only != '1' OR e.member_only IS NULL)";
        }

        $searchVar->sortOrder = "e.sort_order ASC, e.event_date DESC";
    }

}