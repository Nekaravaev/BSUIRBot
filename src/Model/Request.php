<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 02.02.17
 * Time: 5:22 PM
 */

namespace BSUIRBot\Model;

class Request {

    public function send($url, $params = [])
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Accept: application/json']);

        if (!empty($params['params'])) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params['params']);
        }
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}