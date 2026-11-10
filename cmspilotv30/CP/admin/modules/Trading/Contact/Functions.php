<?
class CP_Admin_Modules_Trading_Contact_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('trading_contact');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new', 'import')
           ,'actBtnsDetail' => array('edit', 'delete', 'duplicate')
           ,'relatedTables' => array('media')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('trading_contact', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
    
    /**
     *
     */
    function setLinksArray($inst) {
        
        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('trading_contact', 'trading_companyLink');

        $inst->registerLinksArray($linkObj, array(
            'displayTitleFieldName' => 'c.company_name'
           ,'historyTableName'      => 'contact'
           ,'linkMultiple'          => 0
           ,'keyFieldForHistory'    => 'company_id'
        ));
    }
}