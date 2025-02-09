<?php

class Admin{
    public function __construct( ){

    }  
        
    public function routeAjaxRequest( ){
        if( ! empty( $_POST['editors_desk'] ) ){
            $response = [ 'data' => $this->getAdminEditorialType( 'Ed_De' ) ];
            echo json_encode( $response );
        } 
        exit;
    }

    public function getUsers( ){
        global $db;
        return $db->query( 'SELECT * FROM users' )->fetchAll( );
    }    
    
    public function getUserRoles( ){
        global $db;
        $roles = [];
        $user_roles = $db->query( 'SELECT * FROM user_roles' )->fetchAll( );
        if( ! empty( $user_roles ) ){
            foreach( $user_roles AS $key => $role ){
                $user_roles[$key]['role_can'] = unserialize( $role['role_can'] );
                if( empty( $user_roles[$key]['role_can'] ) ){
                    $user_roles[$key]['role_can'] = [];
                }
                $roles[$user_roles[$key]['role_id']] = $user_roles[$key];
            }
        }
        return $roles;
    }  

    public function getRole( $role_id ){
        global $db;
        $role = $db->query('SELECT * FROM user_roles WHERE role_id = ?', $role_id )->fetchArray( );
        if( ! empty( $role ) ){
            $role['role_can'] = unserialize( $role['role_can'] );
            if( empty( $role['role_can'] ) ){
                $role['role_can'] = [];
            }
        }
        return $role;
    }

    public function getRolePrivileges( ){
        $priv['mng_users'] = "Manage Users";
        $priv['mng_roles'] = "Manage User Roles";
        $priv['mng_calendar'] = "Manage Calendar";
        return $priv;
    }

    public function mgmtUser( ){
        if( empty( $_POST ) ) return;
        $data = [];
        foreach( $_POST AS $key => $value ){
            if( is_string( $value ) ){
                $value = trim( stripslashes( $value ) );
            }
            $data[ $key ] = $value;
        }
        $errors = $this->_validate_user( $data );
        if( ! empty( $errors ) ){
            return [ 'errors' => $errors, 'user_data' => $data ];
        }
        if( empty( $data[ 'update_password' ] ) && ! empty( $data['user_id'] ) ){
            unset( $data['password'] );
        } else {
            global $dashboard;
            $data[ 'password' ] = $dashboard->encrypt_pwd( $data[ 'password' ] );
        }
        if( empty( $data['available'] ) ){
            $data['available'] = 0;
        }
        // Unset fields not part of the table
        if( isset( $data[ 'confirm_password' ] ) ){ unset( $data['confirm_password'] ); }
        if( isset( $data[ 'update_password' ] ) ){ unset( $data['update_password'] ); }
        if( ! empty( $data['user_id'] ) ){            
            $user_id = $data['user_id'];
            unset( $data['user_id'] );
            // Update/Delete user
            if( $this->_update_user( $data, $user_id ) ){
                return [ 'success' => "User successfully updated." ];
            }
        } else {
            if( $this->_add_user( $data ) ){
                return [ 'success' => "User successfully added." ];
            }
        }
    }
    public function mgmtUserRole( ){
        if( empty( $_POST ) ) return;
        $data = [];
        foreach( $_POST AS $key => $value ){
            if( is_string( $value ) ){
                $value = trim( stripslashes( $value ) );
            }
            $data[ $key ] = $value;
        }
        $errors = $this->_validate_user_role( $data );
        if( ! empty( $errors  ) ){
            return [ 'errors' => $errors, 'role_data' => $data ];
        }
        if( empty( $data['role_can'] ) ){
            $data['role_can'] = [];
        }
        $data['role_can'] = serialize( $data['role_can'] );
        if( ! empty( $data['role_id'] ) ){            
            $role_id = $data['role_id'];
            unset( $data['role_id'] );
            if( $this->_update_user_role( $data, $role_id ) ){
                return [ 'success' => "User role successfully updated." ];
            }
        } else {
            if( $this->_add_user_role( $data ) ){
                return [ 'success' =>  "User role successfully added." ];
            }
        }
    }
    public function getAdminEditorialType( $type ){
        global $db, $ad_cache;
        $cache_key = 'editorial_' . $type;
        $data = unserialize( $ad_cache->fileGetContents( $cache_key ) );
        if( empty( $data ) ){
            $data = [];
            $rs = $db->query( 'SELECT * FROM admin_editorials WHERE editorial_type = ?', $type )->fetchArray( );
            if( ! empty( $rs ) ){
                $timestamp = time( ) + 10; // add 10 seconds :)
                $expired = strtotime( $rs['date_updated'] ) + ( $rs['minutes_expire'] * 60 );
                if( $timestamp > $expired ){
                    $data['expired'] = 1;
                    $data['content'] = '';
                    $data['minutes_expire'] = 0;
                } else {
                    $data = $rs;
                    $data['expired'] = $expired;
                }
            }
            $ad_cache->filePutContents( serialize( $data ), $cache_key );
        } else if( ! empty( $data['content'] ) && $data['expired'] < time( ) ){
            $data['expired'] = 1;
            $data['content'] = '';
            $data['minutes_expire'] = 0;
            $ad_cache->filePutContents( serialize( $data ), $cache_key );
        } 
        return $data;
    }
    public function updateAdminEditorial( ){
        global $db, $ad_cache;
        $data = $this->getAdminEditorialType( $_POST['editorial_type'] );
        $params['content'] = trim( $_POST['content'] );
        $params['date_updated'] = date( 'Y-m-d H:i:s' );
        $params['minutes_expire'] = $_POST['minutes_expire'];
        $params['editorial_type'] = $_POST['editorial_type'];
        if( ! empty( $data ) ){
            if( empty( $params['content'] ) ){
                $params['date_updated'] = "1970-01-01 00:00:00";
            }
            unset( $params['editorial_type'] );
            $data_names = array_keys( $params );
            $params['editorial_type'] = $_POST['editorial_type'];
            $response = $db->query('UPDATE admin_editorials SET ' . implode( '=?,', $data_names ) . '=? WHERE editorial_type=?', $params );        
        } else {
            $data_names = array_keys( $params );
            $response = $db->query('INSERT INTO admin_editorials SET ' . implode( '=?,', $data_names ) . '=?', $params );
        }
        $ad_cache->deleteFile( 'editorial_' . $_POST['editorial_type'] );
        return $response;
    }
    public function update_password( $data ){
        global $dashboard, $db;
        $errors = [];
        if( ! $dashboard->confirm_pwd( $data ) ){
            $errors[] = "Password doesn't match the confirmed password.";
        }
        if( ! $dashboard->validate_pwd( $data['password'] ) ){
            $errors[] = "Password must be at least 8 digits long and contain both upper-case and lower-case letters, numbers and at least one of ! @ $ ? & * special characters. ";
        }
        if( empty( $errors ) ){
            $pwd = $dashboard->encrypt_pwd( $data[ 'password' ] );
            if( empty( $data['user_id'] ) || ! is_numeric( $data['user_id'] ) ){
                $user_id = $_COOKIE[ 'user_id' ];
            } else { 
                $user_id = $data['user_id'];
            }
            $update = $db->query('UPDATE users SET password=? WHERE user_id=?', [ $pwd, $user_id ] );
            $response = [ 'success' => $update, 'msg' => 'Password updated.'];
        } else {
            $response = [ 'success' => false, 'msg' => '- ' . implode( '<br>- ', $errors ) ];
        }
        return $response;
    }
    private function _validate_user( $data ){
        global $dashboard;
        $errors = [];
        if( empty( $data['last_name']  ) || empty( $data['last_name']  ) ){
            $errors[] = "First Name and Last Name are required fields.";
        }
        if( $this->_email_exists( $data ) ){
            $errors[] = "Another user is using the same email.";
        }
        // Stop the check here if there are errors so far
        if( ! empty( $errors ) ){
            return $errors;
        }
        if( ! $dashboard->valid_email( $data['email'] ) ){
            $errors[] = "Email is not valid.";
        }
        if( ( ! empty( $data['user_id'] ) && ! empty( $data['update_password' ] ) ) || empty( $data['user_id'] ) ){
            if( ! $dashboard->confirm_pwd( $data ) ){
                $errors[] = "Password doesn't match the confirmed password.";
            }
            if( ! $dashboard->validate_pwd( $data['password' ] ) ){
                $errors[] = "Password must be at least 8 digits long and contain both upper-case and lower-case letters, numbers and at least one of ! @ $ ? & * special characters. ";
            }
        }
        if( empty( $data['role_id' ] ) ){
            $errors[] = "A Role is required.";
        }
        return $errors;
    }

