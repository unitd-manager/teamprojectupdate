<?
class CP_Www_Widgets_LawNews_EventRegister_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('jqForm-3.15');

    //========================================================//
    function getWidget() {
        $tv = Zend_Registry::get('tv');
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');

        $c = &$this->controller;

        $text = "
        {$this->getRowsHTML()}
        ";

        return $text;
    }

    //========================================================//
    function getRowsHTML() {
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $cpUrl   = Zend_Registry::get('cpUrl');
        $formObj = Zend_Registry::get('formObj');
        $viewHelper = Zend_Registry::get('viewHelper');

        $c = &$this->controller;
        $formObj->mode = $c->mode;

        $formAction = $c->formAction;

        $retUrlText = '';
        if ($c->returnUrl){
            $retUrlText = "<input type='hidden' name='returnUrl' value='{$c->returnUrl}' />";
        }

        $currencyRow = '';
        if ($c->showCurrencySelection){
            $currencyRow = $this->getEventItemCurrencies();
        }

        $wEventItemObj = getCPWidgetObj('event_eventItem');
        $wEventItem = $wEventItemObj->getWidget(array(
             'eventId' => $c->event_id
            ,'unitPriceFld' => $c->unitPriceFld
            ,'currency' => $c->currency
            ,'currencyDisplay' => $c->currencyDisplay
            ,'maxQuantity' => $c->maxQuantity
            ,'showQtyDropDown' => $c->showQtyDropDown
            ,'selectMultipeEventItem' => $c->selectMultipeEventItem
        ));

        $captchaText = '';
        if ($c->showCaptcha){
            $captchaText = "{$formObj->getCaptchaImage($ln->gd('cp.form.fld.antiSpamCode.lbl'), 'captcha_code')}";
        }

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
        );


        $row = array();
        if(isLoggedInWWW()){
            $row = $fn->getRecordRowByID('contact', 'contact_id', $_SESSION['cpContactId']);
        }

        $dataProtectionRow = getCPModuleObj('lawNews_contact')->view->getDataProtectionRow($row);

        $text = "
        <div class='edit'>
            <form name='eventRegisterForm' id='eventRegisterForm' class='yform edit columnar cpJqForm' method='post' action='{$c->formAction}'>
                {$currencyRow}
                <div id='event-eventItem-widget'>
                    {$wEventItem}
                </div>
                <h2 class='ruled mt20'>{$ln->gd($c->attendeeHeading)}</h2>
                <fieldset>
                    {$formObj->getTBRow($ln->gd('cp.form.fld.email.lbl'), 'email', $fn->getIssetParam($row, 'email'), $expReq)}
                    {$formObj->getDDRowByVL($ln->gd('cp.form.fld.salutation.lbl'), 'salutation', 'salutation', $fn->getIssetParam($row, 'salutation'), $expSal)}
                    {$formObj->getTBRow($ln->gd('cp.form.fld.firstName.lbl'), 'first_name', $fn->getIssetParam($row, 'first_name'), $expReq)}
                    {$formObj->getTBRow($ln->gd('cp.form.fld.lastName.lbl'), 'last_name', $fn->getIssetParam($row, 'last_name'), $expReq)}
                    {$formObj->getTBRow($ln->gd('cp.form.fld.position.lbl'), 'position', $fn->getIssetParam($row, 'position'))}
                    {$formObj->getTBRow($ln->gd('cp.form.fld.companyName.lbl'), 'company_name', $fn->getIssetParam($row, 'company_name'), $expReq)}
                    {$formObj->getDDRowByVL($ln->gd('cp.form.fld.companyType.lbl'), 'company_type', 'companyType', $fn->getIssetParam($row, 'company_type'), $expCompanyType)}
                    {$formObj->getTBRow($ln->gd('cp.form.fld.address1.lbl'), 'address1', $fn->getIssetParam($row, 'address1'), $expReq)}
                    {$formObj->getTBRow($ln->gd('cp.form.fld.address2.lbl'), 'address2', $fn->getIssetParam($row, 'address2'))}
                    {$formObj->getTBRow($ln->gd('cp.form.fld.address3.lbl'), 'address3', $fn->getIssetParam($row, 'address3'))}
                    {$formObj->getTBRow($ln->gd('cp.form.fld.addressCity.lbl'), 'address_city', $fn->getIssetParam($row, 'address_city'), $expReq)}
                    {$formObj->getTBRow($ln->gd('cp.form.fld.addressState.lbl'), 'address_state', $fn->getIssetParam($row, 'address_state'))}
                    {$formObj->getTBRow($ln->gd('cp.form.fld.addressZipCode.lbl'), 'address_po_code', $fn->getIssetParam($row, 'address_po_code'))}
                    {$formObj->getTBRow($ln->gd('cp.form.fld.phone.lbl'), 'phone', $fn->getIssetParam($row, 'phone'), $expReq)}
                    {$formObj->getTBRow($ln->gd('cp.form.fld.fax.lbl'), 'fax', $fn->getIssetParam($row, 'fax'), $expReq)}
                    {$dataProtectionRow}
                    {$captchaText}

                    <div class='type-button'>
                        <div class='floatbox'>
                            <div class='float_left'>
                                <input class='submit' type='submit' value='{$ln->gd('cp.form.btn.submit')}'/>
                            </div>
                        </div>
                    </div>
                    <input type='submit' name='x_submit' class='submithidden' />
                    <input type='hidden' name='module' value='{$c->module}'>
                    <input type='hidden' name='event_id' value='{$c->event_id}'>
                    {$viewHelper->getWidgetPropertiesInHiddenVariable($c->name, $c)}
                </fieldset>
            </form>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getEventItemCurrencies() {
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');
        $c = &$this->controller;
        $formObj->mode = $c->mode;

        $expCur = array(
            'useKey' => 1
        );

        $text = "
        <div class='currencyList'>
            {$formObj->getRRow($ln->gd('m.event.event.showPriceInCurrency.lbl'), 'currency', $c->currency, $c->currencyArray, $expCur)}
        </div>
        ";

        return $text;
    }

    /**
     * called on currency change by ajax
     * @return type
     */
    function getEventItem(){
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $event_id        = $fn->getReqParam('event_id');
        $event_item_id   = $fn->getReqParam('event_item_id');
        $currency        = $fn->getReqParam('currency');
        $maxQuantity     = $fn->getReqParam('w-event-eventItem_maxQuantity', 1);
        $showQtyDropDown = $fn->getReqParam('w-event-eventItem_showQtyDropDown');
        $selectMultipeEventItem = $fn->getReqParam('w-event-eventItem_selectMultipeEventItem');

        $wEventItemObj = getCPWidgetObj('event_eventItem');
        $wEventItem = $wEventItemObj->getWidget(array(
             'eventId'            => $event_id
            ,'unitPriceFld'       => 'price_'.$currency
            ,'currency'           => $currency
            ,'currencyDisplay'    => $ln->gd('cp.currency.'.$currency.'.lbl')
            ,'defaultEventItemId' => $event_item_id
            ,'maxQuantity'        => $maxQuantity
            ,'showQtyDropDown'    => $showQtyDropDown
            ,'selectMultipeEventItem' => $selectMultipeEventItem
        ));

        return $wEventItem;
    }
}