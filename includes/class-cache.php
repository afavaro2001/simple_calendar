<?php

class AD_Cache{
    private $_dir;
    public function __construct( ){
        $this->_dir = realpath( __DIR__ . '/..' ) . '/cache';
    }
    public function fileExists( $file ) {
        return file_exists( $this->_dir . '/' . $file );
    }
    public function fileExpired( $timestamp_past, $file ){
        $filepath = $this->_dir . '/' . $file;
        if( file_exists( $filepath ) ){
            return ( filemtime( $filepath ) <= $timestamp_past );
        }
        return true;        
    }
    public function fileGetContents( $file ){
        if( file_exists( $this->_dir . '/' . $file ) ){
            return file_get_contents( $this->_dir . '/' . $file );
        } else { 
            return '';
        }
    }
    public function filePutContents( $content, $file ){
        return file_put_contents( $this->_dir . '/' . $file, $content );
    }
    public function removeUserLogged( $user_id ){
        $this->deleteFile( 'logged-' . $user_id . '.txt' );
    }
    public function deleteFile( $file ){        
        if( $this->fileExists( $file ) ){
            unlink( $this->_dir . '/' . $file );
        }
    }
}
$GLOBALS['ad_cache'] = new AD_Cache( );