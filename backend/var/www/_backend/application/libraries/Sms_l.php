<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sms_l
{

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->account = [
            'user_id' => base64_encode("mjcarsms"),
            'secure' => base64_encode("febbe89d8787b12ffc59442e3d191d03"),
            'mode' => base64_encode("1")
        ];
    }

    public function remained()
    {
        $data = '';
        /******************** 인증정보 ********************/
        $sms_url = "http://sslsms.cafe24.com/sms_remain.php"; // 전송요청 URL

        $host_info = explode("/", $sms_url);
        $host = $host_info[2];
        $path = $host_info[3];
        srand((float)microtime() * 1000000);
        $boundary = "---------------------" . substr(md5(rand(0, 32000)), 0, 10);

        // 헤더 생성
        $header = "POST /" . $path . " HTTP/1.0\r\n";
        $header .= "Host: " . $host . "\r\n";
        $header .= "Content-type: multipart/form-data, boundary=" . $boundary . "\r\n";

        // 본문 생성
        foreach ($this->account as $index => $value) {
            $data .= "--$boundary\r\n";
            $data .= "Content-Disposition: form-data; name=\"" . $index . "\"\r\n";
            $data .= "\r\n" . $value . "\r\n";
            $data .= "--$boundary\r\n";
        }
        $header .= "Content-length: " . strlen($data) . "\r\n\r\n";

        $fp = fsockopen($host, 80);

        if ($fp) {
            fputs($fp, $header . $data);
            $rsp = '';
            while (!feof($fp)) {
                $rsp .= fgets($fp, 8192);
            }
            fclose($fp);
            $msg = explode("\r\n\r\n", trim($rsp));
            $Count = $msg[1]; //잔여건수
            return $Count;
        } else {
            return "Connection Failed";
        }
    }

    public function send($form)
    {
        /******************** 인증정보 ********************/
        $sms_url = "https://sslsms.cafe24.com/sms_sender.php"; // HTTPS 전송요청 URL
        // $sms_url = "http://sslsms.cafe24.com/sms_sender.php"; // 전송요청 URL
        $sms['user_id'] = $this->account['user_id']; //SMS 아이디.
        $sms['secure'] = $this->account['secure']; //인증키
        $sms['msg'] = base64_encode(stripslashes($form['msg']));
        if ($form['smsType'] == "L") {
            $sms['subject'] =  base64_encode($form['subject']);
        }
        $sms['rphone'] = base64_encode($form['rphone']);
        $sms['sphone1'] = base64_encode($form['sphone1']);
        $sms['sphone2'] = base64_encode($form['sphone2']);
        $sms['sphone3'] = base64_encode($form['sphone3']);
        $sms['rdate'] = base64_encode($form['rdate']);
        $sms['rtime'] = base64_encode($form['rtime']);
        $sms['mode'] = base64_encode("1"); // base64 사용시 반드시 모드값을 1로 주셔야 합니다.
        $sms['returnurl'] = base64_encode($form['returnurl']);
        $sms['testflag'] = base64_encode($form['testflag']);
        $sms['destination'] = strtr(base64_encode($form['destination']), '+/=', '-,');
        $returnurl = $form['returnurl'];
        $sms['repeatFlag'] = base64_encode($form['repeatFlag']);
        $sms['repeatNum'] = base64_encode($form['repeatNum']);
        $sms['repeatTime'] = base64_encode($form['repeatTime']);
        $sms['smsType'] = base64_encode($form['smsType']); // LMS일경우 L
        $nointeractive = $form['nointeractive']; //사용할 경우 : 1, 성공시 대화상자(alert)를 생략

        $host_info = explode("/", $sms_url);
        // var_dump($host_info);
        $host = $host_info[2];
        // $path = $host_info[3] . "/" . $host_info[4];
        $path = $host_info[3];

        srand((float)microtime() * 1000000);
        $boundary = "---------------------" . substr(md5(rand(0, 32000)), 0, 10);
        //print_r($sms);

        // 헤더 생성
        $header = "POST /" . $path . " HTTP/1.0\r\n";
        $header .= "Host: " . $host . "\r\n";
        $header .= "Content-type: multipart/form-data, boundary=" . $boundary . "\r\n";

        $data = '';
        // 본문 생성
        foreach ($sms as $index => $value) {
            $data .= "--$boundary\r\n";
            $data .= "Content-Disposition: form-data; name=\"" . $index . "\"\r\n";
            $data .= "\r\n" . $value . "\r\n";
            $data .= "--$boundary\r\n";
        }
        $header .= "Content-length: " . strlen($data) . "\r\n\r\n";

        $fp = fsockopen($host, 80);

        if ($fp) {
            fputs($fp, $header . $data);
            $rsp = '';
            while (!feof($fp)) {
                $rsp .= fgets($fp, 8192);
            }
            fclose($fp);
            $msg = explode("\r\n\r\n", trim($rsp));
            $rMsg = explode(",", $msg[1]);
            $Result = $rMsg[0]; //발송결과
            $Count = $rMsg[1]; //잔여건수

            //발송결과 알림
            if ($Result == "success") {
                $alert = "성공";
                $alert .= " 잔여건수는 " . $Count . "건 입니다.";
            } else if ($Result == "reserved") {
                $alert = "성공적으로 예약되었습니다.";
                $alert .= " 잔여건수는 " . $Count . "건 입니다.";
            } else if ($Result == "3205") {
                $alert = "잘못된 번호형식입니다.";
            } else if ($Result == "0044") {
                $alert = "스팸문자는발송되지 않습니다.";
            } else {
                $alert = "[Error]" . $Result;
            }
        } else {
            $alert = "Connection Failed";
        }
        return ['alert' => $alert, 'count' => $Count];
        // if ($nointeractive == "1" && ($Result != "success" && $Result != "Test Success!" && $Result != "reserved")) {
        //     echo "<script>alert('" . $alert . "')</script>";
        // } else if ($nointeractive != "1") {
        //     echo "<script>alert('" . $alert . "')</script>";
        // }
        // echo "<script>location.href='" . $returnurl . "';</script>";
    }
}
