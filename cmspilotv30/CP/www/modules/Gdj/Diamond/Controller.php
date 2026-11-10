<?
class CP_Www_Modules_Gdj_Diamond_Controller extends CP_Common_Modules_Gdj_Diamond_Controller
{
    /**
     *
     */
    function getController() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $hook = getCPModuleHook('gdj_diamond', 'controller', '', $this);
        if($hook['status']){
            return $hook['html'];
        }
        
        if ($tv['action'] == 'detail'){
            return $this->getDetail();
        } else {
            return $this->getList();
        }
        
        $text = '';
        return $text;
    }
    
}