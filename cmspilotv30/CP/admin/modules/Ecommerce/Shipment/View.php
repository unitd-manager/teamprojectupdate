<?
class CP_Admin_Modules_Ecommerce_Shipment_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        $mediaArray = Zend_Registry::get('mediaArray');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['country_name'])}
            {$listObj->getListDataCell($row['delivery_days'])}
            {$listObj->getListDataCell($row['weight_grams'])}
            {$listObj->getListDataCell($row['cost'])}
            {$listObj->getListDataCell($row['shipment_id'], 'center')}
            {$listObj->getListRowEnd($row['shipment_id'])}
            ";
            $rowCounter++ ;
        }


        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Country Name', 's.country_name')}
        {$listObj->getListHeaderCell('Delivery Days', 's.delivery_days')}
        {$listObj->getListHeaderCell('Weight Grams', 's.weight_grams')}
        {$listObj->getListHeaderCell('Cost', 's.cost')}
        {$listObj->getListHeaderCell('Id', 's.shipment_id', 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    //==================================================================//
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fnMod = Zend_Registry::get('fnMod');

        $sqlCountry = $fnMod->getGeoCountrySQL();
        
        $fieldset = "
        {$formObj->getDDRowBySQL('Country Name', 'country_name', $sqlCountry)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    //========================================================//
    //==================================================================//
    //==================================================================//
    //==================================================================//
    function getEdit($row) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fnMod = Zend_Registry::get('fnMod');
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode = $tv['action'];

        $text = '';

        $fnMod = includeCPClass('ModuleFns', 'ecommerce_shipment');
        $sqlCountry = $fnMod->getGeoCountrySQL();

        $expCountry = array('detailValue' => $row['country_name']);

        $fieldset1 = "
        {$formObj->getDDRowBySQL('Country Name', 'country_code', $sqlCountry, $row['country_code'], $expCountry)}
        {$formObj->getTextBoxRow('Delivery Days', 'delivery_days', $row['delivery_days'])}
        {$formObj->getTextBoxRow('Weight Grams', 'weight_grams', $row['weight_grams'])}
        {$formObj->getTextBoxRow('Cost', 'cost', $row['cost'])}
        {$formObj->getTextBoxRow('Logistic Company','logistic_company', $row['logistic_company'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Details', $fieldset1)}
        ";

        return $text;
    }

    //========================================================//
    //==================================================================//
    //==================================================================//
    //==================================================================//
    function getImportData(){
        $lang = Zend_Registry::get('lang');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        //return;
        
        set_time_limit(50000);
        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $fileName = realpath('../data/') . '/shipment_costs 100715.xls';

        $objReader = PHPExcel_IOFactory::createReader('Excel5');
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($fileName);
        $this->worksheet = $objPHPExcel->getActiveSheet();
        $countRows       = $this->worksheet->getHighestRow();
        $countCols       = $this->worksheet->getHighestColumn();

        for ($i = 'A'; $i <= $countCols; $i++) {
            $cellPos = $i . '1';
            $fieldName      = $this->worksheet->getCell($cellPos)->getValue();
            $fieldsArray[]  = $fieldName;
            $this->fieldsArrayPos[$fieldName] = $i;
        }

        //-------------------------------------------------------//
        $SQL = "DELETE FROM shipment";
        $db->sql_query($SQL);

        for ($curRow = 2; $curRow <= $countRows; $curRow++) {
            $countryCode  = trim($this->getExcelFieldValue('country_code', $curRow));

            if ($countryCode == ''){
                continue;
            }

            $fa = array();
            $fa['country_code']     = $this->getExcelFieldValue('country_code', $curRow);
            $fa['delivery_days']    = $this->getExcelFieldValue('delivery_days', $curRow);
            $fa['weight_grams']     = $this->getExcelFieldValue('weight_gms', $curRow);
            $fa['cost']             = $this->getExcelFieldValue('cost', $curRow);
            $fa['logistic_company'] = $this->getExcelFieldValue('logistic_company', $curRow);
            $fa['creation_date'] = date("Y-m-d H:i:s");
            $SQL        = $dbUtil->getInsertSQLStringFromArray($fa, "shipment");
            $result     = $db->sql_query($SQL);
            $shipment_id = $db->sql_nextid();

            // $SQL = "
            // SELECT *
            // FROM country
            // WHERE published = 1
            // ";
            // $result  = $db->sql_query($SQL);

            // $price_rmb = $this->getExcelFieldValue('cost', $curRow);
            // $price_hkd = $this->getExcelFieldValue('cost', $curRow);
            // $price_usd = $this->getExcelFieldValue('cost', $curRow);

            // while ($row = $db->sql_fetchrow($result)) {
            //     if ($row['country_name'] == 'China') {
            //         $this->getCreateShipmentCountry($shipment_id, $row['country_id'], $price_rmb);
            //     } else if ($row['country_name'] == 'Hong Kong') {
            //         $this->getCreateShipmentCountry($shipment_id, $row['country_id'], $price_hkd);
            //     } else if ($row['country_name'] == 'Rest of the World') {
            //         $this->getCreateShipmentCountry($shipment_id, $row['country_id'], $price_usd);
            //     }
            // }

        }


        $text = "
        <h3>Import Complete.</h3>
        ";

        return $text;
    }

    //==================================================================//
    function getExcelFieldValue($fieldName, $rowNo, $emptyValue = ""){
      global $dbUtil;

      $fieldValue = "";
      require_once 'PHPExcel/RichText.php';

      $fieldsArrayPos = $this->fieldsArrayPos;

      $hasColumn =   array_key_exists($fieldName , $fieldsArrayPos) ? 1 : 0;

      if ($hasColumn){
         $cellPos    = array_key_exists($fieldName , $fieldsArrayPos) ? $fieldsArrayPos[$fieldName] : "";

         if ($cellPos != ""){
            $cellAbsPos = $cellPos . $rowNo;
            $fieldValue = $this->worksheet->getCell($cellAbsPos)->getValue();

            if (gettype($fieldValue)=="object") {
                $fieldValue = $fieldValue->getPlainText();
            }

            $fieldValue = trim($fieldValue);
         }
      }
      else {
         $fieldValue = $emptyValue;
      }

      return $fieldValue;
   }


    /**
     *
     */
    function getCreateShipmentCountry($shipment_id, $country_id, $price) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $SQL = "
        SELECT * FROM shipment_country
        WHERE shipment_id = {$shipment_id}
          AND country_id = {$country_id}
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $fa = array();
        $fa['shipment_id'] = $shipment_id;
        $fa['country_id'] = $country_id;
        $fa['price']      = $price;
        $fa['published']  = 1;
        if ($numRows > 0){
            $row = $db->sql_fetchrow($result);
            $fa['modification_date'] = date("Y-m-d H:i:s");
            $whereCondition = "WHERE shipment_country_id = {$row['shipment_country_id']}";
            $SQL            = $dbUtil->getUpdateSQLStringFromArray($fa, 'shipment_country', $whereCondition);
            $result         = $db->sql_query($SQL);
        } else {
            $fa['creation_date']     = date("Y-m-d H:i:s");
            $fa['modification_date'] = date("Y-m-d H:i:s");
            $SQL    = $dbUtil->getInsertSQLStringFromArray($fa, 'shipment_country');
            $result = $db->sql_query($SQL);
        }
    }


    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $am = Zend_Registry::get('am');
        $fn = Zend_Registry::get('fn');

        $text = '';

        
        return $text;
    }
}