<?
class CP_Www_Widgets_Ecommerce_ShippingDetails_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('jqForm-3.15');

    /**
     *
     */
    function getWidget($exp = array()){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $formObj = Zend_Registry::get('formObj');
        $c = &$this->controller;

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        $basketArray = $cpCfg['cp.basketArray'][$c->modName];

        if ($rowsHTML != ""){
            $heading = ($c->mode == 'edit') ? "<h1>{$ln->gd($c->heading)}</h1>" : '';
            $buttons = ($c->mode == 'edit' && $c->showButtons) ? $this->getButtons() : '';

            $wPaym = getCPWidgetObj('ecommerce_paymentMethods');

            $url = '/index.php?widget=ecommerce_shippingDetails&_spAction=save&showHTML=0';
            $basketArray = $cpCfg['cp.basketArray'][$c->modName];
            $confirmPageUrl = $cpUrl->getUrlByCatType('Confirm Order', $basketArray['basketSecType']);

            $paymentMethods = '';
            if ($c->showPaymentMethods){
                $paymentMethods = "
                {$wPaym->getWidget(array(
                     'modName' => $c->modName
                    ,'mode' => $c->mode
                ))}
                ";
            }

            $frmClass = ($c->mode == 'detail') ? 'detail' : '';

            $formOpen  = '';
            $formClose = '';
            if($c->wrapInForm){
                $formOpen = "<form id='frmShippingDetails' class='yform columnar cpJqForm {$frmClass}' method='post' action='{$url}'>";
                $formClose = "</form>";
            }

            $productItems = '';
            if ($c->showItemsList){
                $wProductItems = getCPWidgetObj('ecommerce_productList');
                $productItems = "
                {$formObj->getFieldSetWrapped($ln->gd($c->lblProducts),
                    $wProductItems->getWidget(array(
                        'showHeading' => false
                    ))
                )}
                ";
            }

            $text = "
            {$heading}
            {$formOpen}
                {$productItems}
                {$rowsHTML}
                <input type='hidden' name='returnUrl' value='{$confirmPageUrl}' />
                <input type='hidden' name='payment_method' value='{$basketArray['defaultPaymentMethod']}' />
                <input type='hidden' name='modName' value='{$c->modName}' />
            {$formClose}
            {$paymentMethods}
            {$buttons }
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getRowsHTML() {
        $hook = getCPWidgetHook('ecommerce_shippingDetails', 'rowsHTML', $this);
        if($hook['status']){
            return $hook['html'];
        }

        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;
        $viewHelper = Zend_Registry::get('viewHelper');

        $fieldset = '';
        $row = array();

        $dataArray = $this->model->dataArray;
        if(count($dataArray) > 0){
            $row = $dataArray[0];
        }

        $confirmEmail = '';
        $acceptTerms = '';

        if ($c->showConfirmEmail){
            $cEmailNotes = $ln->gd2('cp.form.fld.confirmEmail.notes');
            $exp = array();

            if ($cEmailNotes != ''){
                $exp = array('notes' => $cEmailNotes);
            }
            $confirmEmail = $formObj->getTBRow($ln->gd('cp.form.fld.confirmEmail.lbl'), 'confirm_email', '', $exp);
        }

        if ($c->hasAcceptTermsCbox){
            $acceptTerms = $formObj->getSingleCheckBoxRow($ln->gd('cp.form.fld.acceptTerms.lbl'), 'accept_terms');
        }

        $formObj->mode = $c->mode;
        $fieldset1 = "
        {$formObj->getTBRow($ln->gd('cp.form.fld.firstName.lbl'), 'first_name', $fn->getIssetParam($row,'first_name'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.lastName.lbl'), 'last_name', $fn->getIssetParam($row,'last_name'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.email.lbl'), 'email', $fn->getIssetParam($row,'email'))}
        {$confirmEmail}
        {$formObj->getTBRow($ln->gd('cp.form.fld.phone.lbl'), 'phone', $fn->getIssetParam($row,'phone'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.mobile.lbl'), 'mobile', $fn->getIssetParam($row,'mobile'))}
        ";

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();

        $fieldset2 = "
        {$formObj->getTBRow($ln->gd('cp.form.fld.address1.lbl'), 'address1', $fn->getIssetParam($row,'address1'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.address2.lbl'), 'address2', $fn->getIssetParam($row,'address2'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.area.lbl'), 'address_area', $fn->getIssetParam($row,'address_area'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.city.lbl'), 'address_city', $fn->getIssetParam($row,'address_city'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.state.lbl'), 'address_state', $fn->getIssetParam($row,'address_state'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.poCode.lbl'), 'address_po_code', $fn->getIssetParam($row,'address_po_code'))}
        {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.addressCountry.lbl'), 'address_country_code', $sqlCountry, $fn->getIssetParam($row,'address_country_code', $c->defaultCountryCode))}
        ";

        $fieldset3 = '';

        if ($c->showOrgId){
            $sqlOrg = "
            SELECT organization_id
                  ,name
            FROM organization
            ";

            $orgId = $fn->getIssetParam($row,'organization_id');
            $orgDetails = "";
            $expOrg = array();

            $orgRec = $fn->getRecordRowByID('organization', 'organization_id', $orgId);
            $expOrg = array('detailValue' => $orgRec['name']);

            if ($formObj->mode == 'detail' && $orgId > 0){
                $orgDetails = $this->getOrgDetails($orgId);
            }

            $fieldset3 = "
            {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.organization.lbl'), 'organization_id', $sqlOrg, $fn->getIssetParam($row,'organization_id'), $expOrg)}
            {$orgDetails}
            <div id='orgDetails'></div>
            ";

            $fieldset3 = "
            {$formObj->getFieldSetWrapped($ln->gd('cp.form.lgnd.organization'), $fieldset3)}
            ";
        }

        $captcha = '';

        if($c->showCaptcha && !isLoggedInWWW()){
            $captcha = "
    	    {$formObj->getCaptchaImage($ln->gd('cp.form.fld.antiSpamCode.lbl'), 'captcha_code')}
            ";
        }

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('w.ecommerce.shippingDetails.form.lgnd.personalDetails'), $fieldset1)}
        {$formObj->getFieldSetWrapped($ln->gd('cp.form.lgnd.addressDetails'), $fieldset2)}
        {$fieldset3}
        {$captcha}
        {$acceptTerms}
        {$viewHelper->getWidgetPropertiesInHiddenVariable($c->name, $c)}
        ";

        return $text;
    }

    /**
     *
     */
    function getOrgDetails($orgId) {
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $c = &$this->controller;

        if ($orgId == ''){
            return;
        }

        $formObj->mode = 'detail';

        $orgRec = $fn->getRecordRowByID('organization', 'organization_id', $orgId);
        $expOrg = array('detailValue' => $orgRec['name']);
        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();

        $text = "
        {$formObj->getTBRow($ln->gd('cp.form.fld.address1.lbl'), 'org_address1', $fn->getIssetParam($orgRec,'address1'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.address2.lbl'), 'org_address2', $fn->getIssetParam($orgRec,'address2'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.city.lbl'), 'org_address_city', $fn->getIssetParam($orgRec,'address_city'))}
        {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.addressCountry.lbl'), 'org_address_country_code', $sqlCountry, $fn->getIssetParam($orgRec,'address_country_code', $c->defaultCountryCode))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.poCode.lbl'), 'org_address_po_code', $fn->getIssetParam($orgRec,'address_po_code'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.telephone.lbl'), 'org_phone', $fn->getIssetParam($orgRec,'phone'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.mobile.lbl'), 'org_mobile', $fn->getIssetParam($orgRec,'mobile'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.fax.lbl'), 'org_fax', $fn->getIssetParam($orgRec,'fax'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.email.lbl'), 'org_email', $fn->getIssetParam($orgRec,'email'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.operatingHours.lbl'), 'org_operating_hours', $fn->getIssetParam($orgRec,'operating_hours'))}
        {$formObj->getTBRow($ln->gd('cp.form.fld.remarks.lbl'), 'org_remarks', $fn->getIssetParam($orgRec,'remarks'))}
        ";

        return $text;
    }

    /**
     *
     */
    function getButtons() {
        $ln = Zend_Registry::get('ln');
        $c = &$this->controller;
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');

        $hook = getCPWidgetHook('ecommerce_shippingDetails', 'buttons', $this->controller);
        if($hook['status']){
            return $hook['html'];
        }

        $basketArray = $cpCfg['cp.basketArray'][$c->modName];
        $shopUrl = $cpUrl->getUrlBySecType($basketArray['sectionType']);

        $text = "
        <div class='floatbox shopBtns' modName='{$c->modName}'>
            <div class='float_left button btnContinueShopping'>
                <a href='{$shopUrl}'>
                    {$ln->gd($c->continueShopping)}
                </a>
            </div>
            <div class='float_right button btnProceedToConfirm'>
                <a href='javascript:void(0);'>
                    {$ln->gd($c->saveContinue)}
                </a>
            </div>
        </div>
        ";

        return $text;
    }
}