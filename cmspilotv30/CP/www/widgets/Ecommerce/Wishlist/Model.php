<?
class CP_Www_Widgets_Ecommerce_Wishlist_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getAdd() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUrl = Zend_Registry::get('cpUrl');

        
        if(!isLoggedInWWW()){
            $loginUrl = $cpUrl->getUrlBySecType('Login');
            $msg = $ln->gd('w.ecommerce.wishlist.message.login');
            $msg  = str_replace('[[login_url]]', $loginUrl, $msg);
            $arr = array('message' => $msg);
            return json_encode($arr);            
        }
        
        $product_id = $fn->getReqParam('product_id', '', true);
        $contact_id = $fn->getSessionParam('cpContactId');

        $rec = $fn->getRecordByCondition('wishlist', "product_id = {$product_id} AND contact_id = {$contact_id}");
                    
        if (!is_array($rec)){
            $fa = $this->getFields();
            $fa = $fn->addCreationDetailsToFieldsArray($fa, 'wishlist');
            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'wishlist');
            $result = $db->sql_query($SQL);
        }

        $wishlistUrl = $cpUrl->getUrlBySecType('Wishlist');
        $msg = $ln->gd('w.ecommerce.wishlist.message.addSuccess');
        $msg  = str_replace('[[wishlist_url]]', $wishlistUrl, $msg);
        $arr = array('message' => $msg);
                
        return json_encode($arr);    
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa['product_id'] = $fn->getReqParam('product_id');
        $fa['contact_id'] = $fn->getSessionParam('cpContactId');
        
        return $fa;
    }
    
    /**
     *
     */
    function getRemove() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');

        
        if(!isLoggedInWWW()){
            $loginUrl = $cpUrl->getUrlBySecType('Login');
            $msg = $ln->gd('w.ecommerce.wishlist.message.login');
            $msg  = str_replace('[[login_url]]', $loginUrl, $msg);
            $arr = array('message' => $msg);
            return json_encode($arr);            
        }
        
        $product_id = $fn->getReqParam('product_id', '', true);
        $contact_id = $fn->getSessionParam('cpContactId');

        $SQL = "
        DELETE FROM wishlist 
        WHERE contact_id = {$contact_id}
        AND product_id = {$product_id}
        ";
        $result = $db->sql_query($SQL);


        $msg  = '';
        $arr = array('message' => $msg);
                
        return json_encode($arr);    
    }    
}