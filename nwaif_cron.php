<?php
/**
 * Created by ingwar1991 from 0-1-10.com
 */

require_once 'nwaif_settings.php';
require_once 'nwaif_feed.php';

class NWaiF_Cron {
    private static $instance;

    private function __construct() {}

    public static function getInstance() {
        if ( !self::$instance ) {
            self::$instance = new NWaiF_Cron();
        }

        return self::$instance;
    }

    public function getSchedule() {
        if ( !NWaiF_Settings::getInstance()->get( 'frequency' ) ) {
            return false;
        }

        return NWaiF_Settings::getInstance()->get( 'frequency' ) . '_hour';
    }

    public function init() {
        $this->createSchedule();
        add_action( 'nwaif_process_feed_hook', 'nwaif_process_feed' );
    }

    private function createSchedule() {
        add_filter( 'cron_schedules', 'nwaif_cron_custom_intervals' );
    }

    public function activate() {
        $this->deactivate();

        if ( ( $schedule = $this->getSchedule() ) ) {
            wp_schedule_event(
                time(),
                $schedule,
                'nwaif_process_feed_hook'
            );
        }
    }

    public function deactivate() {
        if ( ( $timestamp = wp_next_scheduled( 'nwaif_process_feed_hook' ) ) ) {
            wp_unschedule_event( $timestamp, 'nwaif_process_feed_hook' );
        }
    }
}

function nwaif_cron_custom_intervals( $schedules ) {
    if ( ( $schedule = NWaiF_Cron::getInstance()->getSchedule() ) ) {
        $freq = NWaiF_Settings::getInstance()->get( 'frequency' );

        $schedules[ $schedule ] = array(
            'interval' => $freq * 60 * 60,
            'display'  => __( 'Every ' . $freq . ' hour(s)' )
        );
    }

    return $schedules;
}