<?php
class Reservas_ICal {
    private $calendar_url;
    private $cache_time = 3600; // 1 hora

    public function __construct($calendar_url) {
        $this->calendar_url = $calendar_url;
    }

    public function get_blocked_dates($cabana_id) {
        $cache_key = 'reservas_ical_cache_' . $cabana_id;
        $cached_data = get_transient($cache_key);

        if ($cached_data !== false) {
            return $cached_data;
        }

        $response = wp_remote_get($this->calendar_url);
        
        if (is_wp_error($response)) {
            error_log('Error al obtener calendario iCal: ' . $response->get_error_message());
            return array();
        }

        $ical_data = wp_remote_retrieve_body($response);
        $events = $this->parse_ical($ical_data);
        
        set_transient($cache_key, $events, $this->cache_time);
        
        return $events;
    }

    private function parse_ical($ical_data) {
        $events = array();
        $lines = explode("\n", $ical_data);
        $event = null;

        foreach ($lines as $line) {
            $line = trim($line);
            
            if (strpos($line, 'BEGIN:VEVENT') === 0) {
                $event = array();
            } elseif (strpos($line, 'END:VEVENT') === 0) {
                if ($event) {
                    $events[] = $event;
                }
                $event = null;
            } elseif ($event !== null) {
                if (strpos($line, 'DTSTART:') === 0) {
                    $event['start'] = $this->parse_ical_date(substr($line, 8));
                } elseif (strpos($line, 'DTEND:') === 0) {
                    $event['end'] = $this->parse_ical_date(substr($line, 6));
                } elseif (strpos($line, 'SUMMARY:') === 0) {
                    $event['summary'] = substr($line, 8);
                }
            }
        }

        return $events;
    }

    private function parse_ical_date($date_str) {
        // Formato iCal: YYYYMMDDTHHMMSSZ
        $year = substr($date_str, 0, 4);
        $month = substr($date_str, 4, 2);
        $day = substr($date_str, 6, 2);
        
        return "$year-$month-$day";
    }
} 