<?
class CP_Admin_Modules_Directory_Address_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
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
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
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
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'country_id');
        $fa = $fn->addToFieldsArray($fa, 'state_id');
        $fa = $fn->addToFieldsArray($fa, 'city_id');
        $fa = $fn->addToFieldsArray($fa, 'borough_id');
        $fa = $fn->addToFieldsArray($fa, 'area_id');
        $fa = $fn->addToFieldsArray($fa, 'street_id');
        $fa = $fn->addToFieldsArray($fa, 'shop_center_id');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'building_id');
        $fa = $fn->addToFieldsArray($fa, 'address_block');
        $fa = $fn->addToFieldsArray($fa, 'address_floor_from');
        $fa = $fn->addToFieldsArray($fa, 'address_floor_to');
        $fa = $fn->addToFieldsArray($fa, 'address_unit_from');
        $fa = $fn->addToFieldsArray($fa, 'address_unit_to');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'latitude');
        $fa = $fn->addToFieldsArray($fa, 'longitude');
        $fa = $fn->addToFieldsArray($fa, 'northing');
        $fa = $fn->addToFieldsArray($fa, 'easting');
        return $fa;
    }

    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'title' => $phpExcel->getFldObj('Shop Center')
             ,'country_title' => $phpExcel->getFldObj('Country')
             ,'state_title' => $phpExcel->getFldObj('State')
             ,'city_title' => $phpExcel->getFldObj('City')
             ,'area_title' => $phpExcel->getFldObj('Area')
        );

        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

    function getSQL() {
        $SQL = "
        SELECT DISTINCT ad.*
              ,CONCAT_WS('-', c.country_code, ad.address_id) AS country_code_address_id
        	  ,c.title AS country_title
        	  ,s.title AS state_title
        	  ,ci.title AS city_title
        	  ,b.title AS borough_title
        	  ,a.title AS area_title
        	  ,st.title AS street_title
        	  ,sc.title AS shop_center_title
        	  ,bldg.title AS address_building_title
        	  ,bldg.street_no_from AS address_street_no_from
        	  ,bldg.street_no_to AS address_street_no_to
        FROM address ad
        LEFT JOIN country c ON ad.country_id = c.country_id
        LEFT JOIN state s ON ad.state_id = s.state_id
        LEFT JOIN city ci ON ad.city_id = ci.city_id
        LEFT JOIN borough b ON ad.borough_id = b.borough_id
        LEFT JOIN area a ON ad.area_id = a.area_id
        LEFT JOIN street st ON ad.street_id = st.street_id
        LEFT JOIN shop_center sc ON ad.shop_center_id = sc.shop_center_id
        LEFT JOIN building bldg ON bldg.building_id = ad.building_id
        ";
        return $SQL;
    }

    function getSQLForPager() {
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT count(*)
        FROM address ad
        LEFT JOIN country c ON ad.country_id = c.country_id
        LEFT JOIN state s ON ad.state_id = s.state_id
        LEFT JOIN city ci ON ad.city_id = ci.city_id
        LEFT JOIN borough b ON ad.borough_id = b.borough_id
        LEFT JOIN area a ON ad.area_id = a.area_id
        LEFT JOIN street st ON ad.street_id = st.street_id
        LEFT JOIN shop_center sc ON ad.shop_center_id = sc.shop_center_id
        LEFT JOIN building bldg ON bldg.building_id = ad.building_id
        ";
        return $SQL;
    }

    function setSearchVar() {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar->mainTableAlias = 'sc';

        $country_id = $fn->getSessionParam('cp_country_id');
        $state_id = $fn->getReqParam('state_id');
        $city_id = $fn->getReqParam('city_id');
        $area_id = $fn->getReqParam('area_id');
        $street_id = $fn->getReqParam('street_id');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "ad.address_id = {$tv['record_id']}";
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

        if ($area_id != '' ) {
            $searchVar->sqlSearchVar[] = "ad.area_id = '{$area_id}'";
        }

        if ($street_id != '' ) {
            $searchVar->sqlSearchVar[] = "ad.street_id = '{$street_id}'";
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "
            (   c.title LIKE '%{$tv['keyword']}%' 
             OR s.title LIKE '%{$tv['keyword']}%' 
             OR ci.title LIKE '%{$tv['keyword']}%' 
             OR b.title LIKE '%{$tv['keyword']}%' 
             OR a.title LIKE '%{$tv['keyword']}%' 
             OR st.title LIKE '%{$tv['keyword']}%' 
             OR sc.title LIKE '%{$tv['keyword']}%' 
             OR bldg.title LIKE '%{$tv['keyword']}%' 
            )";
        }

		$searchVar->sortOrder = "sc.title";
    }

    function getAddressRecord() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $modulesArr = Zend_Registry::get('modulesArr');

        $moduleArr = $modulesArr[$tv['module']];
        $tableName = $moduleArr['tableName'];
        $keyField = $moduleArr['keyField'];

        $record_id = $fn->getReqParam('record_id');

        $SQL = $this->getSQL();
        $SQL .= "
        WHERE ad.address_id = '{$record_id}'
        ";

        $row = $fn->getRecordBySQL($SQL, MYSQL_ASSOC);

        return $row;
    }
}
