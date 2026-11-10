<?
class CP_Admin_Modules_Common_GeoCity_View extends CP_Common_Modules_Common_GeoCity_View
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $ln = Zend_Registry::get('ln');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['name'])}
            {$listObj->getListDataCell($row['city_code'])}
            {$listObj->getListDataCell($row['region_name'])}
            {$listObj->getListDataCell($row['country_name'])}
            {$listObj->getListRowEnd($row['geo_city_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.common.header.geoCity.lbl.city', 'City'), 'gc.name')}
        {$listObj->getListHeaderCell($ln->gd('m.common.header.geoCity.lbl.cityCode', 'City Code'), 'gc.city_code')}
        {$listObj->getListHeaderCell($ln->gd('m.common.header.geoCity.lbl.regionName', 'Region Name'), 'region_name')}
        {$listObj->getListHeaderCell($ln->gd('m.common.header.geoCity.lbl.countryName', 'Country Name'), 'country_name')}
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
        {$formObj->getTBRow($ln->gd('m.common.geoCity.lbl.city', 'City'), 'name')}
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
        $cpCfg   = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');

        $sqlRegion = getCPModelObj('common_geoRegion')->getRegionDDSQL();
        $expRegion = array('detailValue' => $row['region_name']);
        
        $description  = '';
        if ($cpCfg['m.common.geoRegion.hasDescription'] == 1) {
            $description = $formObj->getTARow($ln->gd('cp.lbl.description', 'Description') , 'description', $row['description']);
        }

        $fieldset1 = "
        {$formObj->getTextBoxRow($ln->gd('m.common.geoCity.lbl.city', 'City'), 'name', $row['name'])}
        {$formObj->getTextBoxRow($ln->gd('m.common.geoCity.lbl.cityCode', 'City Code'), 'city_code', $row['city_code'])}
        {$formObj->getDDRowBySQL($ln->gd('m.common.geoCity.lbl.region', 'Region'), 'region_code', $sqlRegion, $row['region_code'], $expRegion)}
        {$description}
        ";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.common.geoCity.lbl.cityDetails', 'City Details'), $fieldset1)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');        
        $dbUtil = Zend_Registry::get('dbUtil');        
        return;
        $country_code  = $fn->getReqParam('country_code');

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();

        $text = "
        <td class='fieldValue'>
            <select name='country_code'>
                <option value=''>Country</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCountry, $country_code)}
            </select>
        </td>
        ";
        
        return $text;
        
    }
}