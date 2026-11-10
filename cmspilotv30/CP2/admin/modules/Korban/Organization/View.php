<?
class CP_Admin_Modules_Korban_Organization_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        
        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            $email     = $row['email'];

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['name'])}
            {$listObj->getListDataCell($row['phone'])}
            {$listObj->getListDataCell("<a href='{$email}'>{$email}</a>")}
            {$listObj->getListPublishedImage($row['published'], $row['organization_id'])}
            {$listObj->getListRowEnd($row['organization_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Organization Name', 'o.name')}
        {$listObj->getListHeaderCell('Phone', 'o.phone')}
        {$listObj->getListHeaderCell('Email', 'o.email')}
        {$listObj->getListHeaderCell('Published', 'o.published', 'headerCenter')}
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

        $fielset = "
        {$formObj->getTBRow('Organization Name', 'name')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');

        $fieldset1 = "
        {$formObj->getTBRow('Organization Name', 'name', $row['name'])}
        {$formObj->getTBRow('Phone', 'phone', $row['phone'])}
        {$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}
        {$formObj->getTBRow('Fax', 'fax', $row['fax'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        ";

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name']);

        $fieldset2 = "
        {$formObj->getTBRow('Address 1', 'address1', $row['address1'])}
        {$formObj->getTBRow('Address 2', 'address2', $row['address2'])}
        {$formObj->getTBRow('City/Town', 'address_city', $row['address_city'])}
        {$formObj->getDDRowBySQL('Country', 'address_country_code', $sqlCountry, $row['address_country_code'], $expCountry)}
        {$formObj->getTBRow('Zip Code', 'address_po_code', $row['address_po_code'])}
        ";

        $fieldset3 = "
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        {$formObj->getTARow('Operating hours', 'operating_hours', $row['operating_hours'])}
        {$formObj->getTARow('Remarks', 'remarks', $row['remarks'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Organization Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Address Details', $fieldset2)}
        {$formObj->getFieldSetWrapped('Other Details', $fieldset3)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){

        $text = "
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $special_search = $fn->getReqParam('special_search');

        //==================================================================//
        $spArray = array(
              "Flagged"
             ,"Not-Flagged"
             ,"Published"
             ,"Not-Published"
        );

        $text = "
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
            </select>
        </td>
        ";

        return $text;
    }
}