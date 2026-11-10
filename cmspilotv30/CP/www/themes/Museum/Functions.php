<?
class CP_Www_Themes_Museum_Functions
{
   
    /**
     *
     */
    function getModuleEcommerceProductControllerHook($obj) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = '';

        if ($tv['action'] == 'list'
            && $cpCfg['m.ecommerce.product.list.showIntroContent']
            ){
            
            $wRecord = getCPWidgetObj('content_record');
            $contentArr = $wRecord->getWidget(array(
                 'returnDataOnly' => true
                ,'global' => false
                ,'strictToPage' => true
            ));
            
            if (count($contentArr) > 0){
                $text = getCPModuleObj('webBasic_content')->view->getList($contentArr);
            } else {
                $fnName = $fn->getFnNameByAction();
                $text = $obj->$fnName();
            }            

        } else {
            $fnName = $fn->getFnNameByAction();
            $text = $obj->$fnName();
        }
        
        return $text;
    } 

    /**
     *
     */
    function getPopupNotice() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');        

        $c = &$this->controller;
                        
        $SQL = "
        SELECT c.*
        FROM content c
        WHERE content_type = 'Notice'
        AND c.published = 1 
        ";
        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);
                
        $text = "
        <div class='notice'>
            {$ln->gfv($row, 'description')}
        </div>        
        ";

        $_SESSION['cpNoticeModalDisplayed'.$tv['lang']] = true;
        
        return $text;
    }
      
    
}