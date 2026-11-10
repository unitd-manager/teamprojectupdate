<?php
class CP_Admin_Modules_Directory_Business_ImportDataHK
{

    var $country_id = '';
    var $log_text1 = '';
    var $log_text2 = '';

    function __construct() {
        $fn = Zend_Registry::get('fn');

    }

    function setCountryCode(){
        $fn = Zend_Registry::get('fn');

        $countrySQL = "
        SELECT country_id
        FROM country
        WHERE title = 'Hong Kong'
        ";
        $rowCountry = $fn->getRecordBySQL($countrySQL);
        $this->country_id = $rowCountry['country_id'];
    }
    
    /**
     *
     * http://nearer.localhost/admin/index.php?_topRm=directory&module=directory_business&_spAction=importPhotos
     */
    function getImportPhotos(){
        $fn = Zend_Registry::get('fn');
        set_time_limit(50000);

        $this->setCountryCode();
        
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');

        $fa = array(
            'business_name' => $phpExcel->getImportFldObj('Business Name'),
            'business_name_local' => $phpExcel->getImportFldObj('Business Name Local'),
            'category' => $phpExcel->getImportFldObj('Category'),
            'sub_category' => $phpExcel->getImportFldObj('Sub Category'),
            'street_no_from' => $phpExcel->getImportFldObj('Street From'),
            'street_no_to' => $phpExcel->getImportFldObj('Street To'),
            'street_name' => $phpExcel->getImportFldObj('Street'),
            'picture_ref' => $phpExcel->getImportFldObj('Picture Reference'),
            'folder' => $phpExcel->getImportFldObj('Folder'),
        );
        //$fa['country_id']['defaultValue'] = $rowCountry['country_id'];

        $excelFile = realpath('../../resources/data/HK Data/HKphotos.xls');

        $config = array(
            'module' => 'directory_business',
            'overrideDefaultProcessCallback' => array($this, 'createPicture'),
            'fldsArr' => $fa,
            'excelFilePath' => $excelFile,
        );

        return $phpExcel->importData($config);
    }

    function createPicture($fa, $curRow, $phpExcel) {
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $business_name       = trim($phpExcel->getExcelFieldValue('Business Name', $curRow));
        $business_name_local = trim($phpExcel->getExcelFieldValue('Business Name Local', $curRow));
        $category            = trim($phpExcel->getExcelFieldValue('Category', $curRow));
        $sub_category        = trim($phpExcel->getExcelFieldValue('Sub Category', $curRow));
        $street_no_from      = trim($phpExcel->getExcelFieldValue('Street From', $curRow));
        $street_no_to        = trim($phpExcel->getExcelFieldValue('Streent To', $curRow));
        $street_name         = trim($phpExcel->getExcelFieldValue('Street', $curRow));
        $picture_ref         = trim($phpExcel->getExcelFieldValue('Picture Reference', $curRow));
        $areaFolder          = trim($phpExcel->getExcelFieldValue('Folder', $curRow));

        if ($picture_ref == ''){
            return;
        }

        $street_name_sql     = escapeForDb($street_name);
        $business_name       = escapeForDb($business_name);
        $business_name_local = escapeForDb($business_name_local);
        
        $whereBusName = "AND b.business_name = '{$business_name}'";
        if ($business_name == '') {
            $whereBusName = "AND b.business_name_local_lang = '{$business_name_local}'";
        }
        
        $SQL = "
        SELECT b.business_id
        FROM business b
        JOIN address a ON a.address_id = b.address_id
        JOIN street s ON s.street_id = a.street_id
        WHERE s.title = '{$street_name_sql}'
          {$whereBusName}
          AND a.address_street_no_from = '{$street_no_from}'
          AND a.country_id = '{$this->country_id}'
        ";
        $result = $db->sql_query($SQL);

        $row = $db->sql_fetchrow($result);
        if (!$row) {
            print "Row: {$curRow} not found in db. {$business_name} -- {$business_name_local}<br>";
            return;
        }
        $business_id = $row['business_id'];
        
        $media->model->getDeleteAllMedia('directory_business', 'picture', $business_id);
        $media->model->getDeleteAllMedia('directory_business', 'relatedPicture', $business_id);
        
        //file names ex: 23 -Aberdeen Street.jpg, 23 (I)-Aberdeen Street.jpg, 23 (I2)-Aberdeen Street.jpg
        $actualFileName = $areaFolder . '/' . $street_name . '/' . $picture_ref . '.jpg';
        
        
        $imageSourceFolder = 'D:/Temp/nearer-photos-output/';
        //live server
        $imageSourceFolder = '/var/www/vhosts/nearer.com/httpdocs/temp/nearer-photos-output/';
        
        //D:\Temp\nearer-photos-output\large\Aberdeen Street\10 - C-Aberdeen Street.jpg
        $actualFilePath = $imageSourceFolder . 'large/' . $actualFileName;
        
        if (file_exists($actualFilePath)) {
            $exp = array(
                'actualFileName' => $actualFileName,
                'imageSourceFolder' => $imageSourceFolder,
            );

            $media->model->createMedia('directory_business', 'picture', $business_id, $exp);
        } else {
            print "Image not found: {$actualFilePath}<br>";
        }
        
        $relPicArr = array();
        for ($i = 1; $i < 10; $i++) {
            $picCount = '';
            if ($i > 1) {
                $picCount = $i;
            }
            //285 - A5-Des Voeux Road Central.jpg, 285 - A5 (I)-Des Voeux Road Central.jpg, 285 - A5 (I2)-Des Voeux Road Central.jpg
            $picRefArr = explode('-', $picture_ref);
            $picture_ref_rel = '';
            $arrInd = count($picRefArr) - 2;
            $picRefArr[$arrInd] = $picRefArr[$arrInd] . "(I{$picCount})";
            
            $picture_ref_rel = join('-', $picRefArr);
            
            $actualFilePath = $imageSourceFolder . 'large/' . $areaFolder . '/' . $street_name . '/' . $picture_ref_rel . '.jpg';
            if (file_exists($actualFilePath)) {
                $relPicArr[] = $areaFolder . '/' . $street_name . '/' . $picture_ref_rel . '.jpg';
            }
        }
        
        foreach ($relPicArr as $actualFileName) {
            $exp = array(
                'actualFileName' => $actualFileName,
                'imageSourceFolder' => $imageSourceFolder,
            );
            $media->model->createMedia('directory_business', 'relatedPicture', $business_id, $exp);
        }

        //die();
        return;
    }

