<?php
session_start();

$cookies_data = [
    'full_name' => $_COOKIE['full_name'] ?? '',
    'phone' => $_COOKIE['phone'] ?? '',
    'email' => $_COOKIE['email'] ?? '',
    'birth_date' => $_COOKIE['birth_date'] ?? '',
    'gender' => $_COOKIE['gender'] ?? '',
    'biography' => $_COOKIE['biography'] ?? ''
];

$errors = [];
$error_fields = [];
if (isset($_COOKIE['form_errors'])) {
    $errors = json_decode($_COOKIE['form_errors'], true);
    $error_fields = json_decode($_COOKIE['error_fields'], true);
    setcookie('form_errors', '', time() - 3600, '/');
    setcookie('error_fields', '', time() - 3600, '/');
}

$old_data = $_GET;
foreach ($cookies_data as $key => $value) {
    if (!isset($old_data[$key]) || empty($old_data[$key])) {
        $old_data[$key] = $value;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Анкета разработчика</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .form-content {
            padding: 40px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input[type="text"],
        .form-group input[type="tel"],
        .form-group input[type="email"],
        .form-group input[type="date"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 10px rgba(52, 152, 219, 0.1);
        }
        
        /* Стили для полей с ошибками */
        .form-group.error input,
        .form-group.error textarea,
        .form-group.error select {
            border-color: #e74c3c;
            background-color: #fff8f8;
        }
        
        .error-message {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 5px;
            padding: 5px 10px;
            background-color: #fdf1f0;
            border-radius: 4px;
            border-left: 3px solid #e74c3c;
        }
        
        .radio-group {
            display: flex;
            gap: 20px;
            padding: 10px 0;
        }
        
        .radio-group label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: normal;
            cursor: pointer;
        }
        
        .radio-group input[type="radio"] {
            width: auto;
            margin-right: 5px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
        }
        
        .checkbox-group label {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: normal;
            cursor: pointer;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
        }
        
        select[multiple] {
            height: 150px;
        }
        
        textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .btn-submit {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 18px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
        }
        
        .btn-submit:hover {
            background-color: #2980b9;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .error-summary {
            margin-top: 10px;
            padding-left: 20px;
        }
        
        @media (max-width: 768px) {
            .form-content {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 2em;
            }
            
            .radio-group {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Анкета разработчика</h1>
            <p>Заполните информацию о себе</p>
        </div>
        
        <div class="form-content">
            <?php if (!empty($errors)): ?>
                <div class="message error">
                    <strong>Пожалуйста, исправьте следующие ошибки:</strong>
                    <ul class="error-summary">
                        <?php foreach ($errors as $field => $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <div class="message success">
                    Данные успешно сохранены!
                </div>
            <?php endif; ?>
            
            <form action="process.php" method="POST">
                <div class="form-group <?php echo in_array('full_name', $error_fields) ? 'error' : ''; ?>">
                    <label for="full_name">ФИО *</label>
                    <input type="text" id="full_name" name="full_name" 
                           value="<?php echo htmlspecialchars($old_data['full_name'] ?? ''); ?>">
                    <?php if (in_array('full_name', $error_fields) && isset($errors['full_name'])): ?>
                        <div class="error-message"><?php echo $errors['full_name']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group <?php echo in_array('phone', $error_fields) ? 'error' : ''; ?>">
                    <label for="phone">Телефон *</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?php echo htmlspecialchars($old_data['phone'] ?? ''); ?>">
                    <?php if (in_array('phone', $error_fields) && isset($errors['phone'])): ?>
                        <div class="error-message"><?php echo $errors['phone']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group <?php echo in_array('email', $error_fields) ? 'error' : ''; ?>">
                    <label for="email">E-mail *</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($old_data['email'] ?? ''); ?>">
                    <?php if (in_array('email', $error_fields) && isset($errors['email'])): ?>
                        <div class="error-message"><?php echo $errors['email']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group <?php echo in_array('birth_date', $error_fields) ? 'error' : ''; ?>">
                    <label for="birth_date">Дата рождения *</label>
                    <input type="date" id="birth_date" name="birth_date" 
                           value="<?php echo htmlspecialchars($old_data['birth_date'] ?? ''); ?>">
                    <?php if (in_array('birth_date', $error_fields) && isset($errors['birth_date'])): ?>
                        <div class="error-message"><?php echo $errors['birth_date']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group <?php echo in_array('gender', $error_fields) ? 'error' : ''; ?>">
                    <label>Пол *</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="gender" value="male"
                                   <?php echo ($old_data['gender'] ?? '') == 'male' ? 'checked' : ''; ?>> Мужской
                        </label>
                        <label>
                            <input type="radio" name="gender" value="female"
                                   <?php echo ($old_data['gender'] ?? '') == 'female' ? 'checked' : ''; ?>> Женский
                        </label>
                    </div>
                    <?php if (in_array('gender', $error_fields) && isset($errors['gender'])): ?>
                        <div class="error-message"><?php echo $errors['gender']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group <?php echo in_array('languages', $error_fields) ? 'error' : ''; ?>">
                    <label for="languages">Любимые языки программирования *</label>
                    <select name="languages[]" id="languages" multiple size="6">
                        <?php
                        $languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 
                                     'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
                        $selected_langs = $_COOKIE['languages'] ?? [];
                        if (!empty($selected_langs)) {
                            $selected_langs = json_decode($selected_langs, true);
                        }
                        foreach ($languages as $lang):
                        ?>
                            <option value="<?php echo $lang; ?>" 
                                <?php echo (is_array($selected_langs) && in_array($lang, $selected_langs)) ? 'selected' : ''; ?>>
                                <?php echo $lang; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small>Удерживайте Ctrl для выбора нескольких вариантов</small>
                    <?php if (in_array('languages', $error_fields) && isset($errors['languages'])): ?>
                        <div class="error-message"><?php echo $errors['languages']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group <?php echo in_array('biography', $error_fields) ? 'error' : ''; ?>">
                    <label for="biography">Биография *</label>
                    <textarea id="biography" name="biography"><?php echo htmlspecialchars($old_data['biography'] ?? ''); ?></textarea>
                    <?php if (in_array('biography', $error_fields) && isset($errors['biography'])): ?>
                        <div class="error-message"><?php echo $errors['biography']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group checkbox-group <?php echo in_array('contract_accepted', $error_fields) ? 'error' : ''; ?>">
                    <label>
                        <input type="checkbox" name="contract_accepted" value="1"
                               <?php echo isset($_COOKIE['contract_accepted']) ? 'checked' : ''; ?>>
                        Я ознакомлен(а) с контрактом *
                    </label>
                    <?php if (in_array('contract_accepted', $error_fields) && isset($errors['contract_accepted'])): ?>
                        <div class="error-message"><?php echo $errors['contract_accepted']; ?></div>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn-submit">Сохранить</button>
            </form>
        </div>
    </div>
</body>
</html>