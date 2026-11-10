<?
class CP_Admin_Widgets_Common_MultiCountry_View extends CP_Common_Lib_WidgetViewAbstract
{

    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $SQL = $this->model->getCountrySQL();
        $this->model->setDefaultCountry();

        $cp_country_id = $fn->getSessionParam('cp_country_id');

        $text = "
        {$ln->gd('cp.lbl.chooseCountry', 'Country :')}
        {$formObj->getDropDownBySQL('Country', 'cp_country_id', $SQL, $cp_country_id)}
        ";

        return $text;
    }

}