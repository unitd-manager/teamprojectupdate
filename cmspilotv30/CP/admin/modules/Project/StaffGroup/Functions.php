<?
class CP_Admin_Modules_Project_StaffGroup_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('project_staffGroup');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new')
           ,'actBtnsDetail' => array('delete')
           ,'title'         => 'Staff Group'
           ,'tableName'     => 'staff_group'
           ,'keyField'      => 'staff_group_id'
           ,'hasFlagInList' => 0
        ));

    }
    //==================================================================//
    //==================================================================//
    function getQuickSearch() {
    }

    //==================================================================//
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $cpUtil = Zend_Registry::get('cpUtil');

        $staff_group_id = $fn->getReqParam('staff_group_id');

        if ($staff_group_id != '' ) {
            $searchVar->sqlSearchVar[] = "a.staff_group_id = '{$staff_group_id}'";
        } else if ($tv['record_id'] != '' ) {
            $searchVar->sqlSearchVar[] = "a.staff_group_id = '{$tv['record_id']}'";
        } else {
    
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'a.staff_id');
    
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                                        a.title LIKE '%{$tv['keyword']}%'
                                      )";
            }
    
            //------------------------------------------------------------------------//
            $searchVar->sortOrder = "a.title";
        }
    }
}