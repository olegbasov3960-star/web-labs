<?php
session_start();

$host = 'localhost';
$dbname = 'u82304';
$username = 'u82304';
$password = '3093214';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}

$errors = [];
$old = $_POST;

if (empty($_POST['full_name'])) {
    $errors[] = "Поле ФИО обязательно для заполнения";
} elseif (!preg_match('/^[а-яА-ЯёЁa-zA-Z\s\-]+$/u', $_POST['full_name'])) {
    $errors[] = "ФИО должно содержать только буквы, пробелы и дефисы";
} elseif (strlen($_POST['full_name']) > 150) {
    $errors[] = "ФИО не должно превышать 150 символов";
}

if (empty($_POST['phone'])) {
    $errors[] = "Поле Телефон обязательно для заполнения";
} elseif (!preg_match('/^[\+\d\s\-\(\)]+$/', $_POST['phone'])) {
    $errors[] = "Телефон содержит недопустимые символы";
} elseif (strlen($_POST['phone']) > 20) {
    $errors[] = "Телефон не должен превышать 20 символов";
}

if (empty($_POST['email'])) {
    $errors[] = "Поле E-mail обязательно для заполнения";
} elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Некорректный формат E-mail";
} elseif (strlen($_POST['email']) > 100) {
    $errors[] = "E-mail не должен превышать 100 символов";
}

if (empty($_POST['birth_date'])) {
    $errors[] = "Поле Дата рождения обязательно для заполнения";
} else {
    $date = DateTime::createFromFormat('Y-m-d', $_POST['birth_date']);
    if (!$date || $date->format('Y-m-d') !== $_POST['birth_date']) {
        $errors[] = "Некорректный формат даты рождения";
    } elseif ($date > new DateTime()) {
        $errors[] = "Дата рождения не может быть в будущем";
    }
}

$allowed_genders = ['male', 'female'];
if (empty($_POST['gender'])) {
    $errors[] = "Поле Пол обязательно для заполнения";
} elseif (!in_array($_POST['gender'], $allowed_genders)) {
    $errors[] = "Выбрано недопустимое значение пола";
}

$allowed_languages = [
    'Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 
    'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'
];

if (empty($_POST['languages']) || !is_array($_POST['languages'])) {
    $errors[] = "Выберите хотя бы один язык программирования";
} else {
    foreach ($_POST['languages'] as $lang) {
        if (!in_array($lang, $allowed_languages)) {
            $errors[] = "Выбран недопустимый язык программирования: " . htmlspecialchars($lang);
        }
    }
}

if (empty($_POST['biography'])) {
    $errors[] = "Поле Биография обязательно для заполнения";
} elseif (strlen($_POST['biography']) > 65535) {
    $errors[] = "Биография слишком длинная";
}

if (!isset($_POST['contract_accepted']) || $_POST['contract_accepted'] != '1') {
    $errors[] = "Необходимо подтвердить ознакомление с контрактом";
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = $old;
    header('Location: index.php');
    exit;
}

try {
    $pdo->beginTransaction();
    
    $sql = "INSERT INTO application (full_name, phone, email, birth_date, gender, biography, contract_accepted) 
            VALUES (:full_name, :phone, :email, :birth_date, :gender, :biography, :contract_accepted)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':full_name' => $_POST['full_name'],
        ':phone' => $_POST['phone'],
        ':email' => $_POST['email'],
        ':birth_date' => $_POST['birth_date'],
        ':gender' => $_POST['gender'],
        ':biography' => $_POST['biography'],
        ':contract_accepted' => isset($_POST['contract_accepted']) ? 1 : 0
    ]);
    
    $applicationId = $pdo->lastInsertId();
    
    $placeholders = implode(',', array_fill(0, count($_POST['languages']), '?'));
    $sql = "SELECT id, name FROM programming_language WHERE name IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($_POST['languages']);
    $languages = $stmt->fetchAll();
    
    $sql = "INSERT INTO application_language (application_id, language_id) VALUES (:app_id, :lang_id)";
    $stmt = $pdo->prepare($sql);
    
    foreach ($languages as $lang) {
        $stmt->execute([
            ':app_id' => $applicationId,
            ':lang_id' => $lang['id']
        ]);
    }
    
    $pdo->commit();
    
    $_SESSION['success'] = "Данные успешно сохранены! ID заявки: " . $applicationId;
    
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['errors'] = ["Ошибка при сохранении в БД: " . $e->getMessage()];
}

header('Location: index.php');
exit;