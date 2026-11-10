<?
class CP_Admin_Modules_Directory_Business_View extends CP_Common_Modules_Directory_Business_View
{
    var $jssKeys = array('jqUITimePickerAddon-0.9.3');

    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $rowCounter = 0;
        $rows = '';

        foreach ($dataArray as $row){
            $latlng_verified = $fn->getYesNo($row['map_latlng_verified']);
            $url = 'index.php?_topRm=setup&module=directory_building&_action=detail&record_id=' . $row['building_id'];
            $latlng_verified = "
            <a href='{$url}' target='_blank'>{$latlng_verified}</a>
            ";

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['business_name'], '', '', $row)}
            {$listObj->getListDataCell($row['business_name_local_lang'])}
            {$listObj->getListDataCell($row['category_title'])}
            {$listObj->getListDataCell($row['sub_category_title'])}
            {$listObj->getListDataCell($row['street_no'])}
            {$listObj->getListDataCell($row['street_name'])}
            {$listObj->getListDataCell($row['area_name'])}
            {$listObj->getListDataCell($row['country_name'])}
            {$listObj->getListDataCell($row['country_code_source_id'])}

            {$listObj->getListDataCell($row['has_picture'], 'center')}
            {$listObj->getListDataCell($row['has_logo'], 'center')}
            {$listObj->getListDataCell($row['description_list'])}
            {$listObj->getListDataCell($row['website'])}
            {$listObj->getListDataCell($row['social_medias'])}
            {$listObj->getListDataCell($row['dgd_modified_by'])}
            {$listObj->getListDateCell($row['creation_date'])}
            {$listObj->getListDateCell($row['modification_date'])}

            {$listObj->getListDataCell($row['no_of_followers'], 'center')}
            {$listObj->getListDataCell($row['no_of_reviews'], 'center')}
            {$listObj->getListDataCell($row['no_of_promotions'], 'center')}
            {$listObj->getListDataCell($row['no_of_loyalty_cards'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['business_id'])}
            {$listObj->getListYesNo($row['dgd_verified'], 'center')}
            {$listObj->getListYesNo($row['social_media_verified'], 'center')}
            {$listObj->getListDataCell($latlng_verified, 'center')}
            {$listObj->getListRowEnd($row['business_id'])}
            ";
            $rowCounter++;
        }

        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.directory.business.lbl.businessName'), 'b.business_name', 'w200')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.business.lbl.businessNameLocalLang'), 'b.business_name_local_lang', 'w200')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.business.lbl.category'), 'category_title', 'w150')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.business.lbl.subCategory'), 'sub_category_title', 'w150')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.business.lbl.st.No'), 'street_no', 'w40')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.business.lbl.street'), 'street_name', 'w100')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.business.lbl.area'), 'area_name', 'w100')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.business.lbl.country'), 'country_name', 'w100')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.business.lbl.sourceId'), 'b.source_id', 'w75')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.business.lbl.picture'), '', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.business.lbl.logo'), '', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.business.lbl.description'))}
        {$listObj->getListHeaderCell($ln->gd('m.directory.business.lbl.website'))}
        {$listObj->getListHeaderCell($ln->gd('m.directory.business.lbl.socialMedia'), '', 'w100')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.business.lbl.orName'), '', 'w100')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.business.lbl.creation'), 'b.creation_date', 'w75')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.business.lbl.modification'), 'b.modification_date', 'w75')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.business.lbl.#followers'), '', 'w75 txtCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.business.lbl.#reviews'), '', 'w75 txtCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.business.lbl.#promos.'), '', 'w75 txtCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.business.lbl.#loyaltyCards'), '', 'w100 txtCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.business.lbl.published'), 'b.published', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.business.lbl.dgdVerified'), '', 'w75 headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.business.lbl.socialMediaVerified'), '', 'w75 headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.business.lbl.latLngVerified'), '', 'w100 headerCenter')}
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
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.businessName'), 'business_name')}
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
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');

        $expCategory = array('detailValue' => $row['category_title']);
        $sqlCategory = getCPModelObj('webBasic_category')->getCategorySQLByType('Business');

        $expSubCategoryCond = array(
            'condn' => "category_id = '{$row['category_id']}'"
        );

        $expSubCategory = array('detailValue' => $row['sub_category_title']);
        $sqlSubCategory = '';
        if ($row['category_id'] != '') {
            $sqlSubCategory = $fn->getDDSql('webBasic_subCategory', $expSubCategoryCond);
        }

        $expSubCategory2 = array('detailValue' => $row['sub_category_title2']);
        $expSubCategory3 = array('detailValue' => $row['sub_category_title3']);

        $country_id = $fn->getSessionParam('cp_country_id');
        $exp = array(
            'condn' => $country_id != '' ? "country_id = {$country_id}" : ''
        );
        $sqlBusinessGroup = $fn->getDDSql('directory_businessGroup', $exp);
        $expBusinessGroup = array('detailValue' => $row['business_group_name']);

        $expNoEdit = array('isEditable' => 0);
        $expWebsite = array('urliseContent' => true);

        $expBusinessPrev = array('displayText' => $row['business_name_previous']);
        $businessPrevText = '';
        $businessPrevText = $fn->getRecordDetailLink('directory_business', 'record_id',
                                                     $row['business_id_previous'], $expBusinessPrev);

        $expTelPrefix = array('fldPrefix' => $row['idd_code']);

        $urlGoogle = "https://www.google.com/search?q="
                   . urlencode($row['business_name'] . ' ' . $row['city_name']);
        $findInGoogle = "
        <a href='{$urlGoogle}' target='_blank' tabindex='-1' title='Find this in google'>
            <img src='images/google.png'>
        </a>
        ";

        $urlOpenRice = "http://www.openrice.com/english/restaurant/sr1.htm?tc=top2&inputstrrest="
                   . urlencode($row['business_name']);
        $findInOpenRice = "
        <a href='{$urlOpenRice}' target='_blank' tabindex='-1' title='Find this in Open Rice'>
            <img src='images/openrice.png'>
        </a>
        ";

        $urlYPHK = "http://www.yp.com.hk/s-".urlencode($row['business_name']) . "/p1/en/";
        $findInYPHK = "
        <a href='{$urlYPHK}' target='_blank' tabindex='-1' title='Find this in YPHK'>
            <img src='images/yphk.png'>
        </a>
        ";
        $expBusName = array('notesRight' => $findInGoogle . $findInOpenRice . $findInYPHK);

        $latlng_verified = $fn->getYesNo($row['map_latlng_verified']);
        $url = 'index.php?_topRm=setup&module=directory_building&_action=detail&record_id=' . $row['building_id'];
        $latlng_verified = "
        <a href='{$url}' target='_blank'>{$latlng_verified}</a>
        ";

        $fieldset1 = "
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.businessName'), 'business_name', $row['business_name'], $expBusName)}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.businessNameLocalLang'), 'business_name_local_lang', $row['business_name_local_lang'])}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.sourceId'), 'source_id', $row['country_code_source_id'], $expNoEdit)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.business.lbl.category'), 'category_id', $sqlCategory,
                                 $row['category_id'], $expCategory)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.business.lbl.subCategory'), 'sub_category_id', $sqlSubCategory,
                                 $row['sub_category_id'], $expSubCategory)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.business.lbl.subCategory2'), 'sub_category2_id', $sqlSubCategory,
                                 $row['sub_category2_id'], $expSubCategory2)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.business.lbl.subCategory3'), 'sub_category3_id', $sqlSubCategory,
                                 $row['sub_category3_id'], $expSubCategory3)}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.protectCategory'), 'protect_category', $row['protect_category'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.protectSubCategory'), 'protect_sub_category', $row['protect_sub_category'])}
        {$formObj->getDDRowByVL($ln->gd('m.directory.business.lbl.businessType'), 'business_type', 'businessType', $row['business_type'])}
        {$formObj->getDDRowByVL($ln->gd('m.directory.business.lbl.businessStatus'), 'status', 'businessStatus', $row['status'])}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.closedDate'), 'closed_date', $row['closed_date'], $expNoEdit)}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.businessNamePrevious'), 'business_name_previous',
                            $businessPrevText, $expNoEdit)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.business.lbl.multiple'), 'business_group_id', $sqlBusinessGroup,
                                 $row['business_group_id'], $expBusinessGroup)}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.prohibitBusinessGroupUpdate'), 'prohibit_business_group_update',
                                $row['prohibit_business_group_update'])}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.brands'), 'brands', $row['brands'])}
        ";

        $fieldset2 = "
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.phone(Primary)'), 'phone', $row['phone'], $expTelPrefix)}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.phone(Other)'), 'phone_other', $row['phone_other'], $expTelPrefix)}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.mobile'), 'mobile', $row['mobile'], $expTelPrefix)}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.fax'), 'fax', $row['fax'], $expTelPrefix)}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.contactName'), 'contact_name', $row['contact_name'])}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.email'), 'email', $row['email'])}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.website'), 'website', $row['website'], $expWebsite)}
        ";

        $expAddId = array('displayText' => $row['country_code_address_id']);
        $addressIdText = $fn->getRecordDetailLink('directory_address', 'record_id',
                                                  $row['address_id'], $expAddId);

        $expAddressId = array('isEditable' => 0);
        if ($formObj->mode == 'edit') {
            $expAddressUrl = array('surroundHyperLinkTag' => true, 'width' => 1000);
            $addressLink = $fn->getOpenLinkUrl('directory_business', 'directory_addressLink',
                                               'fld_address_id_hidden', $expAddressUrl);
            $expAddressId['extraHtml'] = $addressLink;
        }

        $expBuilding = array('displayText' => $row['address_building_name']);
        $buildingText = $fn->getRecordDetailLink('directory_building', 'record_id', $row['building_id'], $expBuilding);

        $fieldset3 = "
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.addressId'), 'address_id', $addressIdText, $expAddressId)}
        {$formObj->getHiddenFldObj('address_id_hidden', $row['address_id'])}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.country'), 'country_name', $row['country_name'], $expNoEdit)}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.state'), 'state_name', $row['state_name'], $expNoEdit)}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.city'), 'city_name', $row['city_name'], $expNoEdit)}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.borough'), 'borough_name', $row['borough_name'], $expNoEdit)}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.area'), 'area_name', $row['area_name'], $expNoEdit)}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.street'), 'street_name', $row['street_name'], $expNoEdit)}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.shopCenter'), 'shop_center_name', $row['shop_center_name'], $expNoEdit)}

        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.streetNo(Form)'), 'address_street_no_from', $row['address_street_no_from'], $expNoEdit)}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.streetNo(To)'), 'address_street_no_to', $row['address_street_no_to'], $expNoEdit)}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.buildingName'), 'address_building_name',
                            $buildingText, $expNoEdit)}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.block'), 'address_block', $row['address_block'], $expNoEdit)}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.floor(Form)'), 'address_floor_from', $row['address_floor_from'], $expNoEdit)}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.floor(To)'), 'address_floor_to', $row['address_floor_to'], $expNoEdit)}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.unit(Form)'), 'address_unit_from', $row['address_unit_from'], $expNoEdit)}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.unit(To)'), 'address_unit_to', $row['address_unit_to'], $expNoEdit)}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.zipCode'), 'address_po_code', $row['address_po_code'], $expNoEdit)}

        <h3><b>Geocode</b></h3>
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.latitude'), 'latitude', $row['latitude'], $expNoEdit)}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.longitude'), 'longitude', $row['longitude'], $expNoEdit)}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.northing'), 'northing', $row['northing'], $expNoEdit)}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.easting'), 'easting', $row['easting'], $expNoEdit)}
        ";

        $urlWiki = "http://en.wikipedia.org/w/index.php?search="
                   . urlencode($row['business_name'] . ' ' . $row['city_name']);
        $findInWiki = "
        <a href='{$urlWiki}' target='_blank' title='Find this in Wiki'>
            <img src='images/wiki.png'>
        </a>
        " ;
        $expDesc = array('notesRight' => $findInWiki);
        $fieldset6 = "
        {$formObj->getTARow($ln->gd('m.directory.business.lbl.description'), 'description', $row['description'], $expDesc)}
        {$formObj->getTARow($ln->gd('m.directory.business.lbl.description-Chinese'), 'description_chi', $row['description_chi'])}
        ";

        $fieldset8 = "
        {$formObj->getTARow($ln->gd('m.directory.business.lbl.tags'), 'tags', $ln->gfv($row, 'tags', '0'))}
        ";

        $fieldset12 = "
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.transportName'), 'transport_name', $row['transport_name'])}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.transportCarrier'), 'transport_carrier', $row['transport_carrier'])}
        ";

        $expSportsBroadcast = array();
        if (!$row['feature_tv']) {
            $expSportsBroadcast = array('rowCls' => 'hide');
        }
        $expCorkageAmount = array();
        if (!$row['feature_byob']) {
            $expCorkageAmount = array('rowCls' => 'hide');
        }
        $expParkingType = array();
        if (!$row['feature_parking']) {
            $expParkingType = array('rowCls' => 'hide');
        }
        $expPrivateRmCap = array();
        if (!$row['feature_private_room']) {
            $expPrivateRmCap = array('rowCls' => 'hide');
        }
        $expDressType = array();
        if (!$row['feature_dress_code']) {
            $expDressType = array('rowCls' => 'hide');
        }

        $fieldset13 = "
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.atm'), 'feature_atm', $row['feature_atm'] )}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.qrCode'), 'feature_qr_code', $row['feature_qr_code'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.affiliateScheme'), 'feature_affiliate_scheme',
                                $row['feature_affiliate_scheme'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.in-HouseLoyaltyScheme/Card'), 'feature_inhouse_loyalty_scheme',
                                $row['feature_inhouse_loyalty_scheme'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.wheelchairAccess'), 'feature_wheelchair_access',
                                $row['feature_wheelchair_access'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.freeWiFi'), 'feature_wifi', $row['feature_wifi'])}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.nearest(MTR)StationExit'), 'feature_nearest_mtr_exit',
	                            $row['feature_nearest_mtr_exit'])}

        <h3><b>Restaurants</b></h3>
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.breakFast'), 'feature_breakfast', $row['feature_breakfast'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.tea'), 'feature_tea', $row['feature_tea'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.brunch'), 'feature_brunch', $row['feature_brunch'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.lunch'), 'feature_lunch', $row['feature_lunch'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.dinner'), 'feature_dinner', $row['feature_dinner'])}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.averageMainCourse(HK)$'), 'feature_avg_main_course_price',
                            $row['feature_avg_main_course_price'])}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.signatureDish(es)'), 'feature_signature_dishes',
                            $row['feature_signature_dishes'])}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.noOfSeats'), 'feature_no_of_seats', $row['feature_no_of_seats'])}

        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.reservation'), 'feature_reservation', $row['feature_reservation'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.parking'), 'feature_parking', $row['feature_parking'])}
        {$formObj->getDDRowByVL($ln->gd('m.directory.business.lbl.parkingType'), 'feature_parking_type', 'parkingType',
                                $row['feature_parking_type'], $expParkingType)}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.OutdoorSeating'), 'feature_outdoor_seating',
                                $row['feature_outdoor_seating'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.groupFriendly'), 'feature_group_friendly',
                                $row['feature_group_friendly'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.privateRoom'), 'feature_private_room', $row['feature_private_room'])}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.privateRm.Capacity'),'feature_private_room_capacity',
                            $row['feature_private_room_capacity'], $expPrivateRmCap)}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.childrenFriendly'),'feature_children_friendly',
                                $row['feature_children_friendly'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.petsFriendly'), 'feature_pets_friendly', $row['feature_pets_friendly'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.delivery'), 'feature_delivery', $row['feature_delivery'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.takeaway'), 'feature_takeaway', $row['feature_takeaway'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.catering'), 'feature_catering', $row['feature_catering'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.bar'), 'feature_bar', $row['feature_bar'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.bYOB'), 'feature_byob', $row['feature_byob'])}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.corkage'), 'feature_corkage_amount',
                            $row['feature_corkage_amount'], $expCorkageAmount)}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.dressCode'), 'feature_dress_code', $row['feature_dress_code'])}
        {$formObj->getDDRowByVL($ln->gd('m.directory.business.lbl.dressType'), 'feature_dress_type', 'dressType',
                                $row['feature_dress_type'], $expDressType)}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.exhibitionKitchen'), 'feature_exhibition_kitchen',
                                $row['feature_exhibition_kitchen'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.firePlace'), 'feature_fire_place', $row['feature_fire_place'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.view'), 'feature_view', $row['feature_view'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.seaView'), 'feature_seaview', $row['feature_seaview'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.ambiance'), 'feature_ambiance', $row['feature_ambiance'])}

        <h3><b>{$ln->gd('m.directory.business.lbl.restaurants&Bars/Pubs/ClubsOnly')}</b></h3>
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.liveMusic'), 'feature_live_music', $row['feature_live_music'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.tv'), 'feature_tv', $row['feature_tv'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.sportsBroadcasts'), 'feature_sports_broadcast',
                                $row['feature_sports_broadcast'], $expSportsBroadcast)}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.entryConcession'), 'feature_entry_concession',
                                $row['feature_entry_concession'])}
        ";

        $fieldset14 = "
        {$formObj->getTARow($ln->gd('m.directory.business.lbl.comments'), 'comment', $row['comment'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.dgdVerified'), 'dgd_verified', $row['dgd_verified'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.socialMediaVerified'), 'social_media_verified', $row['social_media_verified'])}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.latLngVerified'), 'latlng_verified', $latlng_verified, $formObj->expNoEdit)}
        ";

        $creation     = $row['dgd_created_by'] . ' on ' . $row['dgd_creation_date'];
        $modification = $row['dgd_modified_by'] . ' on ' . $row['dgd_modification_date'];
        $fieldset15 = "
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.name'), 'dgd_notify_name', $row['dgd_notify_name'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.address'), 'dgd_notify_address', $row['dgd_notify_address'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.category'), 'dgd_notify_category', $row['dgd_notify_category'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.photo'), 'dgd_notify_photo', $row['dgd_notify_photo'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.phone'), 'dgd_notify_phone', $row['dgd_notify_phone'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.openingHours'), 'dgd_notify_opening_hour', $row['dgd_notify_opening_hour'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.tags'), 'dgd_notify_tags', $row['dgd_notify_tags'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.paymentMethod'), 'dgd_notify_payment_method', $row['dgd_notify_payment_method'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.socialMedia'), 'dgd_notify_social_media', $row['dgd_notify_social_media'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.otherInfo'), 'dgd_notify_other_info', $row['dgd_notify_other_info'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.course'), 'dgd_notify_course', $row['dgd_notify_course'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.signatureDish'), 'dgd_notify_signature_dish', $row['dgd_notify_signature_dish'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.feature'), 'dgd_notify_feature', $row['dgd_notify_feature'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.ambiance'), 'dgd_notify_ambiance', $row['dgd_notify_ambiance'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.group'), 'dgd_notify_group', $row['dgd_notify_group'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.business.lbl.delivery'), 'dgd_notify_delivery', $row['dgd_notify_delivery'])}
        ";
        $fieldset16 = "
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.creation'), 'dgd_creation', $creation, $formObj->expNoEdit)}
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.modified'), 'dgd_modification', $modification, $formObj->expNoEdit)}
        ";

        $expMeta = array(
            'lblTitle' => 'Tab Title'
           ,'lblDescription' => 'Description'
           ,'lblKeywords' => 'Keywords'
        );
        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.business.lbl.mainDetails'), $fieldset1)}
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.business.lbl.contactDetails'), $fieldset2)}
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.business.lbl.addressDetails'), $fieldset3)}
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.business.lbl.description'), $fieldset6)}
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.business.lbl.tags&SearchKeywords'), $fieldset8)}
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.business.lbl.transport'), $fieldset12)}
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.business.lbl.features'), $fieldset13)}
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.business.lbl.other'), $fieldset14)}
        {$formObj->getMetaData($row, $expMeta)}
        {$formObj->getCreationModificationText($row)}
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.business.lbl.dGDupdates'), $fieldset15)}
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.business.lbl.dGDnotifyFR'), $fieldset16)}
        ";

        return $text;
    }

    /**
     *
     */
	function getRightPanel($row) {
        $media = Zend_Registry::get('media');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $dld = Zend_Registry::get('displayLinkData');
        $comment = getCPPluginObj('common_comment');

        $record_id = $fn->getIssetParam($row, 'business_id');

        $this->model->createBusinessHours($row);

        $bussHours = $dld->getLinkPortalMain('directory_business', 'directory_businessHoursLink',
                $ln->gd('m.directory.business.lbl.OpeningTimes'), $row);
        $text = "
        {$media->getRightPanelMediaDisplay($ln->gd('m.directory.business.link.picture'), 'directory_business', 'picture', $row)}
        {$media->getRightPanelMediaDisplay($ln->gd('m.directory.business.link.relatedPicture'), 'directory_business', 'relatedPicture', $row)}
        {$media->getRightPanelMediaDisplay($ln->gd('m.directory.business.link.logo'), 'directory_business', 'logo', $row)}
        {$media->getRightPanelMediaDisplay($ln->gd('m.directory.business.link.attachment'), 'directory_business', 'attachment', $row)}
        {$media->getRightPanelMediaDisplay($ln->gd('m.directory.business.link.menu'), 'directory_business', 'menu', $row)}
        {$bussHours}
        {$dld->getLinkPortalMain('directory_business', 'directory_promotionLink', $ln->gd('m.directory.business.link.customPromotions'), $row)}
        {$dld->getLinkPortalMain('directory_business', 'directory_promotion3PartyLink',$ln->gd('m.directory.business.link.3rdPartyPromotions'), $row)}
        {$dld->getLinkPortalMain('directory_business', 'directory_externalSourceLink', $ln->gd('m.directory.business.link.externalReview'), $row)}
        {$dld->getLinkPortalMain('directory_business', 'directory_bookingLink', $ln->gd('m.directory.business.link.externalBookings'), $row)}
        {$dld->getLinkPortalMain('directory_business', 'directory_advertLink', $ln->gd('m.directory.business.link.advertiser'), $row)}
        {$dld->getLinkPortalMain('directory_business', 'directory_paymentLink', $ln->gd('m.directory.business.link.paymentTypes'), $row)}
        {$dld->getLinkPortalMain('directory_business', 'directory_socialMediaLink', $ln->gd('m.directory.business.link.socialMedia'), $row)}
        {$dld->getLinkPortalMain('directory_business', 'directory_ambianceLink', $ln->gd('m.directory.business.link.ambiance'), $row)}
        {$dld->getLinkPortalMain('directory_business', 'directory_deliveryLink', $ln->gd('m.directory.business.link.delivery'), $row)}
        {$dld->getLinkPortalMain('directory_business', 'directory_contactLink', $ln->gd('m.directory.business.link.usersFollowing'), $row)}
        {$dld->getLinkPortalMain('directory_business', 'directory_guideLink', $ln->gd('m.directory.business.link.linkedGuide'), $row)}
        {$comment->getView(array(
             'roomName' => 'directory_business'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;
    }

    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $country_id = $fn->getSessionParam('cp_country_id');
        $state_id   = $fn->getReqParam('state_id');
        $city_id    = $fn->getReqParam('city_id');
        $borough_id = $fn->getReqParam('borough_id');
        $area_id    = $fn->getReqParam('area_id');
        $street_id  = $fn->getReqParam('street_id');
        $tags_id    = $fn->getReqParam('tags_id');
        $special_search = $fn->getReqParam('special_search');

        $spSearchArr = array(
            'Flagged'
           ,'Not-Flagged'
           ,'Published'
           ,'Not-Published'
           ,'Modified'
           ,'Social Media - Unverified'
           ,'Social Media - Verified'
           ,'Social Media - Failed'
           ,'FR - Unverified'
           ,'FR - Verified'
        );

        $sqlTags = "
        SELECT tags_id
              ,tag_text
        FROM tags
        ORDER BY tag_text
        ";

        $sqlCat = getCPModelObj('webBasic_category')->getCategorySQLByType('Business');

        $sqlSubCat = '';
        if ($tv['category_id'] != '') {
            $sqlSubCat = getCPModelObj('webBasic_subCategory')->getSubCategorySQL($tv['category_id']);
        }

        $sqlCountry = $fn->getDDSql('common_country');

        $sqlState = '';
        if ($country_id != '') {
            $sqlState = $fn->getDDSql('directory_state', array('condn' => "country_id = {$country_id}"));
        }

        $sqlCity = '';
        if ($country_id != '') {
            $sqlCity = $fn->getDDSql('directory_city', array('condn' => "country_id = {$country_id}"));
        }

        $sqlBorough = '';
        if ($city_id != '') {
            $sqlBorough = $fn->getDDSql('directory_borough', array('condn' => "city_id = {$city_id}"));
        }

        $sqlArea = '';
        if ($city_id != '') {
            $condn  = "city_id = {$city_id}";
            $condn .= ($borough_id != '') ? " AND borough_id = {$borough_id}" : '';
            $sqlArea = $fn->getDDSql('directory_area', array('condn' => $condn));
        }

        $sqlStreet = '';
        if ($area_id != '') {
            $condn  = "area_id = {$area_id}";
            $condn .= ($borough_id != '') ? " AND borough_id = {$borough_id}" : '';
            $sqlStreet = $fn->getDDSql('directory_street', array('condn' => $condn));
        }

        $text = "
        <td>adasd
            {$formObj->getDropDownBySQL($ln->gd('m.directory.business.lbl.category'), 'category_id', $sqlCat, $tv['category_id'])}
        </td>
        <td>
            {$formObj->getDropDownBySQL($ln->gd('m.directory.business.lbl.subCategory'), 'sub_category_id', $sqlSubCat, $tv['sub_category_id'])}
        </td>

        <td>
            {$formObj->getDropDownBySQL($ln->gd('m.directory.business.lbl.state'), 'state_id', $sqlState, $state_id)}
        </td>
        <td>
            {$formObj->getDropDownBySQL($ln->gd('m.directory.business.lbl.city'), 'city_id', $sqlCity, $city_id)}
        </td>
        <td>
            {$formObj->getDropDownBySQL($ln->gd('m.directory.business.lbl.borough'), 'borough_id', $sqlBorough, $borough_id)}
        </td>
        <td>
            {$formObj->getDropDownBySQL($ln->gd('m.directory.business.lbl.area'), 'area_id', $sqlArea, $area_id)}
        </td>
        <td>
            {$formObj->getDropDownBySQL($ln->gd('m.directory.business.lbl.street'), 'street_id', $sqlStreet, $street_id)}
        </td>
        <td>
            {$formObj->getDropDownBySQL($ln->gd('m.directory.business.lbl.tags'), 'tags_id', $sqlTags, $tags_id)}
        </td>
        <td class='fieldValue'>
            <select name='special_search'>
                <option value=''>{$ln->gd('m.directory.business.lbl.specialSearch')}</option>
                {$cpUtil->getDropDown1($spSearchArr, $special_search)}
            </select>
        </td>
        ";

        return $text;
    }

    /**
     *
     */
    function getImportInstructions() {
        $cpPaths = Zend_Registry::get('cpPaths');

        $url = 'index.php?_spAction=streamFile&showHTML=0&modname=directory_business&filename=directory-import-template.xls';
        $text = "
        <p>Accepted file type: xls</p>
        <p>Template: <a href='{$url}'>Download</a></p>
        ";

        return $text;
    }

    function getSearch() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $sqlBG    = $fn->getDDSql('directory_businessGroup');
        $sqlType    = $fn->getValueListSQL('clientType');
        $sqlCat     = $fn->getValueListSQL('projectCategory');
        $sqlStatus  = $fn->getValueListSQL('projectStatus');

        $sqlStaff = $fn->getDDSql('core_staff', array('condn' => "status = 'Current'"));
        $expVl   = array('sqlType' => 'OneField');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $fieldset = "
        {$formObj->getTBRow($ln->gd('m.directory.business.lbl.businessName'), 'business_name')}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.business.lbl.multiple'), 'business_group_id', $sqlBG)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.business.lbl.projectDetails'), $fieldset)}
        ";

        return $text;
    }

    function getBulkPromotionForm(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $expCheck = array('isLabelOnLeft' => true);

        $count = $fn->getFlaggedRecordCount('directory_business');

        $formAction = "index.php?module=directory_business&_spAction=bulkPromotionSubmit&showHTML=0";
        $message = "
        <p>Please enter the promo details and click <b>Create Promotions</b> button.
        This will add this new promotion to all the tagged <b>{$count}</b> records.</p>
        ";
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$message}
            <fieldset>
                {$formObj->getTBRow('Title', 'title')}
                {$formObj->getTARow('Custom Text', 'custom_text')}
                {$formObj->getSingleCheckBoxRow('Is Happy Hr. Promo.?', 'is_happy_hour_promo',
                                                '', $expCheck)}
                {$formObj->getDateRow('Start Date', 'start_date')}
                {$formObj->getDateRow('End Date', 'end_date')}
                {$formObj->getTimeRow('Start Time', 'start_time')}
                {$formObj->getTimeRow('End Time', 'end_time')}
                {$formObj->getTBRow('Promo URL', 'promotion_url')}
                {$formObj->getDaysOfWeeksRow('Days of Week', 'days_of_week')}
            </fieldset>
        </form>
        ";
        return $text;
    }

    function getBulk3rdPartyPromotionForm(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $expCheck = array('isLabelOnLeft' => true);

        $count = $fn->getFlaggedRecordCount('directory_business');

        $formAction = "index.php?module=directory_business&_spAction=bulk3rdPartyPromotionSubmit&showHTML=0";
        $message = "
        <p>Please enter the promo details and click <b>Create 3rd Party Promotions</b> button.
        This will add this new promotion to all the tagged <b>{$count}</b> records.</p>
        ";

        $sqlCards = $fn->getDDSql('directory_cards');
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$message}
            <fieldset>
                {$formObj->getDDRowBySQL('Loyalty Card', 'card_id', $sqlCards)}
                {$formObj->getTBRow('Title', 'title')}
                {$formObj->getTARow('Custom Text', 'custom_text')}
                {$formObj->getSingleCheckBoxRow('Is Happy Hr. Promo.?', 'is_happy_hour_promo',
                                                '', $expCheck)}
                {$formObj->getDateRow('Start Date', 'start_date')}
                {$formObj->getDateRow('End Date', 'end_date')}
                {$formObj->getTimeRow('Start Time', 'start_time')}
                {$formObj->getTimeRow('End Time', 'end_time')}
                {$formObj->getTBRow('Promo URL', 'promotion_url')}
                {$formObj->getDaysOfWeeksRow('Days of Week', 'days_of_week')}
            </fieldset>
        </form>
        ";
        return $text;
    }

    function setArrays(){
        $this->parkingTypes = array(

        );

    }
}
