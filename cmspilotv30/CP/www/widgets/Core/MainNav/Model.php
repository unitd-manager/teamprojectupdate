<?
class CP_Www_Widgets_Core_MainNav_Model extends CP_Common_Lib_WidgetModelAbstract
{

    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT s.*
        FROM section s
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar->mainTableAlias = 's';

        $btnPos = $this->controller->btnPos;

        $searchVar->sqlSearchVar[] = "s.published = 1";

        if ($btnPos != ''){
            $searchVar->sqlSearchVar[] = "s.button_position = '{$btnPos}'";
        }

        if ($cpCfg['cp.hasSectionsByUserType'] == 1){
            $userType = $fn->getSessionParam('userType', 'Public');
            $searchVar->sqlSearchVar[] = "(s.access_to LIKE '%{$userType}%' OR s.access_to =''  OR s.access_to IS NULL)";
        }

        if (!$cpCfg['cp.isMobileDevice'] && $cpCfg['cp.hasWebOnlySections']){
            $searchVar->sqlSearchVar[] = "s.ok_for_web = 1";
        }

        if ($cpCfg['cp.isMobileDevice'] && $cpCfg['cp.hasMobileOnlySections']){
            $searchVar->sqlSearchVar[] = "s.ok_for_mobile = 1";
        }

        if ($cpCfg['cp.hasMemberOnlySections'] == 1 && $cpCfg['cp.excludeRestrictedNavItems'] && !isLoggedInWWW()){
            $searchVar->sqlSearchVar[] = "s.member_only != 1";
        }

        if ($cpCfg['cp.hasMultiSites']){
            $searchVar->sqlSearchVar[] = "
            s.section_id IN (
                SELECT record_id
                FROM site_link
                WHERE module = 'webBasic_section'
                  AND site_id = {$cpCfg['cp.site_id']}
                  AND published = 1
            )
            ";
        }

