<?
class CP_Www_Widgets_Ecommerce_ShippingDetails_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $modName            = 'ecommerce_product';
    var $contactModName     = 'membership_contact';
    var $heading            = '';
    var $saveContinue       = 'w.ecommerce.basket.btn.saveContinue';
    var $continueShopping   = 'w.ecommerce.basket.btn.continueShopping';
    var $mode               = 'edit';
    var $showPaymentMethods = '';
    var $wrapInForm         = true;
    var $showButtons        = true;
    var $showCaptcha        = '';
    
    /*** these variables are used when product list needs to be shown above the billing details ***/
    var $lblProducts        = 'w.ecommerce.shippingDetails.lbl.products';
    var $showItemsList      = '';
    var $showOrgId          = ''; // if the order needs to be linked to an organization
    var $showCompanyId      = ''; // if the order needs to be linked to an company
    var $showNotesPerItem   = ''; // if the buyer needs to make any notes to the vendor per each item
    var $notesPreText       = 'w.ecommerce.productList.notes';
    var $defaultCountryCode = '';
    var $showConfirmEmail = false;
    var $validatePhone    = false;
    var $hasAcceptTermsCbox = false;

    /**
     *
     */
    function getSave(){
        return $this->model->getSave();
    }

    /**
     *
     */
    function getOrgDetails(){
        $fn = Zend_Registry::get('fn');
        $orgId = $fn->getReqParam('organization_id');
        return $this->view->getOrgDetails($orgId);
    }
}