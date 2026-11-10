<?
class CP_Common_Modules_Ecommerce_Product_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $appendSQL = '';
        if ($cpCfg['m.ecommerce.product.hasProductItem'] == 1){
            $appendSQL = "
              ,(SELECT SUM(pi.stock)
                FROM product_item pi
                WHERE pi.product_id = p.product_id) AS total_stock
            ";
        }

        $sectionAppendSQL = '';
        $sectionJoinSQL = '';
        if ($cpCfg['m.ecommerce.product.hasSection']) {
            $sectionAppendSQL = ",s.title AS section_title";
            $sectionJoinSQL = "LEFT JOIN (section s) ON (p.section_id = s.section_id)";
        }

        $SQL = "
        SELECT p.*
               {$sectionAppendSQL}
              ,c.title AS category_title
              ,c.category_type
              ,sc.title AS sub_category_title
              ,sc.sub_category_type
              {$appendSQL}
        FROM product p
        {$sectionJoinSQL}
        LEFT JOIN (category c)      ON (p.category_id      = c.category_id)
        LEFT JOIN (sub_category sc) ON (p.sub_category_id  = sc.sub_category_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'p';

        $product_id   = $fn->getReqParam('product_id');
        $company_id   = $fn->getReqParam('company_id');
        $category     = $fn->getReqParam('category');
        $sub_category = $fn->getReqParam('sub_category');
        $special_search  = $fn->getReqParam('special_search');

        $row = $fn->getRecordRowByID('category', 'category_id', $tv['subRoom']);
        $row1 = $fn->getRecordRowByID('sub_category', 'sub_category_id', $tv['subCat']);

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

            if ($cpCfg['m.ecommerce.product.hasSection'] && $tv['section_id'] != '') {
                $searchVar->sqlSearchVar[] = "p.section_id  = {$tv['section_id']}";
            }

            if ($cpCfg['m.ecommerce.product.hasSection'] && $tv['room'] != '') {
                $searchVar->sqlSearchVar[] = "p.section_id  = {$tv['room']}";
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

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                p.title LIKE '%{$tv['keyword']}%'
                OR p.description  LIKE '%{$tv['keyword']}%'
                )";
            }

            if($cpCfg['m.ecommerce.product.hasSortOrderFld']){
                $searchVar->sortOrder = "p.sort_order ASC";
            }

        }
    }

    /**
     *
     */
    function getEcommerceProductEcommerceProductLinkSQL($id) {
        $SQL = "
        SELECT rp.related_product_id
              ,p.product_id
              ,p.title
              ,c.title AS category_title
        FROM related_product rp
        JOIN product p  ON (p.product_id = rp.product_id_rel)
        JOIN category c ON (c.category_id = p.category_id)
        WHERE rp.product_id = {$id}
        ";

        return $SQL;
    }

    /**
     *
     */
    function getEcommerceProductEcommerceCountryLinkSQL($id) {
        $SQL = "
        SELECT pc.product_country_id
              ,c.country_name
              ,pc.price
        FROM product_country pc
        JOIN country c ON (c.country_id = pc.country_id)
        WHERE pc.product_id = {$id}
        ";

        return $SQL;
    }

    /**
     *
     */
    function getEcommerceProductEcommerceProductItemLinkSQL($id) {
        $formObj = Zend_Registry::get('formObj');
        $colorFld = ($formObj->mode == 'edit') ? 'pi.color_id' : 'c.title AS color';

        $SQL = "
        SELECT pi.product_item_id
              ,pi.sku_no
              ,{$colorFld}
              ,pi.size
              ,pi.stock
              ,pi.sort_order
        FROM product_item pi
        LEFT JOIN color c ON (c.color_id = pi.color_id)
        WHERE pi.product_id = {$id}
        ";

        return $SQL;
    }

    /**
     *
     */
    function getProductSQL() {
        $SQL = '
        SELECT p.product_id
              ,p.title
        FROM product p
        ORDER BY p.title
        ';
        return $SQL;
    }

    /**
     *
     */
    function getProductContentHistoryLinkSQL($id) {
        $ln = Zend_Registry::get('ln');

        $lnPfx = $ln->getFieldPrefix();

        $SQL = "
        SELECT ch.content_history_id
              ,IF(ch.{$lnPfx}title != '', ch.{$lnPfx}title, ch.title) AS title
        FROM content_history ch
        WHERE ch.record_id = {$id}
        AND ch.room_name = 'product'
        ";

        return $SQL;
    }

    /**
     *
     */
    function getEcommerceProductEcommerceProductVoucherLinkSQL($id) {
        return "
        SELECT product_voucher_id
              ,voucher_no
              ,order_id
        FROM product_voucher
        WHERE product_id = {$id}
        ";
    }
}
