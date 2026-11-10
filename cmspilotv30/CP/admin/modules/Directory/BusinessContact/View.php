<?
class CP_Admin_Modules_Directory_BusinessContact_View extends CP_Common_Modules_Directory_BusinessContact_View
{
    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $rowCounter = 0;
        $rows = '';

        foreach ($dataArray as $row){
            $email = $row['email'];

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['first_name'])}
            {$listObj->getGoToDetailText($rowCounter, $row['last_name'])}
            <td><div align='left'><a href='mailto:{$email}'>{$email}</a></div></td>
            {$listObj->getListDataCell($row['mobile']   )}
            {$listObj->getListDateCell($row['creation_date']   )}
            {$listObj->getListDataCell($fn->getYesNo($row['subscribe']), "center")}
            {$listObj->getListPublishedImage($row['published'], $row['business_contact_id'])}
            {$listObj->getListDataCell($row['business_contact_id'], 'center')}
            {$listObj->getListRowEnd($row['business_contact_id'])}
            ";

        	$rowCounter++;
		}
         
        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.directory.businessContact.lbl.firstName'), 'bc.first_name')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.businessContact.lbl.lastName'), 'bc.last_name')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.businessContact.lbl.email'), 'bc.email')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.businessContact.lbl.mobile'), 'bc.mobile')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.businessContact.lbl.joinedDate'), 'bc.creation_date')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.businessContact.lbl.subscribed'), 'bc.subscribe', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.businessContact.lbl.published'), 'bc.published', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.businessContact.lbl.id'), 'bc.business_contact_id', 'headerCenter')}
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

        $fielset = "
        {$formObj->getTBRow($ln->gd('m.directory.businessContact.lbl.firstName'), 'first_name')}
        {$formObj->getTBRow($ln->gd('m.directory.businessContact.lbl.lastName'), 'last_name')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset)}
        ";

        return $text;
    }

    /**
     *
     */
	function getEdit($row) {
        $formObj = Zend_Registry::get('formObj');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        
        $SQLSalutation = $fn->getValueListSQL('salutation');
        $exp = array('sqlType' => 'OneField');

        $expCity = array('detailValue' => $row['city_name']);
        $sqlCity = $fn->getDDSql('directory_city');

        $expArea = array('detailValue' => $row['area_name']);
        $sqlArea = $fn->getDDSql('directory_area');

        $expCountry    = array('detailValue' => $row['country_name']);
        $sqlCountry = "
        SELECT country_code
              ,name 
        FROM geo_country 
        ORDER BY country_code
        ";

        $fieldset1 = "
        {$formObj->getDDRowBySQL($ln->gd('m.directory.businessContact.lbl.salutation'), 'salutation', $SQLSalutation, $row['salutation'], $exp)}
        {$formObj->getTBRow($ln->gd('m.directory.businessContact.lbl.firstName'), 'first_name', $row['first_name'])}
        {$formObj->getTBRow($ln->gd('m.directory.businessContact.lbl.lastName'), 'last_name', $row['last_name'])}
        {$formObj->getTBRow($ln->gd('m.directory.businessContact.lbl.firstName(Chinese)'), 'chi_first_name', $row['chi_first_name'])}
        {$formObj->getTBRow($ln->gd('m.directory.businessContact.lbl.lastName(Chinese)'), 'chi_last_name', $row['chi_last_name'])}
        {$formObj->getTBRow($ln->gd('m.directory.businessContact.lbl.email'), 'email', $row['email'])}
        {$formObj->getTBRow($ln->gd('m.directory.businessContact.lbl.phone'), 'phone', $row['phone'])}
        {$formObj->getTBRow($ln->gd('m.directory.businessContact.lbl.mobile'), 'mobile', $row['mobile'])}
        ";
                
        $fieldset2 = "
        {$formObj->getTBRow($ln->gd('m.directory.businessContact.lbl.address1'), 'address1', $row['address1'])}
        {$formObj->getTBRow($ln->gd('m.directory.businessContact.lbl.address2'), 'address2', $row['address2'])}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.businessContact.lbl.city'), 'city_id', $sqlCity, $row['city_id'], $expCity)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.businessContact.lbl.district/ Area'), 'area_id', $sqlArea, $row['area_id'], $expArea)}
        {$formObj->getTBRow($ln->gd('m.directory.businessContact.lbl.state'), 'address_state', $row['address_state'])}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.businessContact.lbl.country'), 'country_code', $sqlCountry, $row['country_code'], $expCountry)}
        {$formObj->getTBRow($ln->gd('m.directory.businessContact.lbl.zipCode'), 'address_po_code', $row['address_po_code'])}
        ";

        $subscribed = ($tv['newRecord'] == 1) ? 1 : $row['subscribe'];

        $fieldset4 = "
        {$formObj->getYesNoRRow($ln->gd('m.directory.businessContact.lbl.published'), 'published', $row['published'])}
        {$formObj->getTBRow($ln->gd('m.directory.businessContact.lbl.password'), 'pass_word', $row['pass_word'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.businessContact.lbl.newsletterSubscribed'), 'subscribe', $subscribed)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.businessContact.lbl.mainDetails'), $fieldset1)}
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.businessContact.lbl.addressDetails'), $fieldset2)}
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.businessContact.lbl.otherDetails'), $fieldset4)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $comment = getCPPluginObj('common_comment');

        $record_id = $fn->getIssetParam($row, 'business_contact_id');

        $text = "
        {$media->getRightPanelMediaDisplay($ln->gd('m.directory.businessContact.link.picture'), 'directory_businessContact', 'picture', $row)}
        {$displayLinkData->getLinkPortalMain('directory_businessContact', 'common_interestLink', $ln->gd('m.directory.businessContact.link.interestsLinked'), $row)}
        {$displayLinkData->getLinkPortalMain('directory_businessContact', 'directory_businessLink', $ln->gd('m.directory.businessContact.link.businessesLinked'), $row)}
        {$comment->getView(array(
             'roomName' => 'directory_businessContact'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $interest_id    = $fn->getReqParam('interest_id');
        $business_id    = $fn->getReqParam('business_id');
        $subscribe      = $fn->getReqParam('subscribe');
        $special_search = $fn->getReqParam('special_search');

        $sqlBusiness = "
        SELECT business_id
              ,business_name 
        FROM business 
        ORDER BY business_name
        ";

        $sqlInterest = "
        SELECT interest_id
              ,title 
        FROM interest 
        ORDER BY title
        ";

        //==================================================================//
        $spArray = array(
              "Subscribed"
             ,"Not-Subscribed"
             ,"Flagged"
             ,"Not-Flagged"
             ,"Published"
             ,"Not-Published"
        );

        $text = "
       <td>
            <select name='interest_id' >
                <option value=''>{$ln->gd('m.directory.businessContact.lbl.interestGroup')}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlInterest, $interest_id)}
            </select>
        </td>
        <td>
            <select name='special_search'>
                <option value=''>{$ln->gd('m.directory.businessContact.lbl.specialSearch')}</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
            </select>
        </td>
        ";
        
        return $text;
    }

}