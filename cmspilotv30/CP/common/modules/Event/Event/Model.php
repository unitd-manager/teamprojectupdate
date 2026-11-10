<?
class CP_Common_Modules_Event_Event_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $extraTableNames = "";
        if ($cpCfg['cp.hasMultiSites']) {
            $site_id    = $fn->getReqParam('site_id');
            if ($site_id != "") {
                $extraTableNames .= "JOIN site_link sl ON (e.event_id = sl.record_id AND sl.module ='event_event')";
            }
        }

        $sectionAppendSQL = '';
        $sectionJoinSQL = '';
        if ($cpCfg['m.event.event.hasSection']) {
            $sectionAppendSQL = ",s.title AS section_title";
            $sectionJoinSQL = "LEFT JOIN (section s) ON (e.section_id = s.section_id)";
        }                
        
        $SQL = "
        SELECT e.*
              {$sectionAppendSQL}
              ,ca.title AS category_title
              ,sc.title AS sub_category_title
        FROM event e
        {$sectionJoinSQL}
        LEFT JOIN category ca     ON (e.category_id     = ca.category_id)
        LEFT JOIN sub_category sc ON (e.sub_category_id = sc.sub_category_id)
        {$extraTableNames}
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar->mainTableAlias = 'e';

        $category_id  = $fn->getReqParam('category_id');
        $sub_category_id  = $fn->getReqParam('sub_category_id');
        $event_id     = $fn->getReqParam('event_id');
        $content_type     = $fn->getReqParam('content_type');

        if (CP_SCOPE == 'www') {
            $searchVar->sqlSearchVar['published'] = "e.published = 1";
        }

        if ($cpCfg['cp.hasMultiSites']) {
            $site_id    = $fn->getReqParam('site_id'); 
            if($site_id != ''){
                $searchVar->sqlSearchVar['site_id'] = "sl.site_id = '{$site_id}'";   
            }                  
        }

        if ($event_id != "") {
            $searchVar->sqlSearchVar['event_id'] = "e.event_id = '{$event_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar['event_id'] = "e.event_id = '{$tv['record_id']}'";
        } else {
    
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'e.event_id');
    
            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Subscribed") {
                $searchVar->sqlSearchVar['subscribe'] = "e.subscribe = 1";
            }
    
            if ($tv['special_search'] == "Not-Subscribed") {
                $searchVar->sqlSearchVar['subscribe_2'] = "(e.subscribe != 1 OR e.subscribe IS null)";
            }
    
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar['flag'] = "e.flag = 1";
            }
    
            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar['flag_2'] = "(e.flag != 1 OR e.flag IS null)";
            }
    
            if ($tv['special_search']  == 'Published') {
                $searchVar->sqlSearchVar['published'] = "e.published = 1";
            }
    
            if ($tv['special_search'] == 'Not-Published' ) {
                $searchVar->sqlSearchVar['published_2'] = "e.published = 0 OR e.published IS NULL OR e.published = ''";
            }
            
            if ($tv['special_search'] == 'Ok-For-Mobile') {
                $searchVar->sqlSearchVar['ok_for_mobile'] = "e.ok_for_mobile = 1";
            }   
            //------------------------------------------------------------------------//
            
            if ($cpCfg['m.event.event.hasSection'] && $tv['section_id'] != '') {
                $searchVar->sqlSearchVar[] = "e.section_id  = {$tv['section_id']}";    
            }             
            
            if ($category_id!= '' ) {
                $searchVar->sqlSearchVar['category_id'] = "e.category_id = {$category_id}";
            }

            if ($sub_category_id!= '' ) {
                $searchVar->sqlSearchVar['sub_category_id'] = "e.sub_category_id = {$sub_category_id}";
            }

            if ($content_type != '') {
                $searchVar->sqlSearchVar[] = "c.content_type = '{$content_type}'";
            }

           /* if ($tv['sub_category_id'] != '') {
                $searchVar->sqlSearchVar[] = "e.sub_category_id = {$tv['sub_category_id']}";
            }*/
            //------------------------------------------------------------------------//
    
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar['keyword'] = "(
                    e.title       LIKE '%{$tv['keyword']}%'  OR
                    e.description LIKE '%{$tv['keyword']}%'
                )";
            }            
        }
    }

}
