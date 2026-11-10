<?
class CPL_Admin_Modules_Tradingsg_SubCon_Functions {

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('tradingsg_subCon');
        $modObj['tableName'] = 'sub_con';
        $modObj['keyField']  = 'sub_con_id';
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new')
           ,'actBtnsDetail' => array('edit')
           ,'actBtnsEdit'   => array('save', 'apply')
           ,'relatedTables' => array('media')
           ,'titleField'    => 'company_name'
           ,'title'         => 'Sub Con'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('tradingsg_subCon', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}
