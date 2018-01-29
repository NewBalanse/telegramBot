<?php

include('vendor/autoload.php');
include('TelegramBot.php');
include('Watermap.php');


//получиить смс которое написали боту
$telegramApi = new TelegramBot();
$watermap = new Watermap();

while (true) {
    sleep(2);
    $updates = $telegramApi->getUpdates();
//по каждому смс пробегаемся
    foreach ($updates as $update) {

        if (isset($update->message->location)) {
//получаем погоду
            $result = $watermap->getWather($update->message->location->latitube, $update->message->location->longitube);

            switch ($result->weather[0]->main) {
                case "Clear" :
                    $response = "На улеце очень хорошо, зонтик можно не брать";
                    break;
                case "Clouds":
                    $response = "";
                    break;
                case "Rain":
                    $response = "";
                    break;
                default:
                    $response = "";
            }
            $telegramApi->sendMessage($update->message->chat->id, $response);
        } else {
//ответ на каждое смс
            $telegramApi->sendMessage($update->message->chat->id, "Отправте локацию");

        }
    }

}

