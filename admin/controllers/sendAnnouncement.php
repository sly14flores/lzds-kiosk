<?php

require_once '../../db.php';

$_POST = json_decode(file_get_contents('php://input'), true);

$announcement = $_POST['content'];

$con = new pdo_db();

$results = $con->getData("SELECT chat_id FROM profiles WHERE chat_id IS NOT NULL");

foreach ($results as $key => $result) {

    sendAnnouncement($result['chat_id'], $announcement);

}


function sendAnnouncement($id, $announcement)
{

    $url = 'https://api.telegram.org/bot5910632478:AAFvQtx_zMPYzhJmOXKuQWlD6GLuHhOC_Tk/sendMessage';
    $data = [
        'chat_id' => $id,
        'text' => $announcement
    ];

    // use key 'http' even if you send the request to https://...
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
        ],
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result === false) {
        /* Handle error */
    }

}

echo json_encode(['message' => 'Success']);

?>