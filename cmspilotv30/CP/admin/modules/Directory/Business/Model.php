<?
class CP_Admin_Modules_Directory_Business_Model extends CP_Common_Modules_Directory_Business_Model
{

    var $socialMediaArr = array();

    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        $validate->resetErrorArray();

        $validate->validateData('business_name', 'Please enter business name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');
        $validate->resetErrorArray();

        $validate->validateData('business_name', 'Please enter business name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'title' => $phpExcel->getFldObj('Business Name')
             ,'category_title' => $phpExcel->getFldObj('Category')
             ,'sub_category_title' => $phpExcel->getFldObj('Sub Category')
             ,'street_name' => $phpExcel->getFldObj('Street')
             ,'area_name' => $phpExcel->getFldObj('Area')
             ,'city_name' => $phpExcel->getFldObj('City')
             ,'state_name' => $phpExcel->getFldObj('State')
             ,'country_name' => $phpExcel->getFldObj('Country')
             ,'source_id' => $phpExcel->getFldObj('Source Id')
             ,'no_of_followers' => $phpExcel->getFldObj('# followers')
             ,'no_of_reviews' => $phpExcel->getFldObj('# reviews')
             ,'no_of_promotions' => $phpExcel->getFldObj('# promos.')
             ,'no_of_loyalty_cards' => $phpExcel->getFldObj('# Loyalty Cards')
             ,'creation_date' => $phpExcel->getFldObj('Creation Date')
             ,'business_id' => $phpExcel->getFldObj('ID')
        );

        $file_name = $phpExcel->getSaveFileName();

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

