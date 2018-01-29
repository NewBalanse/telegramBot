<?php

class Watermap
{
    protected $token = "5be588c10cf54d3c45f60e48c226d700";

    public function getWather($lat, $lon)
    {
        $url = "http://api.openweathermap.org/data/2.5/weather";
        $params = [];
        $params['lat'] = $lat;
        $params['lon'] = $lon;
        $params['APPID'] = $this->token;

        $url .= "?" . http_build_query($params);

        $client = new \GuzzleHttp\Client([
            'base_uri' => $url
        ]);
        $result = $client->request('GET');

        return json_decode($result->getBody());

    }
}