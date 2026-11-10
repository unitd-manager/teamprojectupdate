<?
class CP_Admin_Widgets_Common_MultiYear_View extends CP_Common_Lib_WidgetViewAbstract
{

    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');

        $arr = $this->model->getYearArray();
        $this->model->setDefaultYear();

        $cp_year = $fn->getSessionParam('cp_year');

        $text = "
        Choose Year:
        <select name='cp_year'>
            {$cpUtil->getDropDown1($arr, $cp_year)}
        </select>
        ";

        return $text;
    }

    function getCpYearRow($row = null) {
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = '';
        if ($cpCfg['cp.hasMultiYears']){
            $exp['isEditable'] = 0;
            $text = "
            {$formObj->getTBRow('Year', 'cp_year', $row['cp_year'], $exp)}
            ";
        }

        return $text;
    }


}