    /**
     *
     */
    function getImportData_xxx(){
        print "<img src='images/logo.jpg'><br>";
        flush();
        ob_flush();

        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');
        $fa = array(
              'source_id'                => $phpExcel->getImportFldObj('Source Id')
             ,'business_name'            => $phpExcel->getImportFldObj('Business Name')
             ,'business_name_local_lang' => $phpExcel->getImportFldObj('Chinese Name')
             ,'category_id'              => $phpExcel->getImportFldObj('Category')
             ,'subCatDesc'               => $phpExcel->getImportFldObj('Sub Category Caption')
             ,'sub_category_id'          => $phpExcel->getImportFldObj('Sub Category')
             ,'business_type'            => $phpExcel->getImportFldObj('Business Type')
             ,'status'                   => $phpExcel->getImportFldObj('Status')
             ,'description'              => $phpExcel->getImportFldObj('Description')
             ,'phone'                    => $phpExcel->getImportFldObj('Phone')
             ,'fax'                      => $phpExcel->getImportFldObj('Fax')
             ,'mobile'                   => $phpExcel->getImportFldObj('Mobile')
             ,'email'                    => $phpExcel->getImportFldObj('Email')
             ,'website'                  => $phpExcel->getImportFldObj('Website')
             ,'address_floor_from'       => $phpExcel->getImportFldObj('Floor')
             ,'address_unit_from'        => $phpExcel->getImportFldObj('Unit')
             ,'address1'                 => $phpExcel->getImportFldObj('Building Name')
             ,'address_careof'           => $phpExcel->getImportFldObj('Careof')

             ,'address_street_no_from'    => $phpExcel->getImportFldObj('Street Number From')
             ,'address_street_letter_from'=> $phpExcel->getImportFldObj('Street Letter From')
             ,'address_street_no_to'      => $phpExcel->getImportFldObj('Street Number To')
             ,'address_street_letter_to'  => $phpExcel->getImportFldObj('Street Letter From')

             ,'country_id'             => $phpExcel->getImportFldObj('Country')
             ,'state_id'               => $phpExcel->getImportFldObj('State')
             ,'city_id'                => $phpExcel->getImportFldObj('City')
             ,'borough_id'             => $phpExcel->getImportFldObj('Borough')
             ,'area_id'                => $phpExcel->getImportFldObj('Area')
             ,'street_id'              => $phpExcel->getImportFldObj('Street')
             ,'address_po_code'        => $phpExcel->getImportFldObj('Zip Code')

             ,'shop_center_id'         => $phpExcel->getImportFldObj('Shop Centre')
             ,'business_group_id'      => $phpExcel->getImportFldObj('Business Group')

             ,'transport_name'         => $phpExcel->getImportFldObj('Transport Name')
             ,'transport_carrier'      => $phpExcel->getImportFldObj('Transport Carrier')

             ,'latitude'               => $phpExcel->getImportFldObj('Latitude')
             ,'longitude'              => $phpExcel->getImportFldObj('Longitude')
             ,'easting'                => $phpExcel->getImportFldObj('Easting')
             ,'northing'               => $phpExcel->getImportFldObj('Northing')

             ,'published'              => $phpExcel->getImportFldObj('Published')
             ,'picture'                => $phpExcel->getImportFldObj('Picture')
             ,'tags_eng'               => $phpExcel->getImportFldObj('Tags (Eng)')
             ,'tags_chi'               => $phpExcel->getImportFldObj('Tags (Chi)')

             ,'entry_concession'       => $phpExcel->getImportFldObj('Entry Concession')
             ,'feature_price'          => $phpExcel->getImportFldObj('Feature Price')
             ,'feature_cuisine'        => $phpExcel->getImportFldObj('Feature Cuisine')
             ,'feature_atm'            => $phpExcel->getImportFldObj('Feature ATM')
             ,'feature_parties'        => $phpExcel->getImportFldObj('Feature Parties')
             ,'feature_wifi'           => $phpExcel->getImportFldObj('Feature Wifi')
        );

        /******** SPECIAL MANIPULATIONS ********/
        $fa['published']['defaultValue'] = 1;
        //$fa['picture']['refOnly'] = true;
        $fa['tags_eng']['refOnly'] = true;
        $fa['tags_chi']['refOnly'] = true;
        $fa['subCatDesc']['refOnly'] = true;

        $fa['category_id']['specialType'] = 'category';
        $fa['category_id']['exp'] = array('sectionType' => 'Directory');

        $fa['sub_category_id']['specialType'] = 'subCategory';
        $fa['sub_category_id']['exp'] = array(
             'categoryFldKeyInArr' => 'category_id'
            ,'shortDescFldKeyInArr' => 'subCatDesc'
        );

        $fa['country_id']['specialType'] = 'fetchIdFromRefModule';
        $fa['country_id']['exp'] = array('refModule' => 'common_country');

        $fa['state_id']['specialType'] = 'fetchIdFromRefModule';
        $fa['state_id']['exp'] = array(
             'refModule' => 'directory_state'
            ,'extraFldsOnCreation' => array('country_id')
            ,'extraFldsInSqlCondn' => array('country_id')
        );

        $fa['city_id']['specialType'] = 'fetchIdFromRefModule';
        $fa['city_id']['exp'] = array(
             'refModule' => 'directory_city'
            ,'extraFldsOnCreation' => array('country_id', 'state_id')
            ,'extraFldsInSqlCondn' => array('country_id')
        );

        $fa['borough_id']['specialType'] = 'fetchIdFromRefModule';
        $fa['borough_id']['exp'] = array(
             'refModule' => 'directory_borough'
            ,'extraFldsOnCreation' => array('country_id', 'state_id', 'city_id')
            ,'extraFldsInSqlCondn' => array('country_id', 'city_id')
        );

        $fa['area_id']['specialType'] = 'fetchIdFromRefModule';
        $fa['area_id']['exp'] = array(
             'refModule' => 'directory_area'
            ,'extraFldsOnCreation' => array('country_id', 'state_id', 'city_id', 'borough_id')
            ,'extraFldsInSqlCondn' => array('country_id', 'city_id')
        );

        $fa['street_id']['specialType'] = 'fetchIdFromRefModule';
        $fa['street_id']['exp'] = array(
             'refModule' => 'directory_street'
            ,'extraFldsOnCreation' => array('country_id', 'state_id', 'city_id', 'borough_id', 'area_id')
            ,'extraFldsInSqlCondn' => array('country_id', 'city_id', 'area_id')
        );

        $fa['shop_center_id']['specialType'] = 'fetchIdFromRefModule';
        $fa['shop_center_id']['exp'] = array(
             'refModule' => 'directory_shopCenter'
            ,'extraFldsOnCreation' => array('country_id', 'state_id', 'city_id', 'area_id')
            ,'extraFldsInSqlCondn' => array('country_id', 'city_id', 'area_id')
        );

        $fa['business_group_id']['specialType'] = 'fetchIdFromRefModule';
        $fa['business_group_id']['exp'] = array(
             'refModule' => 'directory_businessGroup'
        );
        /****************************************/
        $config = array(
             'module'           => 'directory_business'
            ,'matchFieldArr'    => array('business_name', 'source_id')
            ,'mandatoryFldsArr' => array('business_name', 'country_id')
            ,'fldsArr'          => $fa
            ,'callbackAfterInsert' => array($this, 'callbackAfterImportInsert')
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function callbackAfterImportInsert_xxx($business_id, $fa) {
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');

        if ($fa['picture'] != ''){
            $picture = "IMG_{$fa['picture']}.JPG";
            $sourceFilePath = realpath('../media_import') . "/{$picture}";
            $exp = array(
                 'srcFile' => $sourceFilePath
                ,'actualFileName' => $picture
            );
            //$media->model->createMedia('directory_business', 'picture', $business_id, $exp);
            //print $picture . "<br>";
        }

        // if (isset($fa['tags_chi'])){
        //     getCPModuleObj('web2_tags')->model->updateCloudTagsByCSV('Business Chinese', $business_id, $fa, 'tags_chi', 'chi');
        // }

        // if (isset($fa['tags_eng'])){
        //     getCPModuleObj('web2_tags')->model->updateCloudTagsByCSV('Business English', $business_id, $fa, 'tags_eng', 'eng');
        // }

        $dbObj = Zend_Registry::get('db');
        print "<hr>overall:" . $dbObj->overallQueryTime . '<hr>';
        $dbObj->overallQueryTime = 0;

        flush();
        ob_flush();
    }

    /**
     *
     */
    function linkPictures_xxx() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');
        set_time_limit(500000);

        print "<img src='images/logo.jpg'><br>";
        flush();
        ob_flush();

        $SQL = "
        SELECT business_id
              ,picture
        FROM business
        WHERE picture IS NOT NULL
        ";

        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)){
            $picture = "{$row['picture']}.jpg";
            //$sourceFilePath = realpath('.');
            $sourceFilePath = realpath('../../resources/business-pics') . "/{$picture}";
            print $picture . "<br>";
            //print $sourceFilePath . "<br>";

            $exp = array(
                 'srcFile' => $sourceFilePath
                ,'actualFileName' => $picture
            );
            $media->model->createMedia('directory_business', 'picture', $row['business_id'], $exp);
        }

