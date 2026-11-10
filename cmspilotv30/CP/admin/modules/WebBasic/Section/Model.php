<?
class CP_Admin_Modules_WebBasic_Section_Model extends CP_Common_Modules_WebBasic_Section_Model
{
    /**
     *
     */
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $countryAppendSQL = "";
        $countryJoinSQL = "";

        $extraTableNames = "";
        if ($cpCfg['cp.hasMultiSites']) {
            $site_id    = $fn->getReqParam('site_id');
            if ($site_id != "") {
                $extraTableNames .= "JOIN site_link sl ON (s.section_id = sl.record_id AND sl.module ='webBasic_section')";
            }
        }

        if ($cpCfg['m.webBasic.section.showCountry'] == 1) {
            $countryAppendSQL = ",co.country_name AS country_name";
            $countryJoinSQL = "LEFT JOIN (country co) ON (s.country_id = co.country_id)";
        }

        $SQL = "
        SELECT s.*
              {$countryAppendSQL}
              {$fn->getSiteTitleFld()}
        FROM section s
        {$fn->getSiteFldSqlJoin('s')}
        {$extraTableNames}
        {$countryJoinSQL}
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar->mainTableAlias = 's';

        $button_position = $fn->getReqParam('button_position');
        $country_id      = $fn->getReqParam('country_id');
        $special_search  = $fn->getReqParam('special_search');

        if ($cpCfg['cp.hasMultiSites']) {
            $site_id    = $fn->getReqParam('site_id');
            if($site_id != ''){
                $searchVar->sqlSearchVar[] = "sl.site_id = '{$site_id}'";
            }
        }

