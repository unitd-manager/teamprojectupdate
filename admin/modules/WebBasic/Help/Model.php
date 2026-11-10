<?
class CPL_Admin_Modules_WebBasic_Help_Model extends CP_Common_Lib_ModuleModelAbstract

{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

       

        $SQL = "
        SELECT c.*
              
        FROM content c
       
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
        $searchVar->mainTableAlias = 'c';

        $special_search = $fn->getReqParam('special_search');
        $content_type   = $fn->getReqParam('content_type');
        $country_id     = $fn->getReqParam('country_id');
        $content_month  = $fn->getReqParam('content_month');
        $content_year   = $fn->getReqParam('content_year');

        if ($cpCfg['cp.hasMultiSites']) {
            $site_id    = $fn->getReqParam('site_id');
            if($site_id != ''){
                $searchVar->sqlSearchVar[] = "sl.site_id = '{$site_id}'";
            }
        }

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.content_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.content_id');


            
                $searchVar->sqlSearchVar[] = "c.content_type = 'Help'";
            

          



            //------------------------------------------------------------------------//
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    c.title       LIKE '%{$tv['keyword']}%'  OR
                    c.description LIKE '%{$tv['keyword']}%'
                )";
            }

            $fnModCountry = includeCPClass('ModuleFns', 'common_country');
            $searchVar = $fnModCountry->setCountrySearch($searchVar, 'c');
            $searchVar->sortOrder = "c.sort_order ASC";
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
        $fa['sort_order']   = $fn->getNextSortOrder('content');
        $fa['content_type'] = $fn->getReqParam('content_type', 'Record');
        $fa['show_title']   = '1';
        $fa['section_id']   = $fn->getReqParam('section_id');
        $fa['category_id']  = $fn->getReqParam('category_id');
        $fa['sub_category_id'] = $fn->getReqParam('sub_category_id');

        if ($cpCfg['cp.showOnlyDataOfStaffsCountry'] == 1 && !$fn->isSuperAdmin()){
            $fa['country_id'] = $fn->getStaffCountryID();
        }

        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    function updateCloudTags($record_type, $id) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $tags_arr = $fn->getPostParam('tags_ids', array());

        $new_tag_ids = array();

        foreach($tags_arr as $tags_id) {
            $new_tag_ids[]  = $tags_id;

            /**** check whether the record exists in history ***/
            $SQL = "
            SELECT COUNT(*)
            FROM tags_history
            WHERE tags_id     = {$tags_id}
              AND record_id   = {$id}
                    ";
            $result = $db->sql_query($SQL);
            $row    = $db->sql_fetchrow($result);

            if ($row[0] == 0) {
                $fa = array();
                $fa['tags_id']           = $tags_id;
                $fa['record_id']         = $id;
                $fa['record_type']       = $record_type;
                $fa['creation_date']     = date("Y-m-d H:i:s");
                $fa['modification_date'] = date("Y-m-d H:i:s");

                $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, "tags_history");
                $result = $db->sql_query($SQL);
            }
        }

        $list = "'". implode("', '", $new_tag_ids) ."'";

        /**** delete the redundant tag history records  ***/
        $SQL     = "
        DELETE FROM tags_history
        WHERE tags_id NOT IN ({$list})
          AND record_id   = '{$id}'
          AND record_type = '{$record_type}'
        ";
        $result  = $db->sql_query($SQL);
    }

    /**
     *
     */
    function getEditValidate() {
        $tv = Zend_Registry::get('tv');
        $validate = Zend_Registry::get('validate');

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
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);

        if ($cpCfg['m.webBasic.content.showCloudTags'] == 1) {
            $this->updateCloudTags('Content', $id);
        }

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
        $fa = $fn->addToFieldsArray($fa, 'description', '', true);
        $fa = $fn->addToFieldsArray($fa, 'description_short', '', true);

        $fa = $fn->addToFieldsArray($fa, 'section_id');
        $fa = $fn->addToFieldsArray($fa, 'category_id');
        $fa = $fn->addToFieldsArray($fa, 'sub_category_id');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'author_id');
        $fa = $fn->addToFieldsArray($fa, 'content_date');
        $fa = $fn->addToFieldsArray($fa, 'content_type');
        $fa = $fn->addToFieldsArray($fa, 'show_title');
        $fa = $fn->addToFieldsArray($fa, 'latest');
        $fa = $fn->addToFieldsArray($fa, 'member_only');
        $fa = $fn->addToFieldsArray($fa, 'favourite');
        $fa = $fn->addToFieldsArray($fa, 'external_link');
        $fa = $fn->addToFieldsArray($fa, 'internal_link');
        $fa = $fn->addToFieldsArray($fa, 'embed_code');

        $fa = $fn->addToFieldsArray($fa, 'meta_title', '', $cpCfg['cp.hasMultiLangForMetaData']);
        $fa = $fn->addToFieldsArray($fa, 'meta_keyword', '', $cpCfg['cp.hasMultiLangForMetaData']);
        $fa = $fn->addToFieldsArray($fa, 'meta_description', '', $cpCfg['cp.hasMultiLangForMetaData']);

        $fa = $fn->addToFieldsArray($fa, 'country_id');
        $fa = $fn->addToFieldsArray($fa, 'title_ref');
        $fa = $fn->addToFieldsArray($fa, 'show_brochure');
        $fa = $fn->addToFieldsArray($fa, 'ok_for_web');
        $fa = $fn->addToFieldsArray($fa, 'ok_for_mobile');
        $fa = $fn->addToFieldsArray($fa, 'author');

        return $fa;
    }

    /**
     *
     */
    function getContentContentHistoryLinkSQL($id) {
        $ln = Zend_Registry::get('ln');

        $lnPfx = $ln->getFieldPrefix();

        $SQL = "
        SELECT ch.content_history_id
              ,IF(ch.{$lnPfx}title != '', ch.{$lnPfx}title, ch.title) AS title
        FROM content_history ch
        WHERE ch.record_id = {$id}
        AND ch.room_name = 'content'
        ORDER BY ch.sort_order
        ";

        return $SQL;
    }

    /**
     *
     */
    function getContentTabContentLinkSQL($id) {
        $ln = Zend_Registry::get('ln');

        $lnPfx = $ln->getFieldPrefix();

        $SQL = "
        SELECT tc.tab_content_id
              ,IF(tc.{$lnPfx}title != '', tc.{$lnPfx}title, tc.title) AS title
        FROM tab_content tc
        WHERE tc.record_id = {$id}
        AND tc.room_name = 'content'
        ORDER BY tc.sort_order
        ";

        return $SQL;
    }

    /**
     *
     */
    function getImportData(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');

        $fa = array(
              'section_id'        => $phpExcel->getImportFldObj('Section')
             ,'category_id'       => $phpExcel->getImportFldObj('Category')
             ,'sub_category_id'   => $phpExcel->getImportFldObj('Sub Category')
             ,'title'             => $phpExcel->getImportFldObj('Title')
             ,'description_short' => $phpExcel->getImportFldObj('Short Description')
             ,'chi_description_short' => $phpExcel->getImportFldObj('Short Description (Chinese)')
             ,'description'       => $phpExcel->getImportFldObj('Description')
             ,'content_date'      => $phpExcel->getImportFldObj('Date')
             ,'picture'           => $phpExcel->getImportFldObj('Picture Ref')
             ,'published'         => $phpExcel->getImportFldObj('Published')
             ,'content_type'      => $phpExcel->getImportFldObj('Content Type')
        );

        $fa['published']['defaultValue'] = 1;
        $fa['content_type']['defaultValue'] = 'Record';
        $fa['picture']['refOnly'] = true;

        $fa['section_id']['specialType'] = 'fetchIdFromRefModule';
        $fa['section_id']['exp'] = array('refModule' => 'webBasic_section');

        $fa['category_id']['specialType'] = 'fetchIdFromRefModule';
        $fa['category_id']['exp'] = array(
             'refModule' => 'webBasic_category'
            ,'extraFldsOnCreation' => array('section_id')
            ,'extraFldsInSqlCondn' => array('section_id')
        );

        $fa['sub_category_id']['specialType'] = 'fetchIdFromRefModule';
        $fa['sub_category_id']['exp'] = array(
             'refModule' => 'webBasic_subCategory'
            ,'extraFldsOnCreation' => array('category_id')
            ,'extraFldsInSqlCondn' => array('category_id')
        );

        /****************************************/
        $config = array(
             'module'              => 'webBasic_content'
            ,'mandatoryFldsArr'    => array('title')
            ,'fldsArr'             => $fa
        );

        return $phpExcel->importData($config);
    }
}
