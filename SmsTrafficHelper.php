<?php
/**
 * Created by PhpStorm.
 * User: kenzhegulov
 * Date: 16.11.2018
 * Time: 12:30
 */

class SmsTrafficHelper
{


    public static function Send($data)
    {
        $main_data = [
            'login' => Yii::app()->params['sms_traffic']['login'],
            'password' => Yii::app()->params['sms_traffic']['password'],
        ];

        $send_data = array_merge($data, $main_data);
        $url = Yii::app()->params['sms_traffic']['main_url'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Нужно явно указать, что будет POST запрос
        curl_setopt($ch, CURLOPT_POST, true);
        // Здесь передаются значения переменных
        curl_setopt($ch, CURLOPT_POSTFIELDS, $send_data);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public static function SendSmsCode($phone, $lang)
    {
        $msg_arr = self::GetMsgText($lang);
        $data_send = [
            'want_sms_ids' => 1,
            'phones' => $phone,
            'message' => $msg_arr['msg'],
            'start_date' => '',
            'rus' => 5,
            'originator' => 'Company Name'
        ];

        $response_basic = new SimpleXMLElement(self::Send($data_send));
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

    public function CheckAnswer($sms_id)
    {
        $data_send = [
            'operation' => 'incoming',
            'from_date' => date('Y-m-d'),
            'want_sms_ids' => 1,
            'sms_id' => $sms_id,
        ];
        return new SimpleXMLElement(self::Send($data_send));
    }

    public static function parseSendSmsCode($response)
    {
        $return = [];
        if (isset($response->result) && $response->result == 'OK') {
            $return['result'] = 1;
            if (isset($response->message_infos->message_info->sms_id)) {
                $return['sms_id'] = (string)$response->message_infos->message_info->sms_id;
            }
        }
        return $return;
    }

    public static function sendIssueSuccess($message)
    {
        $data_send = [
            'want_sms_ids' => 1,
            'phones' => $message->contact_person_phone,
            'message' => $message->text,
            'start_date' => '',
            'rus' => 5,
            'originator' => 'Company Name'
        ];
        $response_basic = new SimpleXMLElement(self::Send($data_send));

        return $response_basic;
    }




}