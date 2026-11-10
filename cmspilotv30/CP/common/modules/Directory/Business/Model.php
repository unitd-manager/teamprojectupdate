                                                                                                                                                                                                                                                                                                                                                                                                <?
class CP_Common_Modules_Directory_Business_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $tags_id  = $fn->getReqParam('tags_id');

        $tagsSQL = '';

        if ($tv['action'] != 'list'){
            $tagsSQL = "
            ,(
            SELECT GROUP_CONCAT(t.tag_text ORDER BY t.tag_text SEPARATOR ', ')
            FROM tags t, tags_history th
            WHERE t.tags_id = th.tags_id
              AND th.record_id   = b.business_id
              AND th.record_type = 'Business'
            ) AS tags

            ,(
            SELECT GROUP_CONCAT(t.chi_tag_text ORDER BY t.chi_tag_text SEPARATOR ', ')
            FROM tags t, tags_history th
            WHERE t.tags_id = th.tags_id
              AND th.record_id   = b.business_id
              AND th.record_type = 'Business'
            ) AS chi_tags

            ,(
            SELECT GROUP_CONCAT(t.pin_tag_text ORDER BY t.pin_tag_text SEPARATOR ', ')
            FROM tags t, tags_history th
            WHERE t.tags_id = th.tags_id
              AND th.record_id   = b.business_id
              AND th.record_type = 'Business'
            ) AS pin_tags

            ,(
            SELECT GROUP_CONCAT(t.ch2_tag_text ORDER BY t.ch2_tag_text SEPARATOR ', ')
            FROM tags t, tags_history th
            WHERE t.tags_id = th.tags_id
              AND th.record_id   = b.business_id
              AND th.record_type = 'Business'
            ) AS ch2_tags
            ";
        }

        $extraTableNames = '';
        if ($tags_id != "") {
            $extraTableNames = "
            JOIN (tags_history th1) ON (b.business_id = th1.record_id)
            JOIN (tags t1) ON (t1.tags_id = th1.tags_id)
            ";
        }

        $onPromoSql = '';
        $reviewCountsSql = '';
        if (CP_SCOPE == 'www') {
            $theme = Zend_Registry::get('currentTheme');

            $onPromoSql = "
            ,(SELECT CONCAT_WS(' ', p.end_date, p.end_time)
              FROM promotion p
              WHERE p.business_id = b.business_id
              ORDER BY p.end_date, p.end_time
              LIMIT 0, 1
            ) AS promoDateTime
            ";

            if ($theme->isLoggedInUser()){
                $cpContactId = $fn->getSessionParam('cpContactId');

                $reviewCountsSql = "
                ,(SELECT COUNT(*)
                  FROM comment
                  WHERE record_id = b.business_id
                    AND room_name = 'directory_business'
                    AND contact_id IN (
                        SELECT friend_id
                        FROM contact_friend
                        WHERE contact_id = '{$cpContactId}'
                    )
                    AND contact_module = 'directory_contact'
                ) AS no_of_reviews_by_friends

                ,(SELECT FORMAT(AVG(rating), 1)
                  FROM comment
                  WHERE record_id = b.business_id
                    AND room_name = 'directory_business'
                    AND contact_id IN (
                        SELECT friend_id
                        FROM contact_friend
                        WHERE contact_id = '{$cpContactId}'
                    )
                    AND contact_module = 'directory_contact'
                ) AS avg_rating_by_friends

                ,(SELECT FORMAT(AVG(rating), 1)
                  FROM comment
                  WHERE record_id = b.business_id
                    AND room_name = 'directory_business'
                    AND contact_id = '{$cpContactId}'
                    AND contact_module = 'directory_contact'
                ) AS rating_by_me

                ,(SELECT COUNT(*)
                  FROM comment
                  WHERE record_id = b.business_id
                    AND room_name = 'directory_business'
                    AND contact_id = '{$cpContactId}'
                    AND contact_module = 'directory_contact'
                ) AS no_of_reviews_by_me
                ";
            } else {
                $reviewCountsSql = "
                ,0 AS no_of_reviews_by_friends
                ,0 AS avg_rating_by_friends
                ,0 AS rating_by_me
                ,0 AS no_of_reviews_by_me
                ";
            }
        }

        $SQL = "
        SELECT b.business_id
              ,b.business_name
              ,b.business_name_local_lang
              ,b.category_id
              ,b.sub_category_id
              ,'' AS sub_category_title2
              ,'' AS sub_category_title3
              ,b.business_type
              ,b.status
              ,b.email
              ,b.email_override
              ,b.phone
              ,b.phone2
              ,b.mobile
              ,b.fax
              ,b.website
              ,b.website_override
              ,b.country_id
              ,b.description
              ,b.description_chi
              ,b.accepted_cards
              ,b.notes
              ,b.creation_date
              ,b.modification_date
              ,b.published
              ,b.business_start_date
              ,b.business_end_date
              ,b.business_group_id
              ,b.address_careof
              ,b.entry_concession
              ,b.transport_name
              ,b.transport_carrier
              ,b.feature_atm
              ,b.feature_parties
              ,b.feature_wifi
              ,b.source_id
              ,b.picture
              ,b.meta_title
              ,b.meta_description
              ,b.meta_keyword
              ,b.closed_date
              ,b.business_id_previous
              ,b.business_id_new
              ,b.pin_business_name
              ,b.flag
              ,b.search_keyword
              ,b.address_id
              ,b.brands
              ,b.feature_qr_code
              ,b.feature_affiliate_scheme
              ,b.feature_inhouse_loyalty_scheme
              ,b.feature_wheelchair_access
              ,b.feature_nearest_mtr_exit
              ,b.feature_breakfast
              ,b.feature_tea
              ,b.feature_brunch
              ,b.feature_lunch
              ,b.feature_avg_main_course_price
              ,b.feature_no_of_seats
              ,b.feature_signature_dishes
              ,b.feature_reservation
              ,b.feature_parking
              ,b.feature_parking_type
              ,b.feature_outdoor_seating
              ,b.feature_group_friendly
              ,b.feature_private_room
              ,b.feature_private_room_capacity
              ,b.feature_children_friendly
              ,b.feature_pets_friendly
              ,b.feature_delivery
              ,b.feature_delivery_company
              ,b.feature_takeaway
              ,b.feature_catering
              ,b.feature_dinner
              ,b.feature_bar
              ,b.feature_byob
              ,b.feature_dress_code
              ,b.feature_dress_type
              ,b.feature_live_music
              ,b.feature_tv
              ,b.feature_sports_broadcast
              ,b.delivery_id
              ,b.feature_exhibition_kitchen
              ,b.feature_fire_place
              ,b.feature_view
              ,b.feature_ambiance
              ,b.feature_ambiance_type
              ,b.feature_entry_concession
              ,b.protect_category
              ,b.protect_sub_category
              ,b.ch2_business_name
              ,b.feature_corkage_amount
              ,b.feature_has_promotion
              ,b.comment
              ,b.contact_name
              ,b.feature_seaview
              ,b.prohibit_business_group_update
              ,b.phone2_type
              ,b.modified_tag
              ,b.phone_other
              ,b.dgd_notify_name
              ,b.dgd_notify_address
              ,b.dgd_notify_category
              ,b.dgd_notify_photo
              ,b.dgd_notify_phone
              ,b.dgd_notify_opening_hour
              ,b.dgd_notify_tags
              ,b.dgd_notify_payment_method
              ,b.dgd_notify_social_media
              ,b.dgd_notify_other_info
              ,b.dgd_notify_course
              ,b.dgd_notify_signature_dish
              ,b.dgd_notify_feature
              ,b.dgd_notify_ambiance
              ,b.dgd_notify_group
              ,b.dgd_notify_delivery
              ,b.sub_category2_id
              ,b.sub_category3_id
              ,b.dgd_created_by
              ,b.dgd_modified_by
              ,b.dgd_creation_date
              ,b.dgd_modification_date
              ,b.created_by
              ,b.modified_by
              ,b.domain_name
              ,b.top_level_domain
              ,b.email_user
              ,b.domain_name_email
              ,b.top_level_domain_email
              ,b.import_tag
              ,b.social_media_verified
              ,b.dgd_verified

              ,b.business_name AS title
              ,c.title AS category_title
              ,c.category_type AS category_type
              ,sc.title AS sub_category_title
              #,sc2.title AS sub_category_title2
              #,sc3.title AS sub_category_title3
              ,s.title AS street_name
              ,s.pin_title AS pin_street_name
              ,a.title AS area_name
              ,bo.title AS borough_name
              ,ci.title AS city_name
              ,st.title AS state_name
              ,co.title AS country_name
              ,co.country_code
              ,CONCAT_WS('-', co.country_code, b.source_id) AS country_code_source_id
              ,co.idd_code
              ,shc.title AS shop_center_name
              ,bg.title AS business_group_name
              ,b2.business_name AS business_name_previous

              ,CONCAT_WS(', ', s.title, a.title, bo.title, ci.title, st.title, co.title, ad.address_po_code) AS address

              ,CONCAT_WS('-', co.country_code, ad.address_id) AS country_code_address_id
              ,ad.address_id
              ,ad.country_id
              ,ad.state_id
              ,ad.city_id
              ,ad.borough_id
              ,ad.area_id
              ,ad.street_id
              ,ad.shop_center_id
              ,bldg.building_id
              ,bldg.street_no_from AS address_street_no_from
              ,bldg.street_no_from AS address_street_no_to
              ,bldg.map_latlng_verified
              ,CONCAT_WS('-', bldg.street_no_from, bldg.street_no_to) AS street_no
              ,bldg.title AS address_building_name
              ,ad.address_block
              ,ad.address_floor_from
              ,ad.address_floor_to
              ,ad.address_unit_from
              ,ad.address_unit_to
              ,ad.address_po_code
              ,bldg.chi_title AS chi_address_building_name
              ,bldg.pin_title AS pin_address_building_name
              ,bldg.latitude
              ,bldg.longitude
              ,ad.northing
              ,ad.easting
              ,d.title AS delivery_title
              ,amb.title AS ambiance_title

              ,CASE
               WHEN b.description = '' OR b.description IS NULL THEN ''
               ELSE CONCAT_WS('', SUBSTRING_INDEX(b.description, ' ', 15), '...')
               END AS description_list

              ,(SELECT 'Yes'
                FROM media m
                WHERE b.business_id = m.record_id
                  AND m.room_name = 'directory_business'
                  AND m.record_type = 'picture'
                LIMIT 1
                ) AS has_picture

              ,(SELECT 'Yes'
                FROM media m
                WHERE b.business_id = m.record_id
                  AND m.room_name = 'directory_business'
                  AND m.record_type = 'logo'
                LIMIT 1
                ) AS has_logo

              ,(
              SELECT GROUP_CONCAT(sc.title ORDER BY sc.title SEPARATOR ', ')
              FROM social_media sc
                  ,business_social_media bsc
              WHERE sc.social_media_id = bsc.social_media_id
                AND bsc.business_id = b.business_id
              ) AS social_medias

               {$tagsSQL}
              ,(SELECT COUNT(*)
                FROM my_business mb
                WHERE mb.business_id = b.business_id
              ) AS no_of_followers

              ,(SELECT COUNT(*)
                FROM comment c
                WHERE c.record_id = b.business_id
                  AND c.room_name = 'directory_business'
              ) AS no_of_reviews

              ,(SELECT COUNT(*)
                FROM promotion p
                WHERE p.business_id = b.business_id
              ) AS no_of_promotions

              ,(SELECT FORMAT(AVG(rating), 1)
                FROM comment cm
                WHERE cm.record_id = b.business_id
                  AND room_name = 'directory_business'
              ) AS avg_rating
              {$reviewCountsSql}
              ,(SELECT COUNT(*)
                FROM promotion p
                WHERE p.business_id = b.business_id
                  AND p.card_id IS NOT NULL
                  AND p.card_id > 0
              ) AS no_of_loyalty_cards
              {$onPromoSql}
        FROM business b
        {$extraTableNames}
        LEFT JOIN category c ON b.category_id = c.category_id
        LEFT JOIN sub_category sc ON b.sub_category_id  = sc.sub_category_id
        #LEFT JOIN sub_category sc2 ON b.sub_category2_id  = sc2.sub_category_id
        #LEFT JOIN sub_category sc3 ON b.sub_category3_id  = sc3.sub_category_id
        LEFT JOIN address ad ON ad.address_id = b.address_id
        LEFT JOIN building bldg ON bldg.building_id = ad.building_id
        LEFT JOIN street s ON ad.street_id = s.street_id
        LEFT JOIN area a ON ad.area_id = a.area_id
        LEFT JOIN borough bo ON bo.borough_id = ad.borough_id
        LEFT JOIN city ci ON ad.city_id = ci.city_id
        LEFT JOIN state st ON ad.state_id = st.state_id
        LEFT JOIN country co ON ad.country_id = co.country_id
        LEFT JOIN shop_center shc ON ad.shop_center_id = shc.shop_center_id
        LEFT JOIN delivery d ON d.delivery_id = b.delivery_id
        LEFT JOIN business_group bg ON b.business_group_id = bg.business_group_id
        LEFT JOIN business b2 ON b2.business_id = b.business_id_previous
        LEFT JOIN ambiance amb ON amb.ambiance_id= b.feature_ambiance_type
        ";
        return $SQL;
    }

    function getSQLForPager() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $tags_id = $fn->getReqParam('tags_id');
        $extraTableNames = '';
        if ($tags_id != "") {
            $extraTableNames = "
            JOIN (tags_history th1) ON (b.business_id = th1.record_id)
            JOIN (tags t1) ON (t1.tags_id = th1.tags_id)
            ";
        }

        $SQL = "
        SELECT count(*)
        FROM business b
        {$extraTableNames}
        LEFT JOIN category c ON b.category_id = c.category_id
        LEFT JOIN sub_category sc ON b.sub_category_id  = sc.sub_category_id
        LEFT JOIN sub_category sc2 ON b.sub_category2_id  = sc2.sub_category_id
        LEFT JOIN sub_category sc3 ON b.sub_category3_id  = sc3.sub_category_id
        LEFT JOIN address ad ON ad.address_id = b.address_id
        LEFT JOIN building bldg ON bldg.building_id = ad.building_id
        LEFT JOIN street s ON ad.street_id = s.street_id
        LEFT JOIN area a ON ad.area_id = a.area_id
        LEFT JOIN borough bo ON bo.borough_id = ad.borough_id
        LEFT JOIN city ci ON ad.city_id = ci.city_id
        LEFT JOIN state st ON ad.state_id = st.state_id
        LEFT JOIN country co ON ad.country_id = co.country_id
        LEFT JOIN shop_center shc ON ad.shop_center_id = shc.shop_center_id
        LEFT JOIN delivery d ON d.delivery_id = b.delivery_id
        LEFT JOIN business_group bg ON b.business_group_id = bg.business_group_id
        LEFT JOIN business b2 ON b2.business_id = b.business_id_previous
        LEFT JOIN ambiance amb ON amb.ambiance_id= b.feature_ambiance_type
        ";
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar->mainTableAlias = 'b';
        $businessId = $fn->getIssetParam($this->expForSearchVar, 'businessId');
        $relationalDataOnly  = $fn->getIssetParam($this->expForSearchVar, 'relationalDataOnly');
        $specialFlag = $fn->getIssetParam($this->expForSearchVar, 'specialFlag');
        $country_id = $fn->getSessionParam('cp_country_id');

        $keyword = html_entity_decode($tv['keyword'], ENT_QUOTES);

        if (CP_SCOPE == 'www') {
            $searchVar->sqlSearchVar['published'] = "b.published = 1";
        }

        if ($relationalDataOnly){
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'b.business_id');
        } else if ($tv['record_id'] != '' && $linkRecType == '') {
            $searchVar->sqlSearchVar[] = "b.business_id = '{$tv['record_id']}'";

            if ($country_id != '' && CP_SCOPE == 'www') {
                $searchVar->sqlSearchVar[] = "ad.country_id = '{$country_id}'";
            }

        } else if ($businessId != '' ) {
            $searchVar->sqlSearchVar[] = "b.business_id = '{$businessId}'";

        } else {
            $state_id   = $fn->getReqParam('state_id');
            $city_id    = $fn->getReqParam('city_id');
            $borough_id = $fn->getReqParam('borough_id');
            $area_id    = $fn->getReqParam('area_id');
            $street_id  = $fn->getReqParam('street_id');
            $tags_id    = $fn->getReqParam('tags_id');
            $business_group_id = $fn->getReqParam('business_group_id');

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'b.business_id');

            if ($business_group_id != '' ) {
                $searchVar->sqlSearchVar[] = "b.business_group_id = '{$business_group_id}'";
            }

            if ($country_id != '' ) {
                $searchVar->sqlSearchVar[] = "ad.country_id = '{$country_id}'";
            }

            if ($state_id != '' ) {
                $searchVar->sqlSearchVar[] = "ad.state_id = '{$state_id}'";
            }

            if ($city_id != '' ) {
                $searchVar->sqlSearchVar[] = "ad.city_id = '{$city_id}'";
            }

            if ($borough_id != '' ) {
                $searchVar->sqlSearchVar[] = "ad.borough_id = '{$borough_id}'";
            }

            if ($area_id != '' ) {
                $searchVar->sqlSearchVar[] = "ad.area_id = '{$area_id}'";
            }

            if ($street_id != '' ) {
                $searchVar->sqlSearchVar[] = "ad.street_id = '{$street_id}'";
            }

            if ($tv['category_id'] != '' ) {
                $searchVar->sqlSearchVar['category_id'] = "b.category_id = '{$tv['category_id']}'";
            }

            if ($tv['subRoom'] != '' ) {
                $searchVar->sqlSearchVar['category_id'] = "b.category_id = '{$tv['subRoom']}'";
            }

            if ($tv['sub_category_id'] != '' ) {
                $searchVar->sqlSearchVar['sub_category_id'] = "b.sub_category_id = '{$tv['sub_category_id']}'";
            }

            if ($tv['subCat'] != '' ) {
                $searchVar->sqlSearchVar['sub_category_id'] = "b.sub_category_id = '{$tv['subCat']}'";
            }

            if ($keyword != '') {
                $searchVar->sqlSearchVar[] = "(
                	b.business_name LIKE '{$keyword}%'
                	OR b.business_name_local_lang LIKE '{$keyword}%'
                	OR b.source_id LIKE '{$keyword}'
                	OR s.title LIKE '{$keyword}%'
                	OR bldg.title LIKE '{$keyword}%'
                	OR b.dgd_modified_by LIKE '{$keyword}%'
                )";
            }

            if ($tags_id != '') {
                $searchVar->sqlSearchVar[] = "(t1.tags_id = '{$tags_id}')";
            }

            if ($tv['special_search'] == 'Flagged') {
                $searchVar->sqlSearchVar[] = 'b.flag = 1';
            }
            if ($tv['special_search'] == 'Not-Flagged') {
                $searchVar->sqlSearchVar[] = '(so.flag != 1 OR so.flag IS null)';
            }
            if ($tv['special_search'] == 'Published') {
                $searchVar->sqlSearchVar[] = 'b.published = 1';
            }
            if ($tv['special_search'] == 'Not-Published') {
                $searchVar->sqlSearchVar[] = '(b.published != 1 OR b.published IS null)';
            }
            if ($tv['special_search'] == 'Modified') {
                $searchVar->sqlSearchVar[] = 'b.modified_tag = 1';
            }
            if ($tv['special_search'] == 'Social Media - Unverified') {
                $searchVar->sqlSearchVar[] = 'b.social_media_verified = 0';
            }
            if ($tv['special_search'] == 'Social Media - Verified') {
                $searchVar->sqlSearchVar[] = 'b.social_media_verified = 1';
            }
            if ($tv['special_search'] == 'Social Media - Failed') {
                $searchVar->sqlSearchVar[] = 'b.social_media_failed = 1';
            }
            if ($tv['special_search'] == 'FR - Unverified') {
                $searchVar->sqlSearchVar[] = 'b.dgd_verified = 0';
            }
            if ($tv['special_search'] == 'FR - Verified') {
                $searchVar->sqlSearchVar[] = 'b.dgd_verified = 1';
            }

            if($specialFlag == 'latestRestaurants'){
                $catRec = getCPModelObj('webBasic_category')->getRecordByType('Restaurants', 'Business');
                $searchVar->sqlSearchVar['category_id'] = "b.category_id = '{$catRec['category_id']}'";
            }


            if (CP_SCOPE == 'www') {
                $searchVar->sqlSearchVar[] = "b.business_name != ''";
                if($specialFlag == 'latestBusiness'){
    			         $searchVar->sortOrder = 'creation_date DESC';
                } else if($specialFlag == 'latestRestaurants'){
    			         $searchVar->sortOrder = 'avg_rating DESC';
                } else {
                    $searchVar->sortOrder = 'promoDateTime DESC';
                }
            } else {
                $searchVar->sortOrder = 'b.business_id DESC';
            }
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $business_id = $fn->getReqParam('business_id');
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);

        $SQL = "
        SELECT c.country_code
        FROM country c
        JOIN address a ON a.country_id = c.country_id
        JOIN business b ON b.address_id = a.address_id
        WHERE b.business_id = {$business_id}
        ";
        $rowBusiness = $fn->getRecordBySQL($SQL);
        $country_code = strtoupper($rowBusiness['country_code']);
        if ($rowBusiness && $country_code != 'UK') {
            $source_id = $fn->getSequenceFromSettings('m.directory.business.nextSourceId');
            $fa['source_id'] = $source_id;
        }
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);

        if(isset($_POST['tags'])){
            getCPModuleObj('web2_tags')->model->updateCloudTagsByCSV('Business', $id, $_POST, 'tags');
        }
        return $fn->returnAfterNewSave($id);
    }

    function getFields() {
        $fn = Zend_Registry::get('fn');
        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'business_name');
        $fa = $fn->addToFieldsArray($fa, 'business_name_local_lang');
        $fa = $fn->addToFieldsArray($fa, 'category_id');
        $fa = $fn->addToFieldsArray($fa, 'sub_category_id');
        $fa = $fn->addToFieldsArray($fa, 'sub_category2_id');
        $fa = $fn->addToFieldsArray($fa, 'sub_category3_id');
        $fa = $fn->addToFieldsArray($fa, 'business_type');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'contact_name');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'phone2');
        $fa = $fn->addToFieldsArray($fa, 'phone_other');
        $fa = $fn->addToFieldsArray($fa, 'mobile');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'website');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'description_chi');
        $fa = $fn->addToFieldsArray($fa, 'business_group_id');
        $fa = $fn->addToFieldsArray($fa, 'prohibit_business_group_update');
        $fa = $fn->addToFieldsArray($fa, 'transport_name');
        $fa = $fn->addToFieldsArray($fa, 'transport_carrier');
        $fa = $fn->addToFieldsArray($fa, 'brands');

        $fa = $fn->addToFieldsArray($fa, 'feature_cuisine');
        $fa = $fn->addToFieldsArray($fa, 'feature_atm', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_entry_concession', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_qr_code', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_affiliate_scheme', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_inhouse_loyalty_scheme', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_wheelchair_access', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_wifi', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_nearest_mtr_exit');

        $fa = $fn->addToFieldsArray($fa, 'feature_breakfast', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_tea', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_brunch', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_lunch', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_dinner', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_avg_main_course_price');
        $fa = $fn->addToFieldsArray($fa, 'feature_no_of_seats');

        $fa = $fn->addToFieldsArray($fa, 'feature_signature_dishes');
        $fa = $fn->addToFieldsArray($fa, 'feature_reservation', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_parking', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_parking_type');
        $fa = $fn->addToFieldsArray($fa, 'feature_outdoor_seating', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_group_friendly', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_private_room', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_private_room_capacity');
        $fa = $fn->addToFieldsArray($fa, 'feature_children_friendly', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_pets_friendly', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_delivery', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_delivery_company');
        $fa = $fn->addToFieldsArray($fa, 'feature_takeaway', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_catering', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_dinner', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_bar', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_byob', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_corkage_amount', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_dress_code', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_dress_type');
        $fa = $fn->addToFieldsArray($fa, 'feature_live_music', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_tv', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_sports_broadcast', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_exhibition_kitchen', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_fire_place', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_view', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_seaview', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_ambiance', 0);
        $fa = $fn->addToFieldsArray($fa, 'feature_ambiance_type', 0);

        $fa = $fn->addToFieldsArray($fa, 'entry_concession');
        $fa = $fn->addToFieldsArray($fa, 'meta_title');
        $fa = $fn->addToFieldsArray($fa, 'meta_keyword');
        $fa = $fn->addToFieldsArray($fa, 'meta_description');

        $fa = $fn->addToFieldsArray($fa, 'delivery_id');
        $fa = $fn->addToFieldsArray($fa, 'protect_category');
        $fa = $fn->addToFieldsArray($fa, 'protect_sub_category');
        $fa = $fn->addToFieldsArray($fa, 'address_id_hidden', '', false, 'address_id');
        $fa = $fn->addToFieldsArray($fa, 'modified_tag');

        $fa = $fn->addToFieldsArray($fa, 'dgd_notify_name');
        $fa = $fn->addToFieldsArray($fa, 'dgd_notify_address');
        $fa = $fn->addToFieldsArray($fa, 'dgd_notify_category');
        $fa = $fn->addToFieldsArray($fa, 'dgd_notify_photo');
        $fa = $fn->addToFieldsArray($fa, 'dgd_notify_phone');
        $fa = $fn->addToFieldsArray($fa, 'dgd_notify_opening_hour');
        $fa = $fn->addToFieldsArray($fa, 'dgd_notify_tags');
        $fa = $fn->addToFieldsArray($fa, 'dgd_notify_payment_method');
        $fa = $fn->addToFieldsArray($fa, 'dgd_notify_social_media');
        $fa = $fn->addToFieldsArray($fa, 'dgd_notify_other_info');
        $fa = $fn->addToFieldsArray($fa, 'dgd_notify_course');
        $fa = $fn->addToFieldsArray($fa, 'dgd_notify_signature_dish');
        $fa = $fn->addToFieldsArray($fa, 'dgd_notify_feature');
        $fa = $fn->addToFieldsArray($fa, 'dgd_notify_ambiance');
        $fa = $fn->addToFieldsArray($fa, 'dgd_notify_group');
        $fa = $fn->addToFieldsArray($fa, 'dgd_notify_delivery');

        $fa = $fn->addToFieldsArray($fa, 'comment');

        return $fa;
    }

    /**
     *
     */
    function getBusinessSQL() {
        $sql = "
        SELECT b.business_id
              ,b.business_name
        FROM business b
        ORDER BY b.business_name
        ";
        return $sql;
    }

    /**
     * Custom Promotions
     */
    function getDirectoryBusinessDirectoryPromotionLinkSQL($id) {

        $SQL = "
        SELECT p.promotion_id
              ,p.title
              ,DATE_FORMAT(p.start_date, '%d-%m-%Y') AS start_date
              ,DATE_FORMAT(p.end_date, '%d-%m-%Y') AS end_date
              ,p.start_time
              ,p.end_time
              ,p.days_of_week
              ,p.custom_text
        FROM promotion p
        WHERE p.business_id = '{$id}'
          AND p.record_type = 'IH'
        ORDER BY p.title
        ";

        return $SQL;
    }

    /**
     * Custom Promotions
     */
    function getDirectoryBusinessDirectoryPromotion3PartyLinkSQL($id) {
        $SQL = "
        SELECT p.promotion_id
              ,c.title
              ,DATE_FORMAT(p.start_date, '%d-%m-%Y') AS start_date
              ,DATE_FORMAT(p.end_date, '%d-%m-%Y') AS end_date
              ,p.days_of_week
              ,p.custom_text
        FROM promotion p
        JOIN cards c ON c.card_id = p.card_id
        WHERE p.business_id = '{$id}'
          AND p.record_type = '3P'
        ORDER BY p.title
        ";
        return $SQL;
    }

    function getDirectoryBusinessDirectorySocialMediaLinkSQL($id) {
        $openUrlText = "
        CASE
        WHEN INSTR(bsm.url, 'http') > 0 THEN CONCAT_WS('', \"<a target='_blank' href='\", bsm.url, \"'>open url</a>\")
        ELSE CONCAT_WS('', \"<a target='_blank' href='\", sm.url, bsm.url, \"'>open url</a>\")
        END AS open_url
        ";

        $SQL = "
        SELECT bsm.business_social_media_id
              ,sm.social_media_id
              ,bsm.url
              ,{$openUrlText}
        FROM `business_social_media` bsm
        LEFT JOIN `social_media` sm ON (sm.social_media_id = bsm.social_media_id)
        WHERE bsm.business_id = '{$id}'
        ORDER BY bsm.business_social_media_id
        ";
        return $SQL;
    }

    /**
     *
     */
    function createBusinessHours($row) {
        $fn = Zend_Registry::get('fn');

        $count = $fn->getRecordCount('business_hours', "business_id = {$row['business_id']}");

        if ($count < 7) {
            for ($i = 1; $i <= 7; $i++){
                $recCount = $fn->getRecordCount('business_hours', "business_id = {$row['business_id']} AND week_day = {$i}");

                if ($recCount == 0){
                    $fa = array();
                    $fa['business_id'] = $row['business_id'];
                    $fa['week_day']    = $i;
                    $id = $fn->addRecord($fa, 'business_hours');
                }
            }
        }
    }
}
