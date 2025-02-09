<?php require_once( '../config.php' );
      global $user;
      $user->checkUserLogged( );
;      if( $user->roleCan( 'mng_users' ) ){
        header( 'Location: users.php' );
      } else if( $user->roleCan( 'mng_roles' ) ){
        header( 'Location: user_roles.php' );
      } else if( $user->roleCan( 'mng_calendar' ) ){
        header( 'Location: calendar.php' );
      } 
      echo "You are not allowed to view this page.";
      exit;