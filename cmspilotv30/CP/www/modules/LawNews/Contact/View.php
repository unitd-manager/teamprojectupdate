<?
class CP_Www_Modules_LawNews_Contact_View extends CP_Common_Lib_ModuleViewAbstract
{
    var $jssKeys = array('jqForm-3.15');

    //========================================================//
    function getNew() {
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');

        $c = &$this->controller;

        $formAction = '/index.php?module=lawNews_contact&_spAction=add&showHTML=0';

        $expSal = array(
             'firstOptionLabel' => $ln->gd('cp.form.fld.salutation.firstOptionLabel')
            ,'required' => true
        );

        $expCompanyType = array(
             'firstOptionLabel' => $ln->gd('cp.form.fld.companyType.firstOptionLabel')
            ,'required' => true
        );

        $expPass = array(
             'password' => 1
            ,'required' => true
            ,'notes'    => $ln->gd('cp.form.fld.password.notes')
            ,'disableAutoComplete'    => true
        );

        $expConfPass = array(
             'password' => 1
            ,'required' => true
            ,'disableAutoComplete'    => true
        );

        $expReq = array(
            'required' => true
            ,'disableAutoComplete'    => true
        );

        $expCommon = array(
            'disableAutoComplete'    => true
        );

        $text = "
        <form name='registerForm' id='registerForm' class='yform columnar cpJqForm' method='post' action='{$formAction}'>
            <fieldset>
                <h1 class='ruled'>{$ln->gd('m.lawNews.contact.form.subscribe.heading')}</h1>
                <p>{$ln->gd('m.lawNews.contact.form.subscribe.info')}</p>
                {$formObj->getTBRow($ln->gd('cp.form.fld.email.lbl'), 'email', '', $expReq)}
                {$formObj->getTBRow($ln->gd('cp.form.fld.password.lbl'), 'pass_word', '', $expPass)}
                {$formObj->getTBRow($ln->gd('cp.form.fld.confirmPassword.lbl'), 'cpass_word', '', $expConfPass)}
                {$formObj->getDDRowByVL($ln->gd('cp.form.fld.salutation.lbl'), 'salutation', 'salutation', '', $expSal)}
                {$formObj->getTBRow($ln->gd('cp.form.fld.firstName.lbl'), 'first_name', '', $expReq)}
                {$formObj->getTBRow($ln->gd('cp.form.fld.lastName.lbl'), 'last_name', '', $expReq)}
                {$formObj->getTBRow($ln->gd('cp.form.fld.position.lbl'), 'position', '', $expCommon)}
                {$formObj->getTBRow($ln->gd('cp.form.fld.companyName.lbl'), 'company_name', '', $expReq)}
                {$formObj->getDDRowByVL($ln->gd('cp.form.fld.companyType.lbl'), 'company_type', 'companyType', '', $expCompanyType)}
                {$formObj->getTBRow($ln->gd('cp.form.fld.address1.lbl'), 'address1', '', $expReq)}
                {$formObj->getTBRow($ln->gd('cp.form.fld.address2.lbl'), 'address2', '', $expCommon)}
                {$formObj->getTBRow($ln->gd('cp.form.fld.address3.lbl'), 'address3', '', $expCommon)}
                {$formObj->getTBRow($ln->gd('cp.form.fld.addressCity.lbl'), 'address_city', '', $expReq)}
                {$formObj->getTBRow($ln->gd('cp.form.fld.addressState.lbl'), 'address_state', '', $expCommon)}
                {$formObj->getTBRow($ln->gd('cp.form.fld.addressZipCode.lbl'), 'address_po_code', '', $expCommon)}
                {$formObj->getTBRow($ln->gd('cp.form.fld.phone.lbl'), 'phone', '', $expReq)}
                {$formObj->getTBRow($ln->gd('cp.form.fld.fax.lbl'), 'fax', '', $expReq)}
                {$this->getDataProtectionRow()}
      	    	{$formObj->getCaptchaImage($ln->gd('cp.form.fld.antiSpamCode.lbl'), 'captcha_code', $expCommon)}
                <div class='type-button'>
                    <div class='floatbox'>
                        <div class='float_left'>
                            <input type='submit' value='{$ln->gd('cp.form.btn.submit')}'/>
                            <input type='reset'value='{$ln->gd('cp.form.btn.cancel')}' onclick='history.back()'/>
                        </div>
                    </div>
                </div>
                <input type='submit' name='x_submit' class='submithidden' />
                <input type='hidden' name='successMsg' value='" . htmlspecialchars($ln->gd('m.membership.contact.form.new.message.success'), ENT_QUOTES) . "' />
            </fieldset>
        </form>
        ";

        return $text;
    }

