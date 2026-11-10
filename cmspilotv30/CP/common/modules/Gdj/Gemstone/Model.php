<?
class CP_Common_Modules_Gdj_Gemstone_Model extends CP_Common_Lib_ModuleModelAbstract
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

        $product_id   = $fn->getReqParam('product_id');
        $company_id   = $fn->getReqParam('company_id');
        $category     = $fn->getReqParam('category');
        $sub_category = $fn->getReqParam('sub_category');
        $special_search  = $fn->getReqParam('special_search');
        $lab            = $fn->getReqParam('lab');
        $shape          = $fn->getReqParam('shape');
        $carat          = $fn->getReqParam('carat');
        $price          = $fn->getReqParam('price');
        $cut            = $fn->getReqParam('cut');
        $title          = $fn->getReqParam('title');
        $status         = $fn->getReqParam('status');

        $searchVar->sqlSearchVar[] = "p.record_type = 'Gemstones'";    

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
            
            if ($company_id != '' ) {
                $searchVar->sqlSearchVar[] = "p.company_id = '{$tv['company_id']}'";
            }

            if ($tv['subCat'] != '' ) {
                $searchVar->sqlSearchVar[] = "p.sub_category_id = '{$tv['subCat']}'";
            }
    
            if ($special_search != '' ) {
                if ($special_search == 'Published') {
                    $searchVar->sqlSearchVar[] = "p.published = 1";
                }
    
                if ($special_search == 'Not-Published') {
                    $searchVar->sqlSearchVar[] = "p.published = 0 OR p.published IS NULL OR p.published = ''";
                }
    
                if ($special_search == 'Latest' ) {
                    $searchVar->sqlSearchVar[] = "p.latest = 1";
                }
    
                if ($special_search == 'Flag' ) {
                    $searchVar->sqlSearchVar[] = "p.flag = 1";
                }
            }


            if ($shape != '' ) {
                $searchVar->sqlSearchVar[] = "p.shape = '{$shape}'";
            }

            if ($title != '' ) {
                $searchVar->sqlSearchVar[] = "p.title = '{$title}'";
            }

            if ($carat != '' ) {
		        $arr = explode('-', $carat);
		        $no_of_array = count($arr);
		        
		        $min_carat  = $arr[0];
		        if ($no_of_array == 2) {
		            $max_carat  = $arr[1];
                    
                    $searchVar->sqlSearchVar[] = "p.carat BETWEEN {$min_carat} AND {$max_carat}";
		        } else {
		            $arr = explode('+', $carat);
		            $min_carat  = $arr[0];

                    $searchVar->sqlSearchVar[] = "p.carat > {$min_carat}";
		        }
            }

            if ($cut != '' ) {
                $searchVar->sqlSearchVar[] = "p.cut = '{$cut}'";
            }

            if ($lab != '' ) {
                $searchVar->sqlSearchVar[] = "p.lab = '{$lab}'";
            }

            if ($status != '' ) {
                $searchVar->sqlSearchVar[] = "p.status = '{$status}'";
            }

            if ($price != '' ) {
		        $arr = explode('-', $price);
		        $no_of_array = count($arr);

		        $min_price  = $arr[0];
		        if ($no_of_array == 2) {
		            $max_price  = $arr[1];
                        
                    $searchVar->sqlSearchVar[] = "p.price BETWEEN {$min_price} AND {$max_price}";
		        } else {
		            $arr = explode('+', $price);
		            $min_price  = $arr[0];
                    
                    $searchVar->sqlSearchVar[] = "p.price > {$min_price}";
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
