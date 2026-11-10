<?
class CP_Www_Lib_Url extends CP_Common_Lib_Url{

    /**
     *
     * @return <type>
     */
    function getExtIntUrl($row){
        $fn = Zend_Registry::get('fn');
        if (isset($row['internal_link']) && trim($row['internal_link']) != ''){
            return $fn->replaceUrlVars($row['internal_link']);
        }

        if (isset($row['external_link']) && trim($row['external_link']) != ''){
            return $row['external_link'];
        }
    }

    /**
     * compare the incoming url against the system generated one
     * based on the incoming parameters like room, sub-room etc..
     * if the incoming url does not match with the system generated url
     * then 301 redirect to the system generated url
     */
    function compareTheUrlAndRedirect() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $request_uri = urldecode($_SERVER['REQUEST_URI']);

        //==================================================================//
        if ($tv['countryCodeReq'] != ''){
            $this->urlArray['countryCodeReq'] = $tv['countryCodeReq'];
        }

        if ($tv['siteType'] != ''){
            $this->urlArray['siteType'] = $tv['siteType'];
        }

        if ($tv['sitePfxId'] != ''){
            $this->urlArray['sitePfxId'] = $tv['sitePfxId'];
        }

        //==================================================================//
        if ($cpCfg['cp.hasMultiUniqueSites'] && $tv['cp_site'] != ''){
            $this->urlArray['cp_site'] = $tv['cp_site'];
        }
        //==================================================================//
        if ($cpCfg['cp.hasMultiYears'] == 1){
            $this->urlArray['cp_year'] = $tv['cp_year'];
        }
        //==================================================================//
        if ($tv['lang'] != '' && $cpCfg['cp.multiLang'] == 1){
            $this->urlArray['lang']  = $tv['lang'];
        }

        //==================================================================//
        if (is_numeric($tv['room'])){
            $sectionRec = $fn->getRecordRowByID('section', 'section_id', $tv['room']);
        } else if (is_numeric ($tv['subRoom'])) {
            /** if the user enters the wrong section name then try to find the correct name by the category ***/
            $catRecord = $fn->getRecordRowByID('category', 'category_id', $tv['subRoom']);
            $section_id = $catRecord['section_id'];
            $sectionRec = $fn->getRecordRowByID('section', 'section_id', $section_id);
        }

        if (is_array($sectionRec)){
            $this->urlArray['section_title'] = $sectionRec['title'];
        }

        //==================================================================//
        $exp = ($cpCfg['cp.hasAliasSectionsAcrossMUSites']) ? array('globalForAllSites' => true) : array();
        if (is_numeric($tv['subRoom']) ){
            $catRecord = $fn->getRecordRowByID('category', 'category_id', $tv['subRoom'], $exp);
            if (is_array($catRecord)){
                $this->urlArray['category_id'] = $catRecord['category_id'];
                $this->urlArray['category_title'] = $catRecord['title'];
            }
        }

        //==================================================================//
        if (is_numeric($tv['subCat'])){
            $subCatRecord = $fn->getRecordRowByID('sub_category', 'sub_category_id', $tv['subCat'], $exp);

            if (is_array($subCatRecord) ){
                $this->urlArray['sub_category_id'] = $tv['subCat'];
                $this->urlArray['sub_category_title'] = $subCatRecord['title'];
            }
        }

        //==================================================================//
        if ($tv['actionName'] != ''){
            $this->urlArray['action_name'] = $tv['actionName'];
        }

        //==================================================================//
        if (is_numeric($tv['record_id'])){

            if ($tv['record_title'] == ''){
                $this->urlArray['record_id'] = $tv['record_id'];
            } else {
                $record = $this->getRecordForUrlComparision();

                if (is_array($record) ){
                    $this->urlArray['record_id'] = $record['id'];
                    $this->urlArray['record_title'] = $record['title'];
                }
            }
        }

        if ($tv['page'] > 0 ){
            $this->urlArray['page_no'] = $tv['page'];
        }

        $tempArr = explode('?', $request_uri);
        if (count($tempArr) == 2){
            $this->urlArray['queryStr'] = $tempArr[1];
        }

        //==================================================================//
        $actual_uri = $this->make_seo_url($this->urlArray);

        // print $request_uri . "<br>";
        // print $actual_uri;
        // return;

        /** if the record does not exist in a section then try to search in
         * other section and 301 redirect to it
         */
        if ($request_uri != $actual_uri && $tv['room_name'] != "") {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $actual_uri);
            exit();
        }
    }

    /**
     * This function is written separately for more control
     * when the record is actually from different tables
     * than the content alone
     */
    function getRecordForUrlComparision() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');

        $record = '';
        $keyField  = $modulesArr[$tv['module']]["keyField"];
        $tableName = $modulesArr[$tv['module']]["tableName"];
        $titleField = $modulesArr[$tv['module']]["titleField"];
        $exp = ($cpCfg['cp.hasAliasSectionsAcrossMUSites']) ? array('globalForAllSites' => true) : array();

        if (is_numeric ($tv['record_id']) ){
            $rec = $fn->getRecordRowByID($tableName, $keyField, $tv['record_id'], $exp);

            if (is_array($rec)){
                $record['id'] = $tv['record_id'];
                $record['title'] = $rec[$titleField];
            }
        }

        return $record;
    }


}