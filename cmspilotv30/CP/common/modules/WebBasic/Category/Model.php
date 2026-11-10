<?
class CP_Common_Modules_WebBasic_Category_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getCategorySQLByType($sectionType, $order_by = 'c.title') {
        $cpCfg = Zend_Registry::get('cpCfg');
        $append = '';

        if (CP_SCOPE == 'www') {
            $append .= " AND c.published = 1";
        }   
        
        if ($cpCfg['cp.hasMultiSites']) {
            $append .= " AND c.category_id IN (
                SELECT record_id
                FROM site_link
                WHERE module = 'webBasic_category'
                  AND site_id = {$cpCfg['cp.site_id']}
                  AND published = 1
              )";
        }        
        
        $sql = "
        SELECT c.category_id
              ,c.title
        FROM category c
        LEFT JOIN (section s) ON (s.section_id = c.section_id)
        WHERE (s.section_type = '{$sectionType}')
          {$append}
        ORDER BY {$order_by}
        ";

        return $sql;
    }

    /**
     *
     */
    function getCategorySQLBySection($section_id) {
        $cpCfg = Zend_Registry::get('cpCfg');

        $append = '';
        
        $titleFld = 'c.title';
        
        if (CP_SCOPE == 'www') {
            $append = "c.published = 1";
        } else {
            if ($cpCfg['m.webBasic.category.showRefTitle']){
                $titleFld = "IF(c.title_ref IS NOT NULL, CONCAT_WS('', c.title, ': ',c.title_ref), c.title)";
            }
        }

        $SQL = "
        SELECT DISTINCT c.category_id
              ,{$titleFld} AS title
        FROM category c
        WHERE c.section_id = {$section_id}
        ORDER BY c.title
        ";
        return $SQL;
    }

    /**
     *
     */
    function getRecordByType($categoryType, $sectionType = ""){
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');

        $categoryType    = qstr($categoryType, false);
        $sectionType     = qstr($sectionType, false);

        $lnPfx = $ln->getFieldPrefix();

        $condn = '';
        if ($categoryType != "" && $sectionType != ""){
            $condn = "
            AND s.section_type  = '{$sectionType}'
            ";
        }

        $SQL = "
        SELECT c.category_id
              ,c.title AS category_title
              ,s.title AS section_title
              ,s.section_id
              ,IF(c.{$lnPfx}title != '', c.{$lnPfx}title, c.title)AS title_lang
        FROM category c
        JOIN section  s ON (c.section_id = s.section_id )
        WHERE c.category_type = '{$categoryType}'
             {$condn}
        LIMIT 0, 1
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows == 0){
            return;
        }

        $row = $db->sql_fetchrow($result);

        return $row;
    }

    /**
     *
     */    
    function getCategoryJsonBySecId() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        
        $section_id = $fn->getReqParam('section_id', '', true);

        $json  = array();
        
        if ($section_id == ''){
            $json[] = array('value' => '', 'caption' => 'Please Select');
            return json_encode($json);
        }

        $SQL = $this->getCategorySQLBySection($section_id);
        $result = $db->sql_query($SQL);  

        $json[] = array('value' => '', 'caption' => 'Please Select');
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['category_id'], "caption" => $row['title']);
        }
        
        return json_encode($json);
    }
}
