<?
class CP_Admin_Modules_Ek_School_View extends CP_Common_Modules_Ek_School_View
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $rows  = "";
        $rowCounter = 0;
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['school_type'])}
            {$listObj->getListDataCell($row['theme'])}
            {$listObj->getListDataCell($row['skin'])}
            {$listObj->getListPublishedImage($row['published'], $row['school_id'])}
            {$listObj->getListDataCell($row['school_id'], 'center')}
            {$listObj->getListRowEnd($row['school_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 's.title')}
        {$listObj->getListHeaderCell('School Type', 's.school_type')}
        {$listObj->getListHeaderCell('Theme', 's.theme')}
        {$listObj->getListHeaderCell('Skin', 's.skin')}
        {$listObj->getListHeaderCell('Published', 's.published', 'headerCenter')}
        {$listObj->getListHeaderCell('ID', 's.school_id' , 'headerCenter')}
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

        $fieldset = "
        {$formObj->getTBRow('Title', 'title')}
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

        $sqlSchoolType = $fn->getValueListSQL('schoolType');
        $expVl = array('sqlType' => 'OneField');

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country']);
        
        $fielset1 = "
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getDDRowBySQL('School Type', 'school_type', $sqlSchoolType, $row['school_type'], $expVl)}
        {$formObj->getDDRowByArr('Theme', 'theme', $cpCfg['cp.availableThemes'], $row['theme'])}
        {$formObj->getDDRowByArr('Skin', 'skin', $cpCfg['cp.availableSkins'], $row['skin'])}
        {$formObj->getTBRow('Site Url', 'site_url', $row['site_url'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        {$formObj->getTBRow('Phone', 'phone', $row['phone'])}
		";
		
        $fielset2 = "
        {$formObj->getTBRow('Address', 'address1', $row['address1'])}
        {$formObj->getTBRow('Street', 'address2', $row['address2'])}
        {$formObj->getTBRow('City', 'address_city', $row['address_city'])}
        {$formObj->getTBRow('State', 'address_state', $row['address_state'])}
        {$formObj->getDDRowBySQL('Country', 'address_country_code', $sqlCountry, $row['address_country_code'], $expCountry)}
        {$formObj->getTBRow('Zip Code', 'address_po_code', $row['address_po_code'])}
		";
		
        $fielset3 = "
        {$formObj->getTBRow('Licensed User', 'licensed_users', $row['licensed_users'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped('General Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Address Details', $fielset2)}
        {$formObj->getFieldSetWrapped('Other Details', $fielset3)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');

        $record_id = $fn->getIssetParam($row, 'parent_id');
        
        $text ="
        {$media->getRightPanelMediaDisplay("Picture", "ek_school", "logo", $row)}
        ";
        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $school_type    = $fn->getReqParam('school_type');
        $sqlSchoolType  = $fn->getValueListSQL('schoolType');

        $text = "
        <td>
            <select name='school_type'>
                <option value=''>School Type</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlSchoolType, $school_type)}
            </select>
        </td>
        ";       
        
        return $text;
    }
}