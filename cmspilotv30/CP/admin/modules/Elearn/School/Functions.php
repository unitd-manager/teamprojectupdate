<?
class CP_Admin_Modules_ELearn_School_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('elearn_school');
        $modObj['tableName'] = 'school';
        $modObj['keyField']  = 'school_id';
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

        $school_id     = $fn->getReqParam('school_id');

        if ($school_id != "") {
            $searchVar->sqlSearchVar[] = "s.school_id = '{$school_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "s.school_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 's.klass_id');
            
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                   s.school_name LIKE '%{$tv['keyword']}%'
                )";            
            }
        }    
        
    }
    /**
     *
     * @return <type>
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('elearn_school', 'elearn_klassLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'school_klass'
        ));
        //------------------------------------------------------------------------------//
    }
    /**
     *
     * @return <type>
     */

    function getLanguagesArray(){
        $langArray = array(
             'chi' => 'Simplified Chinese'
            ,'cht' => 'Traditional Chinese'
        );

        return $langArray;
    }

    /**
     *
     * @return <type>
     */
}