<?php
    class User { 
        private $_user;       
        public function __construct( ){            
            // $this->checkUserLogged( );
        
        }        
        public function loginUser( ){
            if( ! empty( $_POST[ 'email' ] && ! empty( $_POST[ 'password' ] ) ) ){
                global $db, $dashboard;
                $password = $dashboard->encrypt_pwd( $_POST[ 'password' ] );
                $user = $db->query( 'SELECT * FROM users WHERE email = ? AND available = ?', trim( $_POST[ 'email' ] ), 1 )->fetchArray( );
                if( ! empty( $user ) && $password === $user['password'] ){
                    global $ad_cache;
                    $ad_cache->removeUserLogged( $user['user_id'] );
                    setcookie( 'user_id', $user[ 'user_id' ] );
                    header( "Location: " . SITE_URL . "/admin" );
                    exit;
                } else {
                    if( ! empty( $user) ){
                        return "Wrong password.";
                    } else {
                        return "User not found (maybe wrong email?)";
                    }
                }
            }
        } 

        public function getUser( $user_id ){
            if( empty( $this->_user ) ){
                global $db;
                $this->_user = $db->query('SELECT * FROM users WHERE user_id = ?', $user_id )->fetchArray( );
                if( ! empty( $this->user ) ){
                    $this->_user[ 'role_can' ] = $this->_getUserPrivilegs( $this->_user[ 'role_id' ] );
                }
            }
            return $this->_user;
        }
        public function getUserLogged( ){
            $user = [];
            global $ad_cache;
            if( ! empty( $_COOKIE['user_id'] ) && $ad_cache->fileExists( 'logged-' . $_COOKIE[ 'user_id' ] .'.txt' ) ){
                $user = unserialize( $ad_cache->fileGetContents( 'logged-' . $_COOKIE[ 'user_id' ] .'.txt' ) );
            }
            return $user;
        }
        public function checkUserLogged( ){
            if( preg_match( '/login\.php|forgot\-pwd\.php|reset\-pwd.php/', $_SERVER['PHP_SELF'] ) ) return;
            if( ! empty( $_COOKIE[ 'user_id' ] ) ){                
                global $ad_cache;
                if( $ad_cache->fileExists( 'logged-' . $_COOKIE[ 'user_id' ] .'.txt' ) ){
                    return;
                } else {
                    global $db;
                    $user = $db->query( 'SELECT * FROM users WHERE user_id=? AND available = ?', $_COOKIE[ 'user_id' ] , 1 )->fetchArray( );                    
                    if( ! empty( $user ) ){
                        unset( $user['password'] );
                        $user[ 'role_can' ] = $this->_getUserPrivilegs( $user[ 'role_id' ] );
                        $ad_cache->filePutContents( serialize( $user ), 'logged-' . $user['user_id' ] . '.txt' );
                        return;
                    }
                }
            }
            $this->_redirect_user( );
        }
        public function isUserLogged( ){
            global $ad_cache;
            return ! empty( $_COOKIE[ 'user_id' ] ) && $ad_cache->fileExists( 'logged-' . $_COOKIE[ 'user_id' ] .'.txt' );
        }
        public function roleCan( $role_can ){
            $user = $this->getUserLogged( );
            return in_array( $role_can, $user['role_can'] );
        }
        public function hasPrivileges( ){
            $user = $this->getUserLogged( );
            return ! empty(  $user['role_can'] );
        } 
        public function logout( ){
            global $ad_cache;
            $ad_cache->removeUserLogged( $_COOKIE['user_id'] );
            setcookie( 'user_id', '', time() - 3600 );
            header( "Location: " . SITE_URL . '/login.php' );
            exit;
        }
        public function forceLogout( $user_id ){
            global $ad_cache;
            $ad_cache->deleteFile( 'logged-' . $user_id .'.txt' );
            $this->_js_logout_user( $user_id );

        }
        public function forgot_pwd( ){
            global $db;
            if( ! empty( $_POST['email'] ) ){
                $row = $db->query( 'SELECT * FROM users WHERE email=? AND available = 1', trim( $_POST['email'] ) )->fetchArray( );
                if( ! empty( $row ) ){
                    return [ 'success' => $this->_send_forgot_pwd( $row ), 'type' => 'Email Sent' ]; 
                } else {
                    return [ 'success' => false, 'type' => 'user_email' ];
                }
            }
        }
        private function _getUserPrivilegs( $role_id ){
            global $db;
            $rs = $db->query('SELECT * FROM user_roles WHERE role_id = ?', $role_id )->fetchArray( );
            if( ! empty( $rs['role_can' ] ) ){ 
                return unserialize( $rs[ 'role_can' ] );
            } else {
                return [];
            }
        }
        private function _redirect_user( ){
            header( "Location: " . SITE_URL . "/login.php" );
            exit;
        }
        private function _js_logout_user( $user_id ){
            $event['event'] = 'logout_user';
            $event['data'] = $user_id;
            $event['id'] = time( );
            $events_path = ASSETS_PATH . "/js-streams/admin-events.txt";
            file_put_contents( $events_path, json_encode( [ $event ] ) );
        }  
        private function _send_forgot_pwd( $user ){
            global $dashboard;
            $token = urlencode( $dashboard->encrypt_string( $user['user_id'] . '|' . time( ) ) );
            $link_url = SITE_URL . "/reset-pwd.php?email=" . $token;

            $email_body = "This link will allow to reset your password. Please be aware that this link will expire in about 1 hour.\r\n\r\n";
            $email_body .= "<a href=\"{$link_url}\">{$link_url}</a>\r\n\r\n";

            $response['html_body'] = preg_replace("/\r\n/","<br />\r\n", $email_body);
            $response['text_body'] = preg_replace("/<[^>]+>/","", $email_body);
            
            $response['To'][] = $user['email'];
            $response['FromName'] = "Dashboard Password Reset";
            $response['subject'] = "Dashboard Password Reset";
            
            $phpmailer = new CustomPhpMailer();
            return $phpmailer->sendEmail( $response );
        } 	
    }
    $GLOBALS[ 'user' ] = new User( );