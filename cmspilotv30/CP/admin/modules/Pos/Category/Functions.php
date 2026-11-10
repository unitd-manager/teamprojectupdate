<?
class CP_Admin_Modules_Pos_Category_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('pos_category');
        $modules->registerModule($modObj, array(
            'actBtnsDetail' => array('edit', 'delete', 'printListScreen')
           ,'actBtnsList' => array('new', 'printListScreen')
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('pos_category', 'pos_subCategoryLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'sub_category'
           ,'historyTableKeyField'  => 'sub_category_id'
           ,'fieldlabel'            => array('Title')
           ,'showLinkPanelInEdit'   => 0
        ));

    }
}