<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
// http://n4v.yzz.me/
require './vendor/autoload.php';

$config = include 'main-local.php';

use Krugozor\Database\Mysql;

$db = Mysql::create($config['host'], $config['username'], $config['password'])
    ->setErrorMessagesLang('ru')
    ->setDatabaseName($config['db'])
    ->setCharset("utf8mb4")
    ->setStoreQueries(true);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");

$POST = file_get_contents('php://input');
$POST = json_decode($POST, TRUE);

$table = 'notes';
$action = 'view';
if (isset($POST['action']))
    switch ($POST['action']) {
        case 'add':
            $action = 'add';
            if (isset($POST['text'])) {
                $db->query('INSERT INTO `' . $table . '` SET `text` = "?s", `checked`=0', $POST['text']);
            }
            break;
        case 'edit':
            $action = 'edit';
            if (isset($POST['text'], $POST['id'])) {
                $db->query('UPDATE `' . $table . '` SET `text` = "?s" WHERE id="?i"', $POST['text'], $POST['id']);
            }
            break;
        case 'check':
            $action = 'check';
            if (isset($POST['id'])) {
                $db->query('UPDATE `' . $table . '` SET checked = NOT checked WHERE id="?i"', $POST['id']);
            }
            break;
        case 'delete':
            $action = 'delete';
            if (isset($POST['id'])) {
                $db->query('DELETE FROM `' . $table . '` WHERE id="?i"', $POST['id']);
            }
            break;
        default:
            $action = 'no_action';
            break;
    }

$result = $db->query("SELECT * FROM `notes` WHERE 1 ORDER BY id DESC LIMIT 100");

$data = $result->fetchAssocArray();

echo json_encode(['status' => 'ok', 'result' => $data, 'action' => $action], JSON_UNESCAPED_UNICODE);


