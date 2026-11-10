<?
class CP_Admin_Modules_ELearn_Klass_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('elearn_klass');
        $modObj['title'] = 'Class';
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
        ));
    }

    //==================================================================//
    //==================================================================//
    //==================================================================//
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $cpUtil = Zend_Registry::get('cpUtil');

        $klass_id     = $fn->getReqParam('klass_id');

        if ($klass_id != "") {
            $searchVar->sqlSearchVar[] = "k.klass_id = '{$klass_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "k.klass_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'k.klass_id');
            
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                   k.title LIKE '%{$tv['keyword']}%'
                )";            
            }
        }            
    }

    /**
     *
     * @return <type>
     */
    /**
     *
     * @return <type>
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('elearn_klass', 'elearn_bookLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'klass_book'
            ,'fieldlabel'      => array('Title', 'Color')
        ));
        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('elearn_klass', 'elearn_teacherLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'klass_teacher'
           ,'displayTitleFieldName'  => "CONCAT_WS(' ', a.first_name, a.last_name)"
        ));
    }

}