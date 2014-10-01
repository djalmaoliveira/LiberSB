<?php
/**
*   A simple Mailer class, for sending e-mails.
*   It is capable to send text or html messages.
*  @package classes
*/
class Mailer {

    private $aMail      = Array('to'=>Array(), 'subject'=>'', 'body'=>'','headers'=>Array());
    private $files      = Array();

    function __construct() {
        $this->html();
        $this->charset();
        $this->aMail['headers']['From'] = $_SERVER['SERVER_ADMIN']; // default server address
    }

    /**
    *   Return Array of data stored on current object Mailer.
    *   @return Array
    */
    public function toArray() {
        return $this->aMail;
    }

    /**
    *   Set current charset of mail, default value 'utf-8'.
    *   @param String $charset
    */
    public function charset($charset='UTF-8') {
        $this->aMail['charset'] = $charset;
    }

    /**
    *   Set if mail is a html message, default value 'false'.
    *   @param boolean $isHtml
    */
    public function html($isHtml = false) {
        $this->aMail['html'] = $isHtml;
    }

    /**
    *   Set the subject of mail.
    *   @param String $subject
    */
    public function subject($subject) {
        $this->aMail['subject'] = trim($subject);
    }

    /**
    *   Set mail body.
    *   @param String $body
    */
    public function body($body) {
        $this->aMail['body'] = trim($body);
    }

    /**
    *   Set headers of mail, like Reply-to, From.
    *   @param String $header
    *   @param String $value
    */
    public function header($header, $value) {
        $this->aMail['headers'][$header] = $value;
    }


    /**
    *   Set To mail field.
    *   <code>
    *   You can use:
    *       // send to 'email' with 'name'
    *       ->to('name', 'email');
    *
    *       // send to 'email'
    *       ->to('email');
    *
    *       // send to multiple addresses
    *       ->to( Array('reply@domain.com', 'reply@domain2.com', 'Liber' => 'djalmaoliveira@gmail.com') );
    *   </code>
    *   @param String $name
    *   @param String $email
    */
    public function to($name, $email='') {
        $to = '';
        if ( is_array($name) )  {
            $this->aMail['to'] = $name;
        } else {
            if ( func_num_args() == 1 ) {
                $to = $this->_parseAddresses(Array(trim($name)));
            } else {
                $to = $this->_parseAddresses(Array($name=>trim($email)));
            }
            $this->aMail['to'] = Array($to);
        }

    }


    /**
    *   Add one or more destinations to mail.
    *   <code>
    *   You can use:
    *       // add one destination
    *       ->addTo('myname@mydomain.com');
    *
    *       // add multiples destinations
    *       ->addTo( Array('myname@mydomain.com', 'othermail@mydomain.com') );
    *   </code>
    *   @param String | Array $to
    */
    public function addTo($to) {
        if ( is_array($to) ) {
            $this->aMail['to'] = $this->aMail['to']+$to;
        } else {
            $this->aMail['to'][] = trim($to);
        }

    }

    /**
    *   Set original sender(s) to mail.
    *   <code>
    *   You can use:
    *       // set from with name
    *       ->from('name', 'email');
    *
    *       // set from only 'email'
    *       ->from('email');
    *
    *       // set multiples froms
    *       ->from( Array('reply@domain.com', 'reply@domain2.com', 'Liber' => 'djalmaoliveira@gmail.com') );
    *   </code>
    *   @param String $name
    *   @param String $email
    */
    public function from($name, $email='') {
        $from = '';
        if ( is_array($name) )  {
            $from = $this->_parseAddresses($name);
        } else {
            if ( func_num_args() == 1 ) {
                $from = $this->_parseAddresses(Array(trim($name)));
            } else {
                $from = $this->_parseAddresses(Array($name=>trim($email)));
            }
        }

        $this->header('From', $from);
    }


    /**
    *   Set Reply-To field with email address.
    *   <code>
    *   You can use:
    *       // set reply with name
    *       ->reply('name', 'email@domain.com');
    *
    *       // set reply only email
    *       ->reply('email@domain.com');
    *
    *       // set multiples replies
    *       ->reply( Array('reply@domain.com', 'reply@domain2.com', 'Liber' => 'djalmaoliveira@gmail.com') );
    *
    *   </code>
    *   @param String $name
    *   @param String $email
    */
    public function reply($name, $email='') {
        $reply = '';
        if ( is_array($name) )  {
            $reply = $this->_parseAddresses($name);
        } else {
            if ( func_num_args() == 1 ) {
                $reply = $this->_parseAddresses(Array(trim($name)));
            } else {
                $reply = $this->_parseAddresses(Array($name=>trim($email)));
            }
        }

        $this->header('Reply-To', $reply);
    }

