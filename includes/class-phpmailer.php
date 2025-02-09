<?php
    // Import PHPMailer classes into the global namespace
    // These must be at the top of your script, not inside a function
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    // Load Composer's autoloader
    require VENDOR_PATH . DIRECTORY_SEPARATOR  . 'autoload.php';

    class CustomPhpMailer {

        private $phpmailer;
        public function __construct(){            

            $this->_setPhpMailer();

        }        
        public function sendEmail( $data ){
            /*
            Except an array with the following keys:
                'FromName' => string;
                'To' => array of emails ;
                'Cc' => array of emails;
                'Bcc' => array of emails;
                'replyTo' => email string;
                'html' => bolean;
                'subject' => string;
                'html_body' => string;
                'text_body' => string;
            */
            if( ! empty ( $data ) ){                
                try {
                    $this->phpmailer->setFrom('wtop_donotreply@wtop.com', ( $data['FromName'] ? $data['FromName'] : '' ) );
                    if( ! empty( $data['To'] ) && is_array( $data['To'] ) ) {
                        foreach ( $data['To'] AS $email ){                            
                            $this->phpmailer->addAddress( $email );
                        }
                    }
                    if( ! empty( $data['Cc'] ) && is_array( $data['Cc'] ) ) {
                        foreach ( $data['Cc'] AS $email ){                            
                            $this->phpmailer->addCC( $email );
                        }
                    }
                    if( ! empty( $data['Bcc'] ) && is_array( $data['Bcc'] ) ) {
                        foreach ( $data['Bcc'] AS $email ){                            
                            $this->phpmailer->addBCC( $email );
                        }
                    }
                    if( ! empty( $data['replyTo'] ) ){
                        $this->phpmailer->addReplyTo( $data['replyTo'] ) ;
                    }

                    // Content
                    $this->phpmailer->isHTML( ( ! empty( $data['html'] ) ? true : false ) ) ;    
                    $this->phpmailer->Subject = $data['subject'];
                    $this->phpmailer->Body    = $data['html_body'];
                    $this->phpmailer->AltBody = $data['text_body'];
                    $this->phpmailer->send();
                    return true;
                } catch( Exception $e ){
                    // echo "Message could not be sent. Mailer Error: {$this->phpmailer->ErrorInfo}";
                }
            }
            return false;
        }
        private function _setPhpMailer(){
            $this->phpmailer = new PHPMailer(true);
            // $this->phpmailer->SMTPDebug = 2;
            $this->phpmailer->IsSMTP(); // enable SMTP
            $this->phpmailer->Host = SMTP_HOST;
            $this->phpmailer->SMTPAuth = true; // authentication enabled
            $this->phpmailer->Username = SMTP_USERNAME;
            $this->phpmailer->Password = SMTP_PASSWORD;
            $this->phpmailer->SMTPSecure = SMTP_SECURE;//or ssl // secure transfer enabled REQUIRED for Gmail
            $this->phpmailer->Port = SMTP_PORT; //
        }
    }