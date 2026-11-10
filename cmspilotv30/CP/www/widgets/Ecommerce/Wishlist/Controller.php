<?
class CP_Www_Widgets_Ecommerce_Wishlist_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $modName  = 'ecommerce_product';
    var $record   = array();
    var $childId  = '';
    var $url      = '/index.php?widget=ecommerce_wishlist&_spAction=add&showHTML=0';
    var $btnLabel = 'w.ecommerce.basket.btn.addToWishlist';
    var $btnLabelViewWishlist = 'w.ecommerce.basket.btn.viewWishlist';
    var $btnStyle = 'button';
    var $btnStyleViewWishlist = 'button viewWishlist';
    
    var $removeUrl = '/index.php?widget=ecommerce_wishlist&_spAction=remove&showHTML=0';
    var $btnLabelRemove = 'w.ecommerce.basket.btn.removeFromWishlist';
    //========================================================//
    /**
     * 
     * @return type
     */
    function getAdd() {
        return $this->model->getAdd();
    }
    
    /**
     * 
     * @return type
     */
    function getRemove() {
        return $this->model->getRemove();
    }

    /**
     *
     */
    function getSave(){
        return $this->model->getSave();
    }
}