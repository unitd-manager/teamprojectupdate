<?
abstract class CP_Common_Lib_ThemeControllerAbstract
{
    var $view = null;
    var $model  = null;
    var $fns    = null;

    /**
     *
     */
    function getMainThemeOutput() {
        return $this->view->getMainThemeOutput();
    }

    /**
     *
     */
    function getLinkThemeOutput($srcRoom = '', $lnkRoom = '') {
    	return $this->view->getLinkThemeOutput($srcRoom, $lnkRoom);
    }

    /**
     *
     */
    function getLoginThemeOutput() {
    	return $this->view->getLoginThemeOutput();
    }
    
    /**
     *
     */
    function getChangePasswordOnLoginThemeOutput() {
    	return $this->view->getChangePasswordOnLoginThemeOutput();
    }
}
