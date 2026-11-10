<?
class CP_Www_Modules_LawNews_Jurisdiction_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $SQL = "
        SELECT j.*
              ,v.value AS region_name
        FROM jurisdiction j
        LEFT JOIN (valuelist v) ON (j.region_id = v.valuelist_id )
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

        $searchVar->sqlSearchVar[] = "j.published = 1";

        if ($cpCfg['cp.hasMultiSites']){
            $searchVar->sqlSearchVar[] = "
            j.jurisdiction_id IN (
                SELECT record_id
                FROM site_link
                WHERE module = 'lawNews_jurisdiction'
                  AND site_id = {$cpCfg['cp.site_id']}
                  AND published = 1
            )
            ";
        }


        if ($tv['record_id'] != ''){
            $searchVar->sqlSearchVar[] = "j.jurisdiction_id  = {$tv['record_id']}";
        }  else {
            //to show only the jurisdictions in the list which has published content
            $searchVar->sqlSearchVar[] = "
            j.jurisdiction_id IN (
                SELECT DISTINCT jc.jurisdiction_id
                FROM jurisdiction_content jc
                LEFT JOIN (content c) ON (jc.content_id = c.content_id )
                WHERE c.published = 1
            )
            ";
        }

        $searchVar->sortOrder = "v.sort_order ASC, j.sort_order ASC";
    }

    /**
     *
     * @return type
     */
    function getJurisdictionDataArray() {
        $ln = Zend_Registry::get('ln');
        $dbUtil = Zend_Registry::get('dbUtil');
        $searchVar = Zend_Registry::get('searchVar');

        $SQL  = $this->getSQL();
        $SQL .= $searchVar->getSearchVar('lawNews_jurisdiction');
        $dataArray = $dbUtil->getSQLResultAsArray($SQL);

        return $dataArray;
    }

    /**
     *
     */
    function getDataArrayGroupedByRegion(){
        $dataArray = $this->getJurisdictionDataArray();

        $dataArray2 = array();
        $gRegionId = "";
        foreach ($dataArray as $row){
            $region_id = $row['region_id'];

            if ($region_id != $gRegionId){
                $dataArray2[$region_id] = array();
                $gRegionId = $region_id;
            }

            $dataArray2[$region_id]['name'] = $row['region_name'];
            $dataArray2[$region_id]['rows'][] = $row;
        }

        return $dataArray2;
    }

    /**
     *
     * @param type $jurisdiction_id
     * @return type
     */
    function getActiveCorrespondentId($jurisdiction_id){
        $db = Zend_Registry::get('db');
        $correspondent_id = 0;

        $current_year = date('Y');
        $SQL = "
        SELECT correspondent_id
        FROM correspondent
        WHERE jurisdiction_id = {$jurisdiction_id}
          AND active = 1
          AND published = 1
          AND years_linked LIKE '%{$current_year}%'
        ";

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        if($numRows > 0){
            $row = $db->sql_fetchrow($result);
            $correspondent_id = $row['correspondent_id'];
        }

        return $correspondent_id;
    }
}