<?
class CP_Common_Modules_Edukite_YearGroup_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukite_yearGroup');
        $modules->registerModule($modObj, array(
            'tableName' => 'year_group'
           ,'keyField'  => 'year_group_id'
           ,'title'     => 'Year Group'
           ,'actBtnsList'   => array('new', 'import')
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
    }
}