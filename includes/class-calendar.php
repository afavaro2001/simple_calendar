<?php 

class Calendar{
    public function __construct(){
        
    }
    public function routeAjaxRequest( ){
        if( ! empty( $_POST['get_events'] ) ){
            $events = $this->_get_events( );
            echo json_encode( $events );
        } else if( ! empty( $_POST['manage_event']) ){
            $data = $this->_unserialize_js( $_POST['data'] );
            if( ! empty( $data ) ){
                if( ! empty( $data['event-id'] ) ){
                    $response = $this->_update_event( $data );                    
                } else {
                    $response = $this->_add_event( $data );
                }
            }
        } else if( ! empty( $_POST['resize_event'] ) ){
            $response = $this->_resize_event( $_POST );
        } else if( ! empty( $_POST['delete_event'] ) ){
            $response = $this->_delete_event( $_POST['event_id'] );
        } else if( ! empty( $_POST['dashboard_tabs' ] ) ) {
            echo json_encode( $this->_get_dashboard_tabs( ) );
        } else if( ! empty( $_POST['reload_homepage_tabs'] ) ){
            echo json_encode( $this->_reload_homepage_tabs( ) ) ;
        } else if( ! empty( $_POST['clone_week'] ) ){
            echo json_encode( $this->_clone_week( ) );
        }
        if( ! empty( $response ) ){            
            if( is_a( $response, 'DB' ) ){
                $response = ["response" => "success"];
                if( ! empty( $data ) ){
                    $response['data'] = $data;
                }
                echo json_encode( $response );
            } else {                    
                $response = ["response" => "failure"];
                echo json_encode( $response );
            }
        }
        exit;
    }
    public function getCategories( ){
        global $db;      
        $categories = [] ; 
        $rs = $db->query( "SELECT * FROM `calendar_categories` ORDER BY category_name" )->fetchAll( );
        if( is_array( $rs ) ){
            foreach( $rs AS $row ){
                $categories[ $row['category_id'] ] = $row;
            }
        }
        return $categories;
    }
    public function getCategory( $category_id ){
        global $db;
        return $db->query('SELECT * FROM calendar_categories WHERE category_id = ?', $category_id )->fetchArray( );
    }
    public function mgmtCategory( ){
        if( empty( $_POST ) ) return;
        $data = [];
        foreach( $_POST AS $key => $value ){
            if( is_string( $value ) ){
                $value = trim( stripslashes( $value ) );
            }
            $data[ $key ] = $value;
        }
        
        if( empty( $data['category_name'] ) || empty( $data['category_color'] ) ){
            $errors[] = "Category Name and Color are both required.";
        }
        if( ! empty( $errors ) ){
            return [ 'errors' => $errors, 'category_data' => $data ];
        }
        if( ! empty( $data['category_id'] ) ){            
            $category_id = $data['category_id'];
            unset( $data['category_id'] );
            // Update/Delete user
            if( $this->_update_category( $data, $category_id ) ){
                return [ 'success' => "Category successfully updated." ];
            }
        } else {
            if( $this->_add_category( $data ) ){
                return [ 'success' => "Category successfully added." ];
            }
        }
    }
    private function _update_category( $table_data, $category_id ){
        global $db;
        $data_names = array_keys( $table_data );
        $table_data[ 'category_id' ] = $category_id;
        $update = $db->query('UPDATE calendar_categories SET ' . implode( '=?,', $data_names ) . '=? WHERE category_id=?', $table_data );
        return $update->affectedRows();
    }
    private function _add_category( $table_data ){
        global $db;
        $data_names = array_keys( $table_data );
        $update = $db->query('INSERT INTO calendar_categories SET ' . implode( '=?,', $data_names ) . '=?', $table_data );
        return $update->affectedRows();        
    }
    public function getWeekStarts( ){
        $days[] = strtotime( 'last sunday' );
        if( date( 'w' ) == 0 ){
            // today is a sunday too
            $days[] = time( );
        }
        $days[] = strtotime( 'next sunday' );
        $days[] = strtotime( 'next sunday +7 days' );
        $days[] = strtotime( 'next sunday +14 days' );
        return $days;
    }
    private function _get_events( ){        
        global $db;
        if( ! empty( $_POST['category_id'] ) && is_numeric( $_POST['category_id'] )){
            $category_id = $_POST['category_id'];
        } else {
            $category_id = 1;
        }
        return $db->query( "SELECT event_name AS 'title', DATE_FORMAT( event_start, '%Y-%m-%dT%H:%i:%s') AS 'start', 
                            DATE_FORMAT( event_end, '%Y-%m-%dT%H:%i:%s') AS 'end', event_id, category_id, event_description AS `description` 
                            FROM `calendar_events` WHERE category_id=? AND ( 
                                ( event_start BETWEEN ? AND  ? ) OR 
                                (event_end BETWEEN ? AND  ?) 
                            )", [ $category_id, $_POST['start'], $_POST['end'], $_POST['start'], $_POST['end'] ] )->fetchAll( );
    }
    private function _add_event( $data ){
       global $db, $ad_cache;
       $this->_add_multiple_events( $data );
       $args = $this->_event_query_args( $data );
       $response = $db->query( "INSERT INTO calendar_events ( event_name, event_start, event_end, event_description, category_id ) VALUES (?,?,?,?,?)", $args );           
       $ad_cache->deleteFile( 'calendar-dashboard' ); 
       return $response; 
    }
    private function _add_multiple_events( $data ){
        global $db; 
        if( ! empty( $data['days'] ) && is_array( $data['days'] ) ){
            $new_dates = [];
            $event_day = date( 'w', strtotime( $data['event-start'] )  );
            $timestamp_start = strtotime( $data['event-start'] );
            $timestamp_end = strtotime( $data['event-end'] );
            foreach( $data['days'] AS $day ){
                if( $day > $event_day ){                                        
                    $new_dates[ $day ]['start_date'] = date( 'Y-m-d', $timestamp_start + ( ( $day - $event_day ) * 86400 ) );                              
                    $new_dates[ $day ]['end_date'] = date( 'Y-m-d', $timestamp_end + ( ( $day - $event_day ) * 86400 ) );
                } else if ( $day < $event_day ){
                    $new_dates[ $day ]['start_date'] = date( 'Y-m-d', $timestamp_start - ( ( $event_day - $day ) * 86400 ) );
                    $new_dates[ $day ]['end_date'] = date( 'Y-m-d', $timestamp_end - ( ( $event_day - $day ) * 86400 ) );
                }
            }
            if( ! empty( $new_dates ) ){
                if( ! empty( $data['event-id'] ) ){
                    unset( $data['event-id'] );
                }
                foreach( $new_dates AS $date ){
                    $data['event-start'] = $date['start_date'];
                    $data['event-end'] = $date['end_date'];
                    $args = $this->_event_query_args( $data );
                    $db->query( "INSERT INTO calendar_events ( event_name, event_start, event_end, event_description, category_id ) VALUES (?,?,?,?,?)", $args );   
                }
            }
        }
    }
    private function _update_event( $data ){
       global $db, $ad_cache;
       $this->_add_multiple_events( $data );
       $args = $this->_event_query_args( $data );
       $response = $db->query( "UPDATE calendar_events SET event_name=?, event_start=?, event_end=?, event_description=?, category_id=? WHERE event_id=?", $args );
       $ad_cache->deleteFile( 'calendar-dashboard' );  
       return $response; 
    }
    private function _resize_event( $data ){
        global $db, $ad_cache;
        $args = [ $data['event_start'], $data['event_end'], $data['event_id'] ];
        $response = $db->query( "UPDATE calendar_events SET event_start=?, event_end=? WHERE event_id=?", $args );          
        $ad_cache->deleteFile( 'calendar-dashboard' ); 
        return $response;
    }
    private function _delete_event( $event_id ){
        global $db, $ad_cache;
        $response = $db->query( "DELETE FROM calendar_events WHERE event_id=?", $event_id );          
        $ad_cache->deleteFile( 'calendar-dashboard' );    
        return $response;     
    }
    private function _unserialize_js( $string ){
        $data = [];
        //$string = urldecode( $string );
        $args = explode( '&', $string );
        if( ! empty( $args ) ){
            foreach( $args AS $item ){
                list( $key, $value ) = explode( "=", $item );
                $key = urldecode( $key );
                if( preg_match( "/\[\]/", $key ) ){
                    $data[ str_replace( '[]', '', $key ) ][] = urldecode( $value );
                } else {
                    $data[ $key ] = urldecode( $value );
                }
            }
        }
        return $data;
    }
    private function _change_to_objects( $array ){
        if( is_array( $array ) ){
            foreach( $array AS $index => $value ){
                if( is_array( $value ) ){
                    $array[ $index ] = (object) $value;
                }
            }
        }
        return $array;
    }
    private function _event_query_args( $data ){        
        $args = [];
        $args[] = $data['event-title'];
        $args[] = $data['event-start'] . " " . $data['event-start-hour'] . ":" . $data['event-start-minute'];
        $args[] = $data['event-end'] . " " . $data['event-end-hour'] . ":" . $data['event-end-minute'];
        $args[] = $data['event-description'];
        $args[] = $data['event-category-id'];
        if( ! empty( $data['event-id'] ) ){
            $args[] = $data['event-id'];
        }
        return $args;
    }
    private function _get_dashboard_tabs( ){
        global $db, $ad_cache;
        $date = date( 'Y-m-d H:i:s' );
        $cache_key = 'calendar-dashboard';
        $data = unserialize( $ad_cache->fileGetContents( $cache_key ) );
        if( ! empty( $data ) ){
            if( ! empty( $data['expire'] ) && $data['expire'] > time( ) ){
                return $data;
            }
        } else {
            $data = [];
        }
        $date = date( 'Y-m-d H:i:s' );
        $rs = $db->query( 'SELECT * FROM calendar_events WHERE event_start <= ? AND event_end >=? ORDER BY event_end',[ $date, $date ])->fetchAll( );
         if( ! empty( $rs ) ){
            $data = [];
            $expire = strtotime( $rs[0]['event_end'] );
            $data = [ 'expire' => $expire ];
            foreach( $rs AS $row ){
                $time_start = date( 'g:i a', strtotime( $row[ 'event_start' ] ) );
                $time_end = date( 'g:i a', strtotime( $row[ 'event_end' ] ) );
                $data['data'][] = [
                    'category_id' => $row[ 'category_id' ],
                    'event_name'  => $row[ 'event_name' ],
                    'time_string'  => "{$time_start} - {$time_end}"
                ];
            }
            $ad_cache->filePutContents( serialize( $data ), $cache_key );
        } 
        return $data;
    }
    private function _reload_homepage_tabs( ){
        global $ad_cache;
        $ad_cache->deleteFile( 'calendar-dashboard' );
        $content = [ [ 'event' => 'reload_calendar',
                     'data' => 'reload_calendar',
                     'id' => time( ) ] ];
        $events_path = ASSETS_PATH . "/js-streams/admin-events.txt";
        file_put_contents( $events_path, json_encode( $content ) );
        return [ "msg" => 'success' ];        
    }
    private function _clone_week( ){
        global $db;
        $clones = [];
        $clone_from_start = date( "Y-m-d 00:00:00", strtotime( $_POST['week_start'] ) );
        $clone_from_end = date( "Y-m-d 00:00:00", strtotime( $_POST['week_end'] ) );
        $clone_to_start = $_POST['clone_to'];
        for( $i = 0; $i < 7; $i++ ){
            $week[ date( 'w', $clone_to_start ) ] = date( 'Y-m-d', $clone_to_start );
            $clone_to_start += ( 3600 * 24 );
        } 
        $category_id = $_POST['category_id'];
        $rs = $db->query( 'SELECT * FROM calendar_events WHERE event_start >= ? AND event_start < ? AND category_id=? ORDER BY event_start', [ $clone_from_start, $clone_from_end, $category_id ] )->fetchAll( );
        if( ! empty( $rs ) ){
            foreach( $rs AS $row ){
                $start_timestamp = strtotime( $row['event_start'] );
                $end_timestamp = strtotime( $row['event_end'] );
                $event_start = $week[ date( 'w', $start_timestamp ) ] . ' ' . date( 'H:i:s', $start_timestamp );
                $event_end = date( 'Y-m-d H:i:s', strtotime( $event_start ) + $end_timestamp - $start_timestamp );
                $clones[] = [ $row['event_name'], $event_start, $event_end, $row['event_description'], $category_id ];
            }
            if( ! empty( $clones ) ){
                // remove the all week events before cloning into it
                $remove_from = date( 'Y-m-d H:i:s', $_POST['clone_to'] );
                $remove_to = date( "Y-m-d H:i:s", $_POST['clone_to'] + ( 3600 * 24 * 7 ) );
                $db->query( 'DELETE FROM calendar_events WHERE event_start >= ? AND event_start < ? AND category_id=?', [ $remove_from, $remove_to, $category_id ] );
                foreach( $clones AS $args ){
                    $db->query( "INSERT INTO calendar_events ( event_name, event_start, event_end, event_description, category_id ) VALUES (?,?,?,?,?)", $args ); 
                }
            }
            $response = ['success' => 'Events have been successfully cloned.' ]; 
        } else {
            $response = [ 'success' => 'There are no events to clone for this week.' ]; 
        }
        return $response;
    }
}
$GLOBALS['calendar'] = new Calendar( );