    /**
    *   Add attachment files.
    *   <code>
    *   You can use:
    *       // attach on file
    *       ->file('/path/to/file');
    *
    *       // attach multiples files
    *       ->file( Array('/path/file1', '/path/file2') );
    *
    *       // attach multiples files with file name
    *       ->file( Array('filename1.txt' => '/path/file1', 'filename2' => '/path/file2') );
    *   </code>
    *   @param String | Array $files
    */
    public function file($files=null) {
        if ( is_array($files) ) {
            $this->files = $this->files + $files;
        } else {
            $this->files[] = $files;
        }
    }


    /**
    *   Try to deliver the mail to MTA.
    *   Return true if get, or false otherwise.
    *   @return boolean
    */
    public function send() {
        $to = $this->_parseAddresses(  $this->aMail['to']  );
        if (empty($to)) {return false;}

        // prepare headers
        $headers = "MIME-Version: 1.0\n";
        foreach( $this->aMail['headers'] as $header => $value ) {
            $headers .= $header.': '.$value."\n";
        }

        $encoding = "Content-Transfer-Encoding: 8bit";
        $message_header = "Content-Type: text/".($this->aMail['html']?'html':'plain')."; charset=".$this->aMail['charset']."\n".$encoding;
        $boundary = 'Multipart_Boundary_x'.md5(time()).'x';

        // attachments
        if ( count($this->files) > 0 ) {

            $headers  .= "Content-Type: multipart/mixed;\n boundary=\""."{$boundary}".'"'."\n";

            $this->aMail['body'] = "This is a multi-part message in MIME format.\n\n" . "--$boundary\n" .$message_header."\n\n".$this->aMail['body']."\n\n";
            $attachs = '';
            foreach( $this->files as $name => $filepath ) {
                if ( is_file($filepath) ) {
                    if ( is_numeric($name) ) {
                        $name = basename($filepath);
                    }

                    $h  = "--$boundary\n";
                    $h .= "Content-Type: application/octet-stream; name=\"$name\"\n";
                    $h .= "Content-Disposition: attachment; filename=\"$name\"\n";
                    $h .= "Content-Transfer-Encoding: base64\n\n";
                    $h .= chunk_split(base64_encode(file_get_contents($filepath)))."\n";

                    $attachs .= $h;
                }
            }
            $attachs .= "--{$boundary}--\n";
            $this->aMail['body'] .= $attachs;
        } else {
            // with text/plain
            if ( $this->aMail['html'] ) {
                $body  = "This is a multi-part message in MIME format.\n--$boundary\n";
                $body .= "Content-Type: text/plain; charset={$this->aMail['charset']}; format=flowed\n$encoding\n\n";
                $body .= strip_tags($this->aMail['body'])."\n\n--$boundary\n";
                $body .= $message_header."\n\n";
                $body .= $this->aMail['body']."\n\n--$boundary--\n";
                $headers = $headers."Content-Type: multipart/alternative;\n boundary=\"$boundary\" \n\n";
                $this->aMail['body'] = &$body;
            } else {
                $headers = $headers.$message_header;
            }
        }

        // detect Return-Path header
        $return_path = NULL;
        if ( !empty($this->aMail['headers']['Return-Path']) ) {
            $return_path = '-f '.$this->aMail['headers']['Return-Path'];
        }
        return mail($to, $this->aMail['subject'], $this->aMail['body'], $headers, $return_path);
    }


    /**
    *   Parse and return a standard list of mail addresses from Array specified.
    *   @param Array $arr
    *   @return String
    */
    private function _parseAddresses($arr) {
        $out = '';
        foreach ($arr as $_name => $_email) {
            if ( filter_var($_email, FILTER_VALIDATE_EMAIL) ) {
                if ( is_string($_name) ) {
                    $out .= $_name.' <'.$_email.'>,';
                } else {
                    $out .= $_email.',';
                }
            }
        }
        return  substr($out, 0, -1);
    }

}
?>