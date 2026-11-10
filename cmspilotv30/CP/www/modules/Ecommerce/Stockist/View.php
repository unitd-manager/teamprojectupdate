<?
class CP_Www_Modules_Ecommerce_Stockist_View extends CP_Common_Modules_Ecommerce_Stockist_View
{

    var $jssKeys = array('jqForm-3.15');

    /**
     *
     */
    function getList($dataArray) {
        $text = "
        {$this->getNew()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew() {
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');

        $text  = "";

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();

        $formAction = "/index.php?module=ecommerce_stockist&_spAction=add&showHTML=0";

        $fieldset1 = "
        {$formObj->getTBRow($ln->gd('cp.form.fld.firstName.lbl'), 'first_name')}
        {$formObj->getTBRow($ln->gd('cp.form.fld.lastName.lbl'), 'last_name')}
        {$formObj->getTBRow($ln->gd('cp.form.fld.companyName.lbl'), 'company_name')}
        {$formObj->getTBRow($ln->gd('cp.form.fld.email.lbl'), 'email')}
        {$formObj->getTBRow($ln->gd('cp.form.fld.phone.lbl'), 'phone')}
        ";

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();

        $fieldset2 = "
        {$formObj->getTBRow('Address 1', 'address1')}
        {$formObj->getTBRow('Address 2', 'address2')}
        {$formObj->getTBRow('Area', 'address_area')}
        {$formObj->getTBRow('City/Town', 'address_city')}
        {$formObj->getTBRow('State', 'address_state')}
        {$formObj->getTBRow('Zip Code', 'address_po_code')}
        {$formObj->getDDRowBySQL('Country', 'address_country_code', $sqlCountry)}
        ";

        $text = "
        <form id='enquiryForm' class='yform columnar cpJqForm' method='post' action='{$formAction}'>
            <input type='hidden' name='successMsg' value='{$ln->gd('m.ecommerce.stockist.form.new.message.success')}' />
            <h1>{$ln->gd('m.ecommerce.stockist.form.new.heading')}</h1>
            {$formObj->getFieldSetWrapped($ln->gd('m.ecommerce.stockist.form.new.lgnd.mainDetails'), $fieldset1)}
            {$formObj->getFieldSetWrapped($ln->gd('cp.form.lgnd.addressDetails'), $fieldset2)}
            {$formObj->getCaptchaImage($ln->gd('cp.form.fld.antiSpamCode.lbl'), 'captcha_code')}
            <div class='type-button'>
                <div class='floatbox'>
                    <div class='float_left'>
                        <input type='submit' value='{$ln->gd('cp.form.btn.submit')}'/>
                        <input type='reset'value='{$ln->gd('cp.form.btn.cancel')}' onclick='history.back()'/>
                    </div>
                </div>
            </div>
            <input type='submit' name='x_submit' class='submithidden' />
        </form>
        ";

        return $text;
    }
}
