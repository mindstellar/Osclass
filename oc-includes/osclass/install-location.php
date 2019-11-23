<?php

    error_reporting( E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_PARSE );

    define( 'ABS_PATH', dirname( dirname( __DIR__ ) ) . '/' );
    define( 'LIB_PATH', ABS_PATH . 'oc-includes/' );

    require_once ABS_PATH . 'config.php';
    require_once LIB_PATH . 'vendor/autoload.php';
    require_once LIB_PATH . 'osclass/classes/database/DBConnectionClass.php';
    require_once LIB_PATH . 'osclass/classes/database/DBCommandClass.php';
    require_once LIB_PATH . 'osclass/classes/database/DBRecordsetClass.php';
    require_once LIB_PATH . 'osclass/classes/database/DAO.php';
    require_once LIB_PATH . 'osclass/Logger/Logger.php';
    require_once LIB_PATH . 'osclass/Logger/LogDatabase.php';
    require_once LIB_PATH . 'osclass/Logger/LogOsclassInstaller.php';
    require_once LIB_PATH . 'osclass/core/Session.php';
    require_once LIB_PATH . 'osclass/core/Params.php';
    require_once LIB_PATH . 'osclass/model/Preference.php';
    require_once LIB_PATH . 'osclass/helpers/hDatabaseInfo.php';
    require_once LIB_PATH . 'osclass/helpers/hDefines.php';
    require_once LIB_PATH . 'osclass/helpers/hErrors.php';
    require_once LIB_PATH . 'osclass/helpers/hLocale.php';
    require_once LIB_PATH . 'osclass/helpers/hPreference.php';
    require_once LIB_PATH . 'osclass/helpers/hPlugins.php';
    require_once LIB_PATH . 'osclass/helpers/hTranslations.php';
    require_once LIB_PATH . 'osclass/helpers/hUtils.php';
    require_once LIB_PATH . 'osclass/compatibility.php';
    require_once LIB_PATH . 'osclass/default-constants.php';
    require_once LIB_PATH . 'osclass/formatting.php';
    require_once LIB_PATH . 'osclass/install-functions.php';
    require_once LIB_PATH . 'osclass/utils.php';
    require_once LIB_PATH . 'osclass/core/Translation.php';
    require_once LIB_PATH . 'osclass/classes/Plugins.php';

    Params::init();
if ( is_osclass_installed() ) {
    die();
}

    $json_message             = array ();
    $json_message[ 'status' ] = true;

    $result                         = basic_info();
    $json_message[ 'email_status' ] = $result[ 'email_status' ];
    $json_message[ 'password' ]     = $result[ 's_password' ];

if ($_POST['skip-location-input'] == 0 && $_POST['locationsql'] !== 'skip') {
    $msg = install_locations();
    $json_message['status'] = $msg;
}

    echo json_encode( $json_message );

    /**
     * @return array
     */
function basic_info()
{
    require_once LIB_PATH . 'osclass/model/Admin.php';
    require_once LIB_PATH . 'osclass/helpers/hSecurity.php';

    $admin = Params::getParam( 's_name' );
    if ( $admin == '' ) {
        $admin = 'admin';
    }

    $password = Params::getParam( 's_passwd', false, false );
    if ( $password == '' ) {
        $password = osc_genRandomPassword();
    }

    Admin::newInstance()->insert(
        array (
            's_name'     => 'Administrator'
        ,
            's_username' => $admin
        ,
            's_password' => osc_hash_password( $password )
        ,
            's_email'    => Params::getParam( 'email' )
        )
    );

    $mPreference = Preference::newInstance();
    $mPreference->insert(
        array (
            's_section' => 'osclass'
        ,
            's_name'    => 'pageTitle'
        ,
            's_value'   => Params::getParam( 'webtitle' )
        ,
            'e_type'    => 'STRING'
        )
    );

    $mPreference->insert(
        array (
            's_section' => 'osclass'
        ,
            's_name'    => 'contactEmail'
        ,
            's_value'   => Params::getParam( 'email' )
        ,
            'e_type'    => 'STRING'
        )
    );

    $body = sprintf( __( 'Hi %s,' ), Params::getParam( 'webtitle' ) ) . '<br/><br/>';
    $body .= sprintf( __( 'Your Osclass installation at %s is up and running. You can access the administration panel with these details:' ), WEB_PATH ) . '<br/>';
    $body .= '<ul>';
    $body .= '<li>' . sprintf( __( 'username: %s' ), $admin ) . '</li>';
    $body .= '<li>' . sprintf( __( 'password: %s' ), $password ) . '</li>';
    $body .= '</ul>';
    $body .= sprintf( __( 'Remember that for any doubts you might have you can consult our <a href="%1$s">documentation</a>' ), 'https://osclass.gitbook.io/osclass-docs/');
    $body .= __( 'Cheers,' ) . '<br/>';
    $body .= __( 'The <a href="https://github.com/navjottomer/osclass">Osclass</a> team' );

    $sitename = strtolower( Params::getServerParam( 'SERVER_NAME' ) );
    if ( 0 === strpos( $sitename, 'www.' ) ) {
        $sitename = substr( $sitename, 4 );
    }

    try {
        $mail           = new PHPMailer( true );
        $mail->CharSet  = 'utf-8';
        $mail->Host     = 'localhost';
        $mail->From     = 'osclass@' . $sitename;
        $mail->FromName = 'Osclass';
        $mail->Subject  = 'Osclass successfully installed!';
        $mail->addAddress( Params::getParam( 'email' ), 'Osclass administrator' );
        $mail->Body    = $body;
        $mail->AltBody = $body;
        if ( ! $mail->send() ) {
            return array (
                'email_status' => Params::getParam( 'email' ) . '<br>' . $mail->ErrorInfo ,
                's_password'   => $password
            );
        }

        return array ( 'email_status' => '' , 's_password' => $password );
    } catch ( phpmailerException $exception ) {
        return array (
            'email_status' => Params::getParam( 'email' ) . '<br>' . $exception->errorMessage() ,
            's_password'   => $password
        );
    }
}


    /**
     * @return bool
     */
function install_locations()
{
    $location = Params::getParam('locationsql');
    if ($location != '') {
        $sql = osc_file_get_contents(osc_get_locations_sql_url($location));
        if ($sql != '') {
            $conn = DBConnectionClass::newInstance();
            $c_db = $conn->getOsclassDb();
            $comm = new DBCommandClass( $c_db );
            $comm->query('SET FOREIGN_KEY_CHECKS = 0');
            $imported = $comm->importSQL($sql);
            $comm->query('SET FOREIGN_KEY_CHECKS = 1');

            return true;
        }
    }

    return false;
}
