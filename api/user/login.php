<?php

// необходимые HTTP-заголовки
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// получаем соединение с базой данных
include_once "../config/database.php";

// создание объекта товара
include_once "../objects/user.php";
$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$link = mysqli_connect("localhost", "root", "", "data_base");

// Функция для генерации случайной строки
function generateCode($length = 6)
{
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHI JKLMNOPRQSTUVWXYZ0123456789";
    $code = "";
    $clen = strlen($chars) - 1;
    while (strlen($code) < $length) {
        $code .= $chars[mt_rand(0, $clen)];
    }
    return $code;
}

// получаем отправленные данные
$post = json_decode(file_get_contents("php://input"));



// Вытаскиваем из БД запись, у которой логин равняеться введенному
$mysqli = mysqli_connect("localhost", "root", "", "data_base");
$query = mysqli_query($link, "SELECT `id`, `password` FROM users WHERE `login`='" . mysqli_real_escape_string($link, $post->login) . "' LIMIT 1");

while ($row = mysqli_fetch_assoc($query))
    if ($row['password'] === md5(md5($post->password))) {
        // Генерируем случайное число и шифруем его
        $hash = md5(generateCode(10));

        if ($user->login()) {
            // установим код ответа - 201 создано
            http_response_code(201);

            // сообщим пользователю
            echo json_encode(array("message" => "Совершен вход."), JSON_UNESCAPED_UNICODE);

            // Ставим куки
            setcookie("id", $row['id'], time() + 60 * 60 * 24 * 30, "/");
            setcookie("hash", $hash, time() + 60 * 60 * 24 * 30, "/", null, null, true);
            // Переадресовываем браузер на страницу проверки нашего скрипта
            header("Location: check.php");
            exit(); // httponly !!!
        }
        // если не удается войти, сообщим пользователю
        else {
            // установим код ответа - 503 сервис недоступен
            http_response_code(503);

            // сообщим пользователю
            echo json_encode(array("message" => "Не удается войти."), JSON_UNESCAPED_UNICODE);
        }
    } else {
        echo json_encode(array("message" => "Вы ввели неправильный логин/пароль"), JSON_UNESCAPED_UNICODE);
    }
