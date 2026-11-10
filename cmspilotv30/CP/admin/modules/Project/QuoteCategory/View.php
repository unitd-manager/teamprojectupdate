<?
class CP_Admin_Modules_Project_QuoteCategory_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getNew() {  
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
                              
        $quote_id       = $fn->getGetParam('quote_id');
                              
        if ($quote_id == ''){
            return;            
        }                     
                              
        $sqlCatName = "
        SELECT valuelist_id
              ,value 
        FROM valuelist 
        WHERE key_text = 'quoteCategoryName' 
        ORDER BY value
        ";

        $sqlCatType = $fn->getValueListSQL('quoteCategoryType');
                              
        $formAction = "index.php?module=project_quoteCategory&_spAction=add&showHTML=0";
                              
        $exp = array('sqlType' => 'OneField');
                              
        $text = "             
        <form id='quoteForm' class='yform columnar' method='post' action='{$formAction}'>
            <div id='errorDisplayBox'></div>
            <fieldset>        
                {$formObj->getDDRowBySQL('Category Name'    , 'valuelist_id'     , $sqlCatName, '', array('sqlType' => 'TwoFields'))}
                {$formObj->getDDRowBySQL('Category Type'    , 'category_type'    , $sqlCatType, 'Normal', $exp)}
                {$formObj->getTBRow('Sort Order'            , 'sort_order'       )}
            </fieldset>       
            <input type='hidden' name='quote_id' value='{$quote_id}'>
        </form>               
        ";                    
                              
        return $text;         
    }                         
                              
    /**
     *
     */
    function getEdit() {  
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
                              
        $category_id    = $fn->getGetParam('category_id');
                              
        if ($category_id == ''){
            return;            
        }                     
                                
        $SQL     = "
        SELECT * 
        FROM quote_category 
        WHERE quote_category_id = {$category_id}
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
                              
        if ($numRows == 0){   
            return;            
        }                     
                              
        $row     = $db->sql_fetchrow($result);
                              
        $text    = '';

        $sqlCatName = "
        SELECT valuelist_id
              ,value 
        FROM valuelist 
        WHERE key_text = 'quoteCategoryName' 
        ORDER BY sort_order
        ";

        $sqlCatType = $fn->getValueListSQL('quoteCategoryType');

        $formAction = "index.php?module=project_quoteCategory&_spAction=save&showHTML=0";
                              
        $exp = array('sqlType' => 'OneField');
                              
        $text = "             
        <form id='quoteForm' class='yform columnar' method='post' action='{$formAction}'>
            <div id='errorDisplayBox'></div>
            <fieldset>        
                {$formObj->getDDRowBySQL('Category Name'     , 'valuelist_id'    , $sqlCatName   , $row['valuelist_id'], array('sqlType' => 'TwoFields'))}
                {$formObj->getDDRowBySQL('Category Type'     , 'category_type'   , $sqlCatType   , $row['category_type'], $exp )}
                {$formObj->getTBRow('Sort Order'             , 'sort_order'      , $row['sort_order'] )}
            </fieldset>       
            <input type='hidden' name='category_id' value='{$category_id}'>
        </form>
        ";               

        return $text;         
    }                         
}
