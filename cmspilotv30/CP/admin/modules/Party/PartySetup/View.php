<?
class CP_Admin_Modules_Party_PartySetup_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['event_name'])}
            {$listObj->getListDataCell($row['celebrant_name'])}
            {$listObj->getListDateCell($row['event_date'])}
            {$listObj->getListDataCell($row['event_time'])}
            {$listObj->getListDataCell($row['charity_title'])}
            {$listObj->getListDataCell($row['gift_chosen'])}
            {$listObj->getListDataCell($row['contact_name'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($row['creation_date'])}
            {$listObj->getListDataCell($row['party_setup_id'], 'center')}
            {$listObj->getListRowEnd($row['party_setup_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Event Name', 'ps.event_name')}
        {$listObj->getListHeaderCell('Celebrant Name', 'ps.celebrant_name')}
        {$listObj->getListHeaderCell('Event Date', 'ps.event_date')}
        {$listObj->getListHeaderCell('Event Time', 'ps.event_time')}
        {$listObj->getListHeaderCell('Charity', 'charity_title')}
        {$listObj->getListHeaderCell('Gift', 'ps.gift_chosen')}
        {$listObj->getListHeaderCell('Contact Name', 'contact_name')}
        {$listObj->getListHeaderCell('Status', 'status')}
        {$listObj->getListHeaderCell('Creation', 'creation_date')}
        {$listObj->getListHeaderCell('Event Id', 'party_setup_id', 'center')}
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
        {$formObj->getTBRow('Event Name', 'event_name')}
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

        $sqlStatus = $fn->getValueListSQL('partyStatus');
        $sqlPercentToDonate = $fn->getValueListSQL('partyPercentageToDonate');
        $exp = array('sqlType' => 'OneField');



        $contactText = '';
        $cardText = '';
        $charityText = '';
        if ($formObj->mode == 'detail') {
            $expContact = array('displayText' => $row['contact_name']);
            $contactText = $fn->getRecordDetailLink('common_contact', 'record_id',
                               $row['contact_id'], $expContact);
            $contactText = $formObj->getTBRow('Contact Name', 'contact_id', $contactText);

            //card
            $expCharity = array('displayText' => $row['charity_title']);
            $charityText = $fn->getRecordDetailLink('party_charity', 'record_id',
                               $row['charity_id'], $expCharity);
            $charityText = $formObj->getTBRow('Charity', 'charity_id', $charityText);

            //charity
            $expCard = array('displayText' => $row['card_title']);
            $cardText = $fn->getRecordDetailLink('party_card', 'record_id',
                               $row['card_id'], $expCard);
            $cardText = $formObj->getTBRow('Card', 'card_id', $cardText);


        } else {
            $expContact = array('displayText' => $row['contact_name']);
            $modContact = getCPModuleObj('common_contact');
            $sqlContact = $modContact->model->getContactSQL();
            $contactText = $formObj->getDDRowBySQL('Contact Name', 'contact_id',
                               $sqlContact, $row['contact_id'], $expContact);

            //card
            $modCard = getCPModuleObj('party_card');
            $sqlCard = $modCard->model->getCardSQL();
            $expCard = array('detailValue' => $row['card_title']);
            $cardText = $formObj->getDDRowBySQL('Card', 'card_id', $sqlCard, $row['card_id'], $expCard);

            //charity
            $modCharity = getCPModuleObj('party_charity');
            $sqlCharity = $modCharity->model->getCharitySQL();
            $expCharity = array('detailValue' => $row['charity_title']);
            $charityText = $formObj->getDDRowBySQL('Charity', 'charity_id', $sqlCharity, $row['charity_id'], $expCharity);

        }


        $expCheck = array('isLabelOnLeft' => true);



        $fieldset1 = "
        {$formObj->getTBRow('Event id', 'party_setup_id', $row['party_setup_id'], $formObj->expNoEdit)}
        {$formObj->getTBRow('Event Name', 'event_name', $row['event_name'] )}
        {$formObj->getTBRow('Celebrant Name', 'celebrant_name', $row['celebrant_name'])}
        {$formObj->getDateRow('Event Date', 'event_date', $row['event_date'])}
        {$formObj->getTimeRow('Event Time', 'event_time', $row['event_time'])}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $exp)}
        {$contactText}
        {$formObj->getSingleCheckBoxRow('Is this a test', 'is_test', $row['is_test'], $expCheck)}
        ";

        $fieldset2 = "
        {$formObj->getTARow('Event Detail' , 'event_detail', $row['event_detail'])}
        {$formObj->getTARow('Additional Instruction' , 'additional_instruction', $row['additional_instruction'])}
        {$charityText}
        {$cardText}
        {$formObj->getTBRow('Gift', 'gift_chosen', $row['gift_chosen'] )}
        {$formObj->getDDRowBySQL('Percentage To Donate', 'percentage_to_donate', $sqlPercentToDonate, $row['percentage_to_donate'], $exp)}
        {$formObj->getSingleCheckBoxRow('Allow Guest To Blind Donation', 'allow_guest_to_blind_donation', $row['allow_guest_to_blind_donation'], $expCheck)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('More Details', $fieldset2)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $media = Zend_Registry::get('media');
        $displayLinkData = Zend_Registry::get('displayLinkData');

        $text = "
        {$media->getRightPanelMediaDisplay('User\'s Card Image', 'party_partySetup', 'picture', $row)}
        {$media->getRightPanelMediaDisplay('Attachments', 'party_partySetup', 'attachment', $row)}
        {$displayLinkData->getLinkPortalMain('party_partySetup', 'party_messageLink', 'Message Linked', $row)}
        {$displayLinkData->getLinkPortalMain('party_partySetup', 'party_guestLink', 'Guests Linked', $row)}
        {$displayLinkData->getLinkPortalMain('party_partySetup', 'ecommerce_orderLink', 'Orders Linked', $row)}
        ";

        return $text;
    }


    function getQuickSearch() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');

        $start_date  = $fn->getReqParam('start_date');
        $end_date    = $fn->getReqParam('end_date');

        //==================================================================//
        $spArray = array('Test Records');
        
        $text = "
        <td class='dateRange'>
            Start Date:
            <input type='text' allowEdit='1' name='start_date' class='fld_date' id='fld_start_date' value='{$start_date}' />
            End Date:
            <input type='text' allowEdit='1' name='end_date' class='fld_date' id='fld_end_date' value='{$end_date}' />
        </td>
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