<?
class CP_Admin_Modules_Wine_Appellation_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['country_name'])}
            {$listObj->getListDataCell($row['region_title'])}
            {$listObj->getListSortOrderField($row, 'appellation_id')}            
            {$listObj->getListRowEnd($row['appellation_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.wine.header.appellation.lbl.appellation', 'Appellation'), 'a.title')}
        {$listObj->getListHeaderCell($ln->gd('m.wine.header.appellation.lbl.country', 'Country'), 'country_name')}
        {$listObj->getListHeaderCell($ln->gd('m.wine.header.appellation.lbl.region', 'Region'), 'region_title')}
        {$listObj->getListSortOrderImage('a')}        
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

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();

        $fieldset = "
        {$formObj->getDDRowBySQL($ln->gd('m.wine.appellation.lbl.country', 'Country'), 'country_code', $sqlCountry)}
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name']);
        
        $expRegion = array('detailValue' => $row['region_title']);
        $sqlRegion = ($row['country_code'] != '') ? $fn->getDDSql('common_region', array('condn' => "country_code = '{$row['country_code']}'")) : '';

        $fieldset1 = "
        {$formObj->getTextBoxRow($ln->gd('m.wine.appellation.lbl.appellation', 'Appellation'), 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getDDRowBySQL($ln->gd('m.wine.appellation.lbl.country', 'Country'), 'country_code', $sqlCountry, $row['country_code'], $expCountry)}
        {$formObj->getDDRowBySQL($ln->gd('m.wine.appellation.lbl.region', 'Region'), 'region_id', $sqlRegion, $row['region_id'], $expRegion)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.wine.appellation.lbl.appellationDetails', 'Appellation Details'), $fieldset1)}
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
        $formObj = Zend_Registry::get('formObj');        
        $ln = Zend_Registry::get('ln');        

        $country_code = $fn->getReqParam('country_code', '', true);
        $region_id = $fn->getReqParam('region_id', '', true);

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();

        $sqlRegion = '';
        if ($country_code != "") {
            $sqlRegion = $fn->getDDSql('common_region', array('condn' => "country_code= '{$country_code}'"));
        }

        $text = "
        <td>
            <select name='country_code'>
                <option value=''>{$ln->gd('m.wine.appellation.lbl.country', 'Country')}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCountry, $country_code)}
            </select>
        </td>
        <td>
            {$formObj->getDropDownBySQL($ln->gd('m.wine.appellation.lbl.region', 'Region'), 'region_id', $sqlRegion, $region_id)}
        </td>
        ";
        
        return $text;
        
    }
}