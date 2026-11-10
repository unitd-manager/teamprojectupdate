<?
class CP_Admin_Modules_Common_Template_Functions
{
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('common_template');
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
        ));
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');

        $template_id = $fn->getReqParam('template_id');

        if ($template_id != '' ) {
            $searchVar->sqlSearchVar[] = "t.template_id  = '{$template_id}'";
        } else if ($tv['record_id'] != '' ) {
            $searchVar->sqlSearchVar[] = "t.template_id  = '{$tv['record_id']}'";
        } else {
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    t.title LIKE '%{$tv['keyword']}%'
                )";
            }
        }
    }
}