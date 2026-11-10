<?
class CP_Www_Modules_WebBasic_ContactUs_View extends CP_Common_Lib_ModuleViewAbstract
{

    var $jssKeys = array('jqForm-3.15');

    /**
     *
     */
    function getList($dataArray) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $hook = getCPModuleHook('webBasic_contactUs', 'list', $dataArray);
        if($hook['status']){
            return $hook['html'];
        }

        $contentObj = getCPModuleObj('webBasic_content');

        $wRecord = getCPWidgetObj('content_record');
        $contentArr = $wRecord->getWidget(array(
            'returnDataOnly' => true
            ,'global' => false
            ,'strictToPage' => true
        ));

        $enqFormBelow = ($cpCfg['m.webBasic.contactUs.showEnqFormBelowList']) ? $this->getNew() : '';
        $enqFormAbove = ($cpCfg['m.webBasic.contactUs.showEnqFormAboveList']) ? $this->getNew() : '';

        if ($enqFormBelow) {
            $enqFormBelow = "
            <div class='mt10'>
                {$enqFormBelow}
            </div>
            ";
        }
        if ($enqFormAbove) {
            $enqFormAbove = "
            <div class='mb10'>
                {$enqFormAbove}
            </div>
            ";
        }

        $text = "
        {$enqFormAbove}
        {$contentObj->view->getList($contentArr)}
        {$enqFormBelow}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew() {
        $hook = getCPModuleHook2('webBasic_contactUs', 'new', $this);
        if($hook['status']){
            return $hook['html'];
        }

        return $this->getEnquiryForm();
    }

    /**
     *
     */
    function getEnquiryForm() {
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $hook = getCPModuleHook2('webBasic_contactUs', 'enquiryForm', $this);
        if($hook['status']){
            return $hook['html'];
        }

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $formAction = "/index.php?module=webBasic_contactUs&_spAction=add&showHTML=0";

        $infoText = '';

        if ($ln->gd2('m.webBasic.contactUs.form.enquiry.info') != ''){
            $infoText = "<div class='infoText'>{$ln->gd('m.webBasic.contactUs.form.enquiry.info')}</div>";
        }

        $countryText = '';
        if (!$cpCfg['m.webBasic.contactUs.hideCountryDropdown']) {
            $countryText = "
            {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.addressCountry.lbl'), 'country_code', $sqlCountry)}
            ";
        }

        $captchaText = '';
        if (!$cpCfg['m.webBasic.contactUs.hideCaptcha']) {
            $captchaText = "
            {$formObj->getCaptchaImage($ln->gd('cp.form.fld.antiSpamCode.lbl'), 'captcha_code')}
            ";
        }

        $cancelButton = '';
        if (!$cpCfg['m.webBasic.contactUs.hideCancelButton']) {
            $cancelButton = "
            <input type='reset'value='{$ln->gd('cp.form.btn.cancel')}' onclick='history.back()'/>
            ";
        }

        $sqlEnquiryType = $fn->getValueListSQL('enquiryType');
        $type  = $fn->getReqParam('type');
        $exp = array('sqlType' => 'OneField');
        $enquiryType = '';
        if ($cpCfg['m.webBasic.contactUs.showEnquiryType']) {
            $enquiryType = "
            {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.enquiryType.lbl'), 'enquiry_type',
                                     $sqlEnquiryType, $type, $exp)}
            ";
        }

        $text = "
        <form id='enquiryForm' class='yform ym-form columnar ym-columnar cpJqForm' method='post' action='{$formAction}'>
            <input type='hidden' name='successMsg' value='{$ln->gd('m.webBasic.contactUs.form.enquiry.message.success')}' />
            <fieldset>
                <legend>{$ln->gd('m.webBasic.contactUs.form.enquiry.heading')}</legend>
                {$infoText}
                {$formObj->getTBRow($ln->gd('cp.form.fld.firstName.lbl'), 'first_name')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.lastName.lbl'), 'last_name')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.email.lbl'), 'email')}
                {$enquiryType}
                {$countryText}
                {$formObj->getTARow($ln->gd('message'), 'comments')}
      	    	{$captchaText}
                <div class='type-button'>
                    <div class='floatbox'>
                        <div class='float_left btnSubmit'>
                            <input type='submit' value='{$ln->gd('cp.form.btn.submit')}'/>
                        </div>
                        <div class='float_left btnReset'>
                            {$cancelButton}
                        </div>
                    </div>
                </div>
                <input type='hidden' name='enquiryForm_secType' value='{$tv['secType']}' />
                <input type='hidden' name='enquiryForm_catType' value='{$tv['catType']}' />
                <input type='hidden' name='enquiryForm_subCatType' value='{$tv['subCatType']}' />
                <input type='submit' name='x_submit' class='submithidden' />
            </fieldset>
        </form>
        ";

        return $text;
    }
}
