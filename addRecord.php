<?php
require_once 'functions.php';

$errors = [];

$incomeCats = [
    'allowance' => 'Allowance',
    'gift'      => 'Gift',
    'others'    => 'Others',
];

$type     = $_GET['type'] ?? 'expense';
$type     = in_array($type, ['expense', 'income'], true) ? $type : 'expense';
$category = $_GET['cat'] ?? ($type === 'income' ? 'allowance' : 'food');
$custom   = '';
$amount   = '';
$where    = 'wallet';
$date     = date('Y-m-d');
$note     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $type     = in_array($_POST['type'] ?? '', ['expense', 'income'], true) ? $_POST['type'] : 'expense';
    $category = $_POST['category'] ?? '';
    $custom   = trim($_POST['custom_category'] ?? '');
    $amount   = $_POST['amount'] ?? '';
    $where    = $_POST['where'] ?? 'wallet';
    $date     = $_POST['date'] ?? date('Y-m-d');
    $note     = $_POST['note'] ?? '';

    $isCustom = false;
    if ($category === '__add') {
        if ($custom === '') {
            $errors[] = 'Please enter a name for the new category.';
        } else {
            $category = $custom;
            $isCustom = true;
        }
    }

    if (empty($errors)) {
        $data             = $_POST;
        $data['type']     = $type;
        $data['category'] = $category;
        $errors           = addTransaction($data);
    }

    if (empty($errors)) {
        if ($isCustom) {
            $label = $category;
        } elseif ($type === 'income') {
            $label = $incomeCats[$category] ?? $category;
        } else {
            $label = getCategoryLabel($category);
        }
        setFlash('Na-save ang ' . $label . ': ' . formatPeso((float) $amount));
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
    <style>
        .tabs { display: flex; gap: 8px; margin-bottom: 16px; }
        .tab {
            flex: 1; padding: 10px; border: 1px solid #ccc; background: #f5f5f5;
            border-radius: 8px; cursor: pointer; font-weight: 600;
        }
        .tab.active { background: #222; color: #fff; border-color: #222; }
        .tab[data-type="income"].active { background: #1b8a4b; border-color: #1b8a4b; }
        .tab[data-type="expense"].active { background: #c0392b; border-color: #c0392b; }
        .hidden { display: none; }
    </style>
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

    <input type="hidden" name="type" id="typeInput" value="<?= e($type) ?>">
    <div class="tabs">
        <button type="button" class="tab <?= $type === 'expense' ? 'active' : '' ?>" data-type="expense">Expense</button>
        <button type="button" class="tab <?= $type === 'income' ? 'active' : '' ?>" data-type="income">Income</button>
    </div>

    <fieldset id="expenseCats" class="<?= $type === 'expense' ? '' : 'hidden' ?>" <?= $type === 'expense' ? '' : 'disabled' ?>>
        <legend>Category</legend>
        <div class="choices">
            <?php foreach (getCategoryKeys() as $key): ?>
                <label class="choice">
                    <input type="radio" name="category" value="<?= e($key) ?>"
                        <?= ($type === 'expense' && $category === $key) ? 'checked' : '' ?>>
                    <span><i class="dot <?= getCategoryClass($key) ?>"></i><?= getCategoryLabel($key) ?></span>
                </label>
            <?php endforeach; ?>
            <label class="choice">
                <input type="radio" name="category" value="__add" id="addRadioExpense"
                    <?= ($type === 'expense' && $category === '__add') ? 'checked' : '' ?>>
                <span>Add</span>
            </label>
        </div>
        <div class="field <?= ($type === 'expense' && $category === '__add') ? '' : 'hidden' ?>" id="customFieldExpense">
            <label for="customInputExpense">New category name</label>
            <input type="text" id="customInputExpense" name="custom_category" maxlength="30"
                   value="<?= e($custom) ?>" placeholder="ex. Load">
        </div>
    </fieldset>

    <fieldset id="incomeCats" class="<?= $type === 'income' ? '' : 'hidden' ?>" <?= $type === 'income' ? '' : 'disabled' ?>>
        <legend>Category</legend>
        <div class="choices">
            <?php foreach ($incomeCats as $key => $label): ?>
                <label class="choice">
                    <input type="radio" name="category" value="<?= e($key) ?>"
                        <?= ($type === 'income' && $category === $key) ? 'checked' : '' ?>>
                    <span><?= e($label) ?></span>
                </label>
            <?php endforeach; ?>
            <label class="choice">
                <input type="radio" name="category" value="__add" id="addRadioIncome"
                    <?= ($type === 'income' && $category === '__add') ? 'checked' : '' ?>>
                <span>Add</span>
            </label>
        </div>
        <div class="field <?= ($type === 'income' && $category === '__add') ? '' : 'hidden' ?>" id="customFieldIncome">
            <label for="customInputIncome">New category name</label>
            <input type="text" id="customInputIncome" name="custom_category" maxlength="30"
                   value="<?= e($custom) ?>" placeholder="ex. Salary">
        </div>
    </fieldset>

    <div class="field">
        <label for="amount">Amount(₱)</label>
        <input type="number" id="amount" name="amount" min="0.01" step="0.01" value="<?= e($amount) ?>" placeholder="0.00" required>
        <div class="chips">

        </div>
    </div>

    <fieldset>
        <legend id="whereLegend"><?= $type === 'income' ? 'Received to' : 'Spend from' ?></legend>
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
        <input type="text" id="note" name="note" maxlength="120" value="<?= e($note) ?>"
               placeholder="<?= $type === 'income' ? 'ex. baon ngayong linggo' : 'ex. jeep papuntang school' ?>">
    </div>

    <button class="btn" type="submit" id="saveBtn">Save record</button>
</form>

<script>
(function () {
    var typeInput   = document.getElementById('typeInput');
    var tabs        = document.querySelectorAll('.tab');
    var expenseCats = document.getElementById('expenseCats');
    var incomeCats  = document.getElementById('incomeCats');
    var whereLegend = document.getElementById('whereLegend');
    var note        = document.getElementById('note');
    var addRadioExpense = document.getElementById('addRadioExpense');
    var addRadioIncome  = document.getElementById('addRadioIncome');
    var fieldExpense    = document.getElementById('customFieldExpense');
    var fieldIncome     = document.getElementById('customFieldIncome');
    var inputExpense    = document.getElementById('customInputExpense');
    var inputIncome     = document.getElementById('customInputIncome');

    function setType(type) {
        typeInput.value = type;

        tabs.forEach(function (t) {
            t.classList.toggle('active', t.dataset.type === type);
        });

        var isIncome = type === 'income';

        expenseCats.classList.toggle('hidden', isIncome);
        expenseCats.disabled = isIncome;
        incomeCats.classList.toggle('hidden', !isIncome);
        incomeCats.disabled = !isIncome;

        var group = isIncome ? incomeCats : expenseCats;
        if (!group.querySelector('input[name="category"]:checked')) {
            var first = group.querySelector('input[name="category"]');
            if (first) first.checked = true;
        }

        whereLegend.textContent = isIncome ? 'Received to' : 'Spend from';
        note.placeholder = isIncome ? 'ex. baon ngayong linggo' : 'ex. jeep papuntang school';
        toggleCustom();
    }

    function toggleCustom() {
        var isIncome = typeInput.value === 'income';

        var showExp = !isIncome && addRadioExpense.checked;
        fieldExpense.classList.toggle('hidden', !showExp);
        inputExpense.required = showExp;

        var showInc = isIncome && addRadioIncome.checked;
        fieldIncome.classList.toggle('hidden', !showInc);
        inputIncome.required = showInc;

        if (showExp) inputExpense.focus();
        if (showInc) inputIncome.focus();
    }

    tabs.forEach(function (t) {
        t.addEventListener('click', function () { setType(t.dataset.type); });
    });

    incomeCats.addEventListener('change', toggleCustom);
    expenseCats.addEventListener('change', toggleCustom);

    toggleCustom();
})();
</script>

</body>
</html>