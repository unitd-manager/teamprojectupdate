<?
class CP_Admin_Modules_Payroll_CPFCalculator_Functions {

    /**
     *
     */

    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('payroll_cPFCalculator');
        $modObj['tableName'] = 'cpf_calculator';
        $modObj['keyField']  = 'cpf_calculator_id';
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new')
           ,'actBtnsDetail' => array('edit', 'delete')
           ,'actBtnsEdit'   => array('save', 'apply', 'delete')
           ,'relatedTables' => array('media')
           ,'title'         => 'CPF Calculator'
        ));
    }

    /**
     *
     */

    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('payroll_cPFCalculator', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

}