<?
class CP_Www_Modules_Museum_Collection_Model extends CP_Common_Lib_ModuleModelAbstract
{

    /**
     *
     */
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT c.*
              ,s.title AS section_title
              ,s.section_type
              ,ca.title AS category_title
              ,ca.category_type
              ,sc.title AS sub_category_title
              ,sc.sub_category_type
        FROM collection c
        LEFT JOIN (category ca)    ON (c.category_id      = ca.category_id)
        LEFT JOIN (sub_category sc)ON (c.sub_category_id  = sc.sub_category_id)
        LEFT JOIN (section s)      ON (ca.section_id      = s.section_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'c';

        $searchVar->sqlSearchVar['published'] = "c.published = 1";

        if ($tv['record_id'] != ''){
            $searchVar->sqlSearchVar['content_id'] = "c.collection_id  = {$tv['record_id']}";
        }

        if ($tv['record_id'] == ''){
            if ($tv['room'] != '' && is_numeric($tv['room'])){
                $searchVar->sqlSearchVar['section_id'] = "ca.section_id  = {$tv['room']}";
            }

            if ($tv['subRoom'] != '' && is_numeric($tv['subRoom']) ){
                $searchVar->sqlSearchVar['category_id'] = "c.category_id  = {$tv['subRoom']}";
            }

            if ($tv['subCat'] != '' && is_numeric($tv['subCat']) ){
                $searchVar->sqlSearchVar['sub_category_id'] = "c.sub_category_id  = {$tv['subCat']}";
            }

            if ($tv['sub_category_id'] != '') {
                $searchVar->sqlSearchVar['sub_category_id'] = "c.sub_category_id  = {$tv['sub_category_id']}";
            }

            if ($tv['subRoom'] != '' && $tv['subCat'] == '' &&
                $cpCfg['m.webBasic.content.showOrphanRecords'] == 1) {
                $searchVar->sqlSearchVar['sub_category_id_2'] =
                "(c.sub_category_id IS NULL OR c.sub_category_id ='')";
            }

            if ($tv['keyword'] != ""){
                $searchVar->sqlSearchVar['keyword'] = "(
                    c.title        LIKE '%{$tv['keyword']}%' OR
                    c.description  LIKE '%{$tv['keyword']}%'
                )";
            }
        }

        $searchVar->sortOrder = "c.sort_order ASC";
    }

}