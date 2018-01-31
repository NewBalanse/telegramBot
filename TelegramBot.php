<?php
require_once "vendor/autoload.php";
use GuzzleHttp\Client;

class TelegramBot
{
    protected $token = "509073020:AAE2GhkBrmydUHrDfCLjefPiM0OGkBCJWA8";
    protected $updateId;

    protected function query($method, $params = [])
    {
        $url = "https://api.telegram.org/bot" . $this->token . "/" . $method;

        if (!empty($params)) {
            $url .= "?" . http_build_query($params);
        }

        $client = new Client(array('base_uri' => $url));
        $result = $client->request("GET");
        return json_decode($result->getBody());
    }

    public function getUpdates()
    {
        $response = $this->query('getUpdates',
            array('offset' => $this->updateId + 1));
        if (!empty($response->result))
            $this->updateId = $response->result[count($response->result) - 1]->update_id;

        return $response->result;
    }

    public function sendMessage($chat_id, $text)
    {
        if (!empty($chat_id))
            return $this->QuerySendMessagePost(array(
                'chat_id' => $chat_id,
                'text' => $text), 'sendMessage', "https://api.telegram.org/bot" . $this->token . "/");
    }

    public function sendMessageList($chat_id, $text, $jsonText)
    {
        $test = json_encode($jsonText);

        return $this->QuerySendMessagePost(array(
            'text' => $text,
            'parse_mode' => "Markdown",
            'chat_id' => $chat_id,
            'reply_markup' => $test
        ), 'sendMessage', "https://api.telegram.org/bot" . $this->token . "/");
    }

    public function sendMessageInline($chat_id, $text, $menuMode)
    {
        $jsonMenu = json_encode(array(
            "inline_keyboard" => $menuMode
        ));

        return $this->QuerySendMessagePost(array(
            'chat_id' => $chat_id,
            'parse_mode' => "Markdown",
            'text' => $text,
            'reply_markup' => $jsonMenu), 'sendMessage', "https://api.telegram.org/bot" . $this->token . "/");
    }

    public function getList($update, $currentCityList)
    {
        $listCity = $this->getJsonListCity();
        for ($i = $currentCityList; $i < $currentCityList + 10; $i++) {
            if ($listCity[$i] != null) {
                $this->NewButtonCity($listCity[$i], $update);
            } else
                $this->sendMessage($update->message->chat->id,
                    "Извините список городов у меня в мозгу закончился\nВсе притензии к прорамисту!");
        }
    }

    function QuerySendMessagePost($array = [], $method, $url)
    {
        $client = new Client(array('base_uri' => $url));
        return $client->post($method, array('query' => $array));
    }

    function NewButtonCity($ArrayCity, $update)
    {
        $btn = array(array(array(
            "text" => $ArrayCity["name"],
            "callback_data" => $ArrayCity["id"]
        )));
        $this->sendMessageInline($update->message->chat->id, "city", $btn);
    }

    public function getWeatherTelegram($result, $message_id)
    {
        switch ($result->weather[0]->main) {
            case "Clear" :
                $response = "На улеце очень хорошо, зонтик можно не брать";
                break;
            case "Clouds":
                $response = "Облачно лучше взять зонтик!";
                break;
            case "Rain":
                $response = "Дождик капает по лужам..";
                break;
            default:
                $response = "Сам взгляни в окно я сплю :3";
        }
        $this->sendMessage($message_id, $response);
        $this->sendMessage($message_id, "Минимальная температура за день:" . $result->main->temp_min);
        $this->sendMessage($message_id, "Максимальная температура за день:" . $result->main->temp_max);
    }

    public function callback($update)
    {
        return $update->callback_query->data;
    }

    public function getJsonListCity()
    {
        return json_decode(file_get_contents("city.list.json"), true);
    }
}