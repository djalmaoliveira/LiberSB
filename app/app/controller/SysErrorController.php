<?php

class SysErrorController extends Controller {

    public function index() {
        Liber::loadHelper('HTML');
        header('HTTP/1.0 500 Not Found');

        $this->view()->template('default.html');
        $this->view()->load('syserror.html', Array());
        $oConfig = Liber::loadModel('Config', true);
        $oMail   = Liber::loadClass("Mailer",true);
        $oMail->to($oConfig->data('contact_email'));
        $oMail->from($oConfig->data('contact_email'));
        $oMail->subject("Warning from ".Liber::conf('APP_URL'));
        $oMail->html(true);
        $msg = "

            <strong>LiberSB found a problem.</strong>
            <br/>
            <strong>Where:</strong> ".url_current_(true)."
            <br/>
            <strong>When:</strong> ".date('Y-m-d H:i:s')."
            <hr/>
            <pre>".Liber::log()->toString()."</pre>


        ";
        $oMail->body($msg);
        $oMail->send();
    }
}
?>