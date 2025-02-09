<?php
    require_once( 'config.php' ) ;
    global $user, $dashboard;
    if( $user->isUserLogged( ) ){
        header( "Location: " . SITE_URL );
        exit;
    }
    $success_display = "none";
    $danger_display = "none";  
    if( empty( $_GET['email'] ) ){
        header("location: login.php");
        exit;
    } 
    list( $user_id, $time ) = explode( "|", $dashboard->decrypt_token( $_GET['email'] ) );
    $main_msg = '';
    if( ! is_numeric( $user_id ) || ! is_numeric( $time ) ){
        $main_msg = "The email doesn't seem to be valid.";
    } else if( ( time( ) - 3600 ) > $time ){
        $main_msg = "The link has expired.";        
    }
    if( $main_msg ){
        echo "<h2>{$main_msg}</h2>";
        echo "This page will redirect in 5 seconds";
        echo '<meta http-equiv="refresh" content="5;url=login.php" />';
        exit;
    }
    if( ! empty( $_POST['update_pwd'] ) ){
        $data = $_POST;
        $data['user_id'] = $user_id;
        $response = $admin->update_password( $data );
        if( ! $response['success'] ){
            $danger_display = "block";
            $msg_danger = $response['msg'];
            $data = $_POST;
        } else {
            $success_display = "block";
            $msg_success = "Password successfully update!";
        }
    } 
    ?>
<!DOCTYPE html>
<html>
    <head>
        <title>Reset Password</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">     
        <?php require_once( 'templates/header.php' ); ?>
        <style>
            .container{
                font-size: small;
            }
            .alert{
                display: none;
            }
        </style>
    </head>
    <body>
        
        <div class="container admin-page login" style="padding: 30px; border-radius:20px;" >
            <?php echo $val ?>
            <h4>PASSWORD RESET</h4>
            <span style="font-size: small;font-weight: bold;color: green;">
                Password must be at least 8 digits long and contain both upper-case and lower-case letters, numbers and at least one of ! @ $ ? & * characters. (**passwords are case-sensitive).
                <br /><br />
            </span>
            <div class="alert alert-success" style="display:<?php echo $success_display ?>"><?php echo $msg_success ?></div>

            <div class="alert alert-danger" style="display:<?php echo $danger_display ?>"><?php echo $msg_danger ?></div>
            
                <div id="user_form_container">
                    <?php if( $success_display == "block" ) { ?>
                        <h5>Go to the <a href="login.php">LOGIN</a> page.</h5>
                    <?php } else { ?>
                        <form method="post" autocomplete="off" id="user_form" >
                            <div class="password-group">                        
                                <label class="sr-only" for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Password" value="<?php echo $data['password'] ?>" />
                                <br />
                                <label class="sr-only" for="confirm_password">Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" value="<?php echo $data['confirm_password'] ?>" />
                                <br />
                            </div>
                            <input type="checkbox" id="view_password" name="view_password" value="1" class="form-check-input" > <span style="font-size: small">View Characters</span>
                            <br /><br />
                            <a href="<?php echo $_SERVER['PHP_SELF'] ?>" class="btn btn-primary btn-sm" id="update_password">Update Password</a>
                            <br /><br />
                            <input type="hidden" value="1" name="update_pwd" >
                        </form>
                    <?php } ?>
                </div>
        </div>           
        <script>
            $(document).ready(function( ){                
                $('#view_password').click( function( e ){
                    if( $(this).is(':checked') ){
                        $("#password,#confirm_password").attr('type','text');
                    } else {
                        $("#password,#confirm_password").attr('type','password');
                    }
                });
                $('#update_password').click( function( e ){
                    e.preventDefault();
                    $('#user_form').submit();
                });
                <?php if( $data['view_password'] ) { ?>
                    $('#view_password').click();
                <?php } ?>
            });
        </script>
</body>
</html>