<?
class CP_Admin_Modules_Common_Region_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getListRowEnd($row['region_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.common.header.region.lbl.region', 'Region'), 'gr.title')}
        {$listObj->getListHeaderCell($ln->gd('m.common.header.region.lbl.country', 'Country'), 'country_name')}
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
        {$formObj->getDDRowBySQL($ln->gd('m.common.region.lbl.country', 'Country'), 'country_code', $sqlCountry)}
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
        
        $fieldset1 = "
        {$formObj->getTextBoxRow($ln->gd('m.common.region.lbl.region', 'Region'), 'title', $ln->gfv($row, 'title'))}
        {$formObj->getDDRowBySQL($ln->gd('m.common.region.lbl.country', 'Country'), 'country_code', $sqlCountry, $row['country_code'], $expCountry)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.common.region.lbl.regionDetails', 'Region Details'), $fieldset1)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');        
        $ln = Zend_Registry::get('ln');        
        $dbUtil = Zend_Registry::get('dbUtil');        
        $ln   = Zend_Registry::get('ln');

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