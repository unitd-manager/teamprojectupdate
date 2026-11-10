<?
class CP_Admin_Lib_Bootstrap extends CP_Common_Lib_Bootstrap
{
    function checkAccess(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        if($tv['module'] != '' && !in_array($tv['module'], $cpCfg['cp.availableModules'])){ //module has access
            print "Invalid Module Access";
            exit();
        }

        if ($cpCfg['cp.hasAccessModule']){
            $modulesArrAccess = Zend_Registry::get('modulesArrAccess');

            if($tv['module'] != '' && $tv['spAction'] == ''){
                $hasAccess = isset($modulesArrAccess[$tv['module']]) ? $modulesArrAccess[$tv['module']]['hasAccess'] : 0;

                if (!$hasAccess){
                    $cpUtil->redirect("index.php");
                    print "Invalid Module Access";
                    exit();
                }

                $hasAccessToAction = getCPModuleObj('core_userGroup')->model->hasAccessToAction($tv['module'], $tv['action']);

                if (!$hasAccessToAction){
                    print "Invalid Action Access";
                    exit();
                }
            }
        }
    }
}
