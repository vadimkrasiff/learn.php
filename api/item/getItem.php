<?php

// необходимые HTTP-заголовки
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// подключение файла для соединения с базой и файл с объектом
include_once "../config/database.php";
include_once "../objects/item.php";
include_once "../objects/user.php";

// получаем соединение с базой данных
$database = new Database();
$db = $database->getConnection();

// подготовка объекта
$item = new Item($db);

// установим свойство ID записи для чтения
if(isset($_GET["id"])) {

$item->id = $_GET["id"];

$user = new User($db);

$ceck = $user->check();

if($user->rol== 'admin'){

// получим детали товара
$stmt = $item->getItem();
$num = $stmt->rowCount();

if ($num > 0) {

    $products_arr = array();



    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // извлекаем строку
        extract($row);
       

        $product_item = array(
            "id" => $id,
            "name" => $name,
            "description" => html_entity_decode($description),
            "cost" => $cost,
            "image" => $image,
            "category_id" => $category_id,
            "category_name" => $category_name,
            "manufacturer" => $manufacturer
        );
    }

    // устанавливаем код ответа - 200 OK
    http_response_code(200);

    // выводим данные о товаре в формате JSON
    echo json_encode(array("response" => 1, 'data' => $product_item ), JSON_UNESCAPED_UNICODE);
} else {

    // сообщим пользователю, что такой товар не существует
    echo json_encode(array("response" => 0,"message" => "Продукт не существует"), JSON_UNESCAPED_UNICODE);
}} else echo json_encode(array("response" => 0,"message" => "У Вас нет доступа"), JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(array("response" => 0,"message" => "Не указан номер продукт"), JSON_UNESCAPED_UNICODE);
}
