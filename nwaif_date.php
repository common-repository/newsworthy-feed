<?php
/**
 * Created by ingwar1991 from 0-1-10.com
 */

class NWaiF_Date {
    private static $instance;

    private $defaultFormat = 'Y-m-d H:i:s';

    private $wpFormat;
    private $wpTimezone;

    private function __construct() {}

    public static function getInstance() {
        if ( !self::$instance ) {
            self::$instance = new NWaiF_Date();
        }

        return self::$instance;
    }

    private function isTimestamp( $date ) {
        return
            ( (string)(int) $date == trim( (string) $date ) )
            && ( $date <= PHP_INT_MAX )
            && ( $date >= ~PHP_INT_MAX )
                ? true
                : false;
    }

    private function createUtcDateObj( $date ) {
        $dateObj = $this->isTimestamp( $date )
            ? new \DateTime( '@' . $date )
            : new \DateTime( $date );
        $dateObj->setTimezone( new \DateTimeZone( 'UTC' ) );

        return $dateObj;
    }

    public function getWpFormat() {
        if ( !$this->wpFormat ) {
            $this->wpFormat = trim( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
            $this->wpFormat = $this->wpFormat
                ? $this->wpFormat
                : $this->defaultFormat;
        }

        return $this->wpFormat;
    }

    public function getWpTimezone() {
        if ( !$this->wpTimezone ) {
            $wpTimezone = wp_timezone_string();
            $this->wpTimezone = $wpTimezone
                ? $wpTimezone
                : 'utc';
        }

        return $this->wpTimezone;
    }

    public function utcDate( $date = 'now', $format = false ) {
        $dateObj = $this->createUtcDateObj( $date );

        return $dateObj->format( $format
            ? $format
            : $this->defaultFormat
        );
    }

    public function wpDate( $date = 'now', $format = false ) {
        $dateObj = $this->createUtcDateObj( $date );
        $dateObj->setTimezone( new \DateTimeZone( $this->getWpTimezone() ) );

        return $dateObj->format( $format
            ? $format
            : $this->defaultFormat
        );
    }

    public function compareUtcDates( $date1, $date2 = 'now' ) {
        $time1 = $this->utcDate( $date1, 'U' );
        $time2 = $this->utcDate( $date2, 'U' );

        if ( $time1 == $time2 ) {
            return 0;
        }

        return $time1 > $time2
            ? 1
            : -1;
    }
}