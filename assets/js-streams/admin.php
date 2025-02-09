<?php

    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache'); 

    $events_path = __DIR__ . "/admin-events.txt";
    
    if( file_exists( $events_path ) ){
        $events = json_decode( file_get_contents( $events_path  ) );

        if( ! empty( $events ) ){
            foreach( $events AS $event ){ 
                echo 'event: ' . $event->event . PHP_EOL;               
                echo 'data: {"eventname": "' . $event->data . '"}' . PHP_EOL;
                echo PHP_EOL;
                ob_flush();
                flush();
            }
        }         
        file_put_contents( $events_path, json_encode( [] ) );
    }