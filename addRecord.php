<?php
require_once 'functions.php';

$errors = [];

$category = $_GET['cat'] ?? 'food';
$amount   = '';
$where    = 'wallet';
$date     = date('Y-m-d');
$note     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $category = $_POST['category'] ?? '';
    $amount   = $_POST['amount'] ?? '';
    $where    = $_POST['where'] ?? 'wallet';
    $date     = $_POST['date'] ?? date('Y-m-d');
    $note     = $_POST['note'] ?? '';

    $errors = addTransaction($_POST);

    if (empty($errors)) {
        setFlash('Na-save ang ' . getCategoryLabel($category) . ': ' . formatPeso((float) $amount));
        redirect('history.php');
    }
}

$pageTitle   = 'Add record';
$currentPage = 'add.php';

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= e($pageTitle) ?></title>
</head>
<body>

<header class="pageHead">
    <h1>Add record</h1>
    <p>.</p>
</header>

<?php if (!empty($errors)): ?>
    <div class="flash bad">
        <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" class="card form" id="addForm">
    <fieldset>
        <legend>Category</legend>
        <div class="choices">
            <?php foreach (getCategoryKeys() as $key): ?>
                <label class="choice">
                    <input type="radio" name="category" value="<?= $key ?>" <?= ($category === $key) ? 'checked' : '' ?>>
                    <span><i class="dot <?= getCategoryClass($key) ?>"></i><?= getCategoryLabel($key) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </fieldset>

    <div class="field">
        <label for="amount">Amount(₱)</label>
        <input type="number" id="amount" name="amount" min="0.01" step="0.01" value="<?= e($amount) ?>" placeholder="0.00" required>
        <div class="chips">
            
        </div>
    </div>

    <fieldset>
        <legend id="whereLegend">Spend from</legend>
        <div class="choices two">
            <label class="choice">
                <input type="radio" name="where" id="optAInput" value="<?= ($category === 'savings') ? 'physical' : 'wallet' ?>" <?= ($where !== 'online') ? 'checked' : '' ?>>
                <span id="optA">Wallet (physical)</span>
            </label>
            <label class="choice">
                <input type="radio" name="where" id="optBInput" value="online" <?= ($where === 'online') ? 'checked' : '' ?>>
                <span id="optB">Online</span>
            </label>
        </div>
    </fieldset>

    <div class="field">
        <label for="date">Date</label>
        <input type="date" id="date" name="date" value="<?= e($date) ?>" min="<?= e($_SESSION['tt']['start']) ?>" max="<?= date('Y-m-d') ?>" required>
    </div>

    <div class="field">
        <label for="note">Note</label>
        <input type="text" id="note" name="note" maxlength="120" value="<?= e($note) ?>" placeholder="ex. jeep papuntang school">
    </div>

    <button class="btn" type="submit">Save record</button>
</form>

</body>
</html>