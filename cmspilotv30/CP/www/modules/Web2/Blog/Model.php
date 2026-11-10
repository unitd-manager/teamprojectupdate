<?
class CP_Www_Modules_Web2_Blog_Model extends CP_Common_Lib_ModuleModelAbstract
{

    /**
     *
     */
    function getSQL() {
        return getCPModuleObj('webBasic_content')->model->getSQL(array(
                'includeCommentCount' => true
            )
        );
    }

    /**
     *
     */
    function setSearchVar() {
        return getCPModuleObj('webBasic_content')->model->setSearchVar();
    }
}