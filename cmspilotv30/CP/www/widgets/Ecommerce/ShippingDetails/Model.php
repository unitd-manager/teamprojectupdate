<?
class CP_Www_Widgets_Ecommerce_ShippingDetails_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $modules = Zend_Registry::get('modules');
        $c = &$this->controller;

        $table = $modules->getValueByKey($c->contactModName, 'tableName');

        $SQL   = "
        SELECT c.*
        FROM {$table} c
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $modules = Zend_Registry::get('modules');
        $c = &$this->controller;

        $keyFld = $modules->getValueByKey($c->contactModName, 'keyField');

        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'c';
        $searchVar->sqlSearchVar[] = "c.published = 1";
        $searchVar->sqlSearchVar[] = "c.{$keyFld} = '{$_SESSION['cpContactId']}'";
    }

    /**
     *
     */
    function getDataArray(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $arr = array();
        $c = &$this->controller;
        $basket = $cpCfg['cp.basketArray'][$c->modName];

        $c->heading            = ($c->heading != '')             ? $c->heading            : $basket['shipHeading'];
        $c->showItemsList      = ($c->showItemsList !== '')      ? $c->showItemsList      : $basket['shipShowItemsList'];
        $c->defaultCountryCode = ($c->defaultCountryCode != '')  ? $c->defaultCountryCode : $basket['shipDefaultCountry'];
        $c->showOrgId          = ($c->showOrgId !== '')          ? $c->showOrgId          : $basket['shipShowOrgId'];
        $c->showCaptcha        = ($c->showCaptcha !== '')        ? $c->showCaptcha        : $basket['shipShowCaptcha'];
        $c->contactModName     = ($c->contactModName !== '')     ? $c->contactModName     : $basket['contactModName'];
        $c->showPaymentMethods = ($c->showPaymentMethods !== '') ? $c->showPaymentMethods : $basket['showPaymentMethods'];

        $dataArray = array();

        if (isset($_SESSION['shippingDetails'])){
            $dataArray[] = $_SESSION['shippingDetails'];
        } else if (isLoggedInWWW()){
            $modelHelper = Zend_Registry::get('modelHelper');
            $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'ecommerce_shippingDetails');
        }

        foreach ($dataArray as $key => $row) {
            $arr[$key] = array();
            foreach ($row as $fldName => $fldValue) {
                $arr[$key][$fldName] = isset($_SESSION['shippingDetails'][$fldName]) ? $_SESSION['shippingDetails'][$fldName] : $fldValue;
            }
        }

        $this->dataArray = $arr;
        return $arr;
    }

    /**
     *
     */
    function getSave(){
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $hook = getCPWidgetHook('ecommerce_shippingDetails', 'save', $this);
        if($hook['status']){
            return $hook['html'];
        }

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $_SESSION['shippingDetails'] = $this->getFields();

        $c = &$this->controller;
        $basketArray = $cpCfg['cp.basketArray'][$c->modName];

        if ($basketArray['showCtryDdInBktForShipCalc']){
            $address_country_code = $fn->getReqParam('address_country_code');
            $_SESSION['cpShippingCountryCode'] = $address_country_code;

            if ($address_country_code != ''){
                $rec = $fn->getRecordByCondition('geo_country', "country_code = '{$address_country_code}'");

                if (is_array($rec)){
                    $_SESSION['cpShippingCharge'] = $rec['shipping_charge'];
                }
            }
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getEditValidate() {
        $hook = getCPWidgetHook('ecommerce_shippingDetails', 'editValidate', $this);
        if($hook['status']){
            return $hook['html'];
        }

        $validate = Zend_Registry::get('validate');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $validate->validateData('first_name', $ln->gd('cp.form.fld.firstName.err'));
        $validate->validateData('last_name' , $ln->gd('cp.form.fld.lastName.err'));
        $validate->validateData('email', $ln->gd('cp.form.fld.email.err'), 'email');
        $validate->validateData('address1' , $ln->gd('cp.form.fld.address1.err'));
        $validate->validateData('address_country_code' , $ln->gd('cp.form.fld.country.err'));

        /** if order form is used instead of regular basket widget **/
        $showOrgId = $fn->getReqParam('w-ecommerce-shippingDetails_showOrgId');
        $showConfirmEmail = $fn->getReqParam('w-ecommerce-shippingDetails_showConfirmEmail');
        $validatePhone = $fn->getReqParam('w-ecommerce-shippingDetails_validatePhone');
        $hasAcceptTermsCbox = $fn->getReqParam('w-ecommerce-shippingDetails_hasAcceptTermsCbox');

        if ($showOrgId){
            $validate->validateData("organization_id"  , $ln->gd("cp.form.fld.organization.err"));
        }

        if ($hasAcceptTermsCbox){
            $validate->validateData("accept_terms", $ln->gd("cp.form.fld.acceptTerms.err"));
        }

        if ($validatePhone){
            $validate->validateData("phone", $ln->gd("cp.form.fld.phone.err"));
        }

        if ($showConfirmEmail){
            $isCEmailNotValid = $validate->validateData("confirm_email", $ln->gd("cp.form.fld.confirmEmail.err"), "email");

            if (!$isCEmailNotValid){
                $validate->validateData('confirm_email',  $ln->gd('cp.form.fld.email.err.compare'), 'equal', 'email');
            }
        }

        if (!isLoggedInWWW()){
            $showCaptcha = $fn->getReqParam('w-ecommerce-shippingDetails_showCaptcha');
            if ($showCaptcha){
           	    $captcha_code = $fn->getPostParam('captcha_code');
                require_once (CP_LIBRARY_PATH . 'lib_php/securimage/securimage.php');
                $img = new Securimage;
                if ($img->check($captcha_code) == false) {
                    $validate->errorArray['captcha_code']['name'] = "captcha_code";
                    $validate->errorArray['captcha_code']['msg']  = $ln->gd("cp.form.fld.captchaCode.err");
                }
            }
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'address1');
        $fa = $fn->addToFieldsArray($fa, 'address2');
        $fa = $fn->addToFieldsArray($fa, 'address_area');
        $fa = $fn->addToFieldsArray($fa, 'address_city');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_country_code');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'mobile');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'payment_method');
        $fa = $fn->addToFieldsArray($fa, 'organization_id');
        $fa = $fn->addToFieldsArray($fa, 'modName');
        $fa['contactModName'] = $fn->getPostParam('w-ecommerce-shippingDetails_contactModName');

        $hook = getCPWidgetHook('ecommerce_shippingDetails', 'fields', $this, $fa);
        if($hook['status']){
            return $hook['html'];
        }

        return $fa;
    }
}