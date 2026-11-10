<?php
class CP_Admin_Modules_Directory_Business_ImportDataUK
{

    var $country_id = '';

    function __construct() {
        $fn = Zend_Registry::get('fn');
    }


    public function importData() {
        ini_set("memory_limit", "1000M");
        set_time_limit(50000);
        ini_set('display_errors', 1);

        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');

        $startTime = time();
        $endTime = null;

        $fileName = 'LDC_DATA_05MAR';

        $filesArr = array(
            $fileName . '_NEW',
            $fileName . '_MODIFIED',
            $fileName . '_CLOSE',
        );


        foreach ($filesArr as $fileName) {
            print $cpUtil->getMemoryUsage() . "<br>";

            //$csvPath = realpath('../../resources/data/UK Data/Partials/LDC_DATA_29JAN_NEW.csv');

            $business_status = strtolower(substr($fileName, strrpos($fileName, '_') + 1));

            $fileName = $fileName . '.csv';
            $csvPath = realpath('../../resources/data/UK Data/Partials/' . $fileName);
            $csvPath = str_replace("\\", '/', $csvPath);

            $SQL = "
            TRUNCATE TABLE business_tmp
            ";
            $db->sql_query($SQL);

            $SQL = "
            LOAD DATA LOCAL INFILE '{$csvPath}'
            INTO TABLE business_tmp
            FIELDS TERMINATED BY ','
            ENCLOSED BY '\"'
            LINES TERMINATED BY '\r\n'
            IGNORE 1 LINES;
            ";
            $db->sql_query($SQL);

            $start = 0;
            $limit = 10000000;
            $this->importDataActual($start, $limit, $business_status);

        }

        $endTime = time();

        $processTime = $endTime - $startTime;
        $processTime = $endTime;


        print "<h3>Import completed</h3>";

    }

