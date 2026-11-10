<?
class CP_Www_Widgets_Core_SubNav_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT c.*
              ,s.title AS section_title
        FROM category c
        JOIN section s ON (s.section_id = c.section_id)
        ";

        $hook = getCPWidgetHook('core_subNav', 'SQL', $this, $SQL);
        if($hook['status']){
            return $hook['html'];
        }

        return $SQL;
    }

    /**
     * [setSearchVar description]
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $roomsArray = Zend_Registry::get('roomsArray');

        $section_id      = $this->controller->section_id;
        $category_id     = $this->controller->category_id;
        $category_type   = $this->controller->category_type;
        $noSectionFilter = $this->controller->noSectionFilter;

        $searchVar->mainTableAlias = 'c';

        $searchVar->sqlSearchVar[] = 'c.published = 1';

        if (!$noSectionFilter) {
            if ($section_id != ''){
                $searchVar->sqlSearchVar[] = "c.section_id = {$section_id}";
            } else {
                $roomArr = $roomsArray[$tv['room']];
                if($cpCfg['cp.hasAliasSectionsAcrossMUSites'] && $roomArr['alias_to_section_id'] != ''){
                    $searchVar->addSiteIdForMultiUniqueSites = false;
                    $searchVar->sqlSearchVar[] = "c.section_id = {$roomArr['alias_to_section_id']}";
                    $section_id = $roomArr['alias_to_section_id'];
                } else {
                    $searchVar->sqlSearchVar[] = "c.section_id = {$tv['room']}";
                    $section_id = $tv['room'];
                }
            }

        }

        if ($category_id != ''){
            $searchVar->sqlSearchVar[] = "c.category_id = {$category_id}";
        }
        if ($category_type != ''){
            $searchVar->sqlSearchVar[] = "c.category_type = '{$category_type}'";
        }

        if ($cpCfg['cp.hasSectionsByUserType'] == 1){
            $userType = $fn->getSessionParam('userType', 'Public');
            $searchVar->sqlSearchVar[] = "
            (c.access_to LIKE '%{$userType}%' OR c.access_to ='' OR c.access_to IS NULL)
            ";
        }

        if (!$cpCfg['cp.isMobileDevice'] && $cpCfg['cp.hasWebOnlyCategories']){
            $searchVar->sqlSearchVar[] = "c.ok_for_web = 1";
        }

        if ($cpCfg['cp.isMobileDevice'] && $cpCfg['cp.hasMobileOnlyCategories']){
            $searchVar->sqlSearchVar[] = "c.ok_for_mobile = 1";
        }

        if ($cpCfg['cp.hasProtectedCategories'] == 1){
            $searchVar->sqlSearchVar[] = "c.protected != 1";
        }

//        if ($cpCfg['cp.hasMultiSites'] && $tv['secType'] != 'Product'){
        if ($cpCfg['cp.hasMultiSites']){
            $searchVar->sqlSearchVar[] = "
            c.category_id IN (
                SELECT record_id
                FROM site_link
                WHERE module = 'webBasic_category'
                  AND site_id = {$cpCfg['cp.site_id']}
                  AND published = 1
            )
            ";
        }

        $searchVar->sortOrder = "c.sort_order";

        $hook = getCPWidgetHook('core_subNav', 'searchVar', $this, $searchVar);
        if($hook['status']){
            return $hook['html'];
        }
    }

    /**
     *
     * @param <type> $section_id
     * @return <type>
     */
    function getDataArray(){
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $c = $this->controller;

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'core_subNav');
        $arr = array();

        foreach ($dataArray as $row){
            $tmpArr = &$arr[$row['category_id']];

            $tmpArr['category_id']   = $row['category_id'];
            $tmpArr['title']         = $ln->gfv($row, 'title');
            $tmpArr['titleEng']      = $row['title'];
            $tmpArr['titleActual']   = $ln->gfv($row, 'title', false);
            $tmpArr['category_type'] = $row['category_type'];
            $tmpArr['url']           = $this->getUrl($row);
            $tmpArr['external_link'] = $row['external_link'];
            $tmpArr['internal_link'] = $row['internal_link'];
            $tmpArr['show_in_nav']   = $row['show_in_nav'];
            $tmpArr['member_only']     = isset($row['member_only']) ? $row['member_only'] : 0;
            $tmpArr['non_member_only'] = isset($row['non_member_only']) ? $row['non_member_only'] : 0;
            $tmpArr["module"]          = $fn->getModuleNameByType($row['category_type']);
            $tmpArr['active']          = ($tv['subRoom'] == $row['category_id']) ? 1 : 0;
            $tmpArr['no_follow']       = isset($row['no_follow']) ? $row['no_follow'] : 0;
            $tmpArr['link_target'] = '';

            if ($row['internal_link'] != '') {
                $tmpArr['url'] = $row['internal_link'];
            }
            if ($row['external_link'] != '') {
                $tmpArr['url'] = $row['external_link'];
                $tmpArr['link_target'] = '_blank';
            }
            if(isset($row['css_style_name'])){
                $tmpArr['css_style_name'] = $row['css_style_name'];
            }

            if ($c->includeDescription){
                $tmpArr['description'] = $ln->gfv($row, 'description');
            }
        }

        $this->dataArray = $arr;

        return $this->dataArray;
    }

    /**
     *
     * @param <type> $section_id
     * @return <type>
     */
    function getUrl($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $urlArray = array();

        if ($tv['countryCodeReq'] != ''){
            $urlArray['countryCodeReq'] = $tv['countryCodeReq'];
        }

        if ($cpCfg['cp.hasMultiUniqueSites'] && $tv['cp_site'] != ''){
            $urlArray['cp_site'] = $tv['cp_site'];
        }
        if ($cpCfg['cp.hasMultiYears']){
            $urlArray['cp_year'] = $tv['cp_year'];
        }

        if ($cpCfg['cp.multiLang'] == 1){
            $urlArray['lang'] = $tv['lang'];
        }

        $urlArray['section_id']     = $row['section_id'];
        $urlArray['section_title']  = $row['section_title'];
        $urlArray['category_id']    = $row['category_id'];
        $urlArray['category_title'] = $row['title'];

        /*** if there is a sub category record for the category,
             then change the link with the sub category title & id
        ***/
        $excludeListByType = $fn->getIssetParam($cpCfg, 'cp.categoryByTypeWithNoAutoSelctSubCategory', array());
        $category_type = $row['category_type'];

        $autoSelectFirstSubCategory = $this->controller->autoSelectFirstSubCategory;
        if ($autoSelectFirstSubCategory === null) {
            $autoSelectFirstSubCategory = $cpCfg['cp.autoSelectFirstSubCategory'];
        }
        if ($autoSelectFirstSubCategory  && !in_array($category_type, $excludeListByType)){
            $firstSubCatArray  = $this->getFirstSubCatForCategory($row['section_id'], $row['category_id']);
            if(count($firstSubCatArray) > 0){
                $urlArray['sub_category_id']    = $firstSubCatArray[0];
                $urlArray['sub_category_title'] = $firstSubCatArray[1];
            }
        }

        $url = $cpUrl->make_seo_url($urlArray);

        $cpi = $fn->getReqParam('cpi');
        if ($cpi != '' && $cpCfg['cp.appendCpiWhenAvailable']){
            $url .= "?cpi={$cpi}";
        }

        return $url;
    }

    /**
     *
     * @param <type> $section_id
     * @return <type>
     */
    function getFirstSubCatForCategory($section_id, $category_id ){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $firstSubCatArray = array();

        //*** check there are how many content records for this category without a sub category
        $SQL = "
        SELECT count(*)
        FROM content
        WHERE section_id = '{$section_id}'
          AND category_id = '{$category_id}'
          AND (sub_category_id IS NULL OR sub_category_id ='')
          AND content_type = 'Record'
          AND published = 1
        ";

        if (!$cpCfg['cp.isMobileDevice'] && $cpCfg['cp.hasWebOnlyCategories']){
            $SQL .= "
            AND ok_for_web = 1
            ";
        }

        if ($cpCfg['cp.isMobileDevice'] && $cpCfg['cp.hasMobileOnlyCategories']){
            $SQL .= "
            AND ok_for_mobile = 1
            ";
        }

        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);

        // if there are no content records
        if ($row[0] == 0){
            $sqlAppend = "";

            if (!$cpCfg['cp.isMobileDevice'] && $cpCfg['cp.hasWebOnlySubCategories']){
                $sqlAppend .= "
                AND a.ok_for_web = 1
                ";
            }

            if ($cpCfg['cp.isMobileDevice'] && $cpCfg['cp.hasMobileOnlySubCategories']){
                $sqlAppend .= "
                AND a.ok_for_mobile = 1
                ";
            }

            if ($cpCfg['cp.hasMultiSites']){
                $sqlAppend .= " AND a.sub_category_id IN (
                    SELECT record_id
                    FROM site_link
                    WHERE module = 'webBasic_subCategory'
                      AND site_id = {$cpCfg['cp.site_id']}
                      AND published = 1
                )";
            }

            $SQL = "
            SELECT a.* FROM sub_category a
            WHERE a.published = 1
              AND a.show_in_nav = 1
              AND a.category_id = {$category_id}
              {$sqlAppend}
            ORDER BY a.sort_order limit 0, 1
            ";

            $result  = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);

            if ($numRows > 0){
               $row  = $db->sql_fetchrow($result);
               $firstSubCatArray = array($row['sub_category_id'], $row['title'], $row['seo_title']);
            }
        }

        return $firstSubCatArray;
    }
}