    function getImportRestaurants(){
        $fn = Zend_Registry::get('fn');
        set_time_limit(50000);

        $this->setCountryCode();

        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');

        $fa = array(
            'business_name' => $phpExcel->getImportFldObj('Business Name'),
            'business_name_local' => $phpExcel->getImportFldObj('Business Name Local'),
            'category' => $phpExcel->getImportFldObj('Address'),
            'phone' => $phpExcel->getImportFldObj('Phone'),
        );

        $excelFile = realpath('../../resources/data/HK Data/Final/SBR - Restaurants.xls');

        $config = array(
            'module' => 'directory_business',
            'overrideDefaultProcessCallback' => array($this, 'importRestaurantsCB'),
            'fldsArr' => $fa,
            'excelFilePath' => $excelFile,
        );
        $phpExcel->importData($config);
        print $this->log_text1 . '<hr>';
        print $this->log_text2 . '<hr>';

    }

    function importRestaurantsCB($fa, $curRow, $phpExcel) {
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $dbz = Zend_Registry::get('dbz');

        $business_name       = trim($phpExcel->getExcelFieldValue('Name', $curRow));
        $business_name_local = trim($phpExcel->getExcelFieldValue('Chinese', $curRow));
        $street_no_from      = trim($phpExcel->getExcelFieldValue('from', $curRow));
        $street_no_to        = trim($phpExcel->getExcelFieldValue('to', $curRow));
        $street              = trim($phpExcel->getExcelFieldValue('street', $curRow));
        $phone               = trim($phpExcel->getExcelFieldValue('Phone', $curRow));

        //check the building db for the street address
        // $SQL = "
        // SELECT b.building_id
        //       ,b.street_id
        // FROM building b
        // JOIN street s ON s.street_id = b.street_id
        // WHERE s.title = ?
        //   AND b.street_no_from = ?
        //   AND s.country_id = ?
        // ";
        // $stmt = $dbz->query($SQL, array($street, $street_no_from, $this->country_id));
        // $rowB = $stmt->fetch();
        
        //check the address exists in the buiding/street tables
        $SQL = "
        SELECT b.building_id
              ,b.street_id
        FROM building b
        JOIN street s ON s.street_id = b.street_id
        WHERE s.title = '" . escapeForDb($street) . "'
          AND b.street_no_from = '{$street_no_from}'
          AND s.country_id = '{$this->country_id}'
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows();
        $rowB = $db->sql_fetchrow($result);
        
        if (!$rowB) {
            $this->log_text1 .= "Address: {$street}, {$street_no_from} - {$street_no_to}: not found<br>";
            return;
        }

        $building_id = $rowB['building_id'];
        $street_id   = $rowB['street_id'];
        
        //check the business already exists
        $SQL = "
        SELECT b.business_id
              ,b.address_id
        FROM business b
        JOIN address a ON a.address_id = b.address_id
        JOIN street s ON s.street_id = a.street_id
        JOIN building bldg ON bldg.building_id = a.building_id
        WHERE s.title = '" . escapeForDb($street) . "'
          AND b.business_name = '" . escapeForDb($business_name) . "'
          AND a.street_id = '{$street_id}'
          AND a.building_id = '{$building_id}'
          AND a.country_id = '{$this->country_id}'
        ";
        //print $SQL;
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows();
        //die($numRows . '');
        
        $rowBus = $db->sql_fetchrow($result);
        //print_r($rowBus);
        //die('');
        
        $address_id  = 0;
        $business_id = 0;
        if ($rowBus) {
            $address_id  = $rowBus['address_id'];
            $business_id = $rowBus['business_id'];
        }

        $expArr = array(
            'street_id' => $street_id,
            'building_id' => $building_id,
        );
        $address_id = $this->createUpdateAddress($address_id, $expArr);

        $fa = array();
        $fa['country_id']               = $this->country_id;
        $fa['business_name']            = $business_name;
        $fa['business_name_local_lang'] = $business_name_local;
        $fa['phone']                    = $phone;
        $fa['status']                   = 'Open';
        $fa['address_id']               = $address_id;
        $fa['modification_date']        = date('Y-m-d H:i:s');
        $fa['import_tag']               = 1;

        $status = '';
        //if business exists already then update
        if ($rowBus) {
            //update
            $whereCondition = "
            WHERE business_id = '{$business_id}'
            ";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'business', $whereCondition);
            $db->sql_query($SQL);
            $status = 'update';
        } else {
            $fa['creation_date'] = date('Y-m-d H:i:s');
            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'business');
            $db->sql_query($SQL);
            $business_id = $db->sql_nextid();
            $status = 'insert';
        }
        $this->log_text2 .= $status . " business: {$business_name}: " . $business_id . "<br>";
    }

    function createUpdateAddress($address_id, $exp) {
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
        
        $fa['country_id']        = $this->country_id;
        $fa['street_id']         = $exp['street_id'];
        $fa['building_id']       = $exp['building_id'];
        $fa['modification_date'] = date('Y-m-d H:i:s');

        //if business exists already then update
        if ($numRows > 0) {
            //update
            $whereCondition = "
            WHERE address_id = '{$address_id}'
            ";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'address', $whereCondition);
            $db->sql_query($SQL);
        } else {
            $fa['creation_date'] = date('Y-m-d H:i:s');
            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'address');
            $db->sql_query($SQL);
            $address_id = $db->sql_nextid();
        }
        
        return $address_id;
    }

    function getUpdateAreaDetailsByPolygon() {
        include_once 'PointLocation.php';
        /*** Example ***/
        $pointLocation = new pointLocation();
        $points = array("30 19", "0 0", "10 0", "30 20", "11 0", "0 11", "0 10", "30 22", "20 20");
        $polygon = array("10 0", "20 0", "30 10", "30 20", "20 30", "10 30", "0 20", "0 10", "10 0");
        
        $points = array("22.346 114.16245");
        $points = array("22.341740 114.178913");
        $polygon = array(
        '22.35064543937895 114.17813300502871', 
        '22.350197671365297 114.17728509170388', 
        '22.349789593446012 114.17655184281443', 
        '22.34752653782571 114.17415528905963', 
        '22.346815798058607 114.17308441711043', 
        '22.346817038445305 114.1718908340988', 
        '22.34709488478677 114.17153275919532', 
        '22.347873223709346 114.16938363922213', 
        '22.347873843897993 114.16913553488826', 
        '22.348678846430456 114.16960693312262', 
        '22.34931081357346 114.16954524231528', 
        '22.34950182957475 114.16981614542578', 
        '22.34928166500479 114.17029290807818', 
        '22.349776569662943 114.17071066213225', 
        '22.350245425086257 114.17026608598803', 
        '22.350055960551558 114.16918549103116', 
        '22.349975647356818 114.16868090046023', 
        '22.349671759174008 114.16868022990798', 
        '22.349615322724244 114.16897862566088', 
        '22.348981496411 114.16889011276339', 
        '22.348703033645723 114.16915766311263', 
        '22.34829185041588 114.16891961706256', 
        '22.348291230229076 114.16853136730765', 
        '22.348482867813782 114.16838384581183', 
        '22.349286006281364 114.16829198015307', 
        '22.349591755848344 114.16799224329566', 
        '22.350009137034384 114.16674099279021', 
        '22.35180206339293 114.16665180934046', 
        '22.35246378444962 114.16706822229003', 
        '22.352959918138346 114.16659414184664', 
        '22.352552468476137 114.16557557297324', 
        '22.350866842144963 114.16524767292117', 
        '22.350316745548135 114.16473939431285', 
        '22.348438834605666 114.16441082370852', 
        '22.347430408313503 114.16536904287909', 
        '22.34667501409701 114.16334799838637', 
        '22.346014505946847 114.1629302443323');

        // The last point's coordinates must be the same as the first one's, to "close the loop"
        foreach($points as $key => $point) {
            echo "$key ($point) is " . $pointLocation->pointInPolygon($point, $polygon) . "<br>";
        }        
    }

    function getImportBuildings(){
        $fn = Zend_Registry::get('fn');
        set_time_limit(50000);

        $this->setCountryCode();

        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');

        $fa = array(
            'business_name' => $phpExcel->getImportFldObj('Name English'),
            'business_name_local' => $phpExcel->getImportFldObj('Name Chinese'),
            'street_no_from' => $phpExcel->getImportFldObj('street from'),
            'street_no_to' => $phpExcel->getImportFldObj('street to'),
            'street' => $phpExcel->getImportFldObj('Street'),
        );

        $excelFile = realpath('../../resources/data/HK Data/Final/SBR - Buildings.xls');

        $config = array(
            'module' => 'directory_business',
            'overrideDefaultProcessCallback' => array($this, 'importBuildingsCB'),
            'fldsArr' => $fa,
            'excelFilePath' => $excelFile,
        );

        $phpExcel->importData($config);
        
        print $this->log_text1 . '<hr>';
        print $this->log_text2 . '<hr>';
    }

    function importBuildingsCB($fa, $curRow, $phpExcel) {
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $dbz = Zend_Registry::get('dbz');

        $building_name     = trim($phpExcel->getExcelFieldValue('Name English', $curRow));
        $building_name_chi = trim($phpExcel->getExcelFieldValue('Name Chinese', $curRow));
        $street_no_from    = trim($phpExcel->getExcelFieldValue('street from', $curRow));
        $street_no_to      = trim($phpExcel->getExcelFieldValue('street to', $curRow));
        $street            = trim($phpExcel->getExcelFieldValue('Street', $curRow));

        //get street rec
        $SQL = "
        SELECT s.street_id
        FROM street s
        WHERE s.title = ?
          AND s.country_id = ?
        ";
        $stmt = $dbz->query($SQL, array($street, $this->country_id));
        $rowS = $stmt->fetch();
        if (!$rowS) {
            $this->log_text1 .= 'street not found --- ' . $street . "<br>";
            return;
        }
        
        $SQL = "
        SELECT b.building_id
        FROM building b
        JOIN street s ON s.street_id = b.street_id
        WHERE b.title = ?
          AND b.chi_title = ?
          AND b.street_no_from = ?
          AND s.title = ?
          AND b.country_id = ?
        ";
        $stmt = $dbz->query($SQL, array($building_name, $building_name_chi, $street_no_from, $street, $this->country_id));
        $numRows = $stmt->rowCount();
        
        $fa = array();
        $fa['country_id']        = $this->country_id;
        $fa['title']             = $building_name;
        $fa['chi_title']         = $building_name_chi;
        $fa['street_no_from']    = $street_no_from;
        $fa['street_no_to']      = $street_no_to;
        $fa['street_id']         = $rowS['street_id'];
        $fa['modification_date'] = date('Y-m-d H:i:s');
        $fa['import_tag']        = 1;

        $building_id = '';
        $status = '';
        //if business exists already then update
        if ($numRows > 0) {
            $rowB = $stmt->fetch();
            $building_id = $rowB['building_id'];
            //update
            $whereCondition = "
            WHERE building_id = '{$building_id}'
            ";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'building', $whereCondition);
            $db->sql_query($SQL);
            $status = 'update';
        } else {
            $fa['creation_date'] = date('Y-m-d H:i:s');
            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'building');
            $db->sql_query($SQL);
            $building_id = $db->sql_nextid();
            $status = 'insert';
        }
        $this->log_text2 .= $status . " building --- {$building_name} --- {$building_name_chi} --- " . $building_id . "<br>";
    }

}
