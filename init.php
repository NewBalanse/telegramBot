<?php

require_once "vendor/autoload.php";
include('TelegramBot.php');
include('Watermap.php');


//get sms who write for bot

/** @var TelegramBot $telegramApi */
$telegramApi = new TelegramBot();

/** @var Watermap $weather */
$weather = new Watermap();

$currentCityList = 0;

while (true) {
    sleep(2);
    $updates = $telegramApi->getUpdates();
    //for each SMS we go over
    foreach ($updates as $update) {

        if ($telegramApi->callback($update)) {
            //get the weather around the city
            foreach ($telegramApi->getJsonListCity() as $item) {
                if ($update->callback_query->data == $item["id"]) {
                    $result = $weather->getWatherId($item["id"]);
                    $telegramApi->getWeatherTelegram($result, $update->callback_query->message->chat->id);
                    break;
                }
            }
        }

        if (isset($update->message->location)) {
            //get the weather by location
            $result = $weather->getWather($update->message->location->latitude, $update->message->location->longitude);
            $telegramApi->getWeatherTelegram($result, $update->message->chat->id);
        }
        elseif (isset($update->message->text)) {
            switch ($update->message->text) {
                case "/start":
                    $currentCityList = 0;
                    if (!empty($update->message->chat->id))
                        $telegramApi->sendMessage($update->message->chat->id,
                            "Use the '/ list' command to list cities \n
                             or just send your location");
                    break;
                case "/list":
                    if (!empty($update->message->chat->id))
                        $telegramApi->sendMessageList($update->message->chat->id,
                            "Here are 10 cities! \n Click next to see the list next!",
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
                            "Use the '/ list' command to display a list of cities\n
                            or just send your location");
                    break;
            }

        }
        else {
            //the answer to each SMS that did not fit the text is not in the locations
            if (!empty($update->message->chat->id))
                $telegramApi->sendMessage($update->message->chat->id,
                    "Send location");
        }

    }

}

