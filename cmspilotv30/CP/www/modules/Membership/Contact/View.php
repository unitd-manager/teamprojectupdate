<?
class CP_Www_Modules_Membership_Contact_View extends CP_Common_Modules_Membership_Contact_View
{
    var $jssKeys = array('jqForm-3.15');

    //==================================================================//
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $fieldset1 = "
        {$formObj->getTBRow($ln->gd('cp.form.fld.firstName.lbl'), 'first_name', $row['first_name'])}
        {$formObj->getTBRow($ln->gd('cp.form.fld.lastName.lbl'), 'last_name', $row['last_name'])}
        {$formObj->getTBRow($ln->gd('cp.form.fld.email.lbl'), 'email', $row['email'])}
        {$formObj->getTBRow($ln->gd('cp.form.fld.phone.lbl'), 'phone', $row['phone'])}
        {$formObj->getTBRow($ln->gd('cp.form.fld.mobile.lbl'), 'mobile', $row['mobile'])}
        {$formObj->getYesNoRRow($ln->gd('w.membership.contact.form.fld.newsletterSubscribed.lbl'), 'subscribe', $row['subscribe'])}
        ";

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();

        $fieldset2 = "
        {$formObj->getTBRow($ln->gd('cp.form.fld.address1.lbl'), 'address1', $row['address1'])}
        {$formObj->getTBRow($ln->gd('cp.form.fld.address2.lbl'), 'address2', $row['address2'])}
        {$formObj->getTBRow($ln->gd('cp.form.fld.area.lbl'), 'address_area', $row['address_area'])}
        {$formObj->getTBRow($ln->gd('cp.form.fld.city.lbl'), 'address_city', $row['address_city'])}
        {$formObj->getTBRow($ln->gd('cp.form.fld.state.lbl'), 'address_state', $row['address_state'])}
        {$formObj->getTBRow($ln->gd('cp.form.fld.poCode.lbl'), 'address_po_code', $row['address_po_code'])}
        {$formObj->getDDRowBySQL($ln->gd('cp.form.fld.addressCountry.lbl'), 'address_country_code', $sqlCountry, $row['address_country_code'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('cp.form.lgnd.primaryDetails'), $fieldset1)}
        {$formObj->getFieldSetWrapped($ln->gd('cp.form.lgnd.addressDetails'), $fieldset2)}
        <input type='hidden' name='memberType' value='{$this->controller->name}' />
        ";

        return $text;
    }
}
