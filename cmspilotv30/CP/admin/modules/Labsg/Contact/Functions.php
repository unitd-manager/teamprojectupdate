<?
class CP_Admin_Modules_Labsg_Contact_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('labsg_contact');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new')
           ,'actBtnsDetail' => array('edit', 'delete', 'duplicate')
           ,'actBtnsEdit'   => array('save', 'apply', 'delete')
           ,'relatedTables' => array('media')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('labsg_contact', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
    
    /**
     *
     */
    function setLinksArray($inst) {
        
        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('labsg_contact', 'trading_companyLink');

        $inst->registerLinksArray($linkObj, array(
            'displayTitleFieldName' => 'c.company_name'
           ,'historyTableName'      => 'contact'
           ,'linkMultiple'          => 0
           ,'keyFieldForHistory'    => 'company_id'
        ));
    }
}