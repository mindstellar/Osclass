<?php use mindstellar\upgrade\Osclass;

/**
 * Class CAdminUpgrade
 */
class CAdminUpgrade extends AdminSecBaseModel
{
    public function __construct()
    {
        parent::__construct();
        osc_run_hook('init_admin_upgrade');
    }

    //Business Layer...
    public function doModel()
    {
        parent::doModel();

        //specific things for this class
        switch ($this->action) {
            default:
                $this->doView('upgrade/index.php');
        }
    }

    //hopefully generic...
}
