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
            case 'upgrade-funcs':
                if (defined('DEMO')) {
                    osc_add_flash_warning_message(_m('This action cannot be done because it is a demo site'), 'admin');
                    $this->redirectTo(osc_admin_base_url(true));
                }
                $this->ajax     = true;
                $upgrade_result = Osclass::upgradeDB(Params::getParam('skipdb'));
                header('Content-Type: application/json');
                echo $upgrade_result;
                break;
            default:
                $this->doView('upgrade/index.php');
        }
    }

    //hopefully generic...
}