        flush();
        ob_flush();
    }

    /**
     *
     */
    function importBusinessHours_xxx() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');
        set_time_limit(50000);

        print "<img src='images/logo.jpg'><br>";
        flush();
        ob_flush();

        $SQL = "
        SELECT bh.*
              ,b.business_id
        FROM business_hours_tmp bh
        JOIN business b ON (b.source_id = bh.b_ID)
        ";

        $weekArr = array(
             'a' => 'Monday'
            ,'b' => 'Tuesday'
            ,'c' => 'Wednesday'
            ,'d' => 'Thursday'
            ,'e' => 'Friday'
            ,'f' => 'Saturday'
            ,'g' => 'Sunday'
        );

        $weekArr2 = array(
             'a' => 1
            ,'b' => 2
            ,'c' => 3
            ,'d' => 4
            ,'e' => 5
            ,'f' => 6
            ,'g' => 7
        );

        $count = 1;
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)){
            for ($i = 'a'; $i <= 'g'; $i++){
                $dayName = strtolower($weekArr[$i]);

                $fldName = "{$i}_{$dayName}_part_";
                $fa = array();
                $fa['business_id'] = $row['business_id'];
                $fa['week_day']    = $weekArr2[$i];
                $fa['start_time']  = $row[$fldName . '1'];
                $fa['end_time']    = $row[$fldName . '4'];

                $fa['start_time2'] = $row[$fldName . '2'];
                $fa['end_time2']   = $row[$fldName . '3'];

                $id = $fn->addRecord($fa, 'business_hours');
            }

            print $count .'<br>';
            $count++;
        }

        flush();
        ob_flush();
    }

    function getDuplicateAndCloseBusiness() {
        $spActionObj = includeCPClass('Lib', 'SpecialAction');
        $exp = array(
          'afterDuplicateHandler' => array($this, 'afterDuplicateAndCloseBusiness')
        );
        return $spActionObj->getDuplicateRecordByID($exp);
    }

    function afterDuplicateAndCloseBusiness($params) {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $business_id     = $params['record_id'];
        $business_id_new = $params['record_id_new'];

        $rowNew = $fn->getRecordRowByID('business', 'business_id', $business_id_new);

        $source_id = $fn->getSequenceFromSettings('m.directory.business.nextSourceId');

        //update closed record status
        $fa = array();
        $fa['status']          = 'Closed';
        $fa['business_id_new'] = $business_id_new;
        $fa['closed_date']     = $fn->getCurrentTimestamp();
        $whereCondition = "
        WHERE business_id = {$business_id}
        ";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'business', $whereCondition);
        $db->sql_query($SQL);

        //set some default values for duplicated new record
        $fa = array();
        $fa['business_name']        = $rowNew['business_name'] . ' - duplicated';
        $fa['business_id_previous'] = $business_id;
        $fa['source_id']            = $source_id;
        $whereCondition = "
        WHERE business_id = {$business_id_new}
        ";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'business', $whereCondition);
        $db->sql_query($SQL);

        //create business hours history
        $SQL = "
        SELECT business_hours_id
        FROM business_hours
        WHERE business_id = {$business_id}
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $exp = array(
                'sourceTableName' => 'business_hours'
               ,'destTableName' => 'business_hours'
               ,'sourceTableIdName' => 'business_hours_id'
               ,'sourceTableIdValue' => $row['business_hours_id']
               ,'omitFieldsArr' => array('business_hours_id')
               ,'fieldValuesArr' => array(
                   'business_id' => $business_id_new
                  ,'creation_date' => $fn->getCurrentTimestamp()
                  ,'modification_date' => $fn->getCurrentTimestamp()
                 )
            );
            $dbUtil->copyRecordByInsert($exp);
        }

    }

    function getCloseBusiness() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $record_id = $fn->getPostParam('record_id');
        $module    = $fn->getPostParam('room');
        $topRm     = $fn->getPostParam('topRoom');

        //update closed record status
        $fa = array();
        $fa['status']        = 'Closed';
        $fa['closed_date'] = $fn->getCurrentTimestamp();
        $whereCondition = "
        WHERE business_id = {$record_id}
        ";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'business', $whereCondition);
        $db->sql_query($SQL);

        $returnUrl = "index.php?_topRm={$topRm}&module={$module}&_action=detail&record_id={$record_id}";

        $arr = array('status' => 'success', 'returnUrl' => $returnUrl);

        return json_encode($arr);
    }

    function getBulkPromotionValidate() {
        $validate = Zend_Registry::get('validate');
        $validate->resetErrorArray();

        $validate->validateData('title', 'Please enter title');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    function getBulkPromotionSubmit() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getBulkPromotionValidate()){
            return $validate->getErrorMessageXML();
        }

        $record_type         = 'IH';
        $title               = $fn->getPostParam('title');
        $custom_text         = $fn->getPostParam('custom_text');
        $is_happy_hour_promo = $fn->getPostParam('is_happy_hour_promo');
        $start_date          = $fn->getPostParam('start_date');
        $end_date            = $fn->getPostParam('end_date');
        $start_time          = $fn->getPostParam('start_time');
        $end_time            = $fn->getPostParam('end_time');
        $promotion_url       = $fn->getPostParam('promotion_url');
        $days_of_week        = $fn->getPostParam('days_of_week');
        $days_of_week = join(', ', $days_of_week);

        $SQL = "
        SELECT *
        FROM business
        WHERE flag = 1
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $fa = array();
            $fa['business_id']         = $row['business_id'];
            $fa['record_type']         = $record_type;
            $fa['title']               = $title;
            $fa['custom_text']         = $custom_text;
            $fa['is_happy_hour_promo'] = $is_happy_hour_promo;
            $fa['start_date']          = $start_date;
            $fa['end_date']            = $end_date;
            $fa['start_time']          = $start_time;
            $fa['end_time']            = $end_time;
            $fa['promotion_url']       = $promotion_url;
            $fa['days_of_week']        = $days_of_week;
            $fa['creation_date']       = $fn->getCurrentTimestamp();

            $SQL2    = $dbUtil->getInsertSQLStringFromArray($fa, 'promotion');
            $result2 = $db->sql_query($SQL2);
        }

        $retUrl = 'index.php?_topRm=directory&module=directory_business' .
                  '&_action=list&searchDone=1&special_search=Flagged';
        return $validate->getSuccessMessageXML($retUrl);
    }


    function getBulk3rdPartyPromotionValidate() {
        $validate = Zend_Registry::get('validate');
        $validate->resetErrorArray();

        $validate->validateData('title', 'Please enter title');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    function getBulk3rdPartyPromotionSubmit() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getBulkPromotionValidate()){
            return $validate->getErrorMessageXML();
        }

        $card_id             = $fn->getPostParam('card_id');
        $record_type         = '3P';
        $title               = $fn->getPostParam('title');
        $custom_text         = $fn->getPostParam('custom_text');
        $is_happy_hour_promo = $fn->getPostParam('is_happy_hour_promo');
        $start_date          = $fn->getPostParam('start_date');
        $end_date            = $fn->getPostParam('end_date');
        $start_time          = $fn->getPostParam('start_time');
        $end_time            = $fn->getPostParam('end_time');
        $promotion_url       = $fn->getPostParam('promotion_url');
        $days_of_week        = $fn->getPostParam('days_of_week');
        $days_of_week = join(', ', $days_of_week);

        $SQL = "
        SELECT *
        FROM business
        WHERE flag = 1
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $fa = array();
            $fa['business_id']         = $row['business_id'];
            $fa['card_id']             = $card_id;
            $fa['record_type']         = $record_type;
            $fa['title']               = $title;
            $fa['custom_text']         = $custom_text;
            $fa['is_happy_hour_promo'] = $is_happy_hour_promo;
            $fa['start_date']          = $start_date;
            $fa['end_date']            = $end_date;
            $fa['start_time']          = $start_time;
            $fa['end_time']            = $end_time;
            $fa['promotion_url']       = $promotion_url;
            $fa['days_of_week']        = $days_of_week;
            $fa['creation_date']       = $fn->getCurrentTimestamp();

            $SQL2    = $dbUtil->getInsertSQLStringFromArray($fa, 'promotion');
            $result2 = $db->sql_query($SQL2);
        }

        $retUrl = 'index.php?_topRm=directory&module=directory_business' .
                  '&_action=list&searchDone=1&special_search=Flagged';
        return $validate->getSuccessMessageXML($retUrl);
    }

    function setSocialMediaArr() {
        $db = Zend_Registry::get('db');

        $SQL = "
        SELECT url
              ,social_media_id
        FROM social_media
        WHERE url IS NOT NULL
          AND url != ''
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)){
            $parse = parse_url($row['url']);
            $host = str_replace('www.', '', $parse['host']);
            $this->socialMediaArr[$row['social_media_id']] = $host;
        }
    }

    function getScrapSocialMediaUrlsMulti() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        set_time_limit(100000);
        $this->setSocialMediaArr();

        //---------------------------------
        // Turn off output buffering
        ini_set('output_buffering', 'off');
        // Turn off PHP output compression
        ini_set('zlib.output_compression', false);

        //Flush (send) the output buffer and turn off output buffering
        ob_end_flush();
        while (@ob_end_flush());

        // Implicitly flush the buffer(s)
        ini_set('implicit_flush', true);
        ob_implicit_flush(true);

        //prevent apache from buffering it for deflate/gzip
        //header("Content-type: text/plain");
        //header('Cache-Control: no-cache'); // recommended to prevent caching of event data.

        // for($i = 0; $i < 1000; $i++)
        // {
        //     echo ' ';
        // }
        //---------------------------------

        $startId = $fn->getReqParam('startId');
        $endId   = $fn->getReqParam('endId');

        if ($startId == '' || $endId == '') {
            //$startId = 10035001;
            //$endId   = 10035000;

            print "start or end ID not specified\n";
            return;
        }

        $ukCountryId = 3;
        $SQL = "
        SELECT business_id
        FROM business
        WHERE country_id = '{$ukCountryId}'
          AND source_id BETWEEN {$startId} AND {$endId}
          AND website != ''
          AND website IS NOT NULL
          AND social_media_verified = 1
          AND social_media_failed = 0
        ORDER BY source_id
        ";
        $result = $db->sql_query($SQL);

        print $startId . ' to ' . $endId . "<br>\n";

        print date('Y-m-d H:i:s')  . "<br>\n";

        $count = 1;
        while ($row = $db->sql_fetchrow($result)){
            //ob_flush();
            flush();

            print $this->getScrapSocialMediaUrls($row['business_id'], $count);

            //ob_flush();
            flush();
            sleep(1);
            $count++;
        }
        print date('Y-m-d H:i:s')  . "<br>\n";
        print "------------------------------------------------------------\n";
    }

    function getScrapSocialMediaUrls($business_id, $count = 1) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        if (empty($this->socialMediaArr)) {
            $this->setSocialMediaArr();
        }

        print 'counter: ' . $count . "<br>\n";

        $rowB = $fn->getRecordRowByID('business', 'business_id', $business_id);
        if (!$rowB) {
            return "Business not found.";
        }

        $text = '';

        $target_url = $rowB['website'];

        $text .= $rowB['source_id'] . "<br>\n";

        $userAgent = 'Googlebot/2.1 (http://www.googlebot.com/bot.html)';

        // make the cURL request to $target_url
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_URL, $target_url);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $html = curl_exec($ch);
        if (!$html) {
            $text .= "\n<br>cURL error number:" . curl_errno($ch);
            $text .= "\n<br>cURL error:" . curl_error($ch);
            $text .= "------------------------------------------------------------\n";

            $fa = array();
            $fa['social_media_failed'] = 1;

            $whereCondition = "
            WHERE business_id = '{$business_id}'
            ";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'business', $whereCondition);
            $db->sql_query($SQL);
            print "page load error: <br>\n";
            return;
        }


        // parse the html into a DOMDocument
        $dom = new DOMDocument();
        @$dom->loadHTML($html);

        print "page loaded: <br>\n";

        // grab all the on the page
        $xpath = new DOMXPath($dom);
        $hrefs = $xpath->evaluate("/html/body//a");

        $smCount = 0;
        for ($i = 0; $i < $hrefs->length; $i++) {
            $href = $hrefs->item($i);
            $url = $href->getAttribute('href');

            if ($retArr = $fn->striposa($url, $this->socialMediaArr)) {

                $social_media_id = key($retArr);

                //check the social media already in the history table
                $SQL = "
                SELECT 1
                FROM business_social_media
                WHERE url LIKE '%%%s%%'
                  AND business_id = '%s'
                  AND social_media_id = '%s'
                ";
                $SQL = sprintf($SQL, str_replace("'", "\'", $url), $business_id, $social_media_id);
                $result2 = $db->sql_query($SQL);

                $smCount++;

                $rowBSM = $db->sql_fetchrow($result2);
                if ($rowBSM) { //already exists then skip to next
                    continue;
                }

                //store social media links
                $fa = array();
                $fa['business_id']     = $business_id;
                $fa['social_media_id'] = $social_media_id;
                $fa['url']             = $url;
                $fa['creation_date']   = date('Y-m-d H:i:s');

                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'business_social_media');
                $db->sql_query($SQL);

                //switch on the verification flag
                $fa = array();
                $fa['social_media_verified'] = 0;

                $whereCondition = "
                WHERE business_id = '{$business_id}'
                ";
                $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'business', $whereCondition);
                $db->sql_query($SQL);


                $text .= $url . "<br>\n";
            }
        }

        if ($smCount == 0) {
            //if no social media links found
            $fa = array();
            $fa['social_media_failed'] = 1;

            $whereCondition = "
            WHERE business_id = '{$business_id}'
            ";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'business', $whereCondition);
            $db->sql_query($SQL);
        }


        return $text;
    }


    function getAddWatermarkBulkPre() {
        $this->getAddWatermarkBulk('picture');
print "done";
    }

    function getAddWatermarkBulk($recordType) {
        set_time_limit(100000);

        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');
        $mediaArrayObj = Zend_Registry::get('mediaArrayObj');

        $module = 'directory_business';
        $mediaArrayObj->setMediaArray($module);
        /** re-get the mediaArray since it would have more values now **/
        $mediaArray = Zend_Registry::get('mediaArray');

        require_once("imageResize.php");
        $imageResize = new ImageResize();

        $mediaArr = $mediaArray[$module][$recordType];

        $imgQuality = 100;
        $hasWatermark  = $mediaArr['hasWatermark'];
        $watermarkLargeFontSize = '';
        $watermarkNormalFontSize = '';
        $watermarkMediumFontSize = '';
        $watermarkText = $mediaArr['watermarkText'];
        $watermarkLargeFontSize = $mediaArr['watermarkLargeFontSize'];

        $watermarkNormalFontSize = intval($watermarkLargeFontSize * ($mediaArr['maxWidthN'] / $mediaArr['maxWidthL']));
        $watermarkMediumFontSize = intval($watermarkLargeFontSize * ($mediaArr['maxWidthM'] / $mediaArr['maxWidthL']));

        //-------------------//
        $imageSourceFolder = realpath('./../media/');
        $largeImageFolder  = 'large';
        $normalImageFolder = 'normal';

        //max business id: 1068481

        $country_id = 2;
        $SQL = "
		SELECT business_id
                      ,business_name
		FROM business
		WHERE creation_date <= '2013-02-15 00:00:00'
		  AND country_id = 2
        ORDER BY business_id
		LIMIT 4002, 15000
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)){
            $picArr  = $media->getMediaFilesArray($module, 'picture', $row['business_id']);
            foreach ($picArr as $arr) {
                $file_name = $arr['file_name'];

                // large image
                $srcFile = $imageSourceFolder . '/' . $largeImageFolder . '/' . $file_name;
                $dest = $srcFile;
                if (file_exists($srcFile)) {
                    $imageResize->watermarkImage($srcFile, $watermarkText, $dest, $watermarkLargeFontSize);

                    print "{$row['business_id']}: {$row['business_name']}<br>\n";
                }

                // normal image
                $srcFile = $imageSourceFolder . '/' . $normalImageFolder . '/' . $file_name;
                $dest = $srcFile;
                if (file_exists($srcFile)) {
                    $imageResize->watermarkImage($srcFile, $watermarkText, $dest, $watermarkNormalFontSize);
                }

            }
        }
    }

}
