<?php
require_once 'vendor/autoload.php';
use GuzzleHttp\Client;

class Watermap
{
    protected $token = "5be588c10cf54d3c45f60e48c226d700";

    public function getWather($lat, $lon)
    {
        $url = "http://api.openweathermap.org/data/2.5/weather";
        $params = [];
        $params['lat'] = $lat;
        $params['lon'] = $lon;
        $params['lang'] = "en";
        $params['units'] = "metric";
        $params['APPID'] = $this->token;

        $url .= "?" . http_build_query($params);
        echo $url;

        $client = new Client(array('base_uri' => $url));
        $result = $client->request("GET");

        return json_decode($result->getBody());

    }

    public function getWatherId($id)
    {
        $url = "http://api.openweathermap.org/data/2.5/weather";
        $params = [];
        $params['id'] = $id;
        $params['lang'] = "en";
        $params['units'] = "metric";
        $params['APPID'] = $this->token;

        $url .= "?" . http_build_query($params);

        $client = new Client(array('base_uri' => $url));
        $result = $client->request("GET");

        return json_decode($result->getBody());

    }
}