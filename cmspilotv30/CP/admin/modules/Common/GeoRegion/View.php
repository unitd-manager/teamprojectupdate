<?
class CP_Admin_Modules_Common_GeoRegion_View extends CP_Common_Modules_Common_GeoRegion_View
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
            {$listObj->getListDataCell($row['region_code'])}
            {$listObj->getListDataCell($row['country_name'])}
            {$listObj->getListRowEnd($row['geo_region_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Region', 'gr.name')}
        {$listObj->getListHeaderCell('Region Code', 'gr.region_code')}
        {$listObj->getListHeaderCell('Country Name', 'country_name')}
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
        {$formObj->getTBRow($ln->gd('m.common.geoRegion.lbl.regionName', 'Region Name'), 'name')}
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
        $ln   = Zend_Registry::get('ln');

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name']);
        
        $description  = '';
        if ($cpCfg['m.common.geoRegion.hasDescription'] == 1) {
            $description = $formObj->getTARow($ln->gd('cp.lbl.description', 'Description'), "description", $row['description']);
        }

        $fieldset1 = "
        {$formObj->getTextBoxRow($ln->gd('m.common.geoRegion.lbl.regionName', 'Region Name'), 'name', $row['name'])}
        {$formObj->getTextBoxRow($ln->gd('m.common.geoRegion.lbl.regionCode', 'Region Code'), 'region_code', $row['region_code'])}
        {$formObj->getDDRowBySQL($ln->gd('cp.lbl.country', 'Country'), 'country_code', $sqlCountry, $row['country_code'], $expCountry)}
        {$description}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Region Details', $fieldset1)}
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