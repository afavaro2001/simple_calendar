<?php require_once( 'config.php' ) ;
      global $user;
      if( ! empty( $_GET['logout'] ) ){
          $user->logout( );
      }
      if( $user->isUserLogged( ) ){
        header( "Location: " . SITE_URL . "/admin" );
        exit;
      }
      $login_data['email'] = '';      
      $login_data['password'] = '';
      if( ! empty( $_POST ) ){
        $error = $user->loginUser( );
        $login_data = $_POST;
      }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Login</title>
    <?php require_once( 'templates/header.php' ); ?>
</head>
<body>
    <div class="container admin-page login" style="padding: 30px; border-radius:20px;">
        <h4>LOGIN</h4>
        <?php 
            if( ! empty( $error ) ){
                echo '<div class="alert alert-danger" role="alert">'. $error . '</div>';
            }
        ?>
        <form method="post" >
            <div class="mb-3">
                <label for="login-email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="login-email" aria-describedby="login-email" name="email" value="<?php echo $login_data['email'] ?>" >
            </div>
            <div class="mb-3">
                <label for="login-password" class="form-label">Password</label>
                <input type="password" class="form-control" id="login-password" name="password" value="<?php echo $login_data['email'] ?>">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Submit</button>  <?php if( ! empty( $error ) ){ ?><a href="forgot-pwd.php" class="btn btn-danger btn-sm">Forgot Password?</a><?php } ?>
        </form>
    </div>
</body>
</html>