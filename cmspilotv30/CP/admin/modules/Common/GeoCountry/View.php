<?
class CP_Admin_Modules_Common_GeoCountry_View extends CP_Common_Modules_Common_GeoCountry_View
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $published ='';
            if ($cpCfg['m.common.geoCountry.showPublished'] == 1){
                $published = $listObj->getListPublishedImage($row['published'], $row['geo_country_id']);
            }

            $stockist = '';
            if ($cpCfg['m.common.geoCountry.hasStockist']){
                $stockist = $listObj->getListDataCell($row['stockist_name']);
            }               
            
            $show_in_nav = '';
            if ($cpCfg['m.common.geoCountry.displayShowInNavFld']){
                $show_in_nav = $listObj->getListDataCell($fn->getYesNo($row['show_in_nav']), 'center');
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['name'])}
            {$stockist}
            {$listObj->getListDataCell($row['country_code'])}
            {$show_in_nav}
            {$published}
            {$listObj->getListRowEnd($row['geo_country_id'])}
            ";
            $rowCounter++ ;
        }

        $published ='';
        if ($cpCfg['m.common.geoCountry.showPublished'] == 1){
            $published = $listObj->getListHeaderCell($ln->gd('cp.lbl.published', 'Published'), 'gc.published', 'headerCenter');
        }
        
        $stockist = '';
        if ($cpCfg['m.common.geoCountry.hasStockist']){
            $stockist = $listObj->getListHeaderCell('Stockist', 'stockist_name');
        }        

        $show_in_nav ='';
        if ($cpCfg['m.common.geoCountry.showPublished']){
            $show_in_nav = $listObj->getListHeaderCell($ln->gd('m.common.geoCountry.lbl.showInNavigation', 'Show in Navigation'), 'gc.show_in_nav', 'headerCenter');
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.common.header.geoCountry.lbl.countryName', 'Country Name'), 'gc.name')}
        {$stockist}
        {$listObj->getListHeaderCell($ln->gd('m.common.header.geoCountry.lbl.code', 'Code'), 'gc.country_code')}
        {$show_in_nav}
        {$published}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $fieldset = "
        {$formObj->getTBRow($ln->gd('m.common.geoCountry.lbl.countryName', 'Country Name'), 'name')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $published ='';
        $paypalEmail ='';
        $shippingCharge ='';
        $stockist ='';
        $showInNav ='';
        
        if ($cpCfg['m.common.geoCountry.showPublished'] == 1){
            $published = $formObj->getYesNoRRow($ln->gd('cp.lbl.published', 'Published'), 'published', $row['published'] );
        }

        if ($cpCfg['m.common.geoCountry.hasPaypalEmail']){
            $paypalEmail = $formObj->getTBRow('Paypal Email', 'paypal_email', $row['paypal_email'] );
        }

        if ($cpCfg['m.common.geoCountry.hasShippingCharge']){
            $shippingCharge = $formObj->getTBRow('Shipping Charge', 'shipping_charge', $row['shipping_charge'] );
        }
        
        if ($cpCfg['m.common.geoCountry.hasStockist']){
            $sqlStockist = $fn->getDDSql('ecommerce_stockist', array('titleFld' => 'company_name'));
            $expStockist = array('detailValue' => $row['stockist_name']);
            $stockist = $formObj->getDDRowBySQL('Stockist', 'stockist_id', $sqlStockist, $row['stockist_id'], $expStockist);
        }

        if ($cpCfg['m.common.geoCountry.displayShowInNavFld']){
            $showInNav = $formObj->getYesNoRRow($ln->gd('m.webBasic.section.lbl.showInNavigation', 'Show in Navigation'), 'show_in_nav', $row['show_in_nav']);
        }

        $fieldset1 = "
        {$formObj->getTextBoxRow($ln->gd('m.common.geoCountry.lbl.countryName', 'Country Name'), 'name', $ln->gfv($row, 'name', '0'))}
        {$formObj->getTextBoxRow($ln->gd('m.common.geoCountry.lbl.countryCode', 'Country Code'), 'country_code', $row['country_code'])}
        {$showInNav}
        {$published}
        {$stockist}
        {$paypalEmail}
        {$shippingCharge}
        ";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.common.geoCountry.lbl.countryDetails', 'Country Details'), $fieldset1)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
    }
}