        if ($country_id != '') {
            $searchVar->sqlSearchVar[] = "s.country_id = {$country_id}";
        }

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "s.section_id = '{$tv['record_id']}'";
        } else {
            if ($button_position != '' ) {
                $searchVar->sqlSearchVar[] = "s.button_position = '{$button_position}'";
            }
    
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                   s.title LIKE '%{$tv['keyword']}%'
                OR s.section_type LIKE '%{$tv['keyword']}%'
                )";
            }
    
            if ($special_search != '' ) {
                if ($special_search == 'Published') {
                    $searchVar->sqlSearchVar[] = "s.published = 1";
                }
            
                if ($special_search == 'Not-Published' ) {
                    $searchVar->sqlSearchVar[] = "(s.published = 0 OR s.published IS NULL OR s.published = '')";
                }
            
                if ($special_search == 'Member Sections') {
                    $searchVar->sqlSearchVar[] = "(s.member_only = 1 OR s.non_member_only = 0)";
                }
            
                if ($special_search == 'Public Sections' ) {
                    $searchVar->sqlSearchVar[] = "(s.member_only = 0 OR s.member_only IS NULL OR s.member_only = '')";
                }
            }
        }


            
        $fnModCountry = includeCPClass('ModuleFns', 'common_country');
        $searchVar = $fnModCountry->setCountrySearch($searchVar, 's');
        $searchVar->sortOrder = "s.sort_order ASC";
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the title');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fa['sort_order'] = $fn->getNextSortOrder("section");
        $fa['button_position']  = $fn->getReqParam('button_position');
        $fa['section_type']  = 'Content';
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');
        $tv = Zend_Registry::get('tv');

        $validate->resetErrorArray();

        if ($tv['lang'] == 'eng') {
            $validate->validateData('title', 'Please enter the title');
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'button_position');
        $fa = $fn->addToFieldsArray($fa, 'external_link');
        $fa = $fn->addToFieldsArray($fa, 'internal_link');
        $fa = $fn->addToFieldsArray($fa, 'section_type');
        $fa = $fn->addToFieldsArray($fa, 'description');

        $fa = $fn->addToFieldsArray($fa, 'meta_title', '', $cpCfg['cp.hasMultiLangForMetaData']);
        $fa = $fn->addToFieldsArray($fa, 'meta_keyword', '', $cpCfg['cp.hasMultiLangForMetaData']);
        $fa = $fn->addToFieldsArray($fa, 'meta_description', '', $cpCfg['cp.hasMultiLangForMetaData']);
        
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'country_id');
        $fa = $fn->addToFieldsArray($fa, 'member_only');
        $fa = $fn->addToFieldsArray($fa, 'non_member_only');
        $fa = $fn->addToFieldsArray($fa, 'css_style_name');
        $fa = $fn->addToFieldsArray($fa, 'show_in_nav');
        $fa = $fn->addToFieldsArray($fa, 'caption');
        $fa = $fn->addToFieldsArray($fa, 'title_ref');
        $fa = $fn->addToFieldsArray($fa, 'alias_to_section_id');
        $fa = $fn->addToFieldsArray($fa, 'ok_for_web');
        $fa = $fn->addToFieldsArray($fa, 'ok_for_mobile');
        
        if (isset($_POST['no_follow'])){
            $fa = $fn->addToFieldsArray($fa, 'no_follow', 0);
        }
        
        if($tv['lang'] == "eng" || $tv['lang'] == ""){
            $titleLang = $ln->getFieldPrefix() . "title";
            $fa['seo_title'] = $cpUrl->getUrlText($fa[$titleLang]);
        }

        if ($cpCfg['m.webBasic.section.hasAdSpaces']){
            $fa = $fn->addToFieldsArray($fa, 'ad_space1');
            $fa = $fn->addToFieldsArray($fa, 'ad_space2');
            $fa = $fn->addToFieldsArray($fa, 'ad_space3');
        }
        
        return $fa;
    }

    /**
     *
     */
    function getSectionSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');

        $whereMultiYear = getCPWidgetObj('common_multiYear', false)
                          ->model->getSqlCondn();
        $whereMultiSite = getCPWidgetObj('common_multiUniqueSite', false)
                          ->model->getSqlCondn();

        $whereArr = array();
        $whereArr = $cpUtil->getAddToArrayIfSet($whereArr, $whereMultiYear);
        $whereArr = $cpUtil->getAddToArrayIfSet($whereArr, $whereMultiSite);
        $whereText = $cpUtil->getWhereConditionFromArr($whereArr);

        if ($cpCfg['cp.showOnlyDataOfStaffsCountry'] == 1 && $fn->isSuperAdmin()){
            $SQL = "
            SELECT s.section_id
                  ,IF(co.country_name IS NOT NULL, CONCAT_WS('', s.title, ': ', co.country_name), s.title) AS title
            FROM section s
            LEFT JOIN (country co) ON (s.country_id = co.country_id)
            {$whereText}
            ORDER BY title
            ";
        } else {
            $titleFld = 'title';

            if ($cpCfg['m.webBasic.section.showRefTitle']){
                $titleFld = "IF(title_ref IS NOT NULL, CONCAT_WS('', title, ': ',title_ref), title)";
            }

            $SQL = "
            SELECT section_id
                  ,{$titleFld}
            FROM section
            {$whereText}
            ORDER BY title
            ";
        }

        return $SQL;
    }

    /**
     *
     */
    function getWebBasicSectionCommonSiteLinkSQL1($id) {
        $formObj = Zend_Registry::get('formObj');
        $titleFld = ($formObj->mode == 'edit') ? 'a.site_id' : 'b.title AS title';

        $SQL = "
        SELECT a.site_link_id
              ,{$titleFld}
        FROM `site_link` a
        LEFT JOIN `site` b ON (a.site_id = b.site_id)
        WHERE a.record_id = {$id}
          AND a.module = 'webBasic_section'
        ORDER BY a.site_link_id
        ";
        return $SQL;
    }

    /**
     *
     */
    function getSectionsGroupedBySiteSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $titleFld = 's.title';

        if ($cpCfg['m.webBasic.section.showRefTitle']){
            $titleFld = "IF(title_ref IS NOT NULL, CONCAT_WS('', title, ': ',title_ref), title)";
        }

        $SQL = "
        SELECT s.section_id
              ,{$titleFld}
              ,st.title
        FROM section s
        JOIN site st ON(s.site_id = st.site_id)
        ORDER BY st.title, s.title
        ";

        return $SQL;
    }

    function getRecordBySectionType($sectionType) {
        $db = Zend_Registry::get('db');
        
        $SQL = "
        SELECT *
        FROM section s
        WHERE s.section_type = '{$sectionType}'
        ";
        $result = $db->sql_query($SQL);

        $row = $db->sql_fetchrow($result);
        
        return $row;
    }

    /**
     *
     */
    function getSecIdByTitleWithAutoCreate($title, $section_type){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $titleQ = escapeForDb($title);

        $rec = $fn->getRecordByCondition('section', "title = '{$titleQ}' AND section_type = '{$section_type}'");

        if(!is_array($rec)){
            $fa = array();
            $fa['title'] = $title;
            $fa['section_type'] = $section_type;
            $fa['published'] = 1;
            $fa['sort_order'] = $fn->getNextSortOrder('section');

            $section_id = $fn->addRecord($fa, 'section');
        } else {
            $section_id = $rec['section_id'];
        }

        return $section_id;
    }
}
