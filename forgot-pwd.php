<?php
    require_once( 'config.php' ) ;
    global $user;
    if( $user->isUserLogged( ) ){
        header( "Location: " . SITE_URL );
        exit;
    }
    $success_display = "none";
    $danger_display = "none";
    $msg_danger = "";
    $msg_success = "";
    if( ! empty( $_POST['forgot_pwd'] ) ){
        $response = $user->forgot_pwd( );
        if( ! $response['success'] ){
            $danger_display = "block";
            if( $response['type'] == 'user_email' ){
                $msg_danger = "The user email is not correct.";
            } else {
                $msg_danger = "The SMPTP service doesn't seem to work properly (email not sent).";
            }
            $data = $_POST;
        } else {
            $success_display = "block";
            $msg_success = "The reset link has been sent.";
        }
    } 
    ?>
<!DOCTYPE html>
<html>
    <head>
        <title>Forgot Password</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">        
        <?php require_once( 'templates/header.php' ); ?>
        <style>
            .container{
                font-size: small;
            }
            .alert, .email_validator {
                display: none;
            }
            .email_validator{
                margin-top: 5px;
                color: red;
            }
        </style>
    </head>
    <body>
        
        <div class="container admin-page login" style="padding: 30px; border-radius:20px;" >
            <h4>FORGOT PASSWORD</h4>
            <div class="alert alert-success" style="display:<?php echo $success_display ?>"><?php echo $msg_success ?></div>

            <div class="alert alert-danger" style="display:<?php echo $danger_display ?>"><?php echo $msg_danger ?></div>
            
                <div id="user_form_container">
                    
                   <form method="post" autocomplete="off" id="user_form" >
                    <div class="password-group">                        
                        <label class="sr-only" for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="<?php echo $data['email'] ?>" pattern="^[^@\\s]+@[^@\\s]+\\.[^@\\s]+$" />
                        <div class="email_validator">Email Required</div>
                        <br />
                    </div>
                    <a href="<?php echo $_SERVER['PHP_SELF'] ?>" class="btn btn-primary btn-sm" id="forgot_pwd">Send Link</a>
                    <br /><br />
                    <input type="hidden" value="1" name="forgot_pwd" >
                  </form>
                </div>
        </div>           
        <script>
            const validateEmail = (email) => {
                    return email.match(
                        /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
                    );
                };
            $(document).ready(function( ){ 
                $('#user_form').on( 'submit', function(){
                    var email = $.trim( $( '#email' ).val( ) );
                    if( ! email ){
                        $('.email_validator').text( 'Email is required.' );
                        $('.email_validator').show();
                        return false;
                    } else if( ! validateEmail( email ) ){                        
                        $('.email_validator').text( 'Email doesn\'t seem valid.' );
                        $('.email_validator').show();
                        return false;
                    }
                    return true;
                });
                $('#forgot_pwd').click( function( e ){
                    e.preventDefault();
                    $('#user_form').submit();
                });
            });
        </script>
</body>
</html>