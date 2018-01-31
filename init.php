<?php

require_once "vendor/autoload.php";

include('TelegramBot.php');
include('Watermap.php');


//получиить смс которое написали боту
$telegramApi = new TelegramBot();
$watermap = new Watermap();
$currentCityList = 0;

while (true) {
    sleep(2);
    $updates = $telegramApi->getUpdates();
//по каждому смс пробегаемся
    foreach ($updates as $update) {

        if ($telegramApi->callback($update)) {
            foreach ($telegramApi->getJsonListCity() as $item) {
                echo "start";
                if ($update->callback_query->data == $item["id"]) {
                    $result = $watermap->getWatherId($item["id"]);
                    var_dump($result);
                    $telegramApi->getWeatherTelegram($result, $update->callback_query->message->chat->id);
                    echo "break";
                    break;
                }
                echo "end";
            }
        }

        if (isset($update->message->location)) {
//получаем погоду
            $result = $watermap->getWather($update->message->location->latitude, $update->message->location->longitude);
            $telegramApi->getWeatherTelegram($result, $update->message->chat->id);
        } elseif (isset($update->message->text)) {
            switch ($update->message->text) {
                case "/start":
                    if (!empty($update->message->chat->id))
                        $telegramApi->sendMessage($update->message->chat->id, "Воспользуйтесь командой '/list' чтобы вывести список городов\nили же просто отправте свою локацию");
                    else
                        echo "Start empty chat\n";
                    break;
                case "/list":
                    $keyboard = [
                        ["Next"]
                    ];
                    $key = array(
                        "resize_keyboard" => true,
                        "keyboard" => $keyboard
                    );
                    echo "SendMessageList";
                    if (!empty($update->message->chat->id))
                        $telegramApi->sendMessageList($update->message->chat->id, "Вот 10 городов!\nнажмите делее чтобы увидить список дальше!", $key);
                    $telegramApi->getList($update, $currentCityList);
                    break;
                case "Next":
                    $currentCityList += 10;
                    $telegramApi->getList($update, $currentCityList);
                    break;
                default:
                    if (!empty($update->message->chat->id))
                        $telegramApi->sendMessage($update->message->chat->id, "Воспользуйтесь командой '/list' чтобы вывести список городов\nили же просто отправте свою локацию");
                    else
                        echo "default empty chat!\n";
                    break;
            }

        } elseif(!isset($update->message->text) && !isset($update->message->location)) {
//ответ на каждое смс
            if (!empty($update->message->chat->id))
                $telegramApi->sendMessage($update->message->chat->id, "Отправте локацию");
            else
                echo "End else empty\n";
        }

    }

}

