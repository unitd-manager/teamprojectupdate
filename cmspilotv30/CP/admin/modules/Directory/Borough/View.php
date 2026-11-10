<?
class CP_Admin_Modules_Directory_Borough_View extends CP_Common_Modules_Directory_Borough_View
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
            {$listObj->getListDataCell($row['city_title'])}
            {$listObj->getListDataCell($row['state_title'])}
            {$listObj->getListDataCell($row['country_title'])}
            {$listObj->getListPublishedImage($row['published'], $row['borough_id'])}
            {$listObj->getListDataCell($row['borough_id'], 'center')}
            {$listObj->getListRowEnd($row['borough_id'])}
			";

        	$rowCounter++;
		}
         
        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.directory.borough.lbl.borough'), 'b.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.borough.lbl.city'), 'ci.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.borough.lbl.state'), 's.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.borough.lbl.country'), 'c.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.borough.lbl.published'), 'b.published', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.borough.lbl.id'), 'b.borough_id', 'headerCenter')}
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
        {$formObj->getTBRow($ln->gd('m.directory.borough.lbl.borough'), 'title')}
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
        $sqlState = '';
        if ($row['country_id'] != '') {
            $sqlState = $fn->getDDSql('directory_state');
        }

        $expCity = array('detailValue' => $row['city_title']);
        $sqlCity = '';
        if ($row['state_id'] != '') {
            $sqlCity = $fn->getDDSql('directory_city');
        }

        $expStateCond = array(
            'condn' => "country_id = '{$row['country_id']}'"
        );
        $expCityCond = array(
            'condn' => array(
                "country_id = '{$row['country_id']}'"
               ,"state_id = '{$row['state_id']}'"
            )
        );
        $sqlState = '';
        $sqlCity  = '';
        if ($row['country_id'] != '') {
            $sqlState = $fn->getDDSql('directory_state', $expStateCond);        
        }
        if ($row['state_id'] != '') {
            $sqlCity = $fn->getDDSql('directory_city', $expCityCond);
        }
        
        $fielset1  = "
        {$formObj->getTBRow($ln->gd('m.directory.borough.lbl.borough'), 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.borough.lbl.country'), 'country_id', $sqlCountry, $row['country_id'], $expCountry)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.borough.lbl.state'), 'state_id', $sqlState, $row['state_id'], $expState)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.borough.lbl.city'), 'city_id', $sqlCity, $row['city_id'], $expCity)}
        {$formObj->getYesNoRRow($ln->gd('m.directory.borough.lbl.published'), 'published', $row['published'] )} 
		";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.borough.lbl.mainDetails'), $fielset1)}
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
        $state_id = $fn->getReqParam('state_id');
        $city_id = $fn->getReqParam('city_id');

        $expState = array(
            'condn' => "country_id = '{$country_id}'"
        );
        $expCity = array(
            'condn' => array(
                "country_id = '{$country_id}'"
               ,"state_id = '{$state_id}'"
            )
        );
        $sqlState = '';
        $sqlCity  = '';
        if ($country_id != '') {
            $sqlState = $fn->getDDSql('directory_state', $expState);        
        }
        if ($state_id != '') {
            $sqlCity = $fn->getDDSql('directory_city', $expCity);
        }
        
        $text = "
        <td>
            <select name='state_id'>
                <option value=''>{$ln->gd('m.directory.borough.lbl.state')}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlState, $state_id)}
            </select>
        </td>    

        <td>
            <select name='city_id'>
                <option value=''>{$ln->gd('m.directory.borough.lbl.city')}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCity, $city_id)}
            </select>
        </td>    
        ";
        
        return $text;
    }
}