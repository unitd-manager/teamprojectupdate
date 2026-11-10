<?
class CP_Admin_Modules_Directory_Area_Model extends CP_Common_Modules_Directory_Area_Model
{
    /**
     *
     */
    function getSQL() {
        $SQL = "
        SELECT a.*
        	  ,c.title AS country_title
        	  ,s.title AS state_title
        	  ,ci.title AS city_name
        	  ,b.title AS borough_title
        FROM area a
        LEFT JOIN (country c) ON (a.country_id = c.country_id)
        LEFT JOIN (state s) ON (a.state_id = s.state_id)
        LEFT JOIN (city ci) ON (a.city_id = ci.city_id)
        LEFT JOIN (borough b) ON (a.borough_id = b.borough_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the area name');

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
        $fa['country_id'] = $fn->getSessionParam('cp_country_id');

        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the area name');

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

        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'country_id');
        $fa = $fn->addToFieldsArray($fa, 'state_id');
        $fa = $fn->addToFieldsArray($fa, 'city_id');
        $fa = $fn->addToFieldsArray($fa, 'borough_id');
        $fa = $fn->addToFieldsArray($fa, 'latlng_coordinates');

        return $fa;
    }

    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'title' => $phpExcel->getFldObj('Area')
             ,'borough_title' => $phpExcel->getFldObj('Borough')
             ,'city_name' => $phpExcel->getFldObj('City')
             ,'state_title' => $phpExcel->getFldObj('State')
             ,'country_title' => $phpExcel->getFldObj('Country')
        );

        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

    function getAreaIdByLatLng($country_id, $latLng) {
        $db = Zend_Registry::get('db');

        include_once 'PointLocation.php';
        $pointLocation = new pointLocation();

        //source $latlng format: 22.346, 114.16245 let's make it space separated
        $point = str_replace(',', ' ', $latLng);

        $SQL = "
        SELECT area_id
              ,title
              ,latlng_coordinates
        FROM area
        WHERE country_id = '{$country_id}'
          AND (latlng_coordinates != '' AND latlng_coordinates IS NOT NULL)
        ORDER BY area_id
        ";
        $result = $db->sql_query($SQL);

        $area_id_match = '';
        while ($row = $db->sql_fetchrow($result)) {
            $polygon = $row['latlng_coordinates'];
            $polygon = str_replace(',', ' ', $polygon);
            $polygon = rtrim($polygon, "\n");
            $polygon = explode("\n", $polygon);

            $polygonLoc = $pointLocation->pointInPolygon($point, $polygon);
            if ($polygonLoc == 'inside') {
                $area_id_match = $row['area_id'];
                break;
            }
        }

        return $area_id_match;
    }

}
