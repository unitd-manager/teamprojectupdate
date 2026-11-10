<?
class CP_Admin_Themes_Trade_Controller extends CP_Admin_Lib_ThemeControllerAbstract
{

    function getProductPriceDisplay(){
        $modObj = getCPModuleObj('tradingsg_pos');
        return $modObj->view->getProductPriceDisplay();
    }

    function getSearchProductTitle() {
        $modObj = getCPModuleObj('tradingsg_pos');
        return $modObj->model->getSearchProductTitle();
    }

}