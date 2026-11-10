<?
class CP_Www_Plugins_Common_SiteSearch_Model extends CP_Common_Lib_PluginModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $prefix = $tv['lang'] == 'eng' ? '' : $tv['lang'] . '_';
        
        //--------------------------------------------------//
        $append = '';
        if ($prefix != ''){
            $append = "
            ,c.{$prefix}title AS {$prefix}title
            ,c.{$prefix}description AS {$prefix}description
            ";
        }
        
        $whereArr1 = array();

        $whereArr1[] = "c.published = 1";
        $whereArr1[] = "c.content_type = 'Record'";

        if ($tv['keyword'] != ""){
            $titleFld = "c.{$prefix}title";
            $descriptionFld = "c.{$prefix}description";
            $whereArr1[] = "(
                c.title LIKE '%{$tv['keyword']}%' OR
                c.description LIKE '%{$tv['keyword']}%' OR
                {$titleFld} LIKE '%{$tv['keyword']}%' OR
                {$descriptionFld} LIKE '%{$tv['keyword']}%'
            )";
        }

        if (!isLoggedInWWW()){
            $whereArr1[] = "(c.member_only != '1' OR c.member_only IS NULL)";
        }

        if ($cpCfg['cp.hasMultiUniqueSites']){
            $whereArr1[] = "c.site_id = '{$cpCfg['cp.site_id']}'";
        }

        $sqlSearchVar = join(" AND \n", $whereArr1);
        //--------------------------------------------------//

        $SQL = "
        SELECT c.content_id AS record_id
              ,c.title AS title
              ,c.description AS description
              {$append}
              ,s.title AS section_title
              ,s.section_type
              ,ca.category_id
              ,ca.title AS category_title
              ,ca.category_type
              ,sc.sub_category_id
              ,sc.title AS sub_category_title
              ,sc.sub_category_type
              ,'webBasic_content' AS module
        FROM content c
        LEFT JOIN (section s)      ON (c.section_id       = s.section_id)
        LEFT JOIN (category ca)    ON (c.category_id      = ca.category_id)
        LEFT JOIN (sub_category sc)ON (c.sub_category_id  = sc.sub_category_id)
        WHERE {$sqlSearchVar}
        ";

        if ($cpCfg['p.common.siteSearch.searchProduct']){
            //--------------------------------------------------//
            $append = '';
            if ($prefix != ''){
                $append = "
                ,p.{$prefix}title AS {$prefix}title
                ,p.{$prefix}description AS {$prefix}description
                ";
            }
            
            $whereArr2 = array();

            $whereArr2[] = "p.published = 1";
    
            if ($tv['keyword'] != ""){
                $titleFld = "p.{$prefix}title";
                $descriptionFld = "p.{$prefix}description";
                $whereArr2[] = "(
                    p.title LIKE '%{$tv['keyword']}%' OR
                    p.description LIKE '%{$tv['keyword']}%' OR
                    {$titleFld} LIKE '%{$tv['keyword']}%' OR
                    {$descriptionFld} LIKE '%{$tv['keyword']}%'
                )";
            }
    
            if (!isLoggedInWWW()){
                $whereArr2[] = "(p.member_only != '1' OR p.member_only IS NULL)";
            }

            if ($cpCfg['cp.hasMultiUniqueSites']){
                $whereArr2[] = "p.site_id = '{$cpCfg['cp.site_id']}'";
            }
    
            $sqlSearchVar = join(" AND \n", $whereArr2);

            $SQL = "(
                SELECT p.product_id AS record_id
                      ,p.title AS title
                      ,p.description AS description
                      {$append}
                      ,'' AS section_title
                      ,'Product' AS section_type
                      ,ca.category_id
                      ,ca.title AS category_title
                      ,ca.category_type AS category_type
                      ,sc.sub_category_id
                      ,sc.title AS sub_category_title
                      ,sc.sub_category_type AS sub_category_type
                      ,'ecommerce_product' AS module
                FROM product p
                LEFT JOIN (category ca)    ON (p.category_id      = ca.category_id)
                LEFT JOIN (sub_category sc)ON (p.sub_category_id  = sc.sub_category_id)
                WHERE {$sqlSearchVar}
            )
            UNION 
            ({$SQL})
            ";
        }

        return $SQL;
    }

    /**
     *
     */
    function setSearchVarOld() {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'c';

        $searchVar->sqlSearchVar[] = "c.published = 1";
        $searchVar->sqlSearchVar[] = "c.content_type = 'Record'";

        $prefix = $tv['lang'] == 'eng' ? '' : $tv['lang'] . '_';
        
        $titleFld = "c.{$prefix}title";
        $descriptionFld = "c.{$prefix}description";
        if ($tv['keyword'] != ""){
            $searchVar->sqlSearchVar[] = "(
                c.title LIKE '%{$tv['keyword']}%' OR
                c.description LIKE '%{$tv['keyword']}%' OR
                {$titleFld} LIKE '%{$tv['keyword']}%' OR
                {$descriptionFld} LIKE '%{$tv['keyword']}%'
            )";
        }

        if (!isLoggedInWWW()){
            $searchVar->sqlSearchVar[] = "(c.member_only != '1' OR c.member_only IS NULL)";
        }

        $searchVar->sortOrder = "c.sort_order ASC";
    }

    //========================================================//
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->setPluginDataArray($this->controller, 'common_siteSearch');

        $useSectionUrlSectionTypes = isset($cpCfg['p.common.siteSearch.useSectionUrlSectionTypes']) ?
                                           $cpCfg['p.common.siteSearch.useSectionUrlSectionTypes'] :
                                           array();
        $hasYearParamInUrl = isset($cpCfg['p.common.siteSearch.hasYearParamInUrl']) ?
                                   $cpCfg['p.common.siteSearch.hasYearParamInUrl'] :
                                   false;
        $counter = 0;
        $arr = array();
        foreach($dataArray AS $row) {
            $arrTemp = &$arr[$counter];

            $section_type = $row['section_type'];
            if (in_array($section_type, $useSectionUrlSectionTypes)) {
                $url = $cpUrl->getUrlBySecType($section_type);
                $url .= ($row['module'] == 'ecommerce_product') ? '?product_id=' : '?content_id=';
                $url .= $row['record_id'];

                if ($hasYearParamInUrl) {
                    $content_date = strtotime($row['content_date']);
                    $year = date('Y', $content_date);
                    $url .= '&year=' .$year;
                }
                
                $arrTemp['url'] = $url;
                
            } else if ($row['module'] == 'ecommerce_product') {
                $arrTemp['url'] = $cpUrl->getUrlByRecord($row, 'record_id', array('secType' => 'Product'));
            } else {
                $arrTemp['url'] = $cpUrl->getUrlByRecord($row, 'record_id');
            }
            
            $arrTemp['title']              = $ln->gfv($row, 'title');
            $arrTemp['description']        = $ln->gfv($row, 'description');
            $arrTemp['module']             = $row['module'];
            $arrTemp['section_title']      = $row['section_title'];
            $arrTemp['section_type']       = $row['section_type'];
            $arrTemp['category_title']     = $row['category_title'];
            $arrTemp['category_type']      = $row['category_type'];
            $arrTemp['sub_category_title'] = $row['sub_category_title'];
            $arrTemp['sub_category_type']  = $row['sub_category_type'];
           
            $counter++;
        }

        $this->dataArray = $arr;
        return $arr;
    }
}