    public function importDataActual($start, $limit, $business_status = '') {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        //die('disabled....');

        $SQL = "
        SELECT *
        FROM business_tmp
        ORDER BY b_ID
        LIMIT {$start}, {$limit}
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows();

        $rows = '';
        $rowCounter = 0;

        $sectionType = 'Business';

        $country = 'United Kingdom';
        $this->country_id = $fn->getRecordIdByTitle('common_country', $country);

        while ($row = $db->sql_fetchrow($result)) {
            //print $row['b_ID'] . "<br>";
            //continue;

            $business_id       = 0;

            $category_id       = 0;
            $category          = $row['cat_Name1'];
            $sub_category_id   = 0;
            $subCategory       = $row['cat_Name2'];
            $business_group_id = 0;
            $business_group    = $row['b_multipleName'];
            $source_id = $row['b_ID'];

            $state_id          = 0;
            $state             = $row['ad_county'];
            $city_id           = 0;
            $city              = $row['ad_City'];
            $borough_id        = 0;
            $borough           = $row['borough_county'];
            $area_id           = 0;
            $area              = $row['area_name'];
            $street_id         = 0;
            $street            = $row['ad_Street'];
            $shop_center_id    = 0;
            $shop_center       = $row['ad_shop_centre'];
            $building_id       = 0;
            $building          = $row['ad_Building'];
            if (trim($building) == '') {
                $building = $row['ad_StreetNo'] . ' ' .  $row['ad_Street'];
            }


            if ($business_status == 'close') {
                $fa = array();
                $fa['modification_date'] = date('Y-m-d H:i:s');
                $fa['closed_date']       = date('Y-m-d');
                $fa['status']            = 'Closed';

                $whereCondition = "
                WHERE source_id = '{$source_id}'
                  AND country_id = '{$this->country_id}'
                ";
                $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'business', $whereCondition);
                $db->sql_query($SQL);
                continue;
            }

            //create category
            if ($category != ''){
                 $category_id = getCPModelObj('webBasic_category')
                                ->getCatIdByTitleWithAutoCreate($category, $sectionType);
            }

            //create sub category
            if ($subCategory != ''){
                $sub_category_id = getCPModelObj('webBasic_subCategory')
                                   ->getSubCatIdByTitleWithAutoCreate($subCategory, $category_id);
            }

            //create state
            if ($state != ''){
                $exp = array(
                     'extraFldsInSqlCondnArr' => array('country_id' => $this->country_id)
                    ,'extraFldsOnCreationArr' => array('country_id' => $this->country_id)
                );
                $state_id = $fn->getRecordIdByTitleWithAutoCreate('directory_state', $state, $exp);
            }

            //create city
            if ($city != ''){
                $exp = array(
                     'extraFldsInSqlCondnArr' => array(
                          'country_id' => $this->country_id
                         ,'state_id' => $state_id
                     )
                    ,'extraFldsOnCreationArr' => array(
                         'country_id' => $this->country_id
                         ,'state_id' => $state_id
                     )
                );
                $city_id = $fn->getRecordIdByTitleWithAutoCreate('directory_city', $city, $exp);
            }

            //create borough
            if ($borough != ''){
                $exp = array(
                     'extraFldsInSqlCondnArr' => array(
                          'country_id' => $this->country_id
                         ,'state_id' => $state_id
                         ,'city_id' => $city_id
                     )
                    ,'extraFldsOnCreationArr' => array(
                          'country_id' => $this->country_id
                         ,'state_id' => $state_id
                         ,'city_id' => $city_id
                     )
                );
                $borough_id = $fn->getRecordIdByTitleWithAutoCreate('directory_borough', $borough, $exp);
            }

            //create area
            if ($area != ''){
                $exp = array(
                    'extraFldsInSqlCondnArr' => array(
                         'country_id' => $this->country_id
                        ,'state_id' => $state_id
                        ,'city_id' => $city_id
                        ,'borough_id' => $borough_id
                    )
                   ,'extraFldsOnCreationArr' => array(
                         'country_id' => $this->country_id
                        ,'state_id' => $state_id
                        ,'city_id' => $city_id
                        ,'borough_id' => $borough_id
                    )
                );
                $area_id = $fn->getRecordIdByTitleWithAutoCreate('directory_area', $area, $exp);
            }

            //create street
            if ($street != ''){
                $exp = array(
                    'extraFldsInSqlCondnArr' => array(
                         'country_id' => $this->country_id
                        ,'state_id' => $state_id
                        ,'city_id' => $city_id
                        ,'borough_id' => $borough_id
                        ,'area_id' => $area_id
                    )
                   ,'extraFldsOnCreationArr' => array(
                         'country_id' => $this->country_id
                        ,'state_id' => $state_id
                        ,'city_id' => $city_id
                        ,'borough_id' => $borough_id
                        ,'area_id' => $area_id
                    )
                );
                $street_id = $fn->getRecordIdByTitleWithAutoCreate('directory_street', $street, $exp);
            }

            //create shop center / shopping mall
            if ($shop_center != ''){
                $exp = array(
                    'extraFldsInSqlCondnArr' => array(
                         'country_id' => $this->country_id
                        ,'state_id' => $state_id
                        ,'city_id' => $city_id
                        ,'borough_id' => $borough_id
                        ,'area_id' => $area_id
                        ,'street_id' => $street_id
                    )
                   ,'extraFldsOnCreationArr' => array(
                         'country_id' => $this->country_id
                        ,'state_id' => $state_id
                        ,'city_id' => $city_id
                        ,'borough_id' => $borough_id
                        ,'area_id' => $area_id
                        ,'street_id' => $street_id
                    )
                );
                $shop_center_id = $fn->getRecordIdByTitleWithAutoCreate(
                                      'directory_shopCenter', $shop_center, $exp);
            }

            //building
            if ($building != ''){
                $exp = array(
                    'extraFldsInSqlCondnArr' => array(
                         'country_id' => $this->country_id
                        ,'state_id' => $state_id
                        ,'city_id' => $city_id
                        ,'borough_id' => $borough_id
                        ,'area_id' => $area_id
                        ,'street_id' => $street_id
                    )
                   ,'extraFldsOnCreationArr' => array(
                         'country_id' => $this->country_id
                        ,'state_id' => $state_id
                        ,'city_id' => $city_id
                        ,'borough_id' => $borough_id
                        ,'area_id' => $area_id
                        ,'street_id' => $street_id
                        ,'street_no_from' => $row['ad_StreetNo']
                        ,'street_no_to' => $row['ad_StreetNo2']
                        ,'latitude' => $row['geocode_latitude']
                        ,'longitude' => $row['geocode_longitude']
                    )
                );
                $building_id = $fn->getRecordIdByTitleWithAutoCreate(
                               'directory_building', $building, $exp);
            }

            //business group
            if ($business_group != ''){
                $exp = array(
                    'extraFldsInSqlCondnArr' => array()
                   ,'extraFldsOnCreationArr' => array()
                );
                $business_group_id = $fn->getRecordIdByTitleWithAutoCreate(
                                         'directory_businessGroup', $business_group, $exp);
            }

            //check the business record exists
            $SQL = "
            SELECT business_id
                  ,address_id
            FROM business
            WHERE source_id = '{$source_id}'
              AND country_id = '{$this->country_id}'
            ";
            $rowBus = $fn->getRecordBySQL($SQL, MYSQL_ASSOC);

            $address_id = 0;
            if ($rowBus) {
                $address_id = $rowBus['address_id'];
                $business_id = $rowBus['business_id'];
            }

            $expArr = array(
                'business_id' => $business_id,
                'state_id' => $state_id,
                'city_id' => $city_id,
                'borough_id' => $borough_id,
                'area_id' => $area_id,
                'street_id' => $street_id,
                'shop_center_id' => $shop_center_id,
                'building_id' => $building_id,
            );
            $address_id = $this->createUpdateAddress($address_id, $row, $expArr);

            $fa = array();
            $fa['country_id']        = $this->country_id;
            $fa['published']         = 1;
            $fa['source_id']         = $row['b_ID'];
            $fa['business_name']     = $row['b_NameFull'];
            $fa['category_id']       = $category_id;
            $fa['sub_category_id']   = $sub_category_id;
            $fa['business_type']     = ''; //cat_SIC??
            $fa['status']            = '';
            //$fa['opening_time']      = $row['']; //come from business_hours table?? we ignore this field if yes.
            $fa['description']       = $row['export_description']; //verify this
            $fa['phone']             = $row['bus_telephone'];
            $fa['fax']               = ''; //no related field
            $fa['mobile']            = ''; //no related field
            $fa['email']             = ''; //no related field
            $fa['website']           = $row['website_name'];

            $fa['business_group_id'] = $business_group_id;

            $fa['transport_name']    = $row['transport_name'];
            $fa['transport_carrier'] = $row['transport_carrier'];

            $fa['address_id']        = $address_id;

            $fa['entry_concession']              = $row['entry_concession'];
            $fa['feature_avg_main_course_price'] = $row['feature_price'];
            $fa['feature_atm']                   = $row['feature_atm'];
            $fa['feature_parties']               = $row['feature_parties'];
            $fa['feature_wifi']                  = $row['feature_wifi'];
            $fa['modification_date']             = date('Y-m-d H:i:s');
            //$fa['feature_cuisine']               = $row['feature_cuisine'];

            //if business exists already then update
            if ($rowBus) {
                //update
                $whereCondition = "
                WHERE business_id = '{$business_id}'
                ";
                $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'business', $whereCondition);
                $db->sql_query($SQL);

                //updates media url
                $this->createPicture($business_id, $row, true);
            } else {
                $fa['creation_date'] = date('Y-m-d H:i:s');
                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'business');
                $db->sql_query($SQL);
                $business_id = $db->sql_nextid();

