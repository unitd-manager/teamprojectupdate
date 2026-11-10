<?
class CP_Www_Modules_Ecommerce_Product_Controller extends CP_Common_Lib_ModuleControllerAbstract
{

    /**
     *
     */
    function getController() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $hook = getCPModuleHook('ecommerce_product', 'controller', '', $this);
        if($hook['status']){
            return $hook['html'];
        }

        $text = '';
        if ($tv['subRoom'] == '' 
            && $tv['action'] == 'list'
            && $cpCfg['m.ecommerce.product.list.showIntroContent']) {
            
            $wRecord = getCPWidgetObj('content_record');
            $contentArr = $wRecord->getWidget(array(
                 'returnDataOnly' => true
                ,'global' => false
                ,'strictToPage' => true
            ));
            
            if (count($contentArr) > 0){
                $text = getCPModuleObj('webBasic_content')->view->getList($contentArr);
            } else {
                $fnName = $fn->getFnNameByAction();
                $text = $this->$fnName();
            }

        } else {
            $fnName = $fn->getFnNameByAction();
            $text = $this->$fnName();
        }

        return $text;
    }

    /**
     *
     */
    function getEnquiryProductMessageToAdmin() {
        return $this->view->getEnquiryProductMessageToAdmin();
    }

    /**
     *
     */
    function getEnquiryProductToAdminSubmit() {
        return $this->view->getEnquiryProductToAdminSubmit();
    }

}