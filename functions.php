<?php
    define( 'ROOT_PATH', __DIR__ );
    define( 'ADMIN_PATH', ROOT_PATH . DIRECTORY_SEPARATOR  .'admin' );
    define( 'ASSETS_PATH', ROOT_PATH . DIRECTORY_SEPARATOR  . 'assets' );
    define( 'INCLUDES_PATH', ROOT_PATH . DIRECTORY_SEPARATOR  . 'includes' );
    define( 'TEMPLATES_PATH', ROOT_PATH . DIRECTORY_SEPARATOR  . 'templates' );
    define( 'VENDOR_PATH', ROOT_PATH . DIRECTORY_SEPARATOR  . 'vendor' );
    require_once( INCLUDES_PATH . DIRECTORY_SEPARATOR  . 'class-db.php' );
    require_once( INCLUDES_PATH . DIRECTORY_SEPARATOR  . 'class-cache.php' );
    require_once( INCLUDES_PATH . DIRECTORY_SEPARATOR  . 'class-admin.php' );
    require_once( INCLUDES_PATH . DIRECTORY_SEPARATOR  . 'class-user.php' );
    require_once( INCLUDES_PATH . DIRECTORY_SEPARATOR  . 'class-calendar.php' );
    require_once( INCLUDES_PATH . DIRECTORY_SEPARATOR  . 'class-phpmailer.php' );
    class Dashboard {
        public function __contruct(){

        }  

        public function decrypt_pwd(  $string, $key = 5  ){
            $result = '';
            $string = base64_decode( trim( $string ) );
            for($i=0,$k=strlen($string); $i< $k ; $i++) {
                $char = substr($string, $i, 1);
                $keychar = substr($key, ($i % strlen($key))-1, 1);
                $char = chr(ord($char)-ord($keychar));
                $result.=$char;
            }
            return $result;
        }

        public function encrypt_pwd( $string, $key = 5 ){
            $result = '';
            $string = trim( $string );
            for($i=0, $k= strlen($string); $i<$k; $i++) {
                $char = substr($string, $i, 1);
                $keychar = substr($key, ($i % strlen($key))-1, 1);
                $char = chr(ord($char)+ord($keychar));
                $result .= $char;
            }
            return base64_encode($result);
        }

        public function validate_pwd( $pwd ){
            return preg_match( "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $pwd );
        }

        public function valid_email( $email ){
            return filter_var( $email, FILTER_VALIDATE_EMAIL );
        }

        public function confirm_pwd( $data ){
            return $data['password'] == $data['confirm_password'];;
        }
        public function encrypt_string( $string ){
            $data = $this->_get_encrypt_options();
            return openssl_encrypt( $string, $data['ciphering'], $data['encryption_key'], $data['options'], $data['encryption_iv'] );
        }
        public function decrypt_token( $token ){
            $data = $this->_get_encrypt_options();
            return openssl_decrypt ( $token, $data['ciphering'], $data['encryption_key'], $data['options'], $data['encryption_iv'] );
        }
        private function _get_encrypt_options( ){
            $response = array( 'ciphering'      => "AES-128-CTR",
                               'encryption_iv'  => '1234567891011121',
                               'options'        => 0,
                               'encryption_key' => '{8/~esq%jGv?'
                            );
            return $response;
        }
    }
    $GLOBALS['dashboard'] = new Dashboard( );