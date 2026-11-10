<?
class CP_Admin_Modules_AgileIms_CreditNote_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('agileIms_creditNote');
        $modObj['tableName'] = 'credit_note';
        $modObj['keyField']  = 'credit_note_id';
        $modules->registerModule($modObj, array(
           'title'         => 'Credit Note'
          ,'actBtnsList'   => array()
          ,'actBtnsDetail' => array()
          ,'hasEditInList' => false
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('agileIms_creditNote', 'attachment', 'attachment');
        $mediaArr->registerMedia($mediaObj, array(
        ));
                
    }
}