<?
class CP_Admin_Modules_Directory_Building_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL = "
        SELECT bl.*
        	  ,CONCAT_WS('-', bl.street_no_from, bl.street_no_to) AS street_no
        	  ,c.title AS country_title
        	  ,st.title AS state_title
        	  ,ci.title AS city_title
        	  ,a.title AS area_title
        	  ,b.title AS borough_title
        	  ,s.title AS street_title
        	  ,s.chi_title AS chi_street_title
        	  ,tl.title AS transport_link_title
        FROM building bl
        LEFT JOIN country c ON bl.country_id = c.country_id
        LEFT JOIN state st ON bl.state_id = st.state_id
        LEFT JOIN city ci ON bl.city_id = ci.city_id
        LEFT JOIN borough b ON bl.borough_id = b.borough_id
        LEFT JOIN area a ON bl.area_id = a.area_id
        LEFT JOIN street s ON bl.street_id = s.street_id
        LEFT JOIN transport_link tl ON tl.transport_link_id = bl.transport_link_id
        ";

        return $SQL;
    }

    function getSQLForPager() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT count(*)
        FROM building bl
        LEFT JOIN country c ON bl.country_id = c.country_id
        LEFT JOIN state st ON bl.state_id = st.state_id
        LEFT JOIN city ci ON bl.city_id = ci.city_id
        LEFT JOIN borough b ON bl.borough_id = b.borough_id
        LEFT JOIN area a ON bl.area_id = a.area_id
        LEFT JOIN street s ON bl.street_id = s.street_id
        ";

        return $SQL;
    }

    function setSearchVar() {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar->mainTableAlias = 'bl';

        $country_id = $fn->getSessionParam('cp_country_id');
        $state_id = $fn->getReqParam('state_id');
        $city_id = $fn->getReqParam('city_id');
        $area_id = $fn->getReqParam('area_id');

        if (CP_SCOPE == 'www') {
            $searchVar->sqlSearchVar[] = "bl.published = 1";
        }

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "bl.building_id = {$tv['record_id']}";
        }

        if ($country_id != '' ) {
            $searchVar->sqlSearchVar[] = "bl.country_id = '{$country_id}'";
        }

        if ($state_id != '' ) {
            $searchVar->sqlSearchVar[] = "bl.state_id = '{$state_id}'";
        }

        if ($city_id != '' ) {
            $searchVar->sqlSearchVar[] = "bl.city_id = '{$city_id}'";
        }

        if ($area_id != '' ) {
            $searchVar->sqlSearchVar[] = "bl.area_id = '{$area_id}'";
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "( bl.title   LIKE '%{$tv['keyword']}%'
                                          )";
        }
        if ($tv['special_search'] == 'Published') {
            $searchVar->sqlSearchVar[] = 'bl.published = 1';
        }
        if ($tv['special_search'] == 'Not-Published') {
            $searchVar->sqlSearchVar[] = '(bl.published != 1 OR bl.published IS null)';
        }
        if ($tv['special_search'] == 'Lat/Lng - Unverified') {
            $searchVar->sqlSearchVar[] = 'bl.map_latlng_verified = 0';
        }
        if ($tv['special_search'] == 'Lat/Lng - Verified') {
            $searchVar->sqlSearchVar[] = 'bl.map_latlng_verified = 1';
        }

        $searchVar->sortOrder = "bl.title";
    }


    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the building name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fa['country_id'] = $fn->getSessionParam('cp_country_id');

        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the building name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'country_id');
        $fa = $fn->addToFieldsArray($fa, 'state_id');
        $fa = $fn->addToFieldsArray($fa, 'city_id');
        $fa = $fn->addToFieldsArray($fa, 'borough_id');
        $fa = $fn->addToFieldsArray($fa, 'area_id');
        $fa = $fn->addToFieldsArray($fa, 'street_id');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'street_no_from');
        $fa = $fn->addToFieldsArray($fa, 'street_no_to');
        $fa = $fn->addToFieldsArray($fa, 'latitude');
        $fa = $fn->addToFieldsArray($fa, 'longitude');
        $fa = $fn->addToFieldsArray($fa, 'transport_link_id');

        return $fa;
    }

    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'title' => $phpExcel->getFldObj('Building')
             ,'chi_title' => $phpExcel->getFldObj('Building - Chi')
             ,'street_no' => $phpExcel->getFldObj('No')
             ,'street_title' => $phpExcel->getFldObj('Street')
             ,'chi_street_title' => $phpExcel->getFldObj('Street - Chi')
             ,'area_title' => $phpExcel->getFldObj('Area')
             ,'borough_title' => $phpExcel->getFldObj('Borough')
             ,'city_title' => $phpExcel->getFldObj('City')
             ,'state_title' => $phpExcel->getFldObj('State')
             ,'country_title' => $phpExcel->getFldObj('Country')
        );

        $config = array(
             'fldsArr' => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

    /**
     *
     * http://nearer.localhost/admin/index.php?_topRm=directory&module=directory_building&_spAction=importData
     */
    function getImportData(){
        $fn = Zend_Registry::get('fn');

        return;

        print "<img src='images/logo.jpg'><br>";
        flush();
        ob_flush();

        $countrySQL = "
        SELECT country_id
        FROM country
        WHERE title = 'Hong Kong'
        ";
        $rowCountry = $fn->getRecordBySQL($countrySQL);

        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');
        $fa = array(
              'title' => $phpExcel->getImportFldObj('Name English')
             ,'chi_title' => $phpExcel->getImportFldObj('Name Chinese')
             ,'street_no_from' => $phpExcel->getImportFldObj('street from')
             ,'street_no_to' => $phpExcel->getImportFldObj('street to')
             ,'street' => $phpExcel->getImportFldObj('Street')
             ,'country_id' => $phpExcel->getImportFldObj('Country')
        );
        $fa['country_id']['defaultValue'] = $rowCountry['country_id'];

        //$excelFile = realpath('../../resources/data/HK Buildings/BuildingsHK.xls');
        $excelFile = realpath('../../resources/data/HK Data/Final/XBR.xls');

        $config = array(
             'module' => 'directory_building'
            ,'matchFieldArr' => array('country_id', 'title')
            ,'overrideDefaultProcessCallback' => 'importDataCBProcessRecord'
            ,'fldsArr' => $fa
            ,'excelFilePath' => $excelFile
            ,'sheetName' => 'Buildings'
        );

        return $phpExcel->importData($config);
    }

    function importDataCBProcessRecord($fldsArr, $curRow, $exlWrap){
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $fa = $exlWrap->getFieldsForImport($curRow, $fldsArr);

        $title      = $fa['title'];
        $chi_title  = $fa['chi_title'];
        $address    = $fa['address'];
        $country_id = $fa['country_id'];

        $address_arr = explode(' ', $address);
        $street_no = array_shift($address_arr);

        $street_title = join(' ', $address_arr);
        $exp = array('extraFldsInSqlCondnArr' => array(
           'country_id' => $country_id
        ));
        $street_id = $fn->getRecordIdByTitle('directory_street', $street_title);
        $rowStreet = $fn->getRecordRowByID('street', 'street_id', $street_id);

        $street_no_arr = preg_split("/[\/-]/", $street_no);

        $street_no_from = $street_no_arr[0];
        $street_no_to   = '';
        if (count($street_no_arr) > 1) {
            $street_no_to = $street_no_arr[1];
        }

        $title2  = str_replace("'", "\'", $title);
        $address2 = str_replace("'", "\'", $address);

        $SQL = "
        SELECT b.building_id
        FROM building b
        WHERE country_id = '{$country_id}'
          AND title = '{$title2}'
          AND street_name_temp = '{$address2}'
        ";
        $db->sql_query($SQL);
        $numRows = $db->sql_numrows();
        $result = $db->sql_query($SQL);
        $rowBuilding = $db->sql_fetchrow($result);

        $fa = array();
        $fa['title']          = $title;
        $fa['chi_title']      = $chi_title;
        $fa['street_no_from'] = $street_no_from;
        $fa['street_no_to']   = $street_no_to;
        $fa['street_id']      = $street_id;
        $fa['city_id']        = $rowStreet['city_id'];
        $fa['borough_id']     = $rowStreet['borough_id'];
        $fa['area_id']        = $rowStreet['area_id'];
        $fa['state_id']       = $rowStreet['state_id'];
        $fa['country_id']     = $country_id;
        $fa['street_name_temp'] = $address;
        $fa['creation_date']     = $fn->getCurrentTimestamp();
        $fa['modification_date'] = $fn->getCurrentTimestamp();

        if ($numRows == 0) {
            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'building');
        } else {
            $whereCondition = "
            WHERE building_id = {$rowBuilding['building_id']}
            ";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'building', $whereCondition);
        }
        $db->sql_query($SQL);
        $business_id = $db->sql_nextid();
        //print $business_id . '<br>';
    }


    function getUpdateLatLngBulk() {
        $db = Zend_Registry::get('db');

        die();

        $country_id = 2; //Hong Kong

        $SQL = "
        SELECT *
        FROM building
        WHERE country_id = '{$country_id}'
          AND (latitude IS NOT NULL AND latitude != '')
          AND (longitude IS NOT NULL AND longitude != '')
        ORDER BY building_id
        LIMIT 1000
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            print $row['title'] . "<br>";
            //$this->getUpdateLatLng($row['building_id']);
        }
    }

    function getUpdateLatLng($building_id = ''){
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        if ($building_id == '') {
            $building_id  = $fn->getReqParam('record_id');
        }

        $latitude  = $fn->getReqParam('lat');
        $longitude = $fn->getReqParam('lng');
        $rowArea = null;
        $rowBldg = $fn->getRecordRowByID('building', 'building_id', $building_id);

        if ($latitude == '') { //ie update bulk
            $latitude  = $rowBldg['latitude'];
            $longitude  = $rowBldg['longitude'];
        }
        if ($latitude == '') { //still no latitude (from existing building record)
            return;
        }

        if ($latitude != '' && $longitude != '') {
            $latLng = $rowBldg['latitude'] . ',' . $rowBldg['longitude'];
            $area_id = getCPModelObj('directory_area')->getAreaIdByLatLng($rowBldg['country_id'], $latLng);
            if ($area_id != '') {
                $rowArea = $fn->getRecordRowByID('area', 'area_id', $area_id);
            }
        }

        $fa = array();
        $fa['latitude']  = $latitude;
        $fa['longitude'] = $longitude;

        if ($rowArea) {
            $fa['state_id']   = $rowArea['state_id'];
            $fa['city_id']    = $rowArea['city_id'];
            $fa['borough_id'] = $rowArea['borough_id'];
            $fa['area_id']    = $rowArea['area_id'];
        }
        $fa = $fn->addModificationDetailsToFieldsArray($fa, 'building');

        $whereCondition = "WHERE building_id = {$building_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'building', $whereCondition);
        $db->sql_query($SQL);

    }

    function getCalculateNearestTransportLink(){
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $building_id = $fn->getReqParam('record_id');
        $rowBldg = $fn->getRecordRowByID('building', 'building_id', $building_id);

        $latitude  = $rowBldg['latitude'];
        $longitude  = $rowBldg['longitude'];

        if ($latitude != '' && $longitude != '') {
            $transport_link_id = getCPModelObj('directory_transportLnk')
                                 ->getNearestTransportLnk($rowBldg['country_id'],
                                                          $rowBldg['latitude'],
                                                          $rowBldg['longitude']);
            if ($transport_link_id != '') {
                $fa = array();
                $fa['transport_link_id']  = $transport_link_id;
                $fa = $fn->addModificationDetailsToFieldsArray($fa, 'building');

                $whereCondition = "WHERE building_id = {$building_id}";
                $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'building', $whereCondition);
                $db->sql_query($SQL);
            }
        }


    }

}