                //new image when creating record
                $this->createPicture($business_id, $row);
            }

            if (isset($row['feature_names'])){
                getCPModuleObj('web2_tags')->model
                ->updateCloudTagsByCSV('Business', $business_id, $row, 'feature_names', 'eng');
            }
            $this->createBusinessHours($business_id, $row['opening_times']);
        } //while loop

    }


    function createPicture($business_id, $row, $businessExists = false) {
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        // if ($fa['picture'] != ''){
        //     $picture = "IMG_{$fa['picture']}.JPG";
        //     $sourceFilePath = realpath('../media_import') . "/{$picture}";
        //     $exp = array(
        //          'srcFile' => $sourceFilePath
        //         ,'actualFileName' => $picture
        //     );
        //     //$media->model->createMedia('directory_business', 'picture', $business_id, $exp);
        //     //print $picture . "<br>";
        // }

        if ($row['image_url'] != ''){
            if ($businessExists) {
                $fa = array(
                     'media_url' => $row['image_url'],
                );

                $whereCondition = "
                WHERE record_id = '{$business_id}'
                  AND room_name = 'directory_business'
                ";
                $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'media', $whereCondition);
                $db->sql_query($SQL);
            } else {
                $exp = array(
                     'mediaUrl' => $row['image_url'],
                );
                $media->model->createMedia('directory_business', 'picture', $business_id, $exp);
            }
        }
    }

    function createUpdateAddress($address_id, $rowBT, $exp) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        //check the business record exists
        $SQL = "
        SELECT 1
        FROM address
        WHERE address_id = '{$address_id}'
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows();

        $fa['country_id']      = $this->country_id;
        $fa['state_id']        = $exp['state_id'];
        $fa['city_id']         = $exp['city_id'];
        $fa['borough_id']      = $exp['borough_id'];
        $fa['area_id']         = $exp['area_id'];
        $fa['street_id']       = $exp['street_id'];
        $fa['building_id']     = $exp['building_id'];
        $fa['shop_center_id']  = $exp['shop_center_id'];
        $fa['address_po_code'] = $rowBT['ad_Zip'];

        $fa['address_floor_from'] = $rowBT['ad_Building_Floor'];
        $fa['address_unit_from']  = $rowBT['ad_Building_Unit'];
        $fa['modification_date']  = date('Y-m-d H:i:s');


        //if business exists already then update
        if ($numRows > 0) {
            //update
            $whereCondition = "
            WHERE address_id = '{$address_id}'
            ";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'address', $whereCondition);
            $db->sql_query($SQL);
        } else {
            $fa['creation_date']  = date('Y-m-d H:i:s');
            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'address');
            $db->sql_query($SQL);
            $address_id = $db->sql_nextid();
        }

        return $address_id;
    }

    public function createBusinessHours($business_id, $businessHours) {
        //$business_hours example
        //Tue 10:00-18:00, Wed 10:00-18:00, Thu 10:00-18:00, Fri 10:00-18:00, Sat 10:00-18:00,

        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $SQL = "
        DELETE FROM
        business_hours
        WHERE business_id = '{$business_id}'
        ";
        $db->sql_query($SQL);

        $businessHours = trim($businessHours, ', ');
        $busHrArr = explode(',', $businessHours);
        $busHrArr = array_map('trim', $busHrArr);

        $weekArr = array(
            'Mon' => '',
            'Tue' => '',
            'Wed' => '',
            'Thu' => '',
            'Fri' => '',
            'Sat' => '',
            'Sun' => '',
        );

        //$timeText ex: Tue 10:00-18:00
        foreach ($busHrArr as $timeText) {
            $day = $openHrs = '';
            if ($timeText != '') {
                list($day, $openHrs) = explode(' ', $timeText);
            }
            $weekArr[$day] = $openHrs;
        }

        $values = '';
        $dayNum = 1;

        //$openHrs ex: 10:00-18:00
        foreach ($weekArr as $day => $openHrs) {
            $start_time = $end_time2 = '';
            if ($openHrs != '') {
                list($start_time, $end_time2) = explode('-', $openHrs);
            }

            $fields = array(
                'business_id' => $business_id,
                'week_day' => $dayNum,
                'start_time' => $start_time,
                'end_time2' => $end_time2,
                'creation_date' => $fn->getCurrentTimestamp(),
                'modification_date' => $fn->getCurrentTimestamp(),
            );
            $values .= sprintf('("%s")', implode('","',array_values($fields))) . ',';
            $dayNum++;
        }
        $values = trim($values, ',');
        $SQL = sprintf('%s (%s) VALUES %s',
                       'INSERT INTO business_hours ',
                       implode(',',array_keys($fields)),
                       $values
               );
        $db->sql_query($SQL);
    }

    public function importTags() {
        set_time_limit(50000);
        ini_set("memory_limit","150M");

        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $SQL = "
        SELECT b.source_id
              ,b.business_id
              ,bt.feature_names
        FROM business_tmp bt
        JOIN business b ON (b.source_id = bt.b_ID)
        ORDER BY b_ID
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows();

        $rows = '';
        $rowCounter = 0;

        while ($row = $db->sql_fetchrow($result)) {
            //print round(memory_get_usage() / 1024, 2) . " KB<br>";
            if ($row['feature_names'] != ''){
                getCPModuleObj('web2_tags')->model
                ->updateCloudTagsByCSV('Business', $row['business_id'], $row, 'feature_names', 'eng');

            }
            //print "------------------------<br>";
        }
        print "<h3>Tag linking completed</h3>";
    }

    public function importData_old() {
        ini_set("memory_limit", "1000M");
        set_time_limit(50000);
        ini_set('display_errors', 1);

        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');

        $startTime = time();
        $endTime = null;


        print $cpUtil->getMemoryUsage() . "<br>";

        $SQL = "
        SELECT count(*) AS count
        FROM business_tmp
        ORDER BY b_ID
        ";
        $result = $db->sql_query($SQL);

        $row = $db->sql_fetchrow($result);
        $total = $row['count'];
        $batch = 50000;

        // $total = 20;
        // $batch = 10;

        $counter = 0;
        while ($counter < $total) {
            $this->importDataActual($counter, $batch);
            $counter += $batch;
            print '--' . $cpUtil->getMemoryUsage() . "<br>";
        }

        $endTime = time();

        $processTime = $endTime - $startTime;
        $processTime = $endTime;


        print "<h3>Import completed</h3>";

    }

    public function importDataActual_old($start, $limit) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        //die('disabled....');

        $SQL = "
        SELECT *
        FROM business_tmp
        ORDER BY b_ID
        LIMIT {$start}, {$limit}
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows();

        $rows = '';
        $rowCounter = 0;

        $sectionType = 'Business';

        $country = 'United Kingdom';
        $this->country_id = $fn->getRecordIdByTitle('common_country', $country);

        while ($row = $db->sql_fetchrow($result)) {
            //print $row['b_ID'] . "<br>";
            //continue;

            $business_id       = 0;

            $category_id       = 0;
            $category          = $row['cat_Name1'];
            $sub_category_id   = 0;
            $subCategory       = $row['cat_Name2'];
            $business_group_id = 0;
            $business_group    = $row['b_multipleName'];
            $source_id = $row['b_ID'];

            $state_id          = 0;
            $state             = $row['ad_county'];
            $city_id           = 0;
            $city              = $row['ad_City'];
            $borough_id        = 0;
            $borough           = $row['borough_county'];
            $area_id           = 0;
            $area              = $row['area_name'];
            $street_id         = 0;
            $street            = $row['ad_Street'];
            $shop_center_id    = 0;
            $shop_center       = $row['ad_shop_centre'];
            $building_id       = 0;
            $building          = $row['ad_Building'];
            if (trim($building) == '') {
                $building = $row['ad_StreetNo'] . ' ' .  $row['ad_Street'];
            }

            //create category
            if ($category != ''){
                 $category_id = getCPModelObj('webBasic_category')
                                ->getCatIdByTitleWithAutoCreate($category, $sectionType);
            }

            //create sub category
            if ($subCategory != ''){
                $sub_category_id = getCPModelObj('webBasic_subCategory')
                                   ->getSubCatIdByTitleWithAutoCreate($subCategory, $category_id);
            }

            //create state
            if ($state != ''){
                $exp = array(
                     'extraFldsInSqlCondnArr' => array('country_id' => $this->country_id)
                    ,'extraFldsOnCreationArr' => array('country_id' => $this->country_id)
                );
                $state_id = $fn->getRecordIdByTitleWithAutoCreate('directory_state', $state, $exp);
            }

            //create city
            if ($city != ''){
                $exp = array(
                     'extraFldsInSqlCondnArr' => array(
                          'country_id' => $this->country_id
                         ,'state_id' => $state_id
                     )
                    ,'extraFldsOnCreationArr' => array(
                         'country_id' => $this->country_id
                         ,'state_id' => $state_id
                     )
                );
                $city_id = $fn->getRecordIdByTitleWithAutoCreate('directory_city', $city, $exp);
            }

            //create borough
            if ($borough != ''){
                $exp = array(
                     'extraFldsInSqlCondnArr' => array(
                          'country_id' => $this->country_id
                         ,'state_id' => $state_id
                         ,'city_id' => $city_id
                     )
                    ,'extraFldsOnCreationArr' => array(
                          'country_id' => $this->country_id
                         ,'state_id' => $state_id
                         ,'city_id' => $city_id
                     )
                );
                $borough_id = $fn->getRecordIdByTitleWithAutoCreate('directory_borough', $borough, $exp);
            }

            //create area
            if ($area != ''){
                $exp = array(
                    'extraFldsInSqlCondnArr' => array(
                         'country_id' => $this->country_id
                        ,'state_id' => $state_id
                        ,'city_id' => $city_id
                        ,'borough_id' => $borough_id
                    )
                   ,'extraFldsOnCreationArr' => array(
                         'country_id' => $this->country_id
                        ,'state_id' => $state_id
                        ,'city_id' => $city_id
                        ,'borough_id' => $borough_id
                    )
                );
                $area_id = $fn->getRecordIdByTitleWithAutoCreate('directory_area', $area, $exp);
            }

            //create street
            if ($street != ''){
                $exp = array(
                    'extraFldsInSqlCondnArr' => array(
                         'country_id' => $this->country_id
                        ,'state_id' => $state_id
                        ,'city_id' => $city_id
                        ,'borough_id' => $borough_id
                        ,'area_id' => $area_id
                    )
                   ,'extraFldsOnCreationArr' => array(
                         'country_id' => $this->country_id
                        ,'state_id' => $state_id
                        ,'city_id' => $city_id
                        ,'borough_id' => $borough_id
                        ,'area_id' => $area_id
                    )
                );
                $street_id = $fn->getRecordIdByTitleWithAutoCreate('directory_street', $street, $exp);
            }

            //create shop center / shopping mall
            if ($shop_center != ''){
                $exp = array(
                    'extraFldsInSqlCondnArr' => array(
                         'country_id' => $this->country_id
                        ,'state_id' => $state_id
                        ,'city_id' => $city_id
                        ,'borough_id' => $borough_id
                        ,'area_id' => $area_id
                        ,'street_id' => $street_id
                    )
                   ,'extraFldsOnCreationArr' => array(
                         'country_id' => $this->country_id
                        ,'state_id' => $state_id
                        ,'city_id' => $city_id
                        ,'borough_id' => $borough_id
                        ,'area_id' => $area_id
                        ,'street_id' => $street_id
                    )
                );
                $shop_center_id = $fn->getRecordIdByTitleWithAutoCreate(
                                      'directory_shopCenter', $shop_center, $exp);
            }

            //building
            if ($building != ''){
                $exp = array(
                    'extraFldsInSqlCondnArr' => array(
                         'country_id' => $this->country_id
                        ,'state_id' => $state_id
                        ,'city_id' => $city_id
                        ,'borough_id' => $borough_id
                        ,'area_id' => $area_id
                        ,'street_id' => $street_id
                    )
                   ,'extraFldsOnCreationArr' => array(
                         'country_id' => $this->country_id
                        ,'state_id' => $state_id
                        ,'city_id' => $city_id
                        ,'borough_id' => $borough_id
                        ,'area_id' => $area_id
                        ,'street_id' => $street_id
                        ,'street_no_from' => $row['ad_StreetNo']
                        ,'street_no_to' => $row['ad_StreetNo2']
                        ,'latitude' => $row['geocode_latitude']
                        ,'longitude' => $row['geocode_longitude']
                    )
                );
                $building_id = $fn->getRecordIdByTitleWithAutoCreate(
                               'directory_building', $building, $exp);
            }

            //business group
            if ($business_group != ''){
                $exp = array(
                    'extraFldsInSqlCondnArr' => array()
                   ,'extraFldsOnCreationArr' => array()
                );
                $business_group_id = $fn->getRecordIdByTitleWithAutoCreate(
                                         'directory_businessGroup', $business_group, $exp);
            }

            //check the business record exists
            $SQL = "
            SELECT business_id
                  ,address_id
            FROM business
            WHERE source_id = '{$source_id}'
              AND country_id = '{$this->country_id}'
            ";
            $rowBus = $fn->getRecordBySQL($SQL, MYSQL_ASSOC);

            $address_id = 0;
            if ($rowBus) {
                $address_id = $rowBus['address_id'];
                $business_id = $rowBus['business_id'];
            }

            $expArr = array(
                'business_id' => $business_id,
                'state_id' => $state_id,
                'city_id' => $city_id,
                'borough_id' => $borough_id,
                'area_id' => $area_id,
                'street_id' => $street_id,
                'shop_center_id' => $shop_center_id,
                'building_id' => $building_id,
            );
            $address_id = $this->createUpdateAddress($address_id, $row, $expArr);

            $fa = array();
            $fa['country_id']        = $this->country_id;
            $fa['published']         = 1;
            $fa['source_id']         = $row['b_ID'];
            $fa['business_name']     = $row['b_NameFull'];
            $fa['category_id']       = $category_id;
            $fa['sub_category_id']   = $sub_category_id;
            $fa['business_type']     = ''; //cat_SIC??
            $fa['status']            = '';
            //$fa['opening_time']      = $row['']; //come from business_hours table?? we ignore this field if yes.
            $fa['description']       = $row['export_description']; //verify this
            $fa['phone']             = $row['bus_telephone'];
            $fa['fax']               = ''; //no related field
            $fa['mobile']            = ''; //no related field
            $fa['email']             = ''; //no related field
            $fa['website']           = $row['website_name'];

            $fa['business_group_id'] = $business_group_id;

            $fa['transport_name']    = $row['transport_name'];
            $fa['transport_carrier'] = $row['transport_carrier'];

            $fa['address_id']        = $address_id;

            $fa['entry_concession']              = $row['entry_concession'];
            $fa['feature_avg_main_course_price'] = $row['feature_price'];
            $fa['feature_atm']                   = $row['feature_atm'];
            $fa['feature_parties']               = $row['feature_parties'];
            $fa['feature_wifi']                  = $row['feature_wifi'];
            $fa['modification_date']             = date('Y-m-d H:i:s');
            //$fa['feature_cuisine']               = $row['feature_cuisine'];

            //if business exists already then update
            if ($rowBus) {
                //update
                $whereCondition = "
                WHERE business_id = '{$business_id}'
                ";
                $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'business', $whereCondition);
                $db->sql_query($SQL);

                //updates media url
                $this->createPicture($business_id, $row, true);
            } else {
                $fa['creation_date'] = date('Y-m-d H:i:s');
                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'business');
                $db->sql_query($SQL);
                $business_id = $db->sql_nextid();

                //new image when creating record
                $this->createPicture($business_id, $row);
            }

            if (isset($row['feature_names'])){
                getCPModuleObj('web2_tags')->model
                ->updateCloudTagsByCSV('Business', $business_id, $row, 'feature_names', 'eng');
            }
            $this->createBusinessHours($business_id, $row['opening_times']);
        } //while loop

    }

}