    private function _email_exists( $user_data ){
        global $db;
        if( ! empty( $user_data[ 'user_id' ] ) ){
            $user = $db->query( 'SELECT * FROM users WHERE email = ? AND user_id <> ?', $user_data[ 'email' ], $user_data[ 'user_id' ] )->fetchArray( );
        } else {
            $user = $db->query( 'SELECT * FROM users WHERE email = ?', $user_data[ 'email' ] )->fetchArray( ); 
        }
        return ! empty( $user );
    }
    private function _add_user( $table_data ) {
        global $db;
        $data_names = array_keys( $table_data );
        $update = $db->query('INSERT INTO users SET ' . implode( '=?,', $data_names ) . '=?', $table_data );
        return $update->affectedRows();
    }
    private function _update_user( $table_data, $user_id ) {
        global $db;
        $data_names = array_keys( $table_data );
        $table_data[ 'user_id' ] = $user_id;
        $update = $db->query('UPDATE users SET ' . implode( '=?,', $data_names ) . '=? WHERE user_id=?', $table_data );
        if( empty( $table_data['available'] ) ){
            global $user;
            $user->forceLogout( $user_id );
        }
        return $update->affectedRows();
    }
    private function _validate_user_role( $data ){
        $errors = [];        
        if( empty( $data['role_name'] ) ){
            $errors[] = 'Role field cannot be empty.';
        }
        if( $this->_role_exists( $data ) ){
            $errors[] = 'This role already exists.';
        }
        return $errors;
    }
    private function _role_exists( $role_data ){
        global $db;
        if( ! empty( $role_data[ 'role_id' ] ) ){
            $role = $db->query( 'SELECT * FROM user_roles WHERE role_name = ? AND role_id <> ?', $role_data[ 'role_name' ], $role_data[ 'role_id' ] )->fetchArray( );
        } else {
            $role = $db->query( 'SELECT * FROM user_roles WHERE role_name = ?', $role_data[ 'role_name' ] )->fetchArray( ); 
        }
        return ! empty( $role );
    }
    private function _add_user_role( $table_data ) {
        global $db;
        $data_names = array_keys( $table_data );
        $update = $db->query('INSERT INTO user_roles SET ' . implode( '=?,', $data_names ) . '=?', $table_data );
        return $update->affectedRows();
    }
    private function _update_user_role( $table_data, $role_id ) {
        global $db;
        $data_names = array_keys( $table_data );
        $table_data[ 'role_id' ] = $role_id;
        $update = $db->query('UPDATE user_roles SET ' . implode( '=?,', $data_names ) . '=? WHERE role_id=?', $table_data );
        return $update->affectedRows();
    }
}

$GLOBALS['admin'] = new Admin( );