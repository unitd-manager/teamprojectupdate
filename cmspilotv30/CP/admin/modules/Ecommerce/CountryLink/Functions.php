<?

class CP_Admin_Modules_Ecommerce_CountryLink_Functions {

    /**
     *
     */
    function setModuleArray($modules) {
        $modObj = $modules->getModuleObj('ecommerce_countryLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'product_country'
            , 'keyField' => 'product_country_id'
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $fn = Zend_Registry::get('fn');

        //------------------------------------------------------------------------------//        
        $linkObj = $inst->getLinksArrayObj('ecommerce_countryLink', 'ecommerce_cityLink');

        $cityArr = $fn->getDdDataAsArray('directory_city');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'product_city'
            , 'linkingType' => 'grid'
            , 'historyTableKeyField' => 'product_city_id'
            , 'showLinkPanelInEdit' => 1
            , 'hasPortalEdit' => 0
            , 'hasPortalDelete' => 1
            , 'fieldlabel' => array('City', 'Stock', 'Threshold', 'Published')
            , 'showAnchorInLinkPortal' => false
            , 'gridFieldTypeArray' => array(
                array('type' => 'dropdown', 'ddArr' => $cityArr)
                , array('type' => 'textbox', 'validationType' => 'number')
                , array('type' => 'textbox', 'validationType' => 'number', 'maxLength' => 4)
                , array('type' => 'singleCheckbox')
            )
            , 'additionalFieldsArray' => array(
                'b.city_id'
                , 'b.stock'
                , 'b.stock_threshold'
                , 'b.published'
            )
            , 'fieldClassArray' => array(
                1 => 'al-right'
            )
        ));
    }

}
