<?
class CP_Admin_Modules_Directory_Street_View extends CP_Common_Modules_Directory_Street_View
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
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['area_title'])}
            {$listObj->getListDataCell($row['borough_title'])}
            {$listObj->getListDataCell($row['city_title'])}
            {$listObj->getListDataCell($row['state_title'])}
            {$listObj->getListDataCell($row['country_title'])}
            {$listObj->getListDataCell($row['street_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['street_id'])}
            {$listObj->getListDataCell($row['creation_date'])}
            {$listObj->getListDataCell($row['modification_date'])}
            {$listObj->getListRowEnd($row['street_id'])}
			";
        	$rowCounter++;
		}
         
        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.directory.street.lbl.street'), 's.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.street.lbl.area'), 'a.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.street.lbl.borough'), 'b.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.street.lbl.city'), 'c.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.street.lbl.state'), 'st.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.street.lbl.country'), 'c.title')}
        {$listObj->getListHeaderCell($ln->gd('m.lbl.id'), 's.street_id', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.lbl.published'), 's.published', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.lbl.creation'), 's.modification_date')}
        {$listObj->getListHeaderCell($ln->gd('m.lbl.modification'), 's.modification_date')}
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
        {$formObj->getTBRow($ln->gd('m.directory.street.lbl.street'), 'title')}
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
               ,"street_id = '{$row['street_id']}'"
            )
        );
               
        $sqlState   = '';
        $sqlCity    = '';
        $sqlBorough = '';
        $sqlArea    = '';
        $sqlStreet  = '';
        $sqlShopCenter  = '';
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
        if ($row['street_id'] != '') {
            $sqlStreet = $fn->getDDSql('directory_street', $expStreetCond);
        }        
        
        //---------------------------------------------------//
        $expCountry = array('detailValue' => $row['country_title']);
        $expState = array('detailValue' => $row['state_title']);
        $expCity = array('detailValue' => $row['city_title']);
        $expArea = array('detailValue' => $row['area_title']);
        $expBorough = array('detailValue' => $row['borough_title']);
        
        $fielset1  = "
        {$formObj->getTBRow($ln->gd('m.directory.street.lbl.street'), 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.street.lbl.country'), 'country_id', $sqlCountry, $row['country_id'], $expCountry)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.street.lbl.state'), 'state_id', $sqlState, $row['state_id'], $expState)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.street.lbl.city'), 'city_id', $sqlCity, $row['city_id'], $expCity)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.street.lbl.borough'), 'borough_id', $sqlBorough, $row['borough_id'], $expBorough)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.street.lbl.area'), 'area_id', $sqlArea, $row['area_id'], $expArea)}
		";

        $fielset2  = "
        {$formObj->getYesNoRRow($ln->gd('m.directory.street.lbl.published'), 'published', $row['published'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.street.lbl.mainDetails'), $fielset1)}
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.street.lbl.otherDetails'), $fielset2)}
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
               
        $sqlState   = '';
        $sqlCity    = '';
        $sqlBorough = '';
        $sqlArea = '';
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
        
        $text = "
        <td>
            <select name='state_id'>
                <option value=''>{$ln->gd('m.directory.street.lbl.state')}State</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlState, $state_id)}
            </select>
        </td>    

        <td>
            <select name='city_id'>
                <option value=''>{$ln->gd('m.directory.street.lbl.city')}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCity, $city_id)}
            </select>
        </td>    

        <td>
            <select name='borough_id'>
                <option value=''>{$ln->gd('m.directory.street.lbl.borough')}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlBorough, $borough_id)}
            </select>
        </td>    

        <td>
            <select name='area_id'>
                <option value=''>{$ln->gd('m.directory.street.lbl.area')}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlArea, $area_id)}
            </select>
        </td>    
        ";
        return $text;
    }
}