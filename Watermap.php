<?php
require_once 'vendor/autoload.php';
require 'ConstFile.php';
use GuzzleHttp\Client;

class Watermap
{
    public function getWather($lat, $lon)
    {
        return json_decode($this->GetRequestClient(array(
            'lat' => $lat,
            'lon' => $lon,
            'lang' => "en",
            'units' => "metric",
            'APPID' => ConstFile::$TOKEN_WEATHER_MAP), "GET"
        )->getBody());

    }

    public function getWatherId($id)
    {
        return json_decode($this->GetRequestClient(array(
            'id' => $id,
            'lang' => "en",
            'units' => "metric",
            'APPID' => ConstFile::$TOKEN_WEATHER_MAP), "GET"
        )->getBody());

    }

    function GetRequestClient($params = [], $method)
    {
        ConstFile::$URL_OPEN_WEATHER_MAP .= "?" . http_build_query($params);

        $client = new Client(array('base_uri' => ConstFile::$URL_OPEN_WEATHER_MAP));
        return $client->request($method);
    }
}