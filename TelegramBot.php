<?php
require_once "vendor/autoload.php";
require_once 'ConstFile.php';
use GuzzleHttp\Client;

class TelegramBot
{
    protected $updateId;

    protected function query($method, $params = [])
    {
        $url = ConstFile::$URL_TELEGRAM_API . ConstFile::$TOKEN_TELEGRAM_BOT . "/" . $method;

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
                'text' => $text), 'sendMessage',
                ConstFile::$URL_TELEGRAM_API . ConstFile::$TOKEN_TELEGRAM_BOT . "/");
    }

    public function sendMessageList($chat_id, $text, $jsonText)
    {
        $test = json_encode($jsonText);

        return $this->QuerySendMessagePost(array(
            'text' => $text,
            'parse_mode' => "Markdown",
            'chat_id' => $chat_id,
            'reply_markup' => $test
        ), 'sendMessage',
            ConstFile::$URL_TELEGRAM_API . ConstFile::$TOKEN_TELEGRAM_BOT . "/");
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
            'reply_markup' => $jsonMenu), 'sendMessage',
            ConstFile::$URL_TELEGRAM_API . ConstFile::$TOKEN_TELEGRAM_BOT . "/");
    }

    public function getList($update, $currentCityList)
    {
        $listCity = $this->getJsonListCity();
        for ($i = $currentCityList; $i < $currentCityList + 10; $i++) {
            if ($listCity[$i] != null) {
                $this->NewButtonCity($listCity[$i], $update);
            } else
                $this->sendMessage($update->message->chat->id,
                    "Sorry the list of cities in my brain is over \n All to the developer!");
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
                $response = "The street is very good, you can not take an umbrella";
                break;
            case "Clouds":
                $response = "Cloudy it is better to take an umbrella!";
                break;
            case "Rain":
                $response = "The rain drips in puddles ..";
                break;
            default:
                $response = "Look at the window and I'm dreaming: 3";
        }
        $this->sendMessage($message_id, $response);
        $this->sendMessage($message_id, "Minimum daily temperature:" . $result->main->temp_min);
        $this->sendMessage($message_id, "Maximum daily temperature:" . $result->main->temp_max);
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