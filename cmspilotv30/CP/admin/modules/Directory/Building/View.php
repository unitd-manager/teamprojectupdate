<?
class CP_Admin_Modules_Directory_Building_View extends CP_Common_Lib_ModuleViewAbstract
{

    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $ln = Zend_Registry::get('ln');

        $rowCounter = 0;
        $rows = '';

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['chi_title'])}
            {$listObj->getListDataCell($row['pin_title'])}
            {$listObj->getListDataCell($row['street_no'])}
            {$listObj->getListDataCell($row['street_title'])}
            {$listObj->getListDataCell($row['chi_street_title'])}
            {$listObj->getListDataCell($row['area_title'])}
            {$listObj->getListDataCell($row['borough_title'])}
            {$listObj->getListDataCell($row['city_title'])}
            {$listObj->getListDataCell($row['state_title'])}
            {$listObj->getListDataCell($row['country_title'])}
            {$listObj->getListDataCell($row['building_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['building_id'])}
            {$listObj->getListYesNo($row['map_latlng_verified'], 'center')}
            {$listObj->getListRowEnd($row['building_id'])}
            ";
            $rowCounter++;
        }

        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.directory.building.lbl.building'), 'bl.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.building.lbl.building-Chi'), 'bl.chi_title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.building.lbl.building-Pin'), 'bl.pin_title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.building.lbl.no'), 'street_no')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.building.lbl.street'), 's.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.building.lbl.street-Chi'), 's.chi_title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.building.lbl.area'), 'a.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.building.lbl.borough'), 'b.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.building.lbl.city'), 'c.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.building.lbl.state'), 'st.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.building.lbl.country'), 'c.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.building.lbl.id'), 'bl.building_id', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.building.lbl.published'), 'bl.published', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.building.lbl.latlngVerified', '', 'headerCenter'))}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";
        return $text;
    }

	function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $fieldset = "
        {$formObj->getTBRow($ln->gd('m.directory.building.lbl.building'), 'title')}
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

        $sqlCountry = $fn->getDDSql('common_country');

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
        $expAreaCond = array(
            'condn' => array(
                "country_id = '{$row['country_id']}'"
               ,"state_id = '{$row['state_id']}'"
               ,"city_id = '{$row['city_id']}'"
               ,"borough_id = '{$row['borough_id']}'"
            )
        );
        $expStreetCond = array(
            'condn' => array(
                "country_id = '{$row['country_id']}'"
               ,"state_id = '{$row['state_id']}'"
               ,"city_id = '{$row['city_id']}'"
               ,"borough_id = '{$row['borough_id']}'"
               ,"area_id = '{$row['area_id']}'"
            )
        );
        $expShopCenterCond = array(
            'condn' => array(
                "country_id = '{$row['country_id']}'"
               ,"state_id = '{$row['state_id']}'"
               ,"city_id = '{$row['city_id']}'"
               ,"borough_id = '{$row['borough_id']}'"
               ,"area_id = '{$row['area_id']}'"
               ,"building_id = '{$row['building_id']}'"
            )
        );

        $sqlState   = '';
        $sqlCity    = '';
        $sqlBorough = '';
        $sqlArea    = '';
        $sqlStreet  = '';
        if ($row['country_id'] != '') {
            $sqlState = $fn->getDDSql('directory_state', $expStateCond);
        }
        if ($row['state_id'] != '') {
            $sqlCity = $fn->getDDSql('directory_city', $expCityCond);
        }
        if ($row['city_id'] != '') {
            $sqlBorough = $fn->getDDSql('directory_borough', $expBoroughCond);
        }
        if ($row['borough_id'] != '') {
            $sqlArea = $fn->getDDSql('directory_area', $expAreaCond);
        }
        if ($row['area_id'] != '') {
            $sqlStreet = $fn->getDDSql('directory_street', $expStreetCond);
        }

        $expTLCond = array(
            'condn' => array(
                "country_id = '{$row['country_id']}'"
            )
        );
        $sqlTL = $fn->getDDSql('directory_transportLnk', $expTLCond);

        //---------------------------------------------------//
        $expCountry = array('detailValue' => $row['country_title']);
        $expState = array('detailValue' => $row['state_title']);
        $expCity = array('detailValue' => $row['city_title']);
        $expArea = array('detailValue' => $row['area_title']);
        $expStreet = array('detailValue' => $row['street_title']);
        $expBorough = array('detailValue' => $row['borough_title']);

        $expTL = array('detailValue' => $row['transport_link_title']);

        $fielset1 = "
        {$formObj->getTBRow($ln->gd('m.directory.building.lbl.building'), 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.building.lbl.country'), 'country_id', $sqlCountry, $row['country_id'], $expCountry)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.building.lbl.state'), 'state_id', $sqlState, $row['state_id'], $expState)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.building.lbl.city'), 'city_id', $sqlCity, $row['city_id'], $expCity)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.building.lbl.borough'), 'borough_id', $sqlBorough, $row['borough_id'], $expBorough)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.building.lbl.area'), 'area_id', $sqlArea, $row['area_id'], $expArea)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.building.lbl.street'), 'street_id', $sqlStreet, $row['street_id'], $expStreet)}
        {$formObj->getTBRow($ln->gd('m.directory.building.lbl.streetNo(from)'), 'street_no_from', $row['street_no_from'])}
        {$formObj->getTBRow($ln->gd('m.directory.building.lbl.streetNo(to)'), 'street_no_to', $row['street_no_to'])}
        ";

        $url = 'index.php?module=directory_building&_spAction=calculateNearestTransportLink' .
               "&record_id={$row['building_id']}&showHTML=0";
        $nearestMTRBtn = "
        <a href='javascript:void(0)'
           link='{$url}'
           id='calculateNearestTLink'
           class='button mt10 mb10' style='margin-left:20%'>{$ln->gd('m.directory.building.btn.calculateNearestMTRExit')}</a>
        ";
        $fielset2 = "
        {$formObj->getTBRow($ln->gd('m.directory.building.lbl.latitude'), 'latitude', $row['latitude'])}
        {$formObj->getTBRow($ln->gd('m.directory.building.lbl.longitude'), 'longitude', $row['longitude'])}
        {$nearestMTRBtn}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.transportLnk.lbl.stationExit'),
                                 'transport_link_id', $sqlTL, $row['transport_link_id'], $expTL)}
        {$formObj->getYesNoRRow($ln->gd('m.directory.building.lbl.published'), 'published', $row['published'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.building.lbl.latlngVerified'), 'map_latlng_verified', $row['map_latlng_verified'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.building.lbl.mainDetails'), $fielset1)}
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.building.lbl.otherDetails'), $fielset2)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row) {
        $wMap = getCPWidgetObj('directory_gMap');

        $address = '';
        if($row['street_no_from'] != '' && $row['street_no_to'] != ''){
            $address .= "{$row['street_no_from']} - {$row['street_no_to']}";
        } elseif($row['street_no_from'] != ''){
            $address .= "{$row['street_no_from']}";
        } elseif($row['street_no_to'] != ''){
            $address .= "{$row['street_no_to']}";
        }

        if($row['street_title'] != ''){
            $address .= ($address != '') ? ", {$row['street_title']}" : "{$row['street_title']}";
        }

        if($row['area_title'] != ''){
            $address .= ($address != '') ? ", {$row['area_title']}" : "{$row['area_title']}";
        }

        if($row['borough_title'] != ''){
            $address .= ($address != '') ? ", {$row['borough_title']}" : "{$row['borough_title']}";
        }

        if($row['city_title'] != ''){
            $address .= ($address != '') ? ", {$row['city_title']}" : "{$row['city_title']}";
        }

        if($row['state_title'] != ''){
            $address .= ($address != '') ? ", {$row['state_title']}" : "{$row['state_title']}";
        }

        if($row['country_title'] != ''){
            $address .= ($address != '') ? ", {$row['country_title']}" : "{$row['country_title']}";
        }


        $gMap = $wMap->getWidget(array(
             'lat'  => $row['latitude']
            ,'lng'  => $row['longitude']
            ,'zoom' => 19
            ,'address' => $address
            ,'saveLatLngUrl' => "index.php?module=directory_building&_spAction=updateLatLng&record_id={$row['building_id']}&showHTML=0"
        ));

        $text = "
        {$gMap}
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
        $cpUtil = Zend_Registry::get('cpUtil');

        $country_id = $fn->getSessionParam('cp_country_id');
        $state_id   = $fn->getReqParam('state_id');
        $city_id    = $fn->getReqParam('city_id');
        $borough_id = $fn->getReqParam('borough_id');
        $area_id    = $fn->getReqParam('area_id');
        $street_id   = $fn->getReqParam('street_id');

        $special_search = $fn->getReqParam('special_search');

        $spSearchArr = array(
            'Published',
            'Not-Published',
            'Lat/Lng - Unverified',
            'Lat/Lng - Verified',
        );

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
        $expArea = array(
            'condn' => array(
                "country_id = '{$country_id}'"
               ,"state_id = '{$state_id}'"
               ,"city_id = '{$city_id}'"
               ,"borough_id = '{$borough_id}'"
            )
        );
        $expStreet = array(
            'condn' => array(
                "country_id = '{$country_id}'"
               ,"state_id = '{$state_id}'"
               ,"city_id = '{$city_id}'"
               ,"borough_id = '{$borough_id}'"
               ,"area_id = '{$area_id}'"
            )
        );

        $sqlState   = '';
        $sqlCity    = '';
        $sqlBorough = '';
        $sqlArea = '';
        $sqlStreet = '';
        if ($country_id != '') {
            $sqlState = $fn->getDDSql('directory_state', $expState);
        }
        if ($state_id != '') {
            $sqlCity = $fn->getDDSql('directory_city', $expCity);
        }
        if ($city_id != '') {
            $sqlBorough = $fn->getDDSql('directory_borough', $expBorough);
        }
        if ($borough_id != '') {
            $sqlArea = $fn->getDDSql('directory_area', $expArea);
        }
        if ($area_id != '') {
            $sqlStreet = $fn->getDDSql('directory_street', $expStreet);
        }

        $text = "
        <td>
            <select name='state_id'>
                <option value=''>{$ln->gd('m.directory.building.lbl.state')}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlState, $state_id)}
            </select>
        </td>

        <td>
            <select name='city_id'>
                <option value=''>{$ln->gd('m.directory.building.lbl.city')}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCity, $city_id)}
            </select>
        </td>

        <td>
            <select name='borough_id'>
                <option value=''>{$ln->gd('m.directory.building.lbl.borough')}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlBorough, $borough_id)}
            </select>
        </td>

        <td>
            <select name='area_id'>
                <option value=''>{$ln->gd('m.directory.building.lbl.area')}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlArea, $area_id)}
            </select>
        </td>

        <td>
            <select name='area_id'>
                <option value=''>{$ln->gd('m.directory.building.lbl.street')}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlStreet, $street_id)}
            </select>
        </td>

        <td class='fieldValue'>
            <select name='special_search'>
                <option value=''>{$ln->gd('m.lbl.specialSearch')}</option>
                {$cpUtil->getDropDown1($spSearchArr, $special_search)}
            </select>
        </td>

        ";
        return $text;
    }
}