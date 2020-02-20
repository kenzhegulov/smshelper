<?php

class InfobipHelper
{
    private static $_instance = null;
    private $host_analytics = '';
    private $host_parser = '';
    private $IS_ACTIVE = 1;
    public $ANALYTICS = 1;
    public $PARSER = 2;

    
    private static function _connect($host, $data='', $method) { 
        
        
        $curl = curl_init();
        
        
        
        $login = Yii::app()->params['infobip']['login'];
        $pass = Yii::app()->params['infobip']['password'];

        curl_setopt_array($curl, array(
          CURLOPT_URL => $host,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => $method,
          CURLOPT_POSTFIELDS => $data?json_encode($data):'',
          CURLOPT_HTTPHEADER => array(
            "authorization: Basic ".base64_encode("$login:$pass"),
            "content-type: application/json",
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
          echo "cURL Error #:" . $err;
        } else {
          return $response;
        }
    }
  
    
    
    public static function SendSmsCode($phone, $lang)
    {
        $host = Yii::app()->params['infobip']['host'];
        
        $msg_arr = self::GetMsgText($lang);
        
        $data = [
            "from" => "Company Name",
            "to" => $phone,
            'text' => $msg_arr['msg'],
            "validityPeriod" => "360",
            "notifyContentType" =>  "application/json"
        ];
        
        $response_basic = json_decode(self::_connect($host, $data, 'POST'));
        $response = self::parseSendSmsCode($response_basic);

        $return_arr['code'] = $msg_arr['code'];
        $return_arr['sms_id'] = $response['sms_id'] ? $response['sms_id'] : 0;
                
        return $return_arr;

    }
    
    
    
    public static function GetMsgText($lang)
    {
        $code = rand(999, 9999);
        if($lang == 0){
            $msg = 'Сообщите код '. $code . ' вашему менеджеру';
        }else{
            $msg = $code . ' кодын менеджеріңізге хабарлаңыз';
        }

        $return['code'] = $code;
        $return['msg'] = $msg;
        return $return;
    }
    
    function std_class_object_to_array($stdclassobject){
        
        $_array = is_object($stdclassobject) ? get_object_vars($stdclassobject) : $stdclassobject;
        foreach ($_array as $key => $value) {
                $value = (is_array($value) || is_object($value)) ? self::std_class_object_to_array($value) : $value;
                $response[$key] = $value;
        }
        
        return $response;
        
    }
    
    function parseSendSmsCode($array)
    {
        $return = [];
        
        $response = self::std_class_object_to_array($array);
        
    //////// ERROR ////////
     
    if(isset($response['requestError'])){
        $return['messageId'] = $response['requestError']['serviceException']['messageId'];
        $return['message'] = $response['requestError']['serviceException']['text'];
    } 
    
    /////// SUCCESS //////
        
        if (isset($response['messages'])) {
            $return['result'] = 1;
            if (isset($response['messages'][0]['messageId'])) {
                $return['sms_id'] = (string)$response['messages'][0]['messageId'];
            }
        }
        
        $return['text'] = $response;
        
        
        return $return;
    }
    
    public function getSingle(){
      
        $host = Yii::app()->params['infobip']['host'];
        
        $data = [
            "from" => "Company Name",
            "to" => "mobile phone",
            "text" => "Hello World!",
            "validityPeriod" => "360",
            "notifyContentType" =>  "application/json"
        ];
        
        $request = self::_connect($host, $data, 'POST');
        
        return $request;
        
    }
    
    public function getReports($message_id){
      
        $host = Yii::app()->params['infobip']['report_host'];
        
        $data = [
            "messageId" => $message_id,
        ];
        
        $request = self::_connect($host, $data, 'GET');
        
        return $request;
        
    }
    
    public function getLogs(){
      
        $host = Yii::app()->params['infobip']['logs_host'];
      
        
        $request = self::_connect($host, '', 'GET');
        
        return $request;
        
    }
    
    public static function sendIssueSuccess($message, $to)
    {
        
        $host = Yii::app()->params['infobip']['host'];
        
        $data = [
            "from" => "Company Name",
            "to" => $to,
            "text" => $message,
            "validityPeriod" => "360",
            "notifyContentType" =>  "application/json"
        ];
        
        $response_basic = self::_connect($host, $data, 'POST');

        return $response_basic;
    }


    
}


