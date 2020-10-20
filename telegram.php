<?php
  // функция отправки сообщения Боту
  function sendTelegram($method, $data, $headers = [])
  {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => 'https://api.telegram.org/bot' . TOKEN . '/' . $method,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => array_merge(array("Content-Type: application/json"), $headers)
    ]);

    $result = curl_exec($curl);
    curl_close($curl);
    return (json_decode($result, 1) ? json_decode($result, 1) : $result);
  }

  // Токен
  define('TOKEN', '<НАШ_ТОКЕН>');

  // Принимаем входящие запросы
  $update = json_decode(file_get_contents('php://input'), true);

  // Пишем в лог входящую информацию
  file_put_contents('file.txt', '$data: ' . print_r($update, 1) . "\n", FILE_APPEND);

  // Информация при получении обычного сообщения
  $chatID = $update['message']['from']['id'];
  $text = $update['message']['text'];

  // Обработка Callback Query
  $query = $update['callback_query'];
  $queryID = $query['id'];
  $queryUserID = $query['from']['id'];
  $queryData = $query['data'];

  // Обработка шагов при получении callback_data из Inline Keyboard
  if ($queryData == 'step2_rashod')
  {
    sendTelegram('sendMessage', ['text' => 'Расход записан', 'chat_id' => $queryUserID]);
    exit();
  }

  // Обработка обычных запросов
  if (is_numeric($text))
  {
    $method = 'sendMessage';
    $sendData = [
        'text' => 'Введите тип операции',
        'reply_markup' => [
            'inline_keyboard' => [
                [
                    ['text' => 'Расход', 'callback_data' => 'step2_rashod'],
                    ['text' => 'Доход', 'callback_data' => 'step2_dohod']
                ]
            ]
        ]
    ];
  } else
  {
    $method = 'sendMessage';
    $sendData = array(
        'text' => 'Не понимаю о чем Вы'
    );
  }

  // Пишем чат ID при отправке обычных запросов что бы не дублировать в if else
  $sendData['chat_id'] = $chatID;

  // Отправляем обычный запрос Боту
  $res = sendTelegram($method, $sendData);