<?
class CP_Www_Widgets_Common_Breadcrumb_Model extends CP_Common_Lib_WidgetModelAbstract
{

    /**
     *
     * @return array
     */
    function getSectionUrlArray(){
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $urlArray = array();
        if ($tv['countryCodeReq'] != ''){
            $urlArray['countryCodeReq']  = $tv['countryCodeReq'];
        }
        if ($tv['lang'] != '' && $cpCfg['cp.multiLang'] == 1){
            $urlArray['lang']  = $tv['lang'];
        }

        $sectionRec = '';
        if (is_numeric($tv['room'])){
            $sectionRec = $fn->getRecordRowByID('section', 'section_id', $tv['room']);
        } else if (is_numeric ($tv['subRoom'])) {
            /** if the user enters the wrong section name then try to find the correct name by the category ***/
            $catRecord = $fn->getRecordRowByID('category', 'category_id', $tv['subRoom']);
            $section_id = $catRecord['section_id'];
            $sectionRec = $fn->getRecordRowByID('section', 'section_id', $section_id);
        }

        if (is_array($sectionRec)){
            $urlArray['section_title'] = $sectionRec['title'];
        }

        return $urlArray;
    }

    /**
     *
     * @return array
     */
    function getCategoryUrlArray(){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $urlArray = $this->getSectionUrlArray();
        if (is_numeric($tv['subRoom']) ){
            $record = $fn->getRecordRowByID('category', 'category_id', $tv['subRoom']);

            if (is_array($record)){
                $urlArray['category_id']    = $record['category_id'];
                $urlArray['category_title'] = $record['title'];
            }
        }

        return $urlArray;
    }

    /**
     *
     * @return array
     */
    function getSubCategoryUrlArray(){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $urlArray = $this->getCategoryUrlArray();
        if (is_numeric($tv['subCat'])){
            $record = $fn->getRecordRowByID('sub_category', 'sub_category_id', $tv['subCat']);

            if (is_array($record) ){
                $urlArray['sub_category_id']    = $record['sub_category_id'];
                $urlArray['sub_category_title'] = $record['title'];
            }
        }

        return $urlArray;
    }

    /**
     *
     * @return array
     */
    function getRecordUrlArray(){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $modulesArr = Zend_Registry::get('modulesArr');

        $urlArray = $this->getSubCategoryUrlArray();
        if (is_numeric($tv['record_id'])){
            $keyField   = $modulesArr[$tv['module']]["keyField"];
            $tableName  = $modulesArr[$tv['module']]["tableName"];
            $titleField = $modulesArr[$tv['module']]["titleField"];

            $record = $fn->getRecordRowByID($tableName, $keyField, $tv['record_id']);
            if (is_array($record) ){
                $urlArray['record_id']    = $record[$keyField];
                $urlArray['record_title'] = $record[$titleField];
            }
        }

        return $urlArray;
    }
}