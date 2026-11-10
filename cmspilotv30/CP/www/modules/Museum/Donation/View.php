<?
class CP_Www_Modules_Museum_Donation_View extends CP_Common_Lib_ModuleViewAbstract
{

    var $jssKeys = array('jqForm-3.15');

    /**
     *
     */
    function getList($dataArray) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $hook = getCPModuleHook('museum_donation', 'list', $dataArray);
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

        $donationFormBelow = ($cpCfg['m.museum.donation.showFormBelowList']) ? $this->getNew() : '';
        $donationFormAbove = ($cpCfg['m.museum.donation.showFormAboveList']) ? $this->getNew() : '';

        if ($donationFormBelow) {
            $donationFormBelow = "
            <div class='mt10'>
                {$donationFormBelow}
            </div>
            ";
        }
        if ($donationFormAbove) {
            $donationFormAbove = "
            <div class='mb10'>
                {$donationFormAbove}
            </div>
            ";
        }

        $text = "
        {$donationFormAbove}
        {$contentObj->view->getList($contentArr)}
        {$donationFormBelow}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew() {

        $hook = getCPModuleHook2('museum_donation', 'new', $this);
        if($hook['status']){
            return $hook['html'];
        }

        return $this->getDonationForm();
    }

    /**
     *
     */
    function getDonationForm() {
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $hook = getCPModuleHook2('museum_donation', 'enquiryForm', $this);
        if($hook['status']){
            return $hook['html'];
        }

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $formAction = "/index.php?module=museum_donation&_spAction=add&showHTML=0";

        $infoText = '';

        $countryText = '';
        if (!$cpCfg['m.museum.donation.hideCountryDropdown']) {
            $countryText = "
            {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.addressCountry.lbl'), 'country_code', $sqlCountry)}
            ";
        }

        $captchaText = '';
        if (!$cpCfg['m.museum.donation.hideCaptcha']) {
            $captchaText = "
            {$formObj->getCaptchaImage($ln->gd('cp.form.fld.antiSpamCode.lbl'), 'captcha_code')}
            ";
        }

        $cancelButton = '';
        if (!$cpCfg['m.museum.donation.hideCancelButton']) {
            $cancelButton = "
            <input type='reset'value='{$ln->gd('cp.form.btn.cancel')}' onclick='history.back()'/>
            ";
        }

        $SQLAmount = "
        SELECT p.product_id, p.price
        FROM product p
        LEFT JOIN (category c) ON (c.category_id = p.category_id)
        LEFT JOIN (section s) ON (s.section_id = c.section_id)
        WHERE s.section_type  = 'Product'
          AND c.category_type = 'Donation'
          AND p.published = 1
        ";

        $text = "
        <form id='enquiryForm' class='yform columnar cpJqForm' method='post' action='{$formAction}'>
            <fieldset>
                <legend>{$ln->gd('m.museum.donation.form.heading')}</legend>
                {$infoText}
                {$formObj->getTBRow($ln->gd('cp.form.fld.firstName.lbl'), 'first_name')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.lastName.lbl'), 'last_name')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.companyName.lbl'), 'company_name')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.email.lbl'), 'email')}
                {$formObj->getRRowBySQL($ln->gd('m.museum.donation.form.fld.donationAmount.lbl'), 'product_id', $SQLAmount, '', array('useKey' => 1))}
                {$countryText}
                {$formObj->getTARow($ln->gd('message'), 'comments')}
      	    	{$captchaText}
                <div class='type-button'>
                    <div class='floatbox'>
                        <div class='float_left m0'>
                            <input type='submit' value='{$ln->gd('cp.form.btn.submit')}'/>
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
