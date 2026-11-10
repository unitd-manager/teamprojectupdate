<?
class CP_Admin_Modules_EnterpriseIms_CreditNote_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('enterpriseIms_creditNote');
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
        $mediaObj = $mediaArr->getMediaObj('enterpriseIms_creditNote', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
                
    }
}