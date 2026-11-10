<?
class CP_Www_Modules_Museum_Facility_Model extends CP_Common_Modules_Museum_Facility_Model
{
    function getSQL() {
        $SQL = "
        SELECT f.*
              ,s.title AS section_title
              ,s.section_type
              ,ca.title AS category_title
              ,ca.category_type
              ,sc.title AS sub_category_title
              ,sc.sub_category_type
        FROM facility f
        LEFT JOIN (section s)      ON (f.section_id       = s.section_id)
        LEFT JOIN (category ca)    ON (f.category_id      = ca.category_id)
        LEFT JOIN (sub_category sc)ON (f.sub_category_id  = sc.sub_category_id)
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

        $searchVar->sqlSearchVar['published'] = "f.published = 1";
        if ($tv['record_id'] != ''){
            $searchVar->sqlSearchVar['facility_id'] = "f.facility_id  = {$tv['record_id']}";
        } else {
            if ($tv['room'] != '' && is_numeric($tv['room'])){
                $searchVar->sqlSearchVar['section_id'] = "f.section_id  = {$tv['room']}";
            }

            if ($tv['subRoom'] != '' && is_numeric($tv['subRoom']) ){
                $searchVar->sqlSearchVar['category_id'] = "f.category_id  = {$tv['subRoom']}";
            }

            if ($tv['subCat'] != '' && is_numeric($tv['subCat']) ){
                $searchVar->sqlSearchVar['sub_category_id'] = "f.sub_category_id  = {$tv['subCat']}";
            }

            if ($tv['sub_category_id'] != '') {
                $searchVar->sqlSearchVar['sub_category_id'] = "f.sub_category_id  = {$tv['sub_category_id']}";
            }

            $excludeList = $cpCfg['cp.roomsWithNoAutoSelctCategory'];
            if (!in_array($tv['room'], $excludeList)) {
                if ($tv['module'] != '' && $tv['subRoom'] == '') {
                    if((!$cpCfg['cp.isMobileDevice'] && $cpCfg['m.webBasic.content.showOrphanRecords']) ||
                    ($cpCfg['cp.isMobileDevice'] && $cpCfg['m.webBasic.content.showOrphanRecordsMobile'])) {                    
                        $searchVar->sqlSearchVar['category_id_2'] =
                        "(f.category_id IS NULL OR f.category_id ='')";
                    }
                }
            }

            if ($tv['keyword'] != ""){
                $searchVar->sqlSearchVar['keyword'] = "(
                    f.title        LIKE '%{$tv['keyword']}%' OR
                    f.description  LIKE '%{$tv['keyword']}%'
                )";
            }
        }

        if ($cpCfg['cp.hasMultiSites']){
            $searchVar->sqlSearchVar['site_link'] = "
            f.facility_id IN (
                SELECT record_id
                FROM site_link
                WHERE module = 'museum_facility'
                  AND site_id = {$cpCfg['cp.site_id']}
                  AND published = 1
            )
            ";
        }

      /*  if (!isLoggedInWWW()){
            $searchVar->sqlSearchVar['member_only'] = "(f.member_only != '1' OR f.member_only IS NULL)";
        } */

       // $searchVar->sortOrder = "f.sort_order ASC, e.event_date DESC";
    }

}