<?
class CP_Admin_Themes_Pos_Controller extends CP_Admin_Lib_ThemeControllerAbstract
{
    /**
     *
     */
	function getSmartCardLoginForm() {
        return $this->view->getSmartCardLoginForm();
    }

    /**
     *
     */
	function getSmartCardLoginSubmit() {
        return $this->model->getSmartCardLoginSubmit();
    }

    /**
     *
     */
	function getTerminalsByShopJSON() {
        return $this->model->getTerminalsByShopJSON();
    }

}