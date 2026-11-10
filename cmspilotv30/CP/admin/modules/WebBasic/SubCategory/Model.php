<?
class CP_Admin_Modules_WebBasic_SubCategory_Model extends CP_Common_Modules_WebBasic_SubCategory_Model
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $countryAppendSQL = '';
        $countryJoinSQL = '';
        $category2Column = '';
        $category3Column = '';
        $category2JoinSQL = '';
        $category3JoinSQL = '';

        $extraTableNames = '';
        if ($cpCfg['cp.hasMultiSites']) {
            $site_id    = $fn->getReqParam('site_id');
            if ($site_id != "") {
                $extraTableNames .= "JOIN site_link sl ON (sc.sub_category_id = sl.record_id AND sl.module ='webBasic_subCategory')";
            }
        }

        if ($cpCfg['m.webBasic.subCategory.showCountry'] == 1) {
            $countryAppendSQL = ",co.country_name AS country_name";
            $countryJoinSQL = "LEFT JOIN country co ON sc.country_id = co.country_id";
        }
        if ($cpCfg['m.webBasic.subCategory.hasCategory2']){
            $category2Column = ',c2.title AS category2_title';
            $category2JoinSQL = 'LEFT JOIN category c2 ON c2.category_id = sc.category2_id';
        }
        if ($cpCfg['m.webBasic.subCategory.hasCategory3']){
            $category3Column = ',c3.title AS category3_title';
            $category3JoinSQL = 'LEFT JOIN category c3 ON c3.category_id = sc.category3_id';
        }

        $SQL = "
        SELECT sc.*
              ,c.title AS category_title
              {$category2Column}
              {$category3Column}
              ,s.title AS section_title
              {$countryAppendSQL}
        FROM sub_category sc
        LEFT JOIN category c ON c.category_id = sc.category_id
        {$category2JoinSQL}
        {$category3JoinSQL}
        LEFT JOIN section s  ON s.section_id  = c.section_id
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
        $searchVar = Zend_Registry::get('searchVar');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar->mainTableAlias = 'sc';

        $country_id = $fn->getReqParam('country_id');

        if ($cpCfg['cp.hasMultiSites']) {
            $site_id    = $fn->getReqParam('site_id');
            if($site_id != ''){
                $searchVar->sqlSearchVar[] = "sl.site_id = '{$site_id}'";
            }
        }

        if ($country_id != '') {
            $searchVar->sqlSearchVar[] = "sc.country_id = {$country_id}";
        }

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "sc.sub_category_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'sc.sub_category_id');

            if ($tv['section_id'] != '' ) {
                $searchVar->sqlSearchVar[] = "s.section_id  = {$tv['section_id']}";

            } else if  (!isset($_REQUEST['section_id'])
                     && $cpCfg['m.webBasic.subCategory.defaultSectionType'] != '') {
                $sectionRec = getCPModelObj('webBasic_section')
                              ->getRecordBySectionType($cpCfg['m.webBasic.subCategory.defaultSectionType']);
                $searchVar->sqlSearchVar[] = "s.section_id = {$sectionRec['section_id']}";
            }

            if ($tv['category_id'] != '' ) {
                $searchVar->sqlSearchVar[] = "c.category_id = '{$tv['category_id']}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(sc.title LIKE '%{$tv['keyword']}%')";
            }
        }

        $fnModCountry = includeCPClass('ModuleFns', 'common_country');
        $searchVar = $fnModCountry->setCountrySearch($searchVar, 'sc');

        $searchVar->sortOrder = "c.title";
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
        $fa['sort_order'] = $fn->getNextSortOrder("sub_category");
        $fa['category_id']  = $fn->getReqParam('category_id');
        $fa['sub_category_type']  = 'Content';

        if ($cpCfg['cp.showOnlyDataOfStaffsCountry'] == 1) {
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
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'category_id');
        $fa = $fn->addToFieldsArray($fa, 'sub_category_type');
        $fa = $fn->addToFieldsArray($fa, 'external_link');
        $fa = $fn->addToFieldsArray($fa, 'internal_link');
        $fa = $fn->addToFieldsArray($fa, 'description', '', true);
        $fa = $fn->addToFieldsArray($fa, 'description_short', '', true);
        $fa = $fn->addToFieldsArray($fa, 'published');

        $fa = $fn->addToFieldsArray($fa, 'meta_title', '', $cpCfg['cp.hasMultiLangForMetaData']);
        $fa = $fn->addToFieldsArray($fa, 'meta_keyword', '', $cpCfg['cp.hasMultiLangForMetaData']);
        $fa = $fn->addToFieldsArray($fa, 'meta_description', '', $cpCfg['cp.hasMultiLangForMetaData']);

        $fa = $fn->addToFieldsArray($fa, 'country_id');
        $fa = $fn->addToFieldsArray($fa, 'show_in_nav');
        $fa = $fn->addToFieldsArray($fa, 'title_ref');
        $fa = $fn->addToFieldsArray($fa, 'ok_for_web');
        $fa = $fn->addToFieldsArray($fa, 'ok_for_mobile');
        $fa = $fn->addToFieldsArray($fa, 'product_group_id');

        if (isset($_POST['no_follow'])){
            $fa = $fn->addToFieldsArray($fa, 'no_follow', 0);
        }

        if ($cpCfg['m.webBasic.subCategory.hasCategory2']){
            $fa = $fn->addToFieldsArray($fa, 'category2_id');
        }
        if ($cpCfg['m.webBasic.subCategory.hasCategory3']){
            $fa = $fn->addToFieldsArray($fa, 'category3_id');
        }


        if ($cpCfg['m.webBasic.subCategory.hasNonMemberOnly'] == 1){
            $fa = $fn->addToFieldsArray($fa, 'member_only');
        }

        if ($cpCfg['m.webBasic.subCategory.hasNonMemberOnly'] == 1){
            $fa = $fn->addToFieldsArray($fa, 'non_member_only');
        }
        return $fa;
    }

    /**
     *
     */
    function getSubCatIdByTitleWithAutoCreate($title, $category_id, $short_desc = ''){

        if ($title == '' || $category_id == ''){
            return;
        }

        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $titleQ = escapeForDb($title);

        $rec = $fn->getRecordByCondition('sub_category', "title = '{$titleQ}' AND category_id = {$category_id}");

        if(!is_array($rec)){
            $fa = array();
            $fa['title']       = $title;
            $fa['category_id'] = $category_id;
            $fa['sub_category_type'] = 'Content';
            $fa['published']   = 1;

            if ($short_desc != '') {
                $fa['description_short'] = $short_desc;
            }
            $fa['sort_order']  = $fn->getNextSortOrder('sub_category');

            $sql = $dbUtil->getInsertSQLStringFromArray($fa, "sub_category");
            $result = $db->sql_query($sql);
            $sub_category_id = $db->sql_nextid();
        } else {
            $sub_category_id = $rec['sub_category_id'];
        }

        return $sub_category_id;
    }

    /**
     *
     */
    function getSubCategorySQLByCategory($category_id) {
        $category_id = $category_id ? $category_id : 0;

        $SQL = "
        SELECT DISTINCT a.sub_category_id
               , a.title
               , b.title
        FROM sub_category a
             , category b
        WHERE a.category_id = b.category_id
          AND a.category_id = {$category_id}
        ORDER BY b.title
        ";

        return $SQL;
    }

    function getSubCategorySQL($category_id = '') {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $wMultiYear = getCPWidgetObj('common_multiYear', false)->model;
        $wMultiUniqueSite = getCPWidgetObj('common_multiUniqueSite', false)->model;
        $cpYearWhere = $wMultiYear->getSqlCondn();
        $cpUniqueSiteWhere = $wMultiUniqueSite->getSqlCondn();

        $whereArr = array();
        if ($category_id != '') {
            $whereArr[] = "category_id = {$category_id}";
        }
        if ($cpYearWhere != '') {
            $whereArr[] = $cpYearWhere;
        }
        if ($cpUniqueSiteWhere != '') {
            $whereArr[] = $cpUniqueSiteWhere;
        }

        $titleFld = 'title';

        if ($cpCfg['m.webBasic.subCategory.showRefTitle']){
            $titleFld = "IF(title_ref IS NOT NULL, CONCAT_WS('', title, ': ',title_ref), title)";
            $SQL = $fn->getDDSql('webBasic_subCategory', array('condn' => $whereArr, 'titleFld' => $titleFld));
        } else {
            $SQL = $fn->getDDSql('webBasic_subCategory', array('condn' => $whereArr));
        }


        return $SQL;
    }



}
