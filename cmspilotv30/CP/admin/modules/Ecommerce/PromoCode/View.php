<?
class CP_Admin_Modules_Ecommerce_PromoCode_View extends CP_Common_Modules_Ecommerce_PromoCode_View
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

        $country = '';
        if ($cpCfg['m.ecommerce.promoCode.showCountry'] == 1) {
            $country = $listObj->getListDataCell($row['country_name']);
        }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['code'])}
            {$listObj->getListDataCell($row['discount_type'])}
            {$listObj->getListDataCell($row['discount_amount'])}
            {$country}
            {$listObj->getListDataCell($fn->getYesNo($row['valid_vip']))}
            {$listObj->getListDateCell($row['start_date'])}
            {$listObj->getListDateCell($row['expiry_date'])}
            {$listObj->getListDateCell($row['creation_date'])}
            {$listObj->getListRowEnd($row['promo_code_id'])}
            ";
            $rowCounter++ ;
        }

        $country = '';
        if ($cpCfg['m.ecommerce.promoCode.showCountry'] == 1) {
            $country = $listObj->getListHeaderCell('Country', 'c.title');
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('cp.header.lbl.title', 'Title'), 'pc.title')}
        {$listObj->getListHeaderCell($ln->gd('m.ecommerce.header.promoCode.lbl.voucherCode', 'Voucher Code'), 'pc.code')}
        {$listObj->getListHeaderCell($ln->gd('m.ecommerce.header.promoCode.lbl.discountType', 'Discount Type'), 'pc.discount_type')}
        {$listObj->getListHeaderCell($ln->gd('m.ecommerce.header.promoCode.lbl.discountAmount', 'Discount Amount / Percentage'), 'pc.discount_amount')}
        {$country}
        {$listObj->getListHeaderCell($ln->gd('m.ecommerce.header.promoCode.lbl.validForVip', 'Valid For Vip'), 'pc.valid_vip')}
        {$listObj->getListHeaderCell($ln->gd('cp.header.lbl.startDate', 'Start Date'), 'pc.start_date')}
        {$listObj->getListHeaderCell($ln->gd('cp.header.lbl.expiryDate', 'Expiry Date'), 'pc.expiry_date')}
        {$listObj->getListHeaderCell($ln->gd('cp.header.lbl.creationDate', 'Creation Date'), 'pc.creation_date')}
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
        {$formObj->getTextBoxRow($ln->gd('cp.lbl.title', 'Title'), 'title')}
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $expDiscount = array('sqlType' => 'OneField');

        $sqlCountry = $fn->getDDSQL('common_country');
        $expCountry = array('detailValue' => $row['country_name']);

        $country = '';
        if ($cpCfg['m.ecommerce.promoCode.showCountry'] == 1) {
            $country = $formObj->getDDRowBySQL('Country', 'country_id', $sqlCountry, $row['country_id'], $expCountry);
        }

        $sqlCity ='';
        if ($row['country_id'] != ''){
            $sqlCity = $fn->getDDSQL('directory_city', array('condn' => "country_id = {$row['country_id']}"));
        }        

        $city = '';
        if ($cpCfg['m.ecommerce.promoCode.showCity'] == 1) {
            $expCity = array('detailValue' => $row['city_name']);
            $city = $formObj->getDDRowBySQL('City', 'city_id', $sqlCity, $row['city_id'], $expCity);
        }

        $fieldset1 = "
        {$formObj->getTextBoxRow($ln->gd('cp.lbl.title', 'Title'), 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getTextBoxRow($ln->gd('m.ecommerce.promoCode.lbl.voucherCode', 'Voucher Code'), 'code', $row['code'])}
        {$formObj->getDDRowByArr($ln->gd('m.ecommerce.promoCode.lbl.discountType', 'Discount Type'), 'discount_type', $cpCfg['m.ecommerce.promoCode.amountTypeArr'], $row['discount_type'])}
        {$formObj->getTextBoxRow($ln->gd('m.ecommerce.promoCode.lbl.discountAmount', 'Discount Amount / Percentage'), 'discount_amount', $row['discount_amount'])}
        {$country}
        {$city}
        {$formObj->getDateRow($ln->gd('cp.lbl.startDate', 'Start Date'),'start_date', $row['start_date'])}
        {$formObj->getDateRow($ln->gd('cp.lbl.expiryDate', 'Expiry Date'),'expiry_date', $row['expiry_date'])}
        {$formObj->getYesNoRRow($ln->gd('m.ecommerce.promoCode.lbl.validForVip', 'Valid For VIP'), 'valid_vip', $row['valid_vip'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.ecommerce.promoCode.lbl.promoCodeDetails', 'Promo Code Details'), $fieldset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {

        $text = '';

        return $text;
    }
    
    /**
     *
     */
    function getRightPanel($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $ln = Zend_Registry::get('ln');

        $links = '';

        if ($cpCfg['m.ecommerce.promoCode.hasPromoCodeCityLink']) {
            $links = $displayLinkData->getLinkPortalMain('ecommerce_promoCode', 'directory_cityLink', ($ln->gd('m.ecommerce.promoCode.lbl.cityLink', 'City Link')), $row);
        }


        $text = "
        {$links}
        ";

        return $text;
    }    
}