    //==================================================================//
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $expSal = array(
             'firstOptionLabel' => $ln->gd('cp.form.fld.salutation.firstOptionLabel')
            ,'required' => true
        );

        $expCompanyType = array(
             'firstOptionLabel' => $ln->gd('cp.form.fld.companyType.firstOptionLabel')
            ,'required' => true
        );

        $expReq = array(
            'required' => true
            ,'disableAutoComplete'    => true
        );

        $expCommon = array(
            'disableAutoComplete'    => true
        );

        $fieldset1 = "
        {$formObj->getTBRow($ln->gd('cp.form.fld.email.lbl'), 'email', $row['email'], array('isEditable' => 0))}
        {$formObj->getDDRowByVL($ln->gd('cp.form.fld.salutation.lbl'), 'salutation', 'salutation', $row['salutation'], $expSal)}
        {$formObj->getTBRow($ln->gd('cp.form.fld.firstName.lbl'), 'first_name', $row['first_name'], $expReq)}
        {$formObj->getTBRow($ln->gd('cp.form.fld.lastName.lbl'), 'last_name', $row['last_name'], $expReq)}
        {$formObj->getTBRow($ln->gd('cp.form.fld.position.lbl'), 'position', $row['position'], $expCommon)}
        {$formObj->getTBRow($ln->gd('cp.form.fld.companyName.lbl'), 'company_name', $row['company_name'], $expReq)}
        {$formObj->getDDRowByVL($ln->gd('cp.form.fld.companyType.lbl'), 'company_type', 'companyType', $row['company_type'], $expCompanyType)}
        ";

        $fieldset2 = "
        {$formObj->getTBRow($ln->gd('cp.form.fld.address1.lbl'), 'address1', $row['address1'], $expReq)}
        {$formObj->getTBRow($ln->gd('cp.form.fld.address2.lbl'), 'address2', $row['address2'], $expCommon)}
        {$formObj->getTBRow($ln->gd('cp.form.fld.address2.lbl'), 'address2', $row['address3'], $expCommon)}
        {$formObj->getTBRow($ln->gd('cp.form.fld.addressCity.lbl'), 'address_city', $row['address_city'], $expReq)}
        {$formObj->getTBRow($ln->gd('cp.form.fld.addressState.lbl'), 'address_state', $row['address_state'], $expCommon)}
        {$formObj->getTBRow($ln->gd('cp.form.fld.addressZipCode.lbl'), 'address_po_code', $row['address_po_code'], $expCommon)}
        {$formObj->getTBRow($ln->gd('cp.form.fld.phone.lbl'), 'phone', $row['phone'], $expReq)}
        {$formObj->getTBRow($ln->gd('cp.form.fld.fax.lbl'), 'fax', $row['fax'], $expReq)}
        ";

        $dialogMessage = "<input name='dialogMessage' type='hidden' value='{$ln->gd('m.lawNews.contact.form.message.editSuccess')}' />";

        $text = "
        <h2 class='ruled'>{$ln->gd('m.lawNews.contact.form.registrationDetails.heading')}</h2>
        <p>{$ln->gd('m.lawNews.contact.form.registrationDetails.info')}</p>
        <h2 class='ruled mt20'>{$ln->gd('m.lawNews.contact.form.yourDetails.heading')}</h2>
        {$formObj->getFieldSetWrapped('', $fieldset1)}
        <h2 class='ruled mt20'>{$ln->gd('m.lawNews.contact.form.address.heading')}</h2>
        {$formObj->getFieldSetWrapped('', $fieldset2)}
        {$this->getDataProtectionRow($row)}
        {$dialogMessage}
        <div class='type-button'>
            <div class=' floatbox'>
                <div class='float_left'>
                    <input type='submit' value='{$ln->gd('cp.form.btn.update')}'/>
                    <input type='reset' value='{$ln->gd('cp.form.btn.cancel')}' onclick='history.back()'/>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     * @param type $row
     * @return type
     */
    function getDataProtectionRow($row = array()){
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $dont_contact_by_phone = $fn->getIssetParam($row, 'dont_contact_by_phone');
        $dont_contact_by_fax   = $fn->getIssetParam($row, 'dont_contact_by_fax');
        $agree_contact_by_third_party = $fn->getIssetParam($row, 'agree_contact_by_third_party', 1);

        $expAgreeContact = array(
             'useKey' => 1
            ,'singleValue' => true
        );

        $doNotContactPhoneArr = array(
             '1' => $ln->gd('cp.form.fld.doNotContactByPhone.text')
        );

        $doNotContactFaxArr = array(
             '1' => $ln->gd('cp.form.fld.doNotContactByFax.text')
        );

        $agreeContact3rdPartyArr = array(
             '1' => $ln->gd('cp.form.fld.agreeContactBy3rdParty.text')
        );

        $agreeTermsArr = array(
            '1' => $ln->gd('cp.form.fld.agreeTerms.text')
        );

        $agreePrivacyArr = array(
            '1' => $ln->gd('cp.form.fld.agreePrivacy.text')
        );

        $fieldset = "
        {$formObj->getCheckBoxArrRowByArr(
                 $ln->gd('cp.form.fld.doNotContactByPhone.lbl')
                ,'dont_contact_by_phone'
                ,$doNotContactPhoneArr
                ,$dont_contact_by_phone
                ,$expAgreeContact
        )}
        {$formObj->getCheckBoxArrRowByArr(
                 $ln->gd('cp.form.fld.doNotContactByFax.lbl')
                , 'dont_contact_by_fax'
                , $doNotContactFaxArr
                , $dont_contact_by_fax
                , $expAgreeContact
        )}
        {$formObj->getCheckBoxArrRowByArr(
                 $ln->gd('cp.form.fld.agreeContactBy3rdParty.lbl')
                , 'agree_contact_by_third_party'
                , $agreeContact3rdPartyArr
                , $agree_contact_by_third_party
                , $expAgreeContact
        )}
        {$formObj->getCheckBoxArrRowByArr(
                 $ln->gd('cp.form.fld.agreeTerms.lbl')
                , 'agree_terms'
                , $agreeTermsArr
                , array()
                , array('useKey' => 1)
        )}
        {$formObj->getCheckBoxArrRowByArr(
                 $ln->gd('cp.form.fld.agreePrivacy.lbl')
                , 'agree_privacy'
                , $agreePrivacyArr
                , array()
                , array('useKey' => 1)
        )}
        ";

        $text = "
        <div class='dataProtection'>
            <h2 class='ruled mt20'>{$ln->gd('m.lawNews.contact.form.dataProtection.heading')}</h2>
            <p>{$ln->gd('m.lawNews.contact.form.dataProtection.info')}</p>
            {$formObj->getFieldSetWrapped('', $fieldset)}
        </div>
        ";

        return $text;
    }


    /**
     *
     * @return json
     */
    function getSaveToMyClips(){
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";

        $content_id = $fn->getReqParam('content_id');

        $json = array();

        if ($content_id == '' || !isLoggedInWWW()){
            return json_encode($json);
        }

        $numRowsContent = $fn->getRecordCount('content', "content_id = '{$content_id}' AND published = 1");
        if ($numRowsContent == 0){
            $json['status']  = 'error';
            $json['message'] = $ln->gd('m.lawNews.contact.saveToMyClips.message.error');
            return json_encode($json);
        }

        $contact_id = $_SESSION['cpContactId'];
        $numRowsContentContact = $fn->getRecordCount('contact_content', "content_id = '{$content_id}' AND contact_id = {$contact_id}");

        if($numRowsContentContact == 0){
            $fa = array();

            $fa['content_id'] = $content_id;
            $fa['contact_id'] = $contact_id;
            $fa['creation_date'] = date("Y-m-d H:i:s");
            $fa['modification_date'] = date("Y-m-d H:i:s");

            $SQL         = $dbUtil->getInsertSQLStringFromArray($fa, "contact_content");
            $result      = $db->sql_query($SQL);
        }

        $json['status']  = 'success';
        $json['message'] = $ln->gd('m.lawNews.contact.saveToMyClips.message.success');
        return json_encode($json);

    }

    /**
     *
     * @return type
     */
    function getMyClippings(){
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');

        $dataArray = $this->model->getMyClippingsArray();
        $rows = '';
        foreach ($dataArray as $row) {
            $url = $cpUrl->getUrlByRecord($row, 'content_id');
            $target = ($row['external_link'] != '') ?  "target='_blank'" : '';

            $deleteUrl = $cpUrl->getUrlByCatType('My Clippings')."?_action=delete&id={$row['contact_content_id']}";
            $rows .= "
            <li>
                <div class='floatbox'>
                    <div class='float_left'>
                        <a href='{$url}' {$target}>{$row['title']}</a>
                    </div>
                    <div class='float_right'>
                        <a href='javascript:void(0)' link='{$deleteUrl}' class='btn delete'>{$ln->gd('cp.lbl.delete')}</a>
                    </div>
                </div>
            </li>
            ";
        }

        $text = "
        <div class='myClippingsList'>
            <h1 class='ruled'>{$ln->gd('m.lawNews.contact.myClippings.heading')}</h1>
            <p>{$ln->gd('m.lawNews.contact.myClippings.info')}</p>
            <ul class='noDefault  highlight'>
                {$rows}
            </ul>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getMyAccount(){
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');

        $text = "
        <div class='myAccount'>
            <h1 class='ruled'>{$ln->gd('m.lawNews.contact.myAccount.heading')}</h1>
            <div class='highlight'>
                <p><strong>{$ln->gd('m.lawNews.contact.myAccount.welcome')} {$_SESSION['cpUserFullNameWWW']}</strong></p>
                <ul class='noDefault'>
                    <li>
                        <h2 class='ruled'>{$ln->gd('m.lawNews.contact.myAccount.myPersonalDetails.heading')}</h2>
                        <p>{$ln->gd('m.lawNews.contact.myAccount.myPersonalDetails.info')}</p>
                        <p>
                            <a href='{$cpUrl->getUrlByCatType('My Profile')}' class='btn'>{$ln->gd('m.lawNews.contact.btn.updateProfile')}</a>
                        </p>
                        <p>
                            <a href='{$cpUrl->getUrlByCatType('Change Password')}' class='btn'>{$ln->gd('m.lawNews.contact.btn.changePassword')}</a>
                        </p>
                    </li>
                    <li>
                        <h2 class='ruled'>{$ln->gd('m.lawNews.contact.myAccount.myClippings.heading')}</h2>
                        <p>{$ln->gd('m.lawNews.contact.myAccount.myClippings.info')}</p>
                        <p>
                            <a href='{$cpUrl->getUrlByCatType('My Clippings')}' class='btn'>{$ln->gd('m.lawNews.contact.btn.manageClippings')}</a>
                        </p>
                    </li>
                </ul>
            </div>
        </div>
        ";

        return $text;
    }
}
