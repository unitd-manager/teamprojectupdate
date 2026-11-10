<?
class CP_Admin_Modules_Pos_SubCategory_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT sc.*
              ,c.title AS category_title
        FROM sub_category sc
        LEFT JOIN (category c) ON (c.category_id = sc.category_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $searchVar = Zend_Registry::get('searchVar');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar->mainTableAlias = 'sc';
        $special_search  = $fn->getReqParam('special_search');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "sc.sub_category_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'sc.sub_category_id');            
            
            if ($tv['category_id'] != '' ) {
                $searchVar->sqlSearchVar[] = "c.category_id = '{$tv['category_id']}'";
            }

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "sc.flag = 1";
            }
    
            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(sc.flag != 1 OR sc.flag IS null)";
            }
            
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(sc.title LIKE '%{$tv['keyword']}%')";
            }
        }

        $searchVar->sortOrder = "c.title";
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
        $fa['sort_order'] = $fn->getNextSortOrder("sub_category");
        $fa['category_id']  = $fn->getReqParam('category_id');

        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
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
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'category_id');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'code', '', true);
        $fa = $fn->addToFieldsArray($fa, 'description', '', true);

        return $fa;
    }

    /**
     *
     */
    function getSubCategorySQLByCategory($category_id) {
        $category_id = $category_id ? $category_id : 0;

        $SQL = "
        SELECT DISTINCT a.sub_category_id
               , a.title
               , b.title
        FROM sub_category a
             , category b
        WHERE a.category_id = b.category_id
          AND a.category_id = {$category_id}
        ORDER BY b.title
        ";

        return $SQL;
    }

    /**
     *
     */
    function getBulkMoveToCategoryValidate() {
        $validate = Zend_Registry::get('validate');
        $validate->resetErrorArray();

        $validate->validateData('to_category_id', 'Please choose the category');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     *
     */
    function getBulkMoveToCategorySubmit() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        
        if (!$this->getBulkMoveToCategoryValidate()){
            return $validate->getErrorMessageXML();
        }
        
        $to_category_id = $fn->getPostParam('to_category_id');
        
        $SQL = "
        SELECT * 
        FROM sub_category 
        WHERE flag = 1
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $fa = array();
            $fa['category_id'] = $to_category_id;
            $fn->saveRecord($fa, 'sub_category', 'sub_category_id', $row['sub_category_id']);

            $SQL2 = "
            SELECT * 
            FROM product
            WHERE sub_category_id = '{$row['sub_category_id']}'
            ";
            $result2 = $db->sql_query($SQL2);
            
            while ($row2 = $db->sql_fetchrow($result2)) {
                $fa = array();
                $fa['category_id'] = $to_category_id;
                $fn->saveRecord($fa, 'product', 'product_id', $row2['product_id']);
            }
        }
        
        $retUrl = 'index.php?_topRm=maintenance&module=pos_subCategory' . 
                  '&_action=list&searchDone=1&special_search=Flagged';
        return $validate->getSuccessMessageXML($retUrl);
    }
}
