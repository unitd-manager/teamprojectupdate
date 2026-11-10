<?
class CP_Admin_Modules_Ek_Parent_View extends CP_Common_Modules_Ek_Parent_View
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
            {$listObj->getGoToDetailText($rowCounter, $row['first_name'])}
            {$listObj->getListDataCell($row['last_name'])}
            {$listObj->getListDataCell($row['email'])}
            {$listObj->getListDataCell($row['gender'])}
            {$listObj->getListDataCell($row['phone'])}
            {$listObj->getListPublishedImage($row['published'], $row['parent_id'])}
            {$listObj->getListDataCell($row['parent_id'], 'center')}
            {$listObj->getListRowEnd($row['parent_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('First Name', 'p.first_name')}
        {$listObj->getListHeaderCell('Last Name', 'p.last_name')}
        {$listObj->getListHeaderCell('Email', 'p.email')}
        {$listObj->getListHeaderCell('Gender', 'p.gender')}
        {$listObj->getListHeaderCell('Phone', 'p.phone')}
        {$listObj->getListHeaderCell('Published', 'p.published', 'headerCenter')}
        {$listObj->getListHeaderCell('ID', 'p.parent_id' , 'headerCenter')}
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
        {$formObj->getTBRow('Email', 'email')}
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

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country']);
        
        $expVl   = array('sqlType' => 'OneField');

        $gendArr = array('Male', 'Female');

        $fielset1 = "
        {$formObj->getTBRow('First Name', 'first_name', $row['first_name'])}
        {$formObj->getTBRow('Last Name', 'last_name', $row['last_name'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        {$formObj->getRRow('Gender', 'gender', $row['gender'], $gendArr, array('rowCls' => 'yesNo'))}
        {$formObj->getTBRow('Age', 'age', $row['age'])}
        {$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}
        {$formObj->getTBRow('Home Phone', 'phone', $row['phone'])}
		";
		
        $fielset2 = "
        {$formObj->getTBRow('Address', 'address1', $row['address1'])}
        {$formObj->getTBRow('Street', 'address2', $row['address2'])}
        {$formObj->getTBRow('City', 'city', $row['city'])}
        {$formObj->getTBRow('State', 'state', $row['state'])}
        {$formObj->getTBRow('Zip Code', 'zip_code', $row['zip_code'])}
        {$formObj->getDDRowBySQL('Country', 'country_code', $sqlCountry, $row['country_code'], $expCountry)}
		";
		
        $fielset3 = "
        {$formObj->getYesNoRRow('Login Enabled', 'login_enabled', $row['login_enabled'])}
        {$formObj->getTBRow('Password', 'pass_word', $row['pass_word'])}
        {$formObj->getTARow('Remarks', 'remarks', $row['remarks'])}
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
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');

        $record_id = $fn->getIssetParam($row, 'parent_id');
        
        $text ="
        {$media->getRightPanelMediaDisplay("Picture", "ek_parent", "picture", $row)}
        {$displayLinkData->getLinkPortalMain("ek_parent", "ek_studentLink", "Student Linked", $row)}
        ";
        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {

        $text = "
        ";       
        
        return $text;
    }
}