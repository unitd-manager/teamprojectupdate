<?
class CP_Www_Modules_WebBasic_Content_Model extends CP_Common_Lib_ModuleModelAbstract
{

    /**
     *
     */
    function getSQL($exp = array()) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $includeCommentCount = $fn->getIssetParam($exp, 'includeCommentCount');

        $tagsSQL = '';
        if ($cpCfg['m.webBasic.content.showCloudTags']) {
            $tagsSQL = "(
                SELECT GROUP_CONCAT(t.tag_text ORDER BY t.tag_text SEPARATOR ',')
                FROM tags t
                    ,tags_history th
                WHERE t.tags_id = th.tags_id
                  AND th.record_id   = c.content_id
                  AND th.record_type = 'Content'
            ) AS cloud_tags,
            ";
        }

        $commentCountSQL = '';
        if ($includeCommentCount || $cpCfg['m.webBasic.content.includeCommentCount']){
            $commentCountSQL = "(
            SELECT count(*) 
            FROM comment
            WHERE room_name = 'webBasic_content' 
              AND record_id = c.content_id
              AND published = 1
            ) AS comments_count,
            ";
        }

        $SQL = "
        SELECT {$tagsSQL}
               {$commentCountSQL}
               c.*
              ,s.title AS section_title
              ,s.section_type
              ,ca.title AS category_title
              ,ca.category_type
              ,sc.title AS sub_category_title
              ,sc.sub_category_type
        FROM content c
        LEFT JOIN (section s)      ON (c.section_id       = s.section_id)
        LEFT JOIN (category ca)    ON (c.category_id      = ca.category_id)
        LEFT JOIN (sub_category sc)ON (c.sub_category_id  = sc.sub_category_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = Zend_Registry::get('searchVar');
        $roomsArray = Zend_Registry::get('roomsArray');
        $searchVar->mainTableAlias = 'c';
        $relationalDataOnly  = $fn->getIssetParam($this->expForSearchVar, 'relationalDataOnly');

        $searchVar->sqlSearchVar['published'] = "c.published = 1";

        if ($relationalDataOnly){
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.content_id');
        } else if ($tv['record_id'] != ''){
            $searchVar->sqlSearchVar['content_id'] = "c.content_id  = {$tv['record_id']}";
        } else {
            $searchVar->sqlSearchVar['content_type'] = "c.content_type = 'Record'";
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.content_id');

            if($cpCfg['cp.hasAliasSectionsAcrossMUSites']){
                if ($tv['room'] != '' && is_numeric($tv['room'])){
                    $roomArr = $roomsArray[$tv['room']];
                    if ($roomArr['alias_to_section_id'] != ''){
                        $searchVar->addSiteIdForMultiUniqueSites = false;
                        $searchVar->sqlSearchVar['section_id'] = "c.section_id  = {$roomArr['alias_to_section_id']}";
                    } else {
                        $searchVar->sqlSearchVar['section_id'] = "c.section_id  = {$tv['room']}";
                    }
                }
            } else {
                if ($tv['room'] != '' && is_numeric($tv['room'])){
                    $searchVar->sqlSearchVar['section_id'] = "c.section_id  = {$tv['room']}";
                }
            }

            if ($tv['subRoom'] != '' && is_numeric($tv['subRoom']) ){
                $searchVar->sqlSearchVar['category_id'] = "c.category_id  = {$tv['subRoom']}";
            }

            if ($tv['subCat'] != '' && is_numeric($tv['subCat']) ){
                $searchVar->sqlSearchVar['sub_category_id'] = "c.sub_category_id  = {$tv['subCat']}";
            }

            if ($tv['sub_category_id'] != '') {
                $searchVar->sqlSearchVar['sub_category_id'] = "c.sub_category_id  = {$tv['sub_category_id']}";
            }

            $excludeList = $cpCfg['cp.roomsWithNoAutoSelctCategory'];
            if (!in_array($tv['room'], $excludeList)) {
                if ($tv['module'] != '' && $tv['subRoom'] == '') {
                    if((!$cpCfg['cp.isMobileDevice'] && $cpCfg['m.webBasic.content.showOrphanRecords']) ||
                    ($cpCfg['cp.isMobileDevice'] && $cpCfg['m.webBasic.content.showOrphanRecordsMobile'])) {                    
                        $searchVar->sqlSearchVar['category_id_2'] =
                        "(c.category_id IS NULL OR c.category_id ='')";
                    }
                }
            }

            $yearMonth = $fn->getReqParam('yearMonth');
            if ($yearMonth != '') {
                $searchVar->sqlSearchVar[] = "DATE_FORMAT(content_date, '%Y-%m') = '{$yearMonth}'";
            }

            if (!$cpCfg['cp.isMobileDevice'] && $cpCfg['cp.hasWebOnlyContent']){
                $searchVar->sqlSearchVar[] = "c.ok_for_web = 1";
            }

            if ($cpCfg['cp.isMobileDevice'] && $cpCfg['cp.hasMobileOnlyContent']){
                $searchVar->sqlSearchVar[] = "c.ok_for_mobile = 1";
            }

            if ($tv['subRoom'] != '' && $tv['subCat'] == ''){
                
                if((!$cpCfg['cp.isMobileDevice'] && $cpCfg['m.webBasic.content.showOrphanRecords']) ||
                  ($cpCfg['cp.isMobileDevice'] && $cpCfg['m.webBasic.content.showOrphanRecordsMobile'])) {
                $searchVar->sqlSearchVar['sub_category_id_2'] =
                "(c.sub_category_id IS NULL OR c.sub_category_id ='')";
                }
            }

            if ($tv['keyword'] != ""){
                $searchVar->sqlSearchVar['keyword'] = "(
                    c.title        LIKE '%{$tv['keyword']}%' OR
                    c.description  LIKE '%{$tv['keyword']}%'
                )";
            }
        }

        if ($cpCfg['cp.hasMultiSites']){
            $searchVar->sqlSearchVar['site_link'] = "
            c.content_id IN (
                SELECT record_id
                FROM site_link
                WHERE module = 'webBasic_content'
                  AND site_id = {$cpCfg['cp.site_id']}
                  AND published = 1
            )
            ";
        }

        if (!isLoggedInWWW()){
            $searchVar->sqlSearchVar['member_only'] = "(c.member_only != '1' OR c.member_only IS NULL)";
        }

        $hook = getCPModuleHook('webBasic_content', 'searchVar', $searchVar);
        if($hook['status']){
            $returnVal = $hook['returnVal'];
            if ($returnVal) {
                if (is_array($returnVal)) {
                    $searchVar->sqlSearchVar = array_merge($searchVar->sqlSearchVar, $hook['returnVal']);
                } else {
                    $searchVar->sqlSearchVar[] = $hook['returnVal'];
                }
            }
        }

        $searchVar->sortOrder = "c.sort_order ASC";
    }

    /**
     *
     */
    function getRecordById($content_id){
        $fn = Zend_Registry::get('fn');

        $SQL  = $this->getSQL();
        $SQL .= " WHERE c.content_id = {$content_id}";
        $row  = $fn->getRecordBySQL($SQL);

        return $row;
    }

    function getRecordByType($contentType, $useCurrSection = true){
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $section_id = $tv['room'];
        $category_id = $tv['subRoom'];
        $sub_category_id = $tv['subCat'];

        $arr = array();
        $arr[] = "c.content_type = '{$contentType}'";
        if ($useCurrSection) {
            $arr[] = "c.section_id = {$section_id}";
            if ($category_id != '') {
                $arr[] = "c.category_id = {$category_id}";
            }
            if ($sub_category_id != '') {
                $arr[] = "c.sub_category_id = {$sub_category_id}";
            }
        }

        if ($cpCfg['cp.hasMultiSites']){
            $arr[] = "
            c.content_id IN (
                SELECT record_id
                FROM site_link
                WHERE module = 'webBasic_content'
                  AND site_id = {$cpCfg['cp.site_id']}
                  AND published = 1
            )";
        }        
        
        $whereCond = join(' AND ', $arr);

        $contentType = qstr($contentType, false);
        $lnPfx = $ln->getFieldPrefix();

        $SQL = "
        SELECT c.content_id
              ,c.title AS category_title
              ,c.show_title
              ,IF(c.{$lnPfx}title != '', c.{$lnPfx}title, c.title)AS title_lang
              ,IF(c.{$lnPfx}description != '', c.{$lnPfx}description, c.title)AS description_lang
        FROM content c
        WHERE {$whereCond}
        LIMIT 0, 1
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows == 0){
            return;
        }

        $row = $db->sql_fetchrow($result, MYSQL_ASSOC);

        return $row;
    }

    function getModuleDataArray(){
        $dataArray = parent::getModuleDataArray();
        return $dataArray;
    }
}