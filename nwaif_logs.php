<?php
/**
 * Created by ingwar1991 from 0-1-10.com
 */

require_once 'nwaif_utils.php';

const NWaiF_ERRORLOG_FILENAME = 'nwaif_errors.log';

class NWaiF_Logs {
    private static $instance;
    private $isEnabled = false;

    public static function getInstance() {
        if ( !self::$instance ) {
            self::$instance = new NWaiF_Logs();
        }

        return self::$instance;
    }

    public function enable( $clearPrev = true ) {
        $this->checkFile( $clearPrev );

        // this code is used ONLY for debugging
        error_reporting( E_ALL );

        ini_set( 'display_errors',1 );
        ini_set( 'log_errors', 1 );
        ini_set( 'error_log', NWaiF_Utils::getInstance()->getPluginPath( NWaiF_ERRORLOG_FILENAME, true ) );

        $this->isEnabled = true;
    }

    private function checkFile( $clearPrev = true ) {
        $openMode = $clearPrev
            ? 'w'
            : 'a';
        $logFile = fopen( NWaiF_Utils::getInstance()->getPluginPath( NWaiF_ERRORLOG_FILENAME, true ), $openMode );
        if ( !$logFile ) {
            die( 'NWaiF: Failed to create log file' );
        }

        fwrite( $logFile, "Logging for NWaiF enabled\n" );
        fclose( $logFile );
    }

    public function log( $msg ) {
        if ( !$this->isEnabled ) {
            $this->enable( false );
        }

        error_log( is_array( $msg )
            ? implode( ' | ', $msg )
            : $msg
        );
    }
}
