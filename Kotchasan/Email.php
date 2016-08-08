<?php
/*
 * @filesource Kotchasan/Email.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Kotchasan;

use \Kotchasan\Language;

/**
 * Email function
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Email extends \Kotchasan\Model
{

  /**
   * ฟังก์ชั่นส่งเมล์แบบกำหนดรายละเอียดเอง
   *
   * @param string $mailto ที่อยู่อีเมล์ผู้รับ  คั่นแต่ละรายชื่อด้วย ,
   * @param string $replyto ที่อยู่อีเมล์สำหรับการตอบกลับจดหมาย ถ้าระบุเป็นค่าว่างจะใช้ที่อยู่อีเมล์จาก noreply_email
   * @param string $subject หัวข้อจดหมาย
   * @param string $msg รายละเอียดของจดหมาย (รองรับ HTML)
   * @return string สำเร็จคืนค่าว่าง ไม่สำเร็จ คืนค่าข้อความผิดพลาด
   */
  public static function send($mailto, $replyto, $subject, $msg)
  {
    $charset = empty(self::$cfg->email_charset) ? 'utf-8' : strtolower(self::$cfg->email_charset);
    if (empty($replyto)) {
      $replyto = array(self::$cfg->noreply_email, strip_tags(self::$cfg->web_title));
    } elseif (preg_match('/^(.*)<(.*?)>$/', $replyto, $match)) {
      $replyto = array($match[1], (empty($match[2]) ? $match[1] : $match[2]));
    } else {
      $replyto = array($replyto, $replyto);
    }
    if ($charset !== 'utf-8') {
      $subject = iconv('utf-8', $charset, $subject);
      $msg = iconv('utf-8', $charset, $msg);
      $replyto[1] = iconv('utf-8', $charset, $replyto[1]);
    }
    $messages = array();
    if (empty(self::$cfg->email_use_phpMailer)) {
      // ส่งอีเมล์ด้วยฟังก์ชั่นของ PHP
      foreach (explode(',', $mailto) as $email) {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=$charset\r\n";
        $headers .= "Content-Transfer-Encoding: quoted-printable\r\n";
        $headers .= "To: $email\r\n";
        $headers .= "From: $replyto[1]\r\n";
        $headers .= "Reply-to: $replyto[0]\r\n";
        $headers .= "X-Mailer: PHP mailer\r\n";
        if (!@mail($email, $subject, $msg, $headers)) {
          $messages = array(Language::get('Unable to send mail'));
        }
      }
    } else {
      // ส่งอีเมล์ด้วย PHPMailer
      include_once VENDOR_DIR.'PHPMailer/class.phpmailer.php';
      // Create a new PHPMailer instance
      $mail = new \PHPMailer;
      // Tell PHPMailer to use SMTP
      $mail->isSMTP();
      // charset
      $mail->CharSet = $charset;
      // use html
      $mail->IsHTML();
      $mail->SMTPAuth = empty(self::$cfg->email_SMTPAuth) ? false : true;
      if ($mail->SMTPAuth) {
        $mail->Username = self::$cfg->email_Username;
        $mail->Password = self::$cfg->email_Password;
        $mail->SMTPSecure = self::$cfg->email_SMTPSecure;
      }
      if (!empty(self::$cfg->email_Host)) {
        $mail->Host = self::$cfg->email_Host;
      }
      if (!empty(self::$cfg->email_Port)) {
        $mail->Port = self::$cfg->email_Port;
      }
      $mail->AddReplyTo($replyto[0], $replyto[1]);
      $mail->SetFrom(self::$cfg->noreply_email, strip_tags(self::$cfg->web_title));
      // subject
      $mail->Subject = $subject;
      // message
      $mail->MsgHTML(preg_replace('/(<br([\s\/]{0,})>)/', "$1\r\n", $msg));
      $mail->AltBody = strip_tags($msg);
      foreach (explode(',', $mailto) as $email) {
        if (preg_match('/^(.*)<(.*)>$/', $email, $match)) {
          if ($mail->ValidateAddress($match[1])) {
            $mail->AddAddress($match[1], $match[2]);
          }
        } else {
          if ($mail->ValidateAddress($email)) {
            $mail->AddAddress($email, $email);
          }
        }
        if (false === $mail->send()) {
          $messages[$mail->ErrorInfo] = $mail->ErrorInfo;
        }
        $mail->clearAddresses();
      }
    }
    return empty($messages) ? '' : implode("\n", $messages);
  }
}