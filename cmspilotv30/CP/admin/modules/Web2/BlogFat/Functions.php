<?
class CP_Admin_Modules_Web2_BlogFat_Functions
{

    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('web2_blogFat');
        $modules->registerModule($modObj, array(
             'keyField'    => 'blog_id'
            ,'tableName'   => 'blog'
            ,'title'       => 'Blog'
        ));
    }

    //==================================================================//
    //==================================================================//
    function getQuickSearch() {
    }

    //==================================================================//
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $cpUtil = Zend_Registry::get('cpUtil');

        $blog_id = $fn->getReqParam('blog_id');

        if ($blog_id != '' ) {
            $searchVar->sqlSearchVar[] = "b.blog_id  = '{$blog_id}'";
        } else if ($tv['record_id'] != '' ) {
            $searchVar->sqlSearchVar[] = "b.blog_id  = '{$tv['record_id']}'";
        } else {
            
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'b.blog_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    a.title LIKE '%{$tv['keyword']}%'
                )";
            }
        }
    }

    /**
     *
     * @return <type>
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('web2_blogFat', 'web2_tagsLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'            => 'tags_history'
           ,'showAnchorInLinkPortal'      => 0
           ,'mainRoomKeyFldNameInHistTbl' => 'record_id'
           ,'recordTypeForHistory'        => 'Blog'
        ));

    }

    //==================================================================//
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('web2_blog', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('web2_blog', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

}