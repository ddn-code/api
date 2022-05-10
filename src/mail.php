<?php
namespace ddn\api;

// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

use ddn\api\Helpers;
use function ddn\api\router\Helpers\pugrender_s as pugrender_s;

if (!defined("__MAIL_SENDMAILS")) {
    define("__MAIL_SENDMAILS", true);
}

class Mail {
    private static $smtp_server = null;
    private static $smtp_port = null;
    private static $smtp_username = null;
    private static $smtp_password = null;
    private static $smtp_secure = null;
    private static $smtp_from = null;
    private static $smtp_from_name = null;
    private static $templates_folder = null;

    public static function set_server($server) {
        self::$smtp_server = $server;
    }

    public static function set_port($port) {
        self::$smtp_port = $port;
    }

    public static function set_username($username) {
        self::$smtp_username = $username;
    }

    public static function set_password($password) {
        self::$smtp_password = $password;
    }

    public static function set_secure($secure) {
        self::$smtp_secure = $secure;
    }

    public static function set_from($from) {
        self::$smtp_from = $from;
    }

    public static function set_from_name($from_name) {
        self::$smtp_from_name = $from_name;
    }

    public static function set_templates_folder($templates_folder) {
        self::$templates_folder = $templates_folder;
    }

    public static function build_body($body, $data = null) {
        $appname = "My awesome APP";
        if (function_exists("get_site_name")) {
            $appname = get_site_name();
        }

        $predefined_data = [
            "url" => Helpers\Web::get_accessed_url(),
            "appname" => $appname
        ];
        if ($data === null) {
            $data = [];
        }
        $data = array_merge($predefined_data, $data);

        if (str_ends_with($body, ".pug")) {
            if (! file_exists($body)) {
                $body = (self::$templates_folder??".") . "/" . trim($body, "/");
            }
            return pugrender_s($body, [ "data" => $data ]);
        } else {
            // At this point we assume that the body is a plain text, and that data has information that can be substituted in the body
            // The idea is to use notation [[key.subkey.subsubkey...]] to substitute the value of the key in the data
            throw new \Exception("Not implemented");
        }
    }

    public static function send($to, $subject, $body, ...$files) {
        if (self::$smtp_server === null) {
            throw new \Exception("SMTP server not configured");
        }

        if (!__MAIL_SENDMAILS) {
            return true;
        }

        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
        ob_start();
        $success = true;
        try {
            //Server settings
            $mail->SMTPDebug = True;                                 // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = self::$smtp_server;  // Specify main and backup SMTP servers
            $mail->Username = self::$smtp_username;                 // SMTP username
            if (self::$smtp_password !== null) {
                $mail->SMTPAuth = true;                               // Enable SMTP authentication
                $mail->Password = self::$smtp_password;                           // SMTP password
            }
            if (self::$smtp_secure !== null) {
                $mail->SMTPSecure = self::$smtp_secure;                            // Enable TLS encryption, `ssl` also accepted
            }
            if (self::$smtp_port !== null) {
                $mail->Port = self::$smtp_port;                                    // TCP port to connect to
            }

            //Recipients
            if (self::$smtp_from !== null) {
                $mail->setFrom(self::$smtp_from, self::$smtp_from_name);
            }
            $mail->addAddress($to);     // Add a recipient

            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $body;

            foreach($files as $file) {
                $mail->addAttachment($file);
            }

            $mail->send();
        } catch (Exception $e) {
            $success = false;
        }
        $result = ob_get_clean();
        return [
            "result" => $success,
            "info" => $result
        ];
    }
}
