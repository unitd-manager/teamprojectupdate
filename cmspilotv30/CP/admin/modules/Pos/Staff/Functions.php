<?
class CP_Admin_Modules_Pos_Staff_Functions
{
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');

        $modObj = $modules->getModuleObj('pos_staff');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
           ,'relatedTables' => array('media')
           ,'tableName'     => $cpCfg['cp.modAccessStaffTable']
           ,'keyField'      => $cpCfg['cp.modAccessStaffIdLabel']
           ,'titleField'    => "CONCAT_WS(' ', first_name, last_name)"
           ,'actBtnsEdit'   => array('save', 'apply', 'cancel', 'delete', 'duplicate')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('pos_staff', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        $arr = &$inst->mediaArray['staff']['picture'];

        //------------------------------------------------------------------------------//
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $fn = Zend_Registry::get('fn');

        $linkObj = $inst->getLinksArrayObj('pos_staff', 'project_staffGroupLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'staff_group_history'
           ,'keyField'         => 'staff_group_id'
        ));
        
        //------------------------------------------------------------------------------//        
        $linkObj = $inst->getLinksArrayObj('pos_staff', 'pos_shopUsergroupLink');

        $extShopArr = $fn->getDdDataAsArray('pos_shop');
        $extUsergroupArr = $fn->getDdDataAsArray('core_userGroup');
        
        $inst->registerLinksArray($linkObj, array(
             'historyTableName'       => 'mod_acc_shop_user_group'
            ,'linkingType'            => 'grid'
            ,'historyTableKeyField'   => 'shop_user_group_id'
            ,'showLinkPanelInEdit'    => 1
            ,'hasPortalEdit'          => 0
            ,'hasPortalDelete'        => 1
            ,'fieldlabel'             => array('Shop', 'Usergroup')
            ,'gridFieldTypeArray'  => array(
                 array('type' => 'dropdown', 'ddArr' => $extShopArr)
                ,array('type' => 'dropdown', 'ddArr' => $extUsergroupArr)
            )
            ,'additionalFieldsArray'  => array(
                'b.shop_id'
               ,'b.user_group_id'
            )
            ,'showAnchorInLinkPortal' => false
        ));
    }

    /**
     *
     */
    function getStaffStatusArray(){
        $arr = array(
             "Current"
            ,"Archive"
        );

        return $arr;
    }

    /**
     *
     */
    function afterDuplicateHandler($record_id_src, $record_id_new){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT *
        FROM mod_acc_shop_user_group
        WHERE staff_id = {$record_id_src}
        ";
        $result  = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $fa = array();
            $fa['staff_id']         = $record_id_new;
            $fa['shop_id']          = $row['shop_id'];
            $fa['user_group_id']   = $row['user_group_id'];
            $fn->addRecord($fa, 'mod_acc_shop_user_group');
		}
    }

}