<?
class CP_Admin_Modules_Museum_Library_Model extends CP_Common_Modules_Museum_Library_Model
{

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
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'accession_number');
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'added_title');
        $fa = $fn->addToFieldsArray($fa, 'added_name');
        $fa = $fn->addToFieldsArray($fa, 'author');
        $fa = $fn->addToFieldsArray($fa, 'added_author');
        $fa = $fn->addToFieldsArray($fa, 'call_no');
        $fa = $fn->addToFieldsArray($fa, 'additional_copies');
        $fa = $fn->addToFieldsArray($fa, 'edition');
        $fa = $fn->addToFieldsArray($fa, 'isbn');
        $fa = $fn->addToFieldsArray($fa, 'issn');
        $fa = $fn->addToFieldsArray($fa, 'language');
        $fa = $fn->addToFieldsArray($fa, 'note');
        $fa = $fn->addToFieldsArray($fa, 'item_type');
        $fa = $fn->addToFieldsArray($fa, 'people');
        $fa = $fn->addToFieldsArray($fa, 'physical_description');
        $fa = $fn->addToFieldsArray($fa, 'published_date');
        $fa = $fn->addToFieldsArray($fa, 'published_place');
        $fa = $fn->addToFieldsArray($fa, 'publisher');
        $fa = $fn->addToFieldsArray($fa, 'search_terms');
        $fa = $fn->addToFieldsArray($fa, 'series');
        $fa = $fn->addToFieldsArray($fa, 'added_series_title');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'subjects');
        $fa = $fn->addToFieldsArray($fa, 'summary');
        $fa = $fn->addToFieldsArray($fa, 'opac_ref');

        if(isset($_POST['published'])){
            $fa = $fn->addToFieldsArray($fa, 'published');
        }

        $fa = $fn->addToFieldsArray($fa, 'creation_date');
        $fa = $fn->addToFieldsArray($fa, 'modification_date');
        $fa = $fn->addToFieldsArray($fa, 'latest');

        if ($cpCfg['m.museum.collection.showMetaData'] == 1) {
            $fa = $fn->addToFieldsArray($fa, 'meta_title');
            $fa = $fn->addToFieldsArray($fa, 'meta_keyword');
            $fa = $fn->addToFieldsArray($fa, 'meta_description');
        }

        return $fa;
    }

   function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');

        $library_id   = $fn->getReqParam('library_id');
        $special_search  = $fn->getReqParam('special_search');
        $itemType = $fn->getReqParam('item_type');
        $language  = $fn->getReqParam('language');
        $status    = $fn->getReqParam('status');

        if ($library_id != '') {
            $searchVar->sqlSearchVar[] = "l.library_id = {$library_id}";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "l.library_id = {$tv['record_id']}";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'l.library_id');

            if($tv['linkName'] == 'library#library'){
                $searchVar->sqlSearchVar[] = "l.library_id != {$tv['linkMasterTableID']}";
            }

            if ($tv['record_id'] != '') {
                $searchVar->sqlSearchVar[] = "l.library_id = '{$tv['record_id']}'";
            }

            if ($itemType != '') {
                $searchVar->sqlSearchVar[] = "l.item_type = '{$itemType}'";
            }

            if ($language != '') {
                $searchVar->sqlSearchVar[] = "l.language = '{$language}'";
            }

            if ($status != '') {
                $searchVar->sqlSearchVar[] = "l.status = '{$status}'";
            }


            if ($special_search != '' ) {

                if ($special_search == 'Published') {
                    $searchVar->sqlSearchVar[] = "l.published = 1";
                }

                if ($special_search == 'Not-Published') {
                    $searchVar->sqlSearchVar[] = "l.published = 0 OR l.published IS NULL OR l.published = ''";
                }

                if ($special_search == 'Latest' ) {
                    $searchVar->sqlSearchVar[] = "l.latest = 1";
                }

                if ($special_search == 'Flag' ) {
                    $searchVar->sqlSearchVar[] = "l.flag = 1";
                }
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "( l.accession_number        LIKE '%{$tv['keyword']}%'  OR
                                                l.title                   LIKE '%{$tv['keyword']}%'  OR
                                                l.added_title             LIKE '%{$tv['keyword']}%'  OR
                                                l.added_name              LIKE '%{$tv['keyword']}%'  OR
                                                l.author                  LIKE '%{$tv['keyword']}%'  OR
                                                l.added_author            LIKE '%{$tv['keyword']}%'  OR
                                                l.call_no                 LIKE '%{$tv['keyword']}%'  OR
                                                l.edition                 LIKE '%{$tv['keyword']}%'  OR
                                                l.isbn                    LIKE '%{$tv['keyword']}%'  OR
                                                l.issn                    LIKE '%{$tv['keyword']}%'  OR
                                                l.note                    LIKE '%{$tv['keyword']}%'  OR
                                                l.people                  LIKE '%{$tv['keyword']}%'  OR
                                                l.people                  LIKE '%{$tv['keyword']}%'  OR
                                                l.physical_description    LIKE '%{$tv['keyword']}%'  OR
                                                l.published_date          LIKE '%{$tv['keyword']}%'  OR
                                                l.publisher               LIKE '%{$tv['keyword']}%'  OR
                                                l.published_place         LIKE '%{$tv['keyword']}%'  OR
                                                l.search_terms            LIKE '%{$tv['keyword']}%'  OR
                                                l.series                  LIKE '%{$tv['keyword']}%'  OR
                                                l.added_series_title      LIKE '%{$tv['keyword']}%'  OR
                                                l.subjects                LIKE '%{$tv['keyword']}%'  OR
                                                l.opac_ref                LIKE '%{$tv['keyword']}%'  OR
                                                l.summary                 LIKE '%{$tv['keyword']}%'
                                              )";
            }
        }
    }

    /**
     *
     */
    function getImportData(){
        $db = Zend_Registry::get('db');

        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper', 'PhpExcelImportWrapper');
        $fa = array(
              'accession_number'    => $phpExcel->getImportFldObj('ACCESSNO')
             ,'title'               => $phpExcel->getImportFldObj('TITLE')
             ,'added_title'         => $phpExcel->getImportFldObj('TITLEX')
             ,'added_name'          => $phpExcel->getImportFldObj('UDF21')
             ,'author'              => $phpExcel->getImportFldObj('CREATOR')
             ,'added_author'        => $phpExcel->getImportFldObj('AUTHORX')
             ,'call_no'             => $phpExcel->getImportFldObj('CALLNO')
             ,'additional_copies'   => $phpExcel->getImportFldObj('COPYNO')
             ,'edition'             => $phpExcel->getImportFldObj('EDITION')
             ,'isbn'                => $phpExcel->getImportFldObj('ISBN')
             ,'issn'                => $phpExcel->getImportFldObj('ISSN')
             ,'language'            => $phpExcel->getImportFldObj('LANGUAGE')
             ,'note'                => $phpExcel->getImportFldObj('NOTES')
             ,'item_type'           => $phpExcel->getImportFldObj('OBJNAME')
             ,'people'              => $phpExcel->getImportFldObj('PEOPLE')
             ,'physical_description'=> $phpExcel->getImportFldObj('PHYSDESC')
             ,'published_date'      => $phpExcel->getImportFldObj('DATE')
             ,'published_place'     => $phpExcel->getImportFldObj('PUBPLACE')
             ,'publisher'           => $phpExcel->getImportFldObj('PUBLISHER')
             ,'search_terms'        => $phpExcel->getImportFldObj('STERMS')
             ,'series'              => $phpExcel->getImportFldObj('SERIES')
             ,'added_series_title'  => $phpExcel->getImportFldObj('SERIESX')
             ,'status'              => $phpExcel->getImportFldObj('STATUS')
             ,'subjects'            => $phpExcel->getImportFldObj('SUBJECTS')
             ,'summary'             => $phpExcel->getImportFldObj('DESCRIP')
             ,'opac_ref'            => $phpExcel->getImportFldObj('OBJECTID')

        );

        $config = array(
             'module'          => 'museum_library'
            ,'matchFieldArr'   => array('opac_ref')
            ,'fldsArr'         => $fa
            ,'callbackAfterInsert' => 'importDataRowCallback'
        );
        
        $SQL ="TRUNCATE TABLE `library`";
        $result = $db->sql_query($SQL);
        
        return $phpExcel->importData($config);
    }

    /**
     *
     * @param type $contact_id
     * @param type $fa
     */
    function importDataRowCallback($library_id, $fa, $exp) {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $is_inserted = $fn->getIssetParam($exp, 'is_inserted', false);

        $fa2 = array();
        if ($is_inserted) {
            $fa2['published'] = 1;
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa2, 'library', "WHERE library_id = '{$library_id}'");
            $result = $db->sql_query($SQL);
        }

    }
}