        $searchVar->sortOrder = "s.sort_order";
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'core_mainNav');

        $arr = array();
        foreach ($dataArray as $row){
            $tmpArr = &$arr[$row['section_id']];
            $tmpArr['section_id']      = $row['section_id'];
            $tmpArr['title']           = $ln->gfv($row, 'title');
            $tmpArr['titleEng']        = $row['title'];
            $tmpArr['titleActual']     = $ln->gfv($row, 'title', false);
            $tmpArr['button_position'] = $row['button_position'];
            $tmpArr['section_type']    = $row['section_type'];
            $tmpArr['url']             = $this->getUrl($row);
            $tmpArr['external_link']   = $row['external_link'];
            $tmpArr['internal_link']   = $row['internal_link'];
            $tmpArr['show_in_nav']     = $row['show_in_nav'];
            $tmpArr["module"]          = $fn->getModuleNameByType($row['section_type']);
            $tmpArr['member_only']     = isset($row['member_only']) ? $row['member_only'] : 0;
            $tmpArr['non_member_only'] = isset($row['non_member_only']) ? $row['non_member_only'] : 0;
            $tmpArr['active']          = ($tv['room'] == $row['section_id']) ? 1 : 0;
            $tmpArr['no_follow']       = isset($row['no_follow']) ? $row['no_follow'] : 0;

            $tmpArr['link_target'] = '';

            if ($row['internal_link'] != '') {
                $tmpArr['url'] = $row['internal_link'];
            }
            if ($row['external_link'] != '') {
                $tmpArr['url'] = $row['external_link'];
                $tmpArr['link_target'] = '_blank';
            }

            if (isset($row['css_style_name'])){
                $tmpArr['css_style_name'] = $row['css_style_name'];
            }

            if (isset($row['caption'])){
                $tmpArr['caption'] = $ln->gfv($row, 'caption');
            }

            if($cpCfg['cp.hasAliasSectionsAcrossMUSites']){
                $tmpArr['alias_to_section_id'] = $row['alias_to_section_id'];
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

        $section_id   = $row['section_id'];
        $title        = $row['title'];
        $section_type = $row['section_type'];

        $urlArray = array();

        $excludeList = $fn->getIssetParam($cpCfg, 'cp.roomsWithNoAutoSelctCategory', array());
        $excludeListByType = $fn->getIssetParam($cpCfg, 'cp.roomsByTypeWithNoAutoSelctCategory', array());

        if ($tv['countryCodeReq'] != ''){
            $urlArray['countryCodeReq'] = $tv['countryCodeReq'];
        }

        if ($tv['siteType'] != ''){
            $urlArray['siteType'] = $tv['siteType'];
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

        $urlArray['section_id']    = $section_id;
        $urlArray['section_title'] = $title;

        /*** if there is a category record for the section,
             then change the link with the category title & id
        ***/
        if($cpCfg['cp.autoSelectFirstCategory'] == 1){
            $firstCatSubCatArray  = $this->getFirstCatForSection($row);
            if (count($firstCatSubCatArray) > 0
                && $section_type != 'Home'
                && !in_array($section_id, $excludeList)
                && !in_array($section_type, $excludeListByType)
            ){
                $urlArray['category_id']    = $firstCatSubCatArray['category_id'];
                $urlArray['category_title'] = $firstCatSubCatArray['category_title'];

                if(isset($firstCatSubCatArray['sub_category_id'])){
                    $urlArray['sub_category_id']    = $firstCatSubCatArray['sub_category_id'];
                    $urlArray['sub_category_title'] = $firstCatSubCatArray['sub_category_title'];
                }
            }
        }

        $url = $cpUrl->make_seo_url($urlArray);

        return $url;
    }

    /**
     *
     * @param <type> $section_id
     * @return <type>
     */
    function getFirstCatForSection($row){
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $firstCatSubCatArray = array();

        $section_id = $row['section_id'];
        if($cpCfg['cp.hasAliasSectionsAcrossMUSites'] && $row['alias_to_section_id'] != ''){
            $section_id = $row['alias_to_section_id'];
        }

        //*** check there are how many content records for this section without a category
        $SQL = "
        SELECT count(*)
        FROM content
        WHERE section_id = {$section_id}
          AND (category_id IS NULL OR category_id ='')
          AND content_type = 'Record'
          AND published = 1
        ";

        if (!$cpCfg['cp.isMobileDevice'] && $cpCfg['cp.hasWebOnlySections']){
            $SQL .= "
            AND ok_for_web = 1
            ";
        }

        if ($cpCfg['cp.isMobileDevice'] && $cpCfg['cp.hasMobileOnlySections']){
            $SQL .=  "
            AND ok_for_mobile = 1
            ";
        }

        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);

        //*** if the count is zero
        if ($row[0] == 0){
            $sqlAppend = "";

            if ($cpCfg['cp.hasSectionsByUserType'] == 1){
                $userType = isset($_SESSION['userType']) ? $_SESSION['userType'] : "Public";
                $sqlAppend .= "
                AND (a.access_to LIKE '%{$userType}%' OR a.access_to =''  OR a.access_to IS NULL)
                ";
            }

            if ($cpCfg['cp.hasMemberOnlyCategories'] == 1 && !isLoggedInWWW()){
                $sqlAppend .= "
                AND (a.member_only = '' OR a.member_only IS NULL)
                ";
            }

            if (!$cpCfg['cp.isMobileDevice'] && $cpCfg['cp.hasWebOnlyCategories']){
                $sqlAppend .= "
                AND a.ok_for_web = 1
                ";
            }

            if ($cpCfg['cp.isMobileDevice'] && $cpCfg['cp.hasMobileOnlyCategories']){
                $sqlAppend .= "
                AND a.ok_for_mobile = 1
                ";
            }

            if ($cpCfg['cp.hasMultiSites']){
                $sqlAppend .= " AND a.category_id IN (
                    SELECT record_id
                    FROM site_link
                    WHERE module = 'webBasic_category'
                      AND site_id = {$cpCfg['cp.site_id']}
                      AND published = 1
                )";
            }

            $SQL = "
            SELECT a.* FROM category a
            WHERE a.published = 1
              AND a.show_in_nav = 1
              AND a.section_id = {$section_id}
              {$sqlAppend}
            ORDER BY a.sort_order limit 0, 1
            ";

            $result  = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);

            if ($numRows > 0){
               $row  = $db->sql_fetchrow($result);
               $firstCatSubCatArray['category_id'] = $row['category_id'];
               $firstCatSubCatArray['category_title'] = $row['title'];
                if($cpCfg['cp.autoSelectFirstSubCategory']){
                    $wSubNav = getCPWidgetObj('core_subNav');
                    $firstSubCatArray = $wSubNav->model->getFirstSubCatForCategory($section_id, $row['category_id'] );
                    if(count($firstSubCatArray) > 0){
                        $firstCatSubCatArray['sub_category_id']    = $firstSubCatArray[0];
                        $firstCatSubCatArray['sub_category_title'] = $firstSubCatArray[1];
                    }
                }
            }
        }

        return $firstCatSubCatArray;
    }

    //========================================================//
    function getMenuDataArray($secArray = '') {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $secArray = ($secArray != '') ? $secArray : $this->dataArray;

        $dataArray = array();

        $subNav = getCPWidgetObj('core_subNav');
        $subCat = getCPWidgetObj('core_subCat');

        foreach($secArray as $key => $row) {
            $url = $this->getUrl($row);

            $tmpArr = &$dataArray['sections'];
            $tmpArr[$key] = $secArray[$row['section_id']];
            $subNav->section_id = $row['section_id'];
            $tmpArr[$key]['categories'] = $subNav->model->getDataArray();


            foreach ($tmpArr[$key]['categories'] as $catId => $catRec){
                $subCat->category_id = $catId;
                $tmpArr2 = &$tmpArr[$key]['categories'][$catRec['category_id']];
                $tmpArr2['subCategories'] = $subCat->model->getDataArray();
            }
        }

       return $dataArray;
    }

    //========================================================//
    function getMenuDataArrayForCatSubCat($secId) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $subNav = getCPWidgetObj('core_subNav');

        $dataArray = array();
        $tmpArr = &$dataArray;
        $subNav = getCPWidgetObj('core_subNav');
        $subNav->section_id = $secId;
        $tmpArr['categories'] = $subNav->model->getDataArray();

        foreach ($tmpArr['categories'] as $catId => $catRec){
            $subCat = getCPWidgetObj('core_subCat');
            $subCat->category_id = $catId;
            $tmpArr2 = &$tmpArr['categories'][$catRec['category_id']];
            $tmpArr2['subCategories'] = $subCat->model->getDataArray();
        }

       //print_r ($dataArray);
       //exit();

       return $dataArray;
    }

    //========================================================//
    function getMenuDataArrayForSubCat($catId) {
        $subCat = getCPWidgetObj('core_subCat');

        $dataArray = array();
        $tmpArr = &$dataArray;

        $subCat->category_id = $catId;
        $dataArray['subCategories'] = $subCat->model->getDataArray();

        return $dataArray;
    }
}