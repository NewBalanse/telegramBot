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
        echo $url;

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
        $url = "https://api.telegram.org/bot" . $this->token . "/";
        $client = new Client(array('base_uri' => $url));
        /*$response = $this->query('sendMessage',
            array('chat_id' => $chat_id,'text'=> $text));
        */
        echo "Ok send message\n";
        if (!empty($chat_id)) {
            $response = $client->post('sendMessage',
                array('query' => array('chat_id' => $chat_id, 'text' => $text),));

            return $response;
        } //else
            //echo "chat id empty!\n";
    }

    public function sendMessageList($chat_id, $text, $jsonText)
    {

        echo "SendMessageList\n" . $chat_id . "\n";
        $test = $jsonText;
        $test = json_encode($test);
        $url = "https://api.telegram.org/bot" . $this->token . "/";
        $client = new Client(array('base_uri' => $url));
       // echo "list\n";
        $response = $client->post('sendMessage',
            array('query' => array(
                'text' => $text,
                'parse_mode' => "Markdown",
                'chat_id' => $chat_id,
                'reply_markup' => $test
            ),));
        return $response;
    }

    public function sendMessageInline($chat_id, $text, $menuMode)
    {
        $menu = $menuMode;
        $json = array(
            "inline_keyboard" => $menu
        );
        $json = json_encode($json);
        $url = "https://api.telegram.org/bot" . $this->token . "/";
        $client = new Client(array('base_uri' => $url));
        //echo "Inline\n";
        $response = $client->post('sendMessage',
            array('query' => array(
                'chat_id' => $chat_id,
                'parse_mode' => "Markdown",
                'text' => $text,
                'reply_markup' => $json
            ),));
        return $response;
    }

    public function getList($update, $currentCityList)
    {
        $listCity = $this->getJsonListCity();
        for ($i = $currentCityList; $i < $currentCityList + 10; $i++) {
            if($listCity[$i] != null){
                $btn = array(array(array(
                    "text" => $listCity[$i]["name"],
                    "callback_data" => $listCity[$i]["id"]
                )));
                $this->sendMessageInline($update->message->chat->id, $i + 1, $btn);
            }else
                $this->sendMessage($update->message->chat->id,"Извините список городов у меня в мозгу закончился\nВсе притензии к прорамисту!");

        }
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
        //echo "callback\n";
        return $update->callback_query->data;
    }

    public function getJsonListCity()
    {
        return json_decode(file_get_contents("city.list.json"), true);
    }
}