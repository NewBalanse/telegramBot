<?php

require_once "vendor/autoload.php";
include('TelegramBot.php');
include('Watermap.php');


//получиить смс которое написали боту

/** @var TelegramBot $telegramApi */
$telegramApi = new TelegramBot();

/** @var Watermap $weather */
$weather = new Watermap();

$currentCityList = 0;

while (true) {
    sleep(2);
    $updates = $telegramApi->getUpdates();
    //по каждому смс пробегаемся
    foreach ($updates as $update) {

        if ($telegramApi->callback($update)) {
            //получаем погоду на городу
            foreach ($telegramApi->getJsonListCity() as $item) {
                if ($update->callback_query->data == $item["id"]) {
                    $result = $weather->getWatherId($item["id"]);
                    $telegramApi->getWeatherTelegram($result, $update->callback_query->message->chat->id);
                    break;
                }
            }
        }

        if (isset($update->message->location)) {
            //получаем погоду по локации
            $result = $weather->getWather($update->message->location->latitude, $update->message->location->longitude);
            $telegramApi->getWeatherTelegram($result, $update->message->chat->id);
        }
        elseif (isset($update->message->text)) {
            switch ($update->message->text) {
                case "/start":
                    $currentCityList = 0;
                    if (!empty($update->message->chat->id))
                        $telegramApi->sendMessage($update->message->chat->id,
                            "Воспользуйтесь командой '/list' чтобы вывести список городов\n
                            или же просто отправте свою локацию");
                    break;
                case "/list":
                    if (!empty($update->message->chat->id))
                        $telegramApi->sendMessageList($update->message->chat->id,
                            "Вот 10 городов!\nнажмите делее чтобы увидить список дальше!",
                            array(
                                "resize_keyboard" => true,
                                "keyboard" => [["Next"]]
                            ));

                    $telegramApi->getList($update, $currentCityList);
                    break;
                case "Next":
                    $currentCityList += 10;
                    $telegramApi->getList($update, $currentCityList);
                    break;
                default:
                    if (!empty($update->message->chat->id))
                        $telegramApi->sendMessage($update->message->chat->id,
                            "Воспользуйтесь командой '/list' чтобы вывести список городов\n
                            или же просто отправте свою локацию");
                    break;
            }

        }
        else {
            //ответ на каждое смс которое не подошло ни по тексту не по локациям
            if (!empty($update->message->chat->id))
                $telegramApi->sendMessage($update->message->chat->id,
                    "Отправте локацию");
        }

    }

}

