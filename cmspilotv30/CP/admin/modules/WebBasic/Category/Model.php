<?
class CP_Admin_Modules_WebBasic_Category_Model extends CP_Common_Modules_WebBasic_Category_Model
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $countryAppendSQL = "";
        $countryJoinSQL = "";

        $extraTableNames = "";
        if ($cpCfg['cp.hasMultiSites']) {
            $site_id    = $fn->getReqParam('site_id');
            if ($site_id != "") {
                $extraTableNames .= "JOIN site_link sl ON (c.category_id = sl.record_id AND sl.module ='webBasic_category')";
            }
        }

        if ($cpCfg['m.webBasic.category.showCountry'] == 1) {
            $countryAppendSQL = ",co.country_name AS country_name";
            $countryJoinSQL = "LEFT JOIN (country co) ON (c.country_id = co.country_id)";
        }

        $SQL = "
        SELECT c.*
              ,s.title AS section_title
              {$countryAppendSQL}
        FROM category c
        LEFT JOIN section s ON c.section_id = s.section_id
        {$extraTableNames}
        {$countryJoinSQL}
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
        $searchVar->mainTableAlias = 'c';

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.category_id = '{$tv['record_id']}'";
        } else {
            $country_id = $fn->getReqParam('country_id');

            if ($cpCfg['cp.hasMultiSites']) {
                $site_id    = $fn->getReqParam('site_id');
                if($site_id != ''){
                    $searchVar->sqlSearchVar[] = "sl.site_id = '{$site_id}'";
                }
            }

            if ($country_id != '') {
                $searchVar->sqlSearchVar[] = "c.country_id = {$country_id}";
            }

            if ($tv['section_id'] != '') {
                $searchVar->sqlSearchVar[] = "s.section_id = {$tv['section_id']}";
            } else if (!isset($_REQUEST['section_id']) && $tv['searchDone'] == 0 && $cpCfg['m.webBasic.category.defaultSectionType'] != '') {
                $sectionRec = getCPModelObj('webBasic_section')->getRecordBySectionType($cpCfg['m.webBasic.category.defaultSectionType']);
                $searchVar->sqlSearchVar[] = "s.section_id = {$sectionRec['section_id']}";
            }

            if ($tv['keyword'] != '') {
                $searchVar->sqlSearchVar[] = "(
                   c.title LIKE '%{$tv['keyword']}%'
                OR c.description LIKE '%{$tv['keyword']}%'
                )";
            }

            $fnModCountry = includeCPClass('ModuleFns', 'common_country');
            $searchVar = $fnModCountry->setCountrySearch($searchVar, 'c');

            $searchVar->sortOrder = "s.section_id, c.sort_order";
        }
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
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fa['sort_order'] = $fn->getNextSortOrder("category");
        $fa['category_type'] = 'Content';
        $fa['section_id']  = $fn->getReqParam('section_id');

        if ($cpCfg['cp.showOnlyDataOfStaffsCountry'] == 1  ) {
            $fa['country_id'] = $fn->getStaffCountryID();
        }

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
        $cpCfg = Zend_Registry::get('cpCfg');

        $fa = array();

        $titleLang = $ln->getFieldPrefix() . "title";
        $descLang  = $ln->getFieldPrefix() . "description";
        //-----------------------------------------------------------------------//

        $fa = $fn->addToFieldsArray($fa, 'section_id');
        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'external_link');
        $fa = $fn->addToFieldsArray($fa, 'internal_link');
        $fa = $fn->addToFieldsArray($fa, 'css_style_name');
        $fa = $fn->addToFieldsArray($fa, 'category_type');
        $fa = $fn->addToFieldsArray($fa, 'caption', '', true);
        $fa = $fn->addToFieldsArray($fa, 'description_short', '', true);
        $fa = $fn->addToFieldsArray($fa, 'description', '', true);

        $fa = $fn->addToFieldsArray($fa, 'meta_title', '', $cpCfg['cp.hasMultiLangForMetaData']);
        $fa = $fn->addToFieldsArray($fa, 'meta_keyword', '', $cpCfg['cp.hasMultiLangForMetaData']);
        $fa = $fn->addToFieldsArray($fa, 'meta_description', '', $cpCfg['cp.hasMultiLangForMetaData']);

        $fa = $fn->addToFieldsArray($fa, 'country_id');
        $fa = $fn->addToFieldsArray($fa, 'css_style_name');
        $fa = $fn->addToFieldsArray($fa, 'show_in_nav');
        $fa = $fn->addToFieldsArray($fa, 'title_ref');
        $fa = $fn->addToFieldsArray($fa, 'ok_for_web');
        $fa = $fn->addToFieldsArray($fa, 'ok_for_mobile');
        $fa = $fn->addToFieldsArray($fa, 'product_group_id');
        if (isset($_POST['no_follow'])) {
            $fa = $fn->addToFieldsArray($fa, 'no_follow', 0);
        }

        if($cpCfg['m.webBasic.category.hasMemberOnly'] == 1){
            $fa = $fn->addToFieldsArray($fa, 'member_only');
        }

        if ($cpCfg['m.webBasic.category.hasNonMemberOnly'] == 1){
            $fa = $fn->addToFieldsArray($fa, 'non_member_only');
        }

        return $fa;
    }

    /**
     *
     */
    function getCatIdByTitleWithAutoCreate($title, $section_type){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $titleQ = escapeForDb($title);

        $secRec = $fn->getRecordByCondition('section', "section_type = '{$section_type}'");
        //if the section does not exist with the give section type then return
        if(!is_array($secRec)){
            return;
        }

        $secId = (is_array($secRec)) ? $secRec['section_id'] : '';
        $rec = $fn->getRecordByCondition('category', "title = '{$titleQ}' AND section_id = {$secId}");

        if(!is_array($rec)){
            $fa = array();
            $fa['title'] = $title;
            $fa['section_id'] = $secId;
            $fa['category_type'] = 'Content';
            $fa['published'] = 1;
            $fa['sort_order'] = $fn->getNextSortOrder('category');

            $category_id = $fn->addRecord($fa, 'category');
        } else {
            $category_id = $rec['category_id'];
        }

        return $category_id;
    }

    /**
     *
     */
    function getCategorySQLByType($sectionType, $categoryType = '') {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');


        $cp_year = $fn->getSessionParam('cp_year');
        $condArr = array();
        $condArr[] = "(s.section_type = '{$sectionType}' OR c.category_type = '{$categoryType}')";
        //$condArr[] = "c.published = 1";
        if ($cpCfg['cp.hasMultiYears']) {
            $condArr[] = "c.cp_year = '{$cp_year}'";
        }

        $where = join(' AND ', $condArr);

        $SQL = "
        SELECT c.category_id
              ,c.title
        FROM category c
        LEFT JOIN (section s) ON (s.section_id = c.section_id)
        WHERE {$where}
        ORDER BY c.title
        ";

        return $SQL;
    }

    function getCategorySQL($section_id = '') {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $wMultiYear = getCPWidgetObj('common_multiYear', false)->model;
        $wMultiUniqueSite = getCPWidgetObj('common_multiUniqueSite', false)->model;
        $cpYearWhere = $wMultiYear->getSqlCondn();
        $cpUniqueSiteWhere = $wMultiUniqueSite->getSqlCondn();

        $whereArr = array();
        if ($section_id != '') {
            $whereArr[] = "section_id = {$section_id}";
        }
        if ($cpYearWhere != '') {
            $whereArr[] = $cpYearWhere;
        }
        if ($cpUniqueSiteWhere != '') {
            $whereArr[] = $cpUniqueSiteWhere;
        }

        $titleFld = 'title';

        if ($cpCfg['m.webBasic.category.showRefTitle']){
            $titleFld = "IF(title_ref IS NOT NULL, CONCAT_WS('', title, ': ',title_ref), title)";
            $SQL = $fn->getDDSql('webBasic_category', array('condn' => $whereArr, 'titleFld' => $titleFld));

        } else {
            $SQL = $fn->getDDSql('webBasic_category', array('condn' => $whereArr));
        }

        return $SQL;
    }

    /**
     *
     */
    function getCategoryGroupedBySectionSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $wMultiUniqueSite = getCPWidgetObj('common_multiUniqueSite', false)->model;

        $titleFld = 'c.title';

        if ($cpCfg['m.webBasic.category.showRefTitle']){
            $titleFld = "IF(c.title_ref IS NOT NULL, CONCAT_WS('', c.title, ': ',c.title_ref), c.title)";
        }

        $cpUniqueSiteWhere = $wMultiUniqueSite->getSqlCondn('c');
        $appendSQL = '';
        if ($cpUniqueSiteWhere != '') {
            $appendSQL = "WHERE {$cpUniqueSiteWhere}";
        }

        $SQL = "
        SELECT DISTINCT
               c.category_id
              ,{$titleFld}
              ,s.title
        FROM category c
        JOIN section s ON(s.section_id = c.section_id)
        {$appendSQL}
        ORDER BY s.title, c.title
        ";

        return $SQL;
    }

    /**
     *
     */
    function getCategorySQLByCategoryType($sectionType, $categoryType = '') {

        $SQL = "
        SELECT c.category_id
              ,c.title
        FROM category c
        LEFT JOIN (section s) ON (s.section_id = c.section_id)
        WHERE s.section_type = '{$sectionType}'
          AND c.category_type = '{$categoryType}'
        ORDER BY c.title
        ";

        return $SQL;
    }
}
