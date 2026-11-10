<?
class CP_Admin_Modules_Directory_Address_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $ln = Zend_Registry::get('ln');

        $rowCounter = 0;
        $rows = '';

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['country_title'])}
            {$listObj->getListDataCell($row['state_title'])}
            {$listObj->getListDataCell($row['city_title'])}
            {$listObj->getListDataCell($row['borough_title'])}
            {$listObj->getListDataCell($row['area_title'])}
            {$listObj->getListDataCell($row['street_title'])}
            {$listObj->getListDataCell($row['shop_center_title'])}
            {$listObj->getListDataCell($row['address_street_no_from'])}
            {$listObj->getListDataCell($row['address_street_no_to'])}
            {$listObj->getListDataCell($row['address_building_title'])}
            {$listObj->getListDataCell($row['address_block'])}
            {$listObj->getListDataCell($row['address_floor_from'])}
            {$listObj->getListDataCell($row['address_unit_from'])}
            {$listObj->getListDataCell($row['address_po_code'])}
            {$listObj->getListPublishedImage($row['published'], $row['address_id'])}
            {$listObj->getListDataCell($row['address_id'], 'center')}
            {$listObj->getListRowEnd($row['address_id'])}
			";

        	$rowCounter++;
		}

        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.directory.address.lbl.country'), 'c.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.address.lbl.state'), 's.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.address.lbl.city'), 'ci.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.address.lbl.borough'), 'b.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.address.lbl.area'), 'a.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.address.lbl.street'), 's.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.address.lbl.shopCenter'), 'sc.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.address.lbl.st.#(from)'), 'ad.address_street_no_from')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.address.lbl.st.#(to)'), 'ad.address_street_no_from')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.address.lbl.buildingName'), 'address_building_title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.address.lbl.block'), 'ad.address_block')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.address.lbl.floor'), 'ad.address_floor_from')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.address.lbl.unit'), 'ad.address_unit_from')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.address.lbl.zipCode'), 'ad.address_po_code')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.address.lbl.published'), 'ad.published', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.lbl.id'), 'ad.address_id', 'headerCenter')}
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
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $country_id = $fn->getSessionParam('cp_country_id');
        $sqlCountry = $fn->getDDSql('common_country');

        $sqlState = '';
        $expStateCond = array(
            'condn' => "country_id = '{$country_id}'"
        );
        $sqlState = $fn->getDDSql('directory_state', $expStateCond);
        $fieldset = "
        {$formObj->getDDRowBySQL($ln->gd('m.directory.address.lbl.country'), 'country_id', $sqlCountry, $country_id)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.address.lbl.state'), 'state_id', $sqlState)}
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
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $expCountry = array('detailValue' => $row['country_title']);
        $sqlCountry = $fn->getDDSql('common_country');

        $expState = array('detailValue' => $row['state_title']);
        $expCity = array('detailValue' => $row['city_title']);
        $expBorough = array('detailValue' => $row['borough_title']);
        $expArea = array('detailValue' => $row['area_title']);
        $expStreet = array('detailValue' => $row['street_title']);
        $expShopCenter = array('detailValue' => $row['shop_center_title']);
        $expBuilding = array('detailValue' => $row['address_building_title']);

        //----------------------------------------//
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
            )
        );
        $expShopCenterCond = array(
            'condn' => array(
                "country_id = '{$row['country_id']}'"
               ,"state_id = '{$row['state_id']}'"
               ,"city_id = '{$row['city_id']}'"
               ,"borough_id = '{$row['borough_id']}'"
               ,"area_id = '{$row['area_id']}'"
               ,"street_id = '{$row['street_id']}'"
            )
        );
        $expBuildingCond = array(
            'condn' => array(
                "country_id = '{$row['country_id']}'"
               ,"state_id = '{$row['state_id']}'"
               ,"city_id = '{$row['city_id']}'"
               ,"borough_id = '{$row['borough_id']}'"
               ,"area_id = '{$row['area_id']}'"
               ,"street_id = '{$row['street_id']}'"
            )
        );

        $sqlState   = '';
        $sqlCity    = '';
        $sqlBorough = '';
        $sqlArea    = '';
        $sqlStreet  = '';
        $sqlShopCenter = '';
        $sqlBuilding = '';
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
        if ($row['city_id'] != '') {
            $sqlStreet = $fn->getDDSql('directory_street', $expStreetCond);
        }
        if ($row['street_id'] != '') {
            $sqlShopCenter = $fn->getDDSql('directory_shopCenter', $expShopCenterCond);
        }
        if ($row['street_id'] != '') {
            $sqlBuilding = $fn->getDDSql('directory_building', $expBuildingCond);
        }

        $expNoEdit = array('isEditable' => 0);
        $fielset1  = "
        {$formObj->getTBRow($ln->gd('m.directory.address.lbl.addressID'), 'address_id', $row['address_id'], $expNoEdit)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.address.lbl.country'), 'country_id', $sqlCountry, $row['country_id'], $expCountry)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.address.lbl.state'), 'state_id', $sqlState, $row['state_id'], $expState)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.address.lbl.city'), 'city_id', $sqlCity, $row['city_id'], $expCity)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.address.lbl.borough'), 'borough_id', $sqlBorough, $row['borough_id'], $expBorough)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.address.lbl.area'), 'area_id', $sqlArea, $row['area_id'], $expArea)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.address.lbl.street'), 'street_id', $sqlStreet, $row['street_id'], $expStreet)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.address.lbl.shopCenter'), 'shop_center_id', $sqlShopCenter, $row['shop_center_id'], $expShopCenter)}

        {$formObj->getDDRowBySQL($ln->gd('m.directory.address.lbl.buildingName'), 'building_id', $sqlBuilding, $row['building_id'], $expBuilding)}
        {$formObj->getTBRow($ln->gd('m.directory.address.lbl.block'), 'address_block', $row['address_block'])}
        {$formObj->getTBRow($ln->gd('m.directory.address.lbl.floor(from)'), 'address_floor_from', $row['address_floor_from'])}
        {$formObj->getTBRow($ln->gd('m.directory.address.lbl.floor(to)'), 'address_floor_to', $row['address_floor_to'])}
        {$formObj->getTBRow($ln->gd('m.directory.address.lbl.unit(from)'), 'address_unit_from', $row['address_unit_from'])}
        {$formObj->getTBRow($ln->gd('m.directory.address.lbl.unit(to)'), 'address_unit_to', $row['address_unit_to'])}
        {$formObj->getTBRow($ln->gd('m.directory.address.lbl.poCode'), 'address_po_code', $row['address_po_code'])}

        {$formObj->getYesNoRRow($ln->gd('m.directory.address.lbl.published'), 'published', $row['published'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.address.lbl.mainDetails'), $fielset1)}
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
        $area_id    = $fn->getReqParam('area_id');
        $street_id  = $fn->getReqParam('street_id');
        $shop_center_id = $fn->getReqParam('shop_center_id');

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
                "a.country_id = '{$country_id}'"
               ,"a.state_id = '{$state_id}'"
               ,"a.city_id = '{$city_id}'"
               ,"a.borough_id = '{$borough_id}'"
               ,"a.area_id = '{$area_id}'"
            )
        );
        $expShopCenter = array(
            'condn' => array(
                "country_id = '{$country_id}'"
               ,"state_id = '{$state_id}'"
               ,"city_id = '{$city_id}'"
               ,"borough_id = '{$borough_id}'"
               ,"area_id = '{$area_id}'"
               ,"street_id = '{$street_id}'"
               ,"shop_center_id = '{$shop_center_id}'"
            )
        );

        $sqlState   = '';
        $sqlCity    = '';
        $sqlBorough = '';
        $sqlArea    = '';
        $sqlStreet  = '';
        $sqlShopCenter  = '';
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
            $sqlStreet = "
            SELECT DISTINCT
                   s.street_id
                  ,s.title
            FROM address a
            JOIN street s ON s.street_id = a.street_id
            WHERE " . join($expStreet['condn'], ' AND ');
        }
        if ($shop_center_id != '') {
            $sqlShopCenter = $fn->getDDSql('directory_shopCenter', $expShopCenter);
        }

        $text = "
        <td>
            <select name='state_id'>
                <option value=''>{$ln->gd('m.directory.address.lbl.state')}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlState, $state_id)}
            </select>
        </td>

        <td>
            <select name='city_id'>
                <option value=''>{$ln->gd('m.directory.address.lbl.city')}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCity, $city_id)}
            </select>
        </td>

        <td>
            <select name='borough_id'>
                <option value=''>{$ln->gd('m.directory.address.lbl.borough')}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlBorough, $borough_id)}
            </select>
        </td>

        <td>
            <select name='area_id'>
                <option value=''>{$ln->gd('m.directory.address.lbl.area')}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlArea, $area_id)}
            </select>
        </td>

        <td>
            <select name='street_id'>
                <option value=''>{$ln->gd('m.directory.address.lbl.street')}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlStreet, $street_id)}
            </select>
        </td>

        <td>
            <select name='shop_center_id'>
                <option value=''>{$ln->gd('m.directory.address.lbl.shopCenter')}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlShopCenter, $shop_center_id)}
            </select>
        </td>
        ";

        return $text;
    }
}