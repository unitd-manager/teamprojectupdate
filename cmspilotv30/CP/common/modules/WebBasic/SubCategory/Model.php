<?
class CP_Common_Modules_WebBasic_SubCategory_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSubCategorySQL($category_id = '') {
        $appendSQL  = ($category_id != '') ? " AND c.category_id = '{$category_id}'" : '';
        $appendSQL .= (CP_SCOPE == 'www')   ? " AND c.published = 1" : '';

        $titleFld = 'sc.title';

        if (CP_SCOPE == 'admin') {
            if ($cpCfg['m.webBasic.subCategory.showRefTitle']){
                $titleFld = "IF(sc.title_ref IS NOT NULL, CONCAT_WS('', sc.title, ': ',sc.title_ref), sc.title)";
            }
        }

        $sql = "
        SELECT DISTINCT sc.sub_category_id
              ,{$titleFld} AS title
        FROM sub_category sc
        JOIN category c ON (sc.category_id = c.category_id)
        {$appendSQL}
        ORDER BY sc.title
        ";

        return $sql;
    }

    /**
     *
     */
     function getGetSubcatJsonByCatId() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $category_id = $fn->getReqParam('category_id', '', true);
        $sub_category_id = $fn->getReqParam('sub_category_id', '', true);

        $json  = array();

        if ($category_id == ''){
            $json[] = array('value' => '', 'caption' => 'Please Select');
            return json_encode($json);
        }

        $SQL = $this->getSubCategorySQL($category_id);
        $result = $db->sql_query($SQL);

        $json[] = array('value' => '', 'caption' => 'Please Select');
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['sub_category_id'], "caption" => $row['title']);
        }

        return json_encode($json);
    }

    /**
     *
     */
    function getSubCatRecordByType($subCategoryType, $categoryType = '', $sectionType = ''){
        $db = Zend_Registry::get('db');

        $sectionType     = qstr($sectionType);
        $subCategoryType = qstr($subCategoryType);

        $categoryTypeSQL = ($categoryType != '') ? " AND c.category_type = '{$categoryType}'" : '';
        $sectionTypeSQL  = ($sectionType  != '') ? " AND s.section_type  = '{$sectionType}'"  : '';
        $publishedSQL    = (CP_SCOPE == 'www')   ? " AND sc.published = 1" : '';
        
        $SQL = "
        SELECT sc.sub_category_id
             , sc.title       AS sub_category_title
             , c.category_id AS category_id
             , c.title       AS category_title
             , s.section_id  AS section_id
             , s.title       AS section_title
        FROM sub_category sc
        JOIN category c  ON (sc.category_id = c.category_id)
        JOIN section  s  ON (c.section_id  = s.section_id )
        WHERE sc.sub_category_type = '{$subCategoryType}'
          {$categoryTypeSQL}
          {$sectionTypeSQL}
          {$publishedSQL}
        ORDER BY sc.sort_order
        LIMIT 0, 1
        ";

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows == 0) return;

        $row = $db->sql_fetchrow($result);

        return $row;
    }

    function getFirstSubCat($category_id){
        $db = Zend_Registry::get('db');

        $SQL = "
        SELECT sc.sub_category_id
              ,sc.title AS sub_category_title
        FROM sub_category sc
        WHERE sc.category_id = '{$category_id}'
        ORDER BY sc.sort_order
        LIMIT 0, 1
        ";

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows == 0) return null;

        $row = $db->sql_fetchrow($result);

        return $row;

    }
}
