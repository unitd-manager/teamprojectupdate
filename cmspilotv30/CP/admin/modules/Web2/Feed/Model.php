<?
class CP_Admin_Modules_Web2_Feed_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $extraTableNames = "";
        
        $SQL = "
        SELECT f.*
        FROM feed f
        ";
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'f';

        $special_search = $fn->getReqParam('special_search');


        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "f.feed_id = '{$tv['record_id']}'";
        } else {
            //------------------------------------------------------------------------//
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    f.title       LIKE '%{$tv['keyword']}%'  OR
                    f.description LIKE '%{$tv['keyword']}%'
                )";
            }
            $searchVar->sortOrder = "";
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the title');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $cpCfg = Zend_Registry::get('cpCfg');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();

        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $tv = Zend_Registry::get('tv');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('title', 'Please enter the title');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'description');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'content_date');
        $fa = $fn->addToFieldsArray($fa, 'actual_url');
        $fa = $fn->addToFieldsArray($fa, 'show_title');

        $fa = $fn->addToFieldsArray($fa, 'meta_title', '', $cpCfg['cp.hasMultiLangForMetaData']);
        $fa = $fn->addToFieldsArray($fa, 'meta_keyword', '', $cpCfg['cp.hasMultiLangForMetaData']);
        $fa = $fn->addToFieldsArray($fa, 'meta_description', '', $cpCfg['cp.hasMultiLangForMetaData']);

        return $fa;
    }
    
    /**
     *
     */
    function getUpdateFeed(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        require_once 'Zend/Feed.php';

        set_time_limit(5000);

        $text = '';
        
        foreach($cpCfg['m.web2.feed.feedSource'] AS $feedSource){
            
            if($feedSource['url'] != ''){
                try {
                    $feedChannel = Zend_Feed::import($feedSource['url']);
                } catch (Zend_Feed_Exception $e) {
                    // feed import failed
                    echo "Exception caught importing feed: {$e->getMessage()}\n";
                    exit;
                }       

                foreach ($feedChannel as $row) {
                    $fa = array();
                    $fa['feed_source']   = $feedSource['url'];
                    $fa['feed_title']    = $feedChannel->title();
                    $fa['content_type']  = $feedSource['type'];
                    $fa['title']         = $row->title();
                    $fa['actual_url']    = $row->link();
                    $fa['description']   = $row->content();
                    $fa['content_date']  = $row->pubDate();
                    
                    if ($fa['content_date'] !=''){
                        $fa['content_date'] = date('Y-m-d', strtotime($fa['content_date']));
                    }

                    $actUrl = qstr($fa['actual_url']);
                    $SQL = "
                    SELECT * 
                    FROM feed f
                    WHERE f.actual_url = '{$actUrl}'
                    ";
                    
                    $result  = $db->sql_query($SQL);
                    $numRows = $db->sql_numrows($result);    
                    
                    if($numRows == 0){
                        $fa['published'] = 1;
                        $fn->addRecord($fa);
                    } else {
                        //$fn->saveRecord($fa);
                    }
                }
            }        
        }
        
        $text = "
        <h1>Feed is successfully updated!</h1>
        <p><a href='javascript:window.close()'>Close this window</a></p>
        ";
        return $text;        
    }   
}
