<?
class CP_Www_Modules_Gdj_Diamond_Model extends CP_Common_Modules_Gdj_Diamond_Model
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
        $polish         = $fn->getReqParam('polish');
        $symmetry       = $fn->getReqParam('symmetry');
        $carat          = $fn->getReqParam('carat');
        $price          = $fn->getReqParam('price');
        $cut            = $fn->getReqParam('cut');

        $searchVar->sqlSearchVar[] = "p.record_type = 'Diamonds'";    

        if ($product_id != '') {
            $searchVar->sqlSearchVar[] = "p.product_id = {$product_id}";

        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "p.product_id = {$tv['record_id']}";
            
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'p.product_id');
            
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
    
            if ($shape != '' ) {
                $searchVar->sqlSearchVar[] = "p.shape = '{$shape}'";
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

            if ($color != '' ) {
                $searchVar->sqlSearchVar[] = "p.color = '{$color}'";
            }

            if ($clarity != '' ) {
                $searchVar->sqlSearchVar[] = "p.clarity = '{$clarity}'";
            }

            if ($cut != '' ) {
                $searchVar->sqlSearchVar[] = "p.cut = '{$cut}'";
            }

            if ($polish != '' ) {
                $searchVar->sqlSearchVar[] = "p.polish = '{$polish}'";
            }

            if ($symmetry != '' ) {
                $searchVar->sqlSearchVar[] = "p.symmetry = '{$symmetry}'";
            }

            if ($lab != '' ) {
                $searchVar->sqlSearchVar[] = "p.lab = '{$lab}'";
            }

            if ($fluorescence != '' ) {
                $searchVar->sqlSearchVar[] = "p.fluorescence = '{$fluorescence}'";
            }

            if ($price != '' ) {
		        $arr = explode('-', $price);
		        $no_of_array = count($arr);

		        $min_price  = $arr[0];
		        if ($no_of_array == 2) {
		            $max_price  = $arr[1];
                        
                    $searchVar->sqlSearchVar[] = "p.price BETWEEN {$min_price} AND {$max_price}";
		        } else {
                    $searchVar->sqlSearchVar[] = "p.price > {$min_price}";
		        }
            }
    
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                p.title LIKE '%{$tv['keyword']}%'
                )";
            }
        }
    }
}