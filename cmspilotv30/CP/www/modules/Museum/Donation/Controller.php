<?
class CP_Www_Modules_Museum_Donation_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getController() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $hook = getCPModuleHook2('museum_donation', 'controller', $this);
        if($hook['status']){
            return $hook['html'];
        }

        $text = '';
        if ($tv['secType'] == 'Support Us'
        || $tv['catType'] == 'Support Us'
        || $tv['subCatType'] == 'Support Us') {
            if($cpCfg['m.museum.donation.showFormBelowList']
                || $cpCfg['m.webBasic.contactUs.showFormAboveList']
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

    function getProccedToPayment() {
        return $this->model->getProccedToPayment();
    }
}