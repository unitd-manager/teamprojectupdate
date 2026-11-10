<?php

class CP_Admin_Modules_Trading_Catalog_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        return getCPModelObj('trading_product')->getSQL();
    }

    function setSearchVar($linkRecType = '') {
        return getCPModelObj('trading_product')->setSearchVar($linkRecType);
    }

    function getTradingProductTradingInventoryLinkSQL($id) {
        return getCPModelObj('trading_product')->getTradingProductTradingInventoryLinkSQL($id);
    }


    function getTradingCatalogTradingPricingTypeLinkSQL($id) {
        $SQL = "
        SELECT DISTINCT
               ppt.product_pricing_type_id
              ,pt.pricing_type
              ,ppt.sell_unit_price_base
              ,'' AS empty
        FROM product_pricing_type ppt
        JOIN pricing_type pt ON pt.pricing_type_id = ppt.pricing_type_id
        WHERE ppt.product_id = {$id}
          AND pt.show_in_catalog = 1
        ORDER BY pt.sort_order
        ";

        return $SQL;
    }
}
