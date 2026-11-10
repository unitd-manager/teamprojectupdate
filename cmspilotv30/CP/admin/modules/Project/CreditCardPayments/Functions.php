<?
class CP_Admin_Modules_Project_CreditCardPayments_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('project_creditCardPayments');
        $modules->registerModule($modObj, array(
            'title'         => 'Credit Card Payments'
           ,'tableName'     => 'credit_card_payment'
           ,'keyField'      => 'credit_card_payment_id'
           ,'actBtnsEdit' => array('save', 'apply', 'cancel', 'delete', 'duplicate')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('project_creditCardPayments', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
                
    }
}