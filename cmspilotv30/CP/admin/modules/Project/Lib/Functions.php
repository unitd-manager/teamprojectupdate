<?
class CP_Admin_Modules_Project_Lib_Functions
{
    //==================================================================//
    function setActionsArray($actArray){
        $cpCfg = Zend_Registry::get('cpCfg');
        $arrayMaster = Zend_Registry::get('arrayMaster');
        $tv = Zend_Registry::get('tv');

        //====================== Convert to Project ================================//
        $actObj = $actArray->getActionObj('convertOppToProject');
        $actArray->registerAction($actObj, array(
            'title' => 'Convert to Project'
        ));

        //====================== Raise Invoice ================================//
        $actObj = $actArray->getActionObj('raiseInvoice');
        $actArray->registerAction($actObj, array(
            'title' => 'Raise Invoice'
           ,'url' => "javascript:Invoice.raiseInvoice('{$tv['topRm']}');"
        ));
        //====================== Duplicate Project ================================//
        $actObj = $actArray->getActionObj('duplicateProject');
        $actArray->registerAction($actObj, array(
            'title' => 'Duplicate Project'
           ,'url' => "javascript:Project.duplicateProject('{$tv['topRm']}');"
        ));
    }
}
