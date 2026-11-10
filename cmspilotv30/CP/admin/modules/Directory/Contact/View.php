<?
class CP_Admin_Modules_Directory_Contact_View extends CP_Common_Modules_Directory_Contact_View
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
            {$listObj->getListDataCell($row['city_name'])}
            {$listObj->getListDataCell($row['country_name'])}
            {$listObj->getListDataCell($row['cards_linked'])}
            {$listObj->getListDataCell($row['comment_count'])}
            {$listObj->getListDataCell($row['my_business_count'])}
            {$listObj->getListDateCell($row['creation_date'])}
            {$listObj->getListDateCell($row['modification_date'])}
            {$listObj->getListDateCell('')}
            {$listObj->getListDataCell($fn->getYesNo($row['subscribe']), "center")}
            {$listObj->getListPublishedImage($row['published']   , $row['contact_id'])}
            {$listObj->getListDataCell($row['contact_id'], 'center')}
            {$listObj->getListRowEnd($row['contact_id'])}
            ";

        	$rowCounter++;
		}

        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.directory.contact.lbl.firstName'), 'c.first_name')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.contact.lbl.lastName'), 'c.last_name')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.contact.lbl.email'), 'c.email')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.contact.lbl.mobile'), 'c.mobile')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.contact.lbl.city'), 'city_name')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.contact.lbl.country'), 'country_name')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.contact.lbl.loyaltyCard'), 'cards_linked')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.contact.lbl.reviewCount'))}
        {$listObj->getListHeaderCell($ln->gd('m.directory.contact.lbl.#businessfollowing'))}
        {$listObj->getListHeaderCell($ln->gd('m.directory.contact.lbl.joinedDate'), 'c.creation_date')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.contact.lbl.lastAccessedOn'))}
        {$listObj->getListHeaderCell($ln->gd('m.directory.contact.lbl.loginCount'))}
        {$listObj->getListHeaderCell($ln->gd('m.directory.contact.lbl.subscribed'), 'c.subscribe', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.contact.lbl.published'), 'c.published', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.directory.contact.lbl.id'), 'c.contact_id', 'headerCenter')}
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
        {$formObj->getTBRow($ln->gd('m.directory.contact.lbl.firstName'), 'first_name')}
        {$formObj->getTBRow($ln->gd('m.directory.contact.lbl.lastName'), 'last_name')}
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
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        
        $SQLSalutation = $fn->getValueListSQL('salutation');
        $exp = array('sqlType' => 'OneField');

        $expCity = array('detailValue' => $row['city_name']);
        $sqlCity = $fn->getDDSql('directory_city');

        $expArea = array('detailValue' => $row['area_name']);
        $sqlArea = $fn->getDDSql('directory_area');

        $expCountry  = array('detailValue' => $row['country_name']);
        $sqlCountry = "
        SELECT country_code
              ,name 
        FROM geo_country 
        ORDER BY country_code
        ";

        $expNoEdit = array('isEditable' => 0);
        $fieldset1 = "
        {$formObj->getDDRowBySQL($ln->gd('m.directory.contact.lbl.salutation'), 'salutation', $SQLSalutation, $row['salutation'], $exp)}
        {$formObj->getTBRow($ln->gd('m.directory.contact.lbl.firstName'), 'first_name', $row['first_name'])}
        {$formObj->getTBRow($ln->gd('m.directory.contact.lbl.lastName'), 'last_name', $row['last_name'])}
        {$formObj->getTBRow($ln->gd('m.directory.contact.lbl.firstName(Chinese)'), 'chi_first_name', $row['chi_first_name'])}
        {$formObj->getTBRow($ln->gd('m.directory.contact.lbl.lastName(Chinese)'), 'chi_last_name', $row['chi_last_name'])}
        {$formObj->getTBRow($ln->gd('m.directory.contact.lbl.aliasName'), 'alias_name', $row['alias_name'])}
        {$formObj->getTBRow($ln->gd('m.directory.contact.lbl.email'), 'email', $row['email'])}
        {$formObj->getTBRow($ln->gd('m.directory.contact.lbl.phone'), 'phone', $row['phone'])}
        {$formObj->getTBRow($ln->gd('m.directory.contact.lbl.mobile'), 'mobile', $row['mobile'])}
        ";
                
        $fieldset2 = "
        {$formObj->getTBRow($ln->gd('m.directory.contact.lbl.address1'), 'address1', $row['address1'])}
        {$formObj->getTBRow($ln->gd('m.directory.contact.lbl.address2'), 'address2', $row['address2'])}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.contact.lbl.city'), 'city_id', $sqlCity, $row['city_id'], $expCity)}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.contact.lbl.district/ Area'), 'area_id', $sqlArea, $row['area_id'], $expArea)}
        {$formObj->getTBRow($ln->gd('m.directory.contact.lbl.state'), 'address_state', $row['address_state'])}
        {$formObj->getDDRowBySQL($ln->gd('m.directory.contact.lbl.country'), 'country_code', $sqlCountry, $row['country_code'], $expCountry)}
        {$formObj->getTBRow($ln->gd('m.directory.contact.lbl.zipCode'), 'address_po_code', $row['address_po_code'])}
        ";

        $fieldset4 = "
        {$formObj->getYesNoRRow($ln->gd('m.directory.contact.lbl.published'), 'published', $row['published'])}
        {$formObj->getTBRow($ln->gd('m.directory.contact.lbl.password'), 'pass_word', $row['pass_word'])}
        {$formObj->getYesNoRRow($ln->gd('m.directory.contact.lbl.newsletterSubscribed'), 'subscribe', $row['subscribe'])}
        {$formObj->getTBRow($ln->gd('m.directory.contact.lbl.lastAccessedOn'), 'last_logged_in_date', 
                            $row['last_logged_in_date'], $expNoEdit)}
        {$formObj->getTBRow($ln->gd('m.directory.contact.lbl.logincount') , 'login_count', $row['login_count'], $expNoEdit)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.contact.lbl.mainDetails'), $fieldset1)}
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.contact.lbl.addressDetails'), $fieldset2)}
        {$formObj->getFieldSetWrapped($ln->gd('m.directory.contact.lbl.otherDetails'), $fieldset4)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $comment = getCPPluginObj('common_comment');

        $rows = "";

        $record_id = $fn->getIssetParam($row, 'contact_id');

        $text = "
        {$media->getRightPanelMediaDisplay($ln->gd('m.directory.contact.link.picture'), 'directory_contact', 'picture', $row)}
        {$displayLinkData->getLinkPortalMain('directory_contact', 'common_interestLink',$ln->gd('m.directory.contact.link.interestsLinked') , $row)}
        {$displayLinkData->getLinkPortalMain('directory_contact', 'directory_preferenceLink',$ln->gd('m.directory.contact.link.preferencesLinked') , $row)}
        {$displayLinkData->getLinkPortalMain('directory_contact', 'directory_cardsLink', $ln->gd('m.directory.contact.link.loyaltyCardsLinked'), $row)}
        {$displayLinkData->getLinkPortalMain('directory_contact', 'directory_businessLink', $ln->gd('m.directory.contact.link.businessesFollowing'), $row)}
        {$displayLinkData->getLinkPortalMain('directory_contact', 'directory_areaLink', $ln->gd('m.directory.contact.link.locationsLinked'), $row)}
        {$displayLinkData->getLinkPortalMain('directory_contact', 'directory_contactLink',$ln->gd('m.directory.contact.link.friendsLinked'), $row)}

        {$comment->getView(array(
             'roomName' => 'directory_contact'
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $interest_id    = $fn->getReqParam('interest_id');
        $card_id        = $fn->getReqParam('card_id');
        $subscribe      = $fn->getReqParam('subscribe');
        $special_search = $fn->getReqParam('special_search');

        $interestText   = "";
        
        if ($cpCfg['m.common.contact.showInterest'] == 1) {
            $sqlCombo = "
            SELECT interest_id
                  ,title 
            FROM interest 
            ORDER BY title
            ";

            $interestText = "
            <td>
                <select name='interest_id' >
                    <option value=''>{$ln->gd('m.directory.contact.lbl.interestGroup')}</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlCombo, $interest_id)}
                </select>
            </td>
            ";
        }
        $sqlCards = $fn->getDDSql('directory_cards');

        $loyaltyCardText = "
        <td>
            <select name='card_id'>
                <option value=''>{$ln->gd('m.directory.contact.lbl.loyaltyCard')}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCards, $card_id)}
            </select>
        </td>
        ";

        //==================================================================//
        $spArray = array(
              'Subscribed'
             ,'Not-Subscribed'
             ,'Flagged'
             ,'Not-Flagged'
             ,'Published'
             ,'Not-Published'
        );

        $text = "
        {$interestText}
        {$loyaltyCardText}
        <td>
            <select name='special_search'>
                <option value=''>{$ln->gd('m.directory.contact.lbl.specialSearch')}</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
            </select>
        </td>
        ";
        
        return $text;
    }
}