<?
class CP_Admin_Modules_Directory_City_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');

        $rowCounter = 0;
        $rows = '';

        foreach ($dataArray as $row){
            $city_code = '';
            if ($cpCfg['m.directory.city.showCodeFld']){
                $city_code = $listObj->getListDataCell($row['city_code']);
            }

            $state = '';
            if ($cpCfg['m.directory.city.hasState']){
                $state = $listObj->getListDataCell($row['state_title']);
            }
            
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$city_code}
            {$state}
            {$listObj->getListDataCell($row['country_name'])}
            {$listObj->getListDataCell($row['city_id'], 'center')}
            {$listObj->getListRowEnd($row['city_id'])}
            ";

            $rowCounter++;
        }

        $city_code = '';
        if ($cpCfg['m.directory.city.showCodeFld']){
            $city_code = $listObj->getListHeaderCell('City Code', 'c.city_code');
        }
         
        $state = '';
        if ($cpCfg['m.directory.city.hasState']){
            $state = $listObj->getListHeaderCell($ln->gd('m.directory.city.lbl.state'), 's.title');
        }

        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.directory.city.lbl.city'), 'c.title')}
        {$city_code}
        {$state}
        {$listObj->getListHeaderCell($ln->gd('m.directory.city.lbl.country'), 'co.title')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.city.lbl.id'), 'c.city_id', 'headerCenter')}
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');

        $city_code = '';
        if ($cpCfg['m.directory.city.showCodeFld']){
            $city_code = $formObj->getTextBoxRow($ln->gd('m.directory.city.lbl.cityCode'), 'city_code');
        }
        
        $fieldset = "
        {$formObj->getTBRow($ln->gd('m.directory.city.lbl.city'), 'title')}
        {$city_code}
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
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
         
        $formObj->mode = $tv['action'];

        $expCountry = array('detailValue' => $row['country_name']);
        $modCountry = getCPModuleObj('common_country');
        $sqlCountry = $modCountry->model->getCountrySQL();
        
        $sqlState = '';
        if ($row['country_id'] != ''){
            $sqlState = $fn->getDDSQL('directory_state', array('condn' => "country_id = {$row['country_id']}"));
        } 
        
        $state = '';
        if ($cpCfg['m.directory.city.hasState']){
            $expState = array('detailValue' => $row['state_title']);                
            $state = $formObj->getDDRowBySQL($ln->gd('m.directory.city.lbl.state'), 'state_id', $sqlState, $row['state_id'], $expState);
        }
                               
        $fieldset1  = "
        {$formObj->getTBRow($ln->gd('m.directory.city.lbl.city'), 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getTextBoxRow($ln->gd('m.directory.city.lbl.cityCode'), 'city_code', $row['city_code'])} 
        {$formObj->getDDRowBySQL($ln->gd('m.directory.city.lbl.country'), 'country_id', $sqlCountry, $row['country_id'], $expCountry)}
        {$state}
		";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.city.lbl.mainDetails'), $fieldset1)}
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');

        //$country_id = $fn->getSessionParam('cp_country_id');
        $country_id = $fn->getSessionParam('country_id');
        $state_id   = $fn->getReqParam('state_id');

        $modCountry = getCPModuleObj('common_country');
        $sqlCountry = $modCountry->model->getCountrySQL();
        
        $state = '';
        if ($cpCfg['m.directory.city.hasState']){
            $exp = array(
                'condn' => "country_id = '{$country_id}'"
            );
            $sqlState = $fn->getDDSql('directory_state', $exp);

            $state = "
            <td>
                <select name='state_id'>
                    <option value=''>{$ln->gd('m.directory.city.lbl.state', 'State')}</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlState, $state_id)}
                </select>
            </td>   
            ";
        }
        
        $text = "
        <td class='fieldValue'>
            <select name='country_id'>
                <option value=''>{$ln->gd('m.directory.city.lbl.country', 'Country')}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCountry, $country_id)}
            </select>
        </td>
        {$state}  
        ";
        
        return $text;
    }
}