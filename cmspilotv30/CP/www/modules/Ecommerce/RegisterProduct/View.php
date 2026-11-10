<?
class CP_Www_Modules_Ecommerce_RegisterProduct_View extends CP_Common_Lib_ModuleViewAbstract
{

    var $jssKeys = array('jqForm-3.15');

    /**
     *
     */
    function getNew() {
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $sqlProduct = $fn->getDdSQL('ecommerce_product', array('sortBy' => 'title'));
        
        $formAction = "/index.php?module=ecommerce_registerProduct&_spAction=add&showHTML=0";

        $text = "
        <form id='registerProductForm' class='yform columnar cpJqForm' method='post' action='{$formAction}'>
            <input type='hidden' name='successMsg' value='{$ln->gd('m.ecommerce.registerProduct.form.message.success')}' />
            <fieldset>
                <h1 class='mb20'>{$ln->gd('m.ecommerce.registerProduct.form.registerYourProduct.heading')}</h1>
                <div class='mb20'>{$ln->gd('m.ecommerce.registerProduct.enquiry.info')}</div>
                {$formObj->getTBRow($ln->gd('cp.form.fld.firstName.lbl'), 'first_name')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.lastName.lbl'), 'last_name')}
                {$formObj->getTBRow($ln->gd('cp.form.fld.email.lbl'), 'email')}
                {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.addressCountry.lbl'), 'country_code', $sqlCountry)}
                {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.productName.lbl'), 'product_id', $sqlProduct)}
                {$formObj->getTBRow($ln->gd('cp.form.fld.productSerial.lbl'), 'product_serial')}
                <div class='type-check'>
                    <input type='checkbox' id='fld_newsletter' class='checkBox' name='newsletter' value='1' />
                    <label for='fld_newsletter'>{$ln->gd('cp.form.fld.subscribe.lbl')}</label>
                </div>
                {$formObj->getSingleCheckBoxRow($ln->gd('cp.form.fld.acceptTerms.lbl'), 'accept_terms')}
                {$formObj->getCaptchaImage($ln->gd('cp.form.fld.antiSpamCode.lbl'), 'captcha_code')}
                <div class='type-button'>
                    <div class='floatbox'>
                        <div class='float_left btnSubmit'>
                            <input type='submit' value='{$ln->gd('cp.form.btn.submit')}'/>
                        </div>
                    </div>
                </div>
                <input type='submit' name='x_submit' class='submithidden' />
            </fieldset>
        </form>
        <div class='footerInfo'>
            {$ln->gd('m.ecommerce.registerProduct.form.footerInfo')}
        </div>
        ";

        return $text;
    }
}
