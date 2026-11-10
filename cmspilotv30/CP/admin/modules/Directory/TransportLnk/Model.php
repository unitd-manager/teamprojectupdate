<?
class CP_Admin_Modules_Directory_TransportLnk_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL = "
        SELECT tl.*
              ,CONCAT_WS(', ', tl.lat, tl.lng) AS lat_lng
              ,c.title AS country_title
        FROM transport_link tl
        LEFT JOIN country c ON c.country_id = tl.country_id
        ";

        return $SQL;
    }

    function setSearchVar($linkRecType) {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "tl.transport_link_id = {$tv['record_id']}";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'tl.transport_link_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    tl.spelling LIKE '%{$tv['keyword']}%'
                 OR tl.transport_link LIKE '%{$tv['keyword']}%'
                )";
            }

            $searchVar->sortOrder = "
            tl.title, tl.station_exit
            ";
    	}
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the transport link');

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
        $validate->validateData('title', 'Please enter the transport link');

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
        $fa = $fn->addToFieldsArray($fa, 'station_exit');
        $fa = $fn->addToFieldsArray($fa, 'lat');
        $fa = $fn->addToFieldsArray($fa, 'lng');
        $fa = $fn->addToFieldsArray($fa, 'country_id');

        return $fa;
    }

    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'title' => $phpExcel->getFldObj('Title')
        );

        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }

    function getNearestTransportLnk($country_id, $lat, $lng) {
        $db = Zend_Registry::get('db');

        $transport_link_id = '';
        if ($lat != '' && $lng != '') {
            $SQL = "
            SELECT transport_link_id
                  ,station_exit
                  ,( (ACOS(
                        SIN({$lat} * PI() / 180) *
                        SIN(lat * PI() / 180) +
                        COS({$lat} * PI() / 180) *
                        COS(lat * PI() / 180) *
                        COS(({$lng} - lng) * PI() / 180)
                     ) * 180 / PI()) * 60 * 1.1515
                   ) AS `distance`
            FROM transport_link
            WHERE country_id = '{$country_id}'
            ORDER BY `distance` ASC
            ";
            $result = $db->sql_query($SQL);

            $rowTL = $db->sql_fetchrow($result);

            $transport_link_id = $rowTL['transport_link_id'];
        }

        return $transport_link_id;
    }

     /**
     *
     * http://nearer.localhost/admin/index.php?_topRm=directory&module=directory_transportLnk&_spAction=importData
     */
    function getImportData(){
        $fn = Zend_Registry::get('fn');

        die();
        
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
              'title' => $phpExcel->getImportFldObj('MTR STATION')
             ,'station_exit' => $phpExcel->getImportFldObj('EXIT')
             ,'latlng_temp' => $phpExcel->getImportFldObj('latlng')
             ,'country_id' => $phpExcel->getImportFldObj('Country')
        );
        $fa['country_id']['defaultValue'] = $rowCountry['country_id'];

        $excelFile = realpath('../../resources/data/HK Data/MTRs-2.xls');

        $config = array(
             'module' => 'directory_transportLnk'
            ,'matchFieldArr' => array('country_id', 'title', 'station_exit')
            ,'fldsArr' => $fa
            ,'excelFilePath' => $excelFile
        );

        return $phpExcel->importData($config);
    }
}
