<?
class CP_Common_Modules_Gdj_Diamond_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL = "
        SELECT p.*
              ,ca.title AS category_title
              ,sc.title AS sub_category_title
        FROM product p
        LEFT JOIN (category ca)    ON (p.category_id      = ca.category_id)
        LEFT JOIN (sub_category sc)ON (p.sub_category_id  = sc.sub_category_id)
        ";
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'p';

        $product_id     = $fn->getReqParam('product_id');
        $category       = $fn->getReqParam('category');
        $sub_category   = $fn->getReqParam('sub_category');
        $color          = $fn->getReqParam('color');
        $clarity        = $fn->getReqParam('clarity');
        $lab            = $fn->getReqParam('lab');
        $fluorescence   = $fn->getReqParam('fluorescence');
        $shape          = $fn->getReqParam('shape');
        $special_search = $fn->getReqParam('special_search');
        $searchVar->sqlSearchVar[] = "p.record_type = 'Diamonds'";    

        if (CP_SCOPE == 'www') {
            $searchVar->sqlSearchVar[] = "p.published = 1";
        }

        if ($product_id != '') {
            $searchVar->sqlSearchVar[] = "p.product_id = {$product_id}";

        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "p.product_id = {$tv['record_id']}";

        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'p.product_id');
            
            if($tv['linkName'] == 'product#product'){
                $searchVar->sqlSearchVar[] = "p.product_id != {$tv['linkMasterTableID']}";
            }
            
            if ($tv['category_id'] != '' ) {
                $searchVar->sqlSearchVar[] = "p.category_id = '{$tv['category_id']}'";
            }

            if ($tv['subRoom'] != '' ) {
                $searchVar->sqlSearchVar[] = "p.category_id = '{$tv['subRoom']}'";
            }

            if ($tv['sub_category_id'] != '' ) {
                $searchVar->sqlSearchVar[] = "p.sub_category_id = '{$tv['sub_category_id']}'";
            }
            
            if ($tv['subCat'] != '' ) {
                $searchVar->sqlSearchVar[] = "p.sub_category_id = '{$tv['subCat']}'";
            }
    
            if ($color != '' ) {
                $searchVar->sqlSearchVar[] = "p.color = '{$color}'";
            }

            if ($clarity != '' ) {
                $searchVar->sqlSearchVar[] = "p.clarity = '{$clarity}'";
            }

            if ($lab != '' ) {
                $searchVar->sqlSearchVar[] = "p.lab = '{$lab}'";
            }

            if ($fluorescence != '' ) {
                $searchVar->sqlSearchVar[] = "p.fluorescence = '{$fluorescence}'";
            }

            if ($shape != '' ) {
                $searchVar->sqlSearchVar[] = "p.shape = '{$shape}'";
            }

            if ($special_search != '' ) {
                if ($special_search == 'Published') {
                    $searchVar->sqlSearchVar[] = "p.published = 1";
                }
    
                if ($special_search == 'Not-Published') {
                    $searchVar->sqlSearchVar[] = "p.published = 0";
                }
    
                if ($special_search == 'Flag' ) {
                    $searchVar->sqlSearchVar[] = "p.flag = 1";
                }
            }
    
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                p.title LIKE '%{$tv['keyword']}%'
                OR p.description  LIKE '%{$tv['keyword']}%'
                )";
            }
        }
    }
}
