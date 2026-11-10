<?
class CP_Www_Widgets_Core_SubCat_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');

        $group = '';
        if($cpCfg['w.core.subCat.hasGrouping']){
            $group = ',sc.group_name';
        }

        $SQL = "
        SELECT sc.*
              ,c.section_id
              ,c.title AS category_title_eng
              ,s.title AS section_title_eng
              {$group}
        FROM sub_category sc
        JOIN category c ON (c.category_id = sc.category_id)
        JOIN section s ON (s.section_id  = c.section_id)
        ";

        $hook = getCPWidgetHook('core_subCat', 'SQL', $this, $SQL);
        if($hook['status']){
            return $hook['html'];
        }

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
        $roomsArray = Zend_Registry::get('roomsArray');
        $searchVar->mainTableAlias = 'sc';

        $c = &$this->controller;

        $searchVar->sqlSearchVar[] = "sc.published = 1";

        $roomArr = $roomsArray[$tv['room']];
        if($cpCfg['cp.hasAliasSectionsAcrossMUSites'] && $roomArr['alias_to_section_id'] != ''){
            $searchVar->addSiteIdForMultiUniqueSites = false;
        }

        if ($c->category_id != ''){
            $searchVar->sqlSearchVar[] = "sc.category_id = {$c->category_id}";
        } else {
            $searchVar->sqlSearchVar[] = "sc.category_id = '{$tv['subRoom']}'";
        }

        if (!$cpCfg['cp.isMobileDevice'] && $cpCfg['cp.hasWebOnlySubCategories']){
            $searchVar->sqlSearchVar[] = "sc.ok_for_web = 1";
        }

        if ($cpCfg['cp.isMobileDevice'] && $cpCfg['cp.hasMobileOnlySubCategories']){
            $searchVar->sqlSearchVar[] = "sc.ok_for_mobile = 1";
        }

        if($cpCfg['w.core.subCat.hasGrouping']){
            $groupArr = join(', ', $cpCfg['w.core.subCat.groupArr']);
            $searchVar->sortOrder = " FIND_IN_SET(sc.group_name, '{$groupArr}'), {$this->controller->sortBy}";
        } else {
            $searchVar->sortOrder = $this->controller->sortBy;
        }

        if ($cpCfg['cp.hasMultiSites']){
            $searchVar->sqlSearchVar[] = "
            sc.sub_category_id IN (
                SELECT record_id
                FROM site_link
                WHERE module = 'webBasic_subCategory'
                  AND site_id = {$cpCfg['cp.site_id']}
                  AND published = 1
            )
            ";
        }

        $hook = getCPWidgetHook('core_subCat', 'searchVar', $this, $searchVar);
        if($hook['status']){
            return $hook['html'];
        }
    }

    /**
     *
     */
    function getDataArray(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $c = $this->controller;

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'core_subCat');

        $arr = array();

        foreach ($dataArray as $row){
            $tmpArr = &$arr[$row['sub_category_id']];

            $tmpArr['sub_category_id']   = $row['sub_category_id'];
            $tmpArr['title']             = $ln->gfv($row, 'title');
            $tmpArr['titleEng']          = $row['title'];
            $tmpArr['sub_category_type'] = $row['sub_category_type'];
            $tmpArr['url']               = $this->getUrl($row);
            $tmpArr['external_link']     = $row['external_link'];
            $tmpArr['internal_link']     = $row['internal_link'];
            $tmpArr['show_in_nav']       = $fn->getIssetParam($row, 'show_in_nav', 1);
            $tmpArr["module"]            = $fn->getModuleNameByType($row['sub_category_type']);
            $tmpArr['member_only']       = isset($row['member_only']) ? $row['member_only'] : 0;
            $tmpArr['non_member_only']   = isset($row['non_member_only']) ? $row['non_member_only'] : 0;
            $tmpArr['active']            = ($tv['subCat'] == $row['sub_category_id']) ? 1 : 0;
            $tmpArr['no_follow']         = isset($row['no_follow']) ? $row['no_follow'] : 0;
            $tmpArr['link_target'] = '';

            if ($row['internal_link'] != '') {
                $tmpArr['url'] = $row['internal_link'];
            }
            if ($row['external_link'] != '') {
                $tmpArr['url'] = $row['external_link'];
                $tmpArr['link_target'] = '_blank';
            }

            if($cpCfg['w.core.subCat.hasGrouping']){
                $tmpArr['group_name'] = $row['group_name'];
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

        $urlArray['section_id']         = $row['section_id'];
        $urlArray['section_title']      = $row['section_title_eng'];
        $urlArray['category_id']        = $row['category_id'];
        $urlArray['category_title']     = $row['category_title_eng'];
        $urlArray['sub_category_id']    = $row['sub_category_id'];
        $urlArray['sub_category_title'] = $row['title'];
        $url = $cpUrl->make_seo_url($urlArray);

        return $url;
    }
}