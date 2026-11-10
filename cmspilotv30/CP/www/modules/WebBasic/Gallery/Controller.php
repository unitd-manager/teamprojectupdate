<?
class CP_Www_Modules_WebBasic_Gallery_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getController() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $text = '';
        if ($tv['catType'] == 'Picture Grid') {
            $text = parent::getList('pictureOnlyList');
        } else {
            $fnName = $fn->getFnNameByAction();
            $text = $this->$fnName();
        }

        return $text;
    }
}