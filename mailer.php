<?php

require_once('PHPMailer/PHPMailerAutoload.php');

class SMTPSettings {

    const HOST = 'appmail.larsentoubro.com';
    const PORT = '25';
    const USERNAME = "";
    const PASSWORD = "";
    const SMTP_SECURE = "";
	const HttpProxyHostname = 'mumproxy.ltindia.com';
	const HttpProxyPort = 2000;
    const IS_AUTH = false;

}

class Mailer {

    var $mailTo;
    var $body;
    var $hdrs;
    var $extra;
    var $file;

    public function setMailTo($mail = "", $extra = array()) {
        $this->mailTo = $mail;
        $this->extra = $extra;
    }

    public function setMail($html, $hdrs, $file = "") {
        $this->body = $html;
        $this->hdrs = $hdrs;
        $this->file = $file;
    }

    public function sendMail() {

        return $this->sendSmtpEmail();
    }

    function sendEmail($to, $from, $fromName, $subject, $body) {

        $headers = array(
            'From' => $fromName . "<" . $from . ">",
            'Subject' => $subject,
            'To' => 'rimpy@logicproviders.com', //$to,
            'Reply-To' => $fromName . "<" . $from . ">",
            'Return-path' => $fromName . "<" . $from . ">",
            'Sender' => $from
        );
		$this->setMailTo($to);
        $this->setMail($body, $headers);
        return $this->sendSmtpEmail();
    }

   
    protected function sendSmtpEmail() {

        $mail = new PHPMailer();
        $mail->IsSMTP(); // telling the class to use SMTP
        $mail->Host = SMTPSettings::HOST; // SMTP server
		//$mail->SMTPDebug = 2;                     // enables SMTP debug information (for testing)
        // 1 = errors and messages
        //2 = messages only
        
		$mail->SMTPAuth = SMTPSettings::IS_AUTH;                  // enable SMTP authentication
		$mail->SMTPSecure = SMTPSettings::SMTP_SECURE;               // sets the prefix to the servier
		$mail->Host = SMTPSettings::HOST;      // sets GMAIL as the SMTP server
		$mail->Port = SMTPSettings::PORT;                   // set the SMTP port for the GMAIL server
		$mail->Username = SMTPSettings::USERNAME;  // GMAIL username
		$mail->Password = SMTPSettings::PASSWORD;            // GMAIL password
        // echo ($this->mailTo) . '<br>';
        // echo ($this->hdrs["To"]) . '<br>';
        //echo ($this->hdrs["Subject"]) . '<br>';
        //echo ($this->hdrs["From"]) . '<br>';
        //  die('here');
        if (!empty($this->mailTo)) {
            $to = $this->mailTo;
        } elseif (!empty($this->hdrs["To"])) {
            $to = $this->hdrs["To"];
        } else {
            throw new Exception("Mailer Error: Email Address To not supplied");
        }

        $subject = $this->hdrs["Subject"];
        $body = $this->body;
        $extra = $this->extra;

		$from = $this->hdrs["From"];
		$fromName = $this->hdrs["From"];
        

        $mail->SetFrom($from, $fromName);

        $mail->Subject = $subject;
        ####################################

        $mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
        $mail->MsgHTML($body);

        $emailToArray = explode(",", $to);
        foreach ($emailToArray as $toEmailAddress) {
            $mail->AddAddress($toEmailAddress, "<" . $toEmailAddress . ">");
        }

        
			//$result = $mail->Send();
			return $this->sendWithRetry($mail);
			
	}
        
    
	
	public function sendWithRetry($mail, $try = 1){
		
		if($this->checkSmtpConnection()){
			return $mail->Send();
		}elseif($try < 5){
			$try++;
			sleep(3);
			return $this->sendWithRetry($mail, $try);
		}else{
			return false;
		}
	}
	public function checkSmtpConnection() {
        $mail = new PHPMailer();
        $smtp = $mail->getSMTPInstance();
	    return $smtp->connect(SMTPSettings::HOST, SMTPSettings::PORT);
    }	

}

?>