<?
class CP_Admin_Modules_Pos_SubCategory_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pos_subCategory');
        $modObj['tableName'] = 'sub_category';
        $modObj['keyField']  = 'sub_category_id';
        $modules->registerModule($modObj, array(
           'title' => 'Sub Category'
           ,'actBtnsDetail' => array('edit', 'delete', 'printListScreen')
           ,'actBtnsList' => array('new', 'printListScreen', 'bulkMoveSubCat')
        ));
    }
    
    /**
     *
     */
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $mediaObj = $mediaArr->getMediaObj('pos_subCategory', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj);
    }

    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('pos_subCategory', 'pos_sizeLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'sub_category_valuelist'
           ,'historyTableKeyField'  => 'sub_category_valuelist_id'
           ,'linkingType'           => 'modal'
           ,'showLinkPanelInEdit'   => 1
           ,'recordTypeForHistory'  => 'Size'
           ,'displayTitleFieldName' => 'a.value'
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('pos_subCategory', 'pos_seasonLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'sub_category_valuelist'
           ,'historyTableKeyField'  => 'sub_category_valuelist_id'
           ,'linkingType'           => 'modal'
           ,'showLinkPanelInEdit'   => 1
           ,'recordTypeForHistory'  => 'Season'
           ,'displayTitleFieldName' => 'a.value'
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('pos_subCategory', 'pos_styleLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'sub_category_valuelist'
           ,'historyTableKeyField'  => 'sub_category_valuelist_id'
           ,'linkingType'           => 'modal'
           ,'showLinkPanelInEdit'   => 1
           ,'recordTypeForHistory'  => 'Style'
           ,'displayTitleFieldName' => 'a.value'
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('pos_subCategory', 'pos_colorLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'sub_category_valuelist'
           ,'historyTableKeyField'  => 'sub_category_valuelist_id'
           ,'linkingType'           => 'modal'
           ,'showLinkPanelInEdit'   => 1
           ,'recordTypeForHistory'  => 'Color'
           ,'displayTitleFieldName' => 'a.value'
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('pos_subCategory', 'pos_elementLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'sub_category_valuelist'
           ,'historyTableKeyField'  => 'sub_category_valuelist_id'
           ,'linkingType'           => 'modal'
           ,'showLinkPanelInEdit'   => 1
           ,'recordTypeForHistory'  => 'Element'
           ,'displayTitleFieldName' => 'a.value'
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('pos_subCategory', 'pos_brandLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'sub_category_valuelist'
           ,'historyTableKeyField'  => 'sub_category_valuelist_id'
           ,'linkingType'           => 'modal'
           ,'showLinkPanelInEdit'   => 1
           ,'recordTypeForHistory'  => 'Brand'
           ,'displayTitleFieldName' => 'a.value'
        ));
    }
}