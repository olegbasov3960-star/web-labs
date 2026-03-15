<?php
session_start();
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
    
    .error-list {
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
            <?php if (isset($_SESSION['success'])): ?>
                <div class="message success">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
                <div class="message error">
                    <strong>Пожалуйста, исправьте следующие ошибки:</strong>
                    <ul class="error-list">
                        <?php foreach ($_SESSION['errors'] as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>
            
            <form action="process.php" method="POST">
                <div class="form-group">
                    <label for="full_name">ФИО *</label>
                    <input type="text" id="full_name" name="full_name" required 
                           value="<?php echo isset($_SESSION['old']['full_name']) ? htmlspecialchars($_SESSION['old']['full_name']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Телефон *</label>
                    <input type="tel" id="phone" name="phone" required 
                           value="<?php echo isset($_SESSION['old']['phone']) ? htmlspecialchars($_SESSION['old']['phone']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">E-mail *</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo isset($_SESSION['old']['email']) ? htmlspecialchars($_SESSION['old']['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="birth_date">Дата рождения *</label>
                    <input type="date" id="birth_date" name="birth_date" required 
                           value="<?php echo isset($_SESSION['old']['birth_date']) ? htmlspecialchars($_SESSION['old']['birth_date']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Пол *</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="gender" value="male" required
                                   <?php echo (isset($_SESSION['old']['gender']) && $_SESSION['old']['gender'] == 'male') ? 'checked' : ''; ?>> Мужской
                        </label>
                        <label>
                            <input type="radio" name="gender" value="female" required
                                   <?php echo (isset($_SESSION['old']['gender']) && $_SESSION['old']['gender'] == 'female') ? 'checked' : ''; ?>> Женский
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="languages">Любимые языки программирования *</label>
                    <select name="languages[]" id="languages" multiple required size="6">
                        <option value="Pascal">Pascal</option>
                        <option value="C">C</option>
                        <option value="C++">C++</option>
                        <option value="JavaScript">JavaScript</option>
                        <option value="PHP">PHP</option>
                        <option value="Python">Python</option>
                        <option value="Java">Java</option>
                        <option value="Haskell">Haskell</option>
                        <option value="Clojure">Clojure</option>
                        <option value="Prolog">Prolog</option>
                        <option value="Scala">Scala</option>
                        <option value="Go">Go</option>
                    </select>
                    <small>Удерживайте Ctrl для выбора нескольких вариантов</small>
                </div>
                
                <div class="form-group">
                    <label for="biography">Биография *</label>
                    <textarea id="biography" name="biography" required><?php echo isset($_SESSION['old']['biography']) ? htmlspecialchars($_SESSION['old']['biography']) : ''; ?></textarea>
                </div>
                
                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" name="contract_accepted" value="1" required
                               <?php echo (isset($_SESSION['old']['contract_accepted']) && $_SESSION['old']['contract_accepted'] == '1') ? 'checked' : ''; ?>>
                        Я ознакомлен(а) с контрактом *
                    </label>
                </div>
                
                <button type="submit" class="btn-submit">Сохранить</button>
            </form>
        </div>
    </div>
    
    <?php 
    unset($_SESSION['old']);
    ?>
</body>
</html>