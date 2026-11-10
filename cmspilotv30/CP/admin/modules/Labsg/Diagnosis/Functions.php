<?
class CP_Admin_Modules_Labsg_Diagnosis_Functions {

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('labsg_diagnosis');
        $modObj['tableName'] = 'diagnosis';
        $modObj['keyField']  = 'diagnosis_id';
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new')
           ,'actBtnsDetail' => array('edit', 'delete')
           ,'actBtnsEdit'   => array('save', 'apply', 'delete')
           ,'relatedTables' => array('media')
           ,'title'         => 'Diagnosis'
        ));
    }

    /**
     *
     */

    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('labsg_diagnosis', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
    /**
     *
     */

    function setLinksArray($inst) {
        $cpCfg = Zend_Registry::get('cpCfg');

    }


}
