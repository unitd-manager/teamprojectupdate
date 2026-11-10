<?
class CP_Www_Modules_WebBasic_ContactUs_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getController() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $hook = getCPModuleHook2('webBasic_contactUs', 'controller', $this);
        if($hook['status']){
            return $hook['html'];
        }

        $text = '';
        if ($tv['secType'] == 'Enquiry Form'
        || $tv['catType'] == 'Enquiry Form'
        || $tv['subCatType'] == 'Enquiry Form') {
            if($cpCfg['m.webBasic.contactUs.showEnqFormBelowList']
                || $cpCfg['m.webBasic.contactUs.showEnqFormAboveList']
            ){
                $text = $this->getList();
            } else {
                $text = $this->view->getNew();
            }
        } else {
            $fnName = $fn->getFnNameByAction();
            $text = $this->$fnName();
        }

        return '' . $text;
    }

    /**
     *
     */
    function getList($alternateListFn = '', $exp = array()){
        return parent::getList($alternateListFn, $exp);
    }
}