<?php
session_start();

$host = 'localhost';
$dbname = 'u82304';
$username = 'u82304';
$password = '3093214';

$errors = [];
$error_fields = [];

if (empty($_POST['full_name'])) {
    $errors['full_name'] = "Поле ФИО обязательно для заполнения";
    $error_fields[] = 'full_name';
} elseif (!preg_match('/^[а-яА-ЯёЁa-zA-Z\s\-]+$/u', $_POST['full_name'])) {
    $errors['full_name'] = "ФИО должно содержать только буквы, пробелы и дефисы. Символы цифр и спецсимволы (@, #, $ и т.д.) недопустимы";
    $error_fields[] = 'full_name';
} elseif (strlen($_POST['full_name']) > 150) {
    $errors['full_name'] = "ФИО не должно превышать 150 символов";
    $error_fields[] = 'full_name';
}

if (empty($_POST['phone'])) {
    $errors['phone'] = "Поле Телефон обязательно для заполнения";
    $error_fields[] = 'phone';
} elseif (!preg_match('/^[\+\d\s\-\(\)]+$/', $_POST['phone'])) {
    $errors['phone'] = "Телефон может содержать только цифры, пробелы, дефисы, круглые скобки и знак +. Буквы и другие символы недопустимы";
    $error_fields[] = 'phone';
} elseif (strlen($_POST['phone']) > 20) {
    $errors['phone'] = "Телефон не должен превышать 20 символов";
    $error_fields[] = 'phone';
}

if (empty($_POST['email'])) {
    $errors['email'] = "Поле E-mail обязательно для заполнения";
    $error_fields[] = 'email';
} elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $_POST['email'])) {
    $errors['email'] = "Некорректный формат E-mail. Допустимы: буквы, цифры, точки, дефисы, знак @. Пример: name@domain.com";
    $error_fields[] = 'email';
} elseif (strlen($_POST['email']) > 100) {
    $errors['email'] = "E-mail не должен превышать 100 символов";
    $error_fields[] = 'email';
}

if (empty($_POST['birth_date'])) {
    $errors['birth_date'] = "Поле Дата рождения обязательно для заполнения";
    $error_fields[] = 'birth_date';
} elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['birth_date'])) {
    $errors['birth_date'] = "Неверный формат даты. Используйте формат ГГГГ-ММ-ДД";
    $error_fields[] = 'birth_date';
} else {
    $date = DateTime::createFromFormat('Y-m-d', $_POST['birth_date']);
    if (!$date || $date->format('Y-m-d') !== $_POST['birth_date']) {
        $errors['birth_date'] = "Некорректная дата";
        $error_fields[] = 'birth_date';
    } elseif ($date > new DateTime()) {
        $errors['birth_date'] = "Дата рождения не может быть в будущем";
        $error_fields[] = 'birth_date';
    }
}

$allowed_genders = ['male', 'female'];
if (empty($_POST['gender'])) {
    $errors['gender'] = "Поле Пол обязательно для заполнения";
    $error_fields[] = 'gender';
} elseif (!in_array($_POST['gender'], $allowed_genders)) {
    $errors['gender'] = "Выбрано недопустимое значение пола";
    $error_fields[] = 'gender';
}

$allowed_languages = [
    'Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 
    'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'
];

if (empty($_POST['languages']) || !is_array($_POST['languages'])) {
    $errors['languages'] = "Выберите хотя бы один язык программирования";
    $error_fields[] = 'languages';
} else {
    foreach ($_POST['languages'] as $lang) {
        if (!in_array($lang, $allowed_languages)) {
            $errors['languages'] = "Выбран недопустимый язык программирования";
            $error_fields[] = 'languages';
            break;
        }
    }
}

if (empty($_POST['biography'])) {
    $errors['biography'] = "Поле Биография обязательно для заполнения";
    $error_fields[] = 'biography';
} elseif (!preg_match('/^[а-яА-ЯёЁa-zA-Z0-9\s\.,!?\-\(\)]+$/u', $_POST['biography'])) {
    $errors['biography'] = "Биография может содержать только буквы, цифры, пробелы и знаки препинания (.,!?-())";
    $error_fields[] = 'biography';
}

if (!isset($_POST['contract_accepted']) || $_POST['contract_accepted'] != '1') {
    $errors['contract_accepted'] = "Необходимо подтвердить ознакомление с контрактом";
    $error_fields[] = 'contract_accepted';
}

if (!empty($errors)) {
    setcookie('form_errors', json_encode($errors), 0, '/');
    setcookie('error_fields', json_encode($error_fields), 0, '/');
    
    $query_string = http_build_query($_POST);
    header("Location: index.php?$query_string");
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
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
        ':contract_accepted' => 1
    ]);
    
    $applicationId = $pdo->lastInsertId();
    
    $sql = "SELECT id FROM programming_language WHERE name = ?";
    $stmt = $pdo->prepare($sql);
    
    $sql_insert = "INSERT INTO application_language (application_id, language_id) VALUES (?, ?)";
    $stmt_insert = $pdo->prepare($sql_insert);
    
    foreach ($_POST['languages'] as $lang) {
        $stmt->execute([$lang]);
        $lang_id = $stmt->fetchColumn();
        $stmt_insert->execute([$applicationId, $lang_id]);
    }
    
    $pdo->commit();
    
    setcookie('full_name', $_POST['full_name'], time() + 365*24*60*60, '/');
    setcookie('phone', $_POST['phone'], time() + 365*24*60*60, '/');
    setcookie('email', $_POST['email'], time() + 365*24*60*60, '/');
    setcookie('birth_date', $_POST['birth_date'], time() + 365*24*60*60, '/');
    setcookie('gender', $_POST['gender'], time() + 365*24*60*60, '/');
    setcookie('biography', $_POST['biography'], time() + 365*24*60*60, '/');
    setcookie('languages', json_encode($_POST['languages']), time() + 365*24*60*60, '/');
    setcookie('contract_accepted', '1', time() + 365*24*60*60, '/');
    
    header('Location: index.php?success=1');
    
} catch (Exception $e) {
    $pdo->rollBack();
    $errors['database'] = "Ошибка при сохранении в БД";
    setcookie('form_errors', json_encode($errors), 0, '/');
    header("Location: index.php?" . http_build_query($_POST));
}
exit;