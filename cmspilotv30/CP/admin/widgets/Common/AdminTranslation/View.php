<?
class CP_Admin_Widgets_Common_AdminTranslation_View extends CP_Common_Lib_WidgetViewAbstract
{

    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');

        $cpAdminIntLang = $fn->getSessionParam('cpAdminIntLang');
        $exp = array(
            'hideFirstOption' => true,
            'useKey' => true,
        );
        $text = "
        {$ln->gd('cp.lbl.chooseLanguage', 'Language :')}
        {$formObj->getDropDownByArray('', 'langAdminInt', $cpCfg['cp.adminInterfaceLangs'], $cpAdminIntLang, $exp)}
        ";

        return $text;
    }

}