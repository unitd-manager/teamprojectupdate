<?
class CP_Www_Widgets_Menu_MeganizrResponsive_Model extends CP_Common_Lib_WidgetModelAbstract
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
        if ($cpCfg['cp.hasMemberOnlySections'] == 1 && !isLoggedInWWW()){
            $searchVar->sqlSearchVar[] = "s.member_only != 1 ";
        }

        if ($cpCfg['cp.hasPublicOnlySections'] == 1 && isLoggedInWWW()){
            $searchVar->sqlSearchVar[] = "s.non_member_only = 1";
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
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'Menu_MegaMenu');

        $arr = array();
        foreach ($dataArray as $row){
            $tmpArr = &$arr[$row['section_id']];
            $tmpArr['title']           = $ln->gfv($row, 'title');
            $tmpArr['titleEng']        = $row['title'];
            $tmpArr['button_position'] = $row['button_position'];
            $tmpArr['section_type']    = $row['section_type'];
            $tmpArr['url']             = $this->getUrl($row);
            $tmpArr['external_link']   = $row['external_link'];
            $tmpArr['internal_link']   = $row['internal_link'];
            $tmpArr['show_in_nav']     = $row['show_in_nav'];
            $tmpArr["module"]          = $fn->getModuleNameByType($row['section_type']);

            if($dbUtil->getColumnExists('section', 'css_style_name')){
                $tmpArr['css_style_name'] = $row['css_style_name'];
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

        if ($cpCfg['cp.multiLang'] == 1){
            $urlArray['lang'] = $tv['lang'];
        }

        $urlArray['section_id']    = $section_id;
        $urlArray['section_title'] = $title;
        $firstCatArray  = $this->getFirstCatForSection($section_id);

        /*** if there is a category record for the section,
             then change the link with the category title & id
        ***/
        if (count($firstCatArray) > 0
            && $section_type != 'Home'
            && $cpCfg['cp.autoSelectFirstCategory'] == 1
            && !in_array($section_id, $excludeList)
        ){
            $urlArray['category_id']    = $firstCatArray[0];
            $urlArray['category_title'] = $firstCatArray[1];
        }
        $url = $cpUrl->make_seo_url($urlArray);

        return $url;
    }

    /**
     *
     * @param <type> $section_id
     * @return <type>
     */
    function getFirstCatForSection($section_id){
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $firstCatArray = array();

        //*** check there are how many content records for this section without a category
        $SQL = "
        SELECT count(*)
        FROM content
        WHERE section_id = {$section_id}
          AND (category_id IS NULL OR category_id ='')
          AND content_type = 'Record'
          AND published = 1
        ";

        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);

        //*** if the count is zero
        if ($row[0] == 0){
            $sqlAppend = "";

            if ($cpCfg['cp.hasSectionsByUserType'] == 1){
                $userType = isset($_SESSION['userType']) ? $_SESSION['userType'] : "Public";
                $sqlAppend .= "AND (a.access_to LIKE '%{$userType}%' OR a.access_to =''  OR a.access_to IS NULL)";
            }

            $SQL = "
            SELECT a.* FROM category a
            WHERE a.published = 1
              AND a.section_id = {$section_id}
              {$sqlAppend}
            ORDER BY a.sort_order limit 0, 1
            ";

            $result  = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);

            if ($numRows > 0){
               $row  = $db->sql_fetchrow($result);
               $firstCatArray = array($row['category_id'], $row['title']);
            }
        }

        return $firstCatArray;
    }

}