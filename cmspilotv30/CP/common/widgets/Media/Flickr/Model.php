<?
class CP_Common_Widgets_Media_Flickr_Model extends CP_Common_Lib_WidgetModelAbstract
{


    function enableCache($phpFlickrObj){
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;
        $dbConf = $cpCfg[CP_ENV]['db'];
        $phpFlickrObj->enableCache("db", "mysql://{$dbConf['username']}:{$dbConf['password']}@{$dbConf['host']}/{$dbConf['dbname']}", $c->refreshCacheSec);
    }

    //========================================================//
     function photosets_getPhotosUpdateCache(){
        $c = &$this->controller;
        $flickrRef = $c->flickrReference;
        $f = new phpFlickr($c->api_key, $c->secret);
        if($c->enableCache){
            $this->enableCache($f);
        }
        $photoset = $f->photosets_getPhotos($flickrRef);

        foreach ($photoset['photoset']['photo'] as $photo) {
            $f->photos_getInfo($photo['id']);
            $f->photos_getSizes($photo['id']);
        }
    }
}