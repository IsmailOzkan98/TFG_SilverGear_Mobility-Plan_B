<?php

function getWeather() {

    $city = "Alicante";
    $apiKey = "ad21d76160cbf5e6962cc15717035bfe";
    $url = "https://api.openweathermap.org/data/2.5/weather?q=$city&units=metric&lang=es&appid=$apiKey";

    $response = file_get_contents($url);

    if ($response === FALSE) {
        return null;
    }

    $data = json_decode($response, true);

    return [
        "city" => $data["name"],
        "temp" => round($data["main"]["temp"]),
        "desc" => ucfirst($data["weather"][0]["description"]),
        "icon" => $data["weather"][0]["icon"]
    ];
}
