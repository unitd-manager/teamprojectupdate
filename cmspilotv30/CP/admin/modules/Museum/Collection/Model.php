<?
class CP_Admin_Modules_Museum_Collection_Model extends CP_Common_Modules_Museum_Collection_Model
{

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

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);
        //print $id;
        //return;
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');
        $tv = Zend_Registry::get('tv');

        $validate->resetErrorArray();

        if ($tv['lang'] == 'eng') {
            $validate->validateData('title', 'Please enter the title');
        }

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
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'category_id');
        $fa = $fn->addToFieldsArray($fa, 'product_group_id');
        $fa = $fn->addToFieldsArray($fa, 'sub_category_id');

        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'description', '', true);
        $fa = $fn->addToFieldsArray($fa, 'sponsor_description', '', true);
        $fa = $fn->addToFieldsArray($fa, 'flickr_ref');

        if(isset($_POST['published'])){
            $fa = $fn->addToFieldsArray($fa, 'published');
        }

        $fa = $fn->addToFieldsArray($fa, 'member_only');
        $fa = $fn->addToFieldsArray($fa, 'latest');

        if ($cpCfg['m.museum.collection.showMetaData'] == 1) {
            $fa = $fn->addToFieldsArray($fa, 'meta_title', '', $cpCfg['cp.hasMultiLangForMetaData']);
            $fa = $fn->addToFieldsArray($fa, 'meta_keyword', '', $cpCfg['cp.hasMultiLangForMetaData']);
            $fa = $fn->addToFieldsArray($fa, 'meta_description', '', $cpCfg['cp.hasMultiLangForMetaData']);
        }

        return $fa;
    }

    /**
     *
     */
    function getUpdateFlickrCache(){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        set_time_limit(5000);

        $text = '';

        $SQL = "
        SELECT c.flickr_ref
        FROM collection c
        WHERE c.flickr_ref != ''
        ";

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        print 'Updating the Flickr Cache...';

        flush();
        ob_flush();
        flush();
        ob_flush();
        flush();
        ob_flush();

        if($numRows > 0){ //Empty the cache
            $SQL1 = "
            DELETE FROM flickr_cache
            ";
            $result1 = $db->sql_query($SQL1);

            $SQL1 = "
            OPTIMIZE TABLE flickr_cache
            ";
            $result1 = $db->sql_query($SQL1);
        }

        while ($row = $db->sql_fetchrow($result)) {
            $wFlickr = getCPWidgetObj('media_flickr');
            $wPhotoSets = $wFlickr->getWidget(array(
                 'api_key'   => $cpCfg['cp.flickr_api_key']
                ,'secret'    => $cpCfg['cp.flickr_secret']
                ,'helperFn'  => 'photosets_getPhotosUpdateCache'
                ,'flickrReference'  => $row['flickr_ref']
                ,'refreshCacheSec'  => 3600 * 24 //every 24 hours
            ));
        }

        if($numRows > 0){
            $SQL1 = "
            UPDATE flickr_cache
            SET expiration = DATE_ADD(expiration, INTERVAL 365 DAY)
            ";
            $result1 = $db->sql_query($SQL1);
        }

        $text = "
        <h1>Flickr cache is successfully updated!</h1>
        <p><a href='javascript:window.close()'>Close this window</a></p>
        ";
        return $text;
    }

}
