<?
class CP_Admin_Modules_Directory_Area_View extends CP_Common_Modules_Directory_Area_View
{
    var $jssKeys = array('googleMap');

    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $ln = Zend_Registry::get('ln');

        $rowCounter = 0;
        $rows = '';

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['borough_title'])}
            {$listObj->getListDataCell($row['city_name'])}
            {$listObj->getListDataCell($row['state_title'])}
            {$listObj->getListDataCell($row['country_title'])}
            {$listObj->getListDataCell($row['area_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['area_id'])}
            {$listObj->getListRowEnd($row['area_id'])}
			";

        	$rowCounter++;
		}
         
        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.directory.area.lbl.area'), 'a.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.area.lbl.borough'), 'b.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.area.lbl.city'), 'ci.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.area.lbl.state'), 's.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.area.lbl.country'), 'c.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.area.lbl.id'), 'a.area_id', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.area.lbl.published'), 'a.published', 'headerCenter')}
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
        {$formObj->getTBRow($ln->gd('m.directory.area.lbl.area'), 'title')}
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
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        
        $expCountry = array('detailValue' => $row['country_title']);
        $sqlCountry = $fn->getDDSql('common_country');

        $expState = array('detailValue' => $row['state_title']);
        $expCity = array('detailValue' => $row['city_name']);
        $expBorough = array('detailValue' => $row['borough_title']);

        $expStateCond = array(
            'condn' => "country_id = '{$row['country_id']}'"
        );
        $expCityCond = array(
            'condn' => array(
                "country_id = '{$row['country_id']}'"
               ,"state_id = '{$row['state_id']}'"
            )
        );
        $expBoroughCond = array(
            'condn' => array(
                "country_id = '{$row['country_id']}'"
               ,"state_id = '{$row['state_id']}'"
               ,"city_id = '{$row['city_id']}'"
            )
        );
        $sqlState   = '';
        $sqlCity    = '';
        $sqlBorough = '';
        if ($row['country_id'] != '') {
            $sqlState = $fn->getDDSql('directory_state', $expStateCond);        
        }
        if ($row['state_id'] != '') {
            $sqlCity = $fn->getDDSql('directory_city', $expCityCond);
        }
        if ($row['city_id'] != '') {
            $sqlBorough = $fn->getDDSql('directory_borough', $expBoroughCond);
        }        
                
        $fieldset1  = "
        {$formObj->getTBRow($ln->gd('m.directory.area.lbl.area'), 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.area.lbl.country'), 'country_id', $sqlCountry, $row['country_id'], $expCountry)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.area.lbl.state'), 'state_id', $sqlState, $row['state_id'], $expState)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.area.lbl.city'), 'city_id', $sqlCity, $row['city_id'], $expCity)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.area.lbl.borough'), 'borough_id', $sqlBorough, $row['borough_id'], $expBorough)}
		";

        $msg = '';
        if ($row['latlng_coordinates'] == '') {
            $msg = 'Click on the map to start drawing...<br>';
        }
        $gMap = "
        {$msg}
        <div class='floatbox'>
            <a href='#' class='delete-polygon button2 mb5 float_right'>Delete polygon</a>
        </div>
        <div id='map-canvas'>
        </div>
        {$formObj->getHiddenFldObj('latlng_coordinates', $row['latlng_coordinates'])}
        ";
        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.area.lbl.mainDetails'), $fieldset1)}
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.area.lbl.mapAreaPolygon'), $gMap)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
	function getRightPanel($row) {

        $text = "
		";

        return $text;
    }
    
    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        
        $country_id = $fn->getSessionParam('cp_country_id');
        $state_id   = $fn->getReqParam('state_id');
        $city_id    = $fn->getReqParam('city_id');
        $borough_id = $fn->getReqParam('borough_id');

        $expState = array(
            'condn' => "country_id = '{$country_id}'"
        );
        $expCity = array(
            'condn' => array(
                "country_id = '{$country_id}'"
               ,"state_id = '{$state_id}'"
            )
        );
        $expBorough = array(
            'condn' => array(
                "country_id = '{$country_id}'"
               ,"state_id = '{$state_id}'"
               ,"city_id = '{$city_id}'"
            )
        );
        $sqlState   = '';
        $sqlCity    = '';
        $sqlBorough = '';
        if ($country_id != '') {
            $sqlState = $fn->getDDSql('directory_state', $expState);        
        }
        if ($state_id != '') {
            $sqlCity = $fn->getDDSql('directory_city', $expCity);
        }
        if ($city_id != '') {
            $sqlBorough = $fn->getDDSql('directory_borough', $expBorough);
        }
        
        $text = "
        <td>
            <select name='state_id'>
                <option value=''>{$ln->gd('m.directory.area.lbl.state', 'State')}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlState, $state_id)}
            </select>
        </td>    

        <td>
            <select name='city_id'>
                <option value=''>{$ln->gd('m.directory.area.lbl.city', 'City')}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCity, $city_id)}
            </select>
        </td>    

        <td>
            <select name='borough_id'>
                <option value=''>{$ln->gd('m.directory.area.lbl.borough', 'Borough')}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlBorough, $borough_id)}
            </select>
        </td>    
        ";
        return $text;
    }
}