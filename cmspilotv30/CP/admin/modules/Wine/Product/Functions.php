<?

class CP_Admin_Modules_Wine_Product_Functions {

    /**
     *
     */
    function setModuleArray($modules) {
        $modObj = $modules->getModuleObj('wine_product');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
            , 'depModulesForJSS' => array('ecommerce_countryLink')
            , 'actBtnsList' => array('new',
                                    'import', 'importStock', 'importThreshold', 'importSpecialSearch',
                                    'export', 'exportFaultyRecord', 'deleteList')
        ));
    }

    function setActionsArray($actArray) {
        $tv = Zend_Registry::get('tv');
        $pager = Zend_Registry::get('pager');
        $cpUrl = Zend_Registry::get('cpUrl');
        $ln = Zend_Registry::get('ln');

        $searchQueryString = $pager->searchQueryString;
        $searchQueryString = preg_replace('/&_action=[a-zA-Z0-9\. _,]+&?/', "&", $searchQueryString);
        if (substr($searchQueryString, -1) == "&") {
            $searchQueryString = substr($searchQueryString, 0, strlen($searchQueryString) - 1);
        }

        $searchQueryString .= $cpUrl->getQnMarkForUrl($searchQueryString);

        //====================== Import Stock ================================//
        $actObj = $actArray->getActionObj('importStock');
        $actArray->registerAction($actObj, array(
              'title' => "{$ln->gd('cp.actionButton.lbl.importStockData', 'Import Stock (JDE)')}"
            , 'url' => "javascript:cpm.wine.product.importFromJDE()"
        ));

        //====================== Import Threshold ================================//
        $actObj = $actArray->getActionObj('importThreshold');
        $actArray->registerAction($actObj, array(
              'title' => "{$ln->gd('cp.actionButton.lbl.importThreshold', 'Import Threshold')}"
            , 'url' => "javascript:Actions.importData('{$tv['module']}', 'threshold')"
        ));

        //====================== Import Special Search ================================//
        $actObj = $actArray->getActionObj('importSpecialSearch');
        $actArray->registerAction($actObj, array(
              'title' => "{$ln->gd('cp.actionButton.lbl.importSpecialSearch', 'Import Special Search')}"
            , 'url' => "javascript:Actions.importData('{$tv['module']}', 'specialSearch')"
        ));

        //====================== Export Faulty Record ================================//
        $actObj = $actArray->getActionObj('exportFaultyRecord');
        $actArray->registerAction($actObj, array(
              'title' => "{$ln->gd('cp.actionButton.lbl.exportFaultyRecord', 'Export Faulty Record')}"
            , 'url' => "{$searchQueryString}&_spAction=exportData&showHTML=0&export=1&hasDB=1&exportType=fault"
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('wine_product', 'picture', 'image');

//        $mediaArr->registerMedia($mediaObj, array(
//             'count' => 'single'
//            ,'isMediaLangSpecific' => false
//        ));

        $mediaArr->registerMedia($mediaObj, array(
             'count' => 'single'
            ,'isMediaLangSpecific' => false
            ,'maxWidthT' => '206'
            ,'maxHeightT' => '314'
            ,'maxWidthN'  => '363'
            ,'maxHeightN' => '553'
            ,'maxWidthL'  => '788'
            ,'maxHeightL' => '1200'
            ,'hasCrop' => true
            ,'cropInfo' => $mediaArr->getCropInfoObj(182,182)
            ,'isMediaLangSpecific' => false
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('wine_product', 'relatedPicture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
            'isMediaLangSpecific' => false
        ));
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('wine_product', 'relatedOffer', 'image');

        $mediaArr->registerMedia($mediaObj, array(
            'isMediaLangSpecific' => false
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');

        $lnPfx = $ln->getFieldPrefix();
        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('wine_product', 'ecommerce_productLink', array(
            'historyTableName' => 'related_product'
            , 'keyFieldForHistory' => 'related_product_id'
            , 'keyField' => 'product_id'
            , 'keyFieldForLinking' => 'product_id_rel'
            , 'className' => 'Product'
            , 'showAnchorInLinkPortal' => 0
            , 'anchorFieldsArr' => array('title' => $inst->getLinkAnchorObj('title', 'product_id'))
            , 'fieldlabel' => array(
                'Product Name'
            )
                ));
        $inst->registerLinksArray($linkObj);

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('wine_product', 'ecommerce_countryLink');

        $countryArr = $fn->getDdDataAsArray('common_country');

        $countryFld = ($formObj->mode == 'detail') ? 'c.title' : 'b.country_id';

        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'product_country'
            , 'linkingType' => 'grid'
            , 'historyTableKeyField' => 'product_country_id'
            , 'showLinkPanelInEdit' => 1
            , 'hasPortalEdit' => 0
            , 'hasPortalDelete' => 1
            , 'addButtonText' => $ln->gd('m.wine.product.lbl.addCountry', "Add Country")
            , 'fieldlabel' => array($ln->gd('m.wine.product.lbl.country', 'Country')
                                            , $ln->gd('m.wine.product.lbl.stock', 'Stock')
                                            , $ln->gd('m.wine.product.lbl.price', 'Price')
                                            , ''
                                            , $ln->gd('m.wine.product.lbl.specialPrice', 'Special Price')
                                            , ''
                                            , $ln->gd('cp.lbl.published', 'Published')
                                    )
            , 'showAnchorInLinkPortal' => false
            , 'gridFieldTypeArray' => array(
                  array('type' => 'dropdown', 'ddArr' => $countryArr)
                , array('type' => 'textbox', 'editable' => 0)
                , array('type' => 'textbox', 'validationType' => 'number')
                , array('type' => 'textbox', 'editable' => 0)
                , array('type' => 'textbox', 'validationType' => 'number')
                , array('type' => 'textbox', 'editable' => 0)
                , array('type' => 'singleCheckbox')
            )
            , 'fieldClassArray' => array(
                  0 => 'w25p'
                , 1 => 'w50'
                , 2 => 'w50'
                , 3 => 'w50'
                , 4 => 'w50'
                , 5 => 'w50'
                , 7 => 'w50'
            )
            , 'hasChildren' => true
            , 'childLinkKey' => 'ecommerce_countryLink#ecommerce_cityLink'
            , 'childFieldLabel' => array($ln->gd('m.wine.product.lbl.city', 'City')
                                        , $ln->gd('m.wine.product.lbl.stock', 'Stock')
                                        , $ln->gd('m.wine.product.lbl.stockThreshold', 'Threshold')
                                        , $ln->gd('cp.lbl.published', 'Published'))
            , 'childFieldClassArray' => array(
                  0 => 'w25p'
                , 1 => 'w50'
                , 2 => 'w50'
            )
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('wine_product', 'ecommerce_ratingLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'rating'
            , 'linkingType' => 'grid'
            , 'historyTableKeyField' => 'rating_id'
            , 'showLinkPanelInEdit' => 1
            , 'hasPortalEdit' => 0
            , 'hasPortalDelete' => 1
            , 'addButtonText' => $ln->gd('cp.lbl.add', "Add")
            , 'fieldlabel' => array($ln->gd('m.wine.product.lbl.source', 'Source')
                                    , $ln->gd('m.wine.product.lbl.rating', 'Rating'))
            , 'gridFieldTypeArray' => array(
                array('type' => 'textbox')
                , array('type' => 'textbox')
            )
            , 'additionalFieldsArray' => array(
                'b.source'
                , 'b.rating'
            )
            , 'showAnchorInLinkPortal' => false
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('wine_product', 'wine_tastingNotesLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'tasting_notes'
            , 'linkingType' => 'grid'
            , 'historyTableKeyField' => 'tasting_notes_id'
            , 'showLinkPanelInEdit' => 1
            , 'hasPortalEdit' => 0
            , 'hasPortalDelete' => 1
            , 'addButtonText' => $ln->gd('cp.lbl.add', "Add")
            , 'fieldlabel' => array($ln->gd('m.wine.product.lbl.tastingNotes', 'Tasting Notes')
                                    , $ln->gd('cp.lbl.published', 'Published'))
            , 'gridFieldTypeArray' => array(
                array('type' => 'textarea')
              , array('type' => 'singleRadio')
            )
            , 'additionalFieldsArray' => array(
                 "b.{$lnPfx}notes"
                ,"b.published"
            )
            , 'showAnchorInLinkPortal' => false
        ));
    }

}