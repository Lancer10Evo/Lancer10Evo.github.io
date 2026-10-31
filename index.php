<?php
// =========================================================================
// ⚙️ НАСТРОЙКИ САЙТА, ЮMONEY И RCON СЕРВЕРА MINECRAFT
// =========================================================================
$SERVER_NAME      = "KINETIK MINE";
$SERVER_IP        = "play.kinetik-mc.ru:25565";
$DISCORD_LINK     = "https://discord.gg/ВАША_ССЫЛКА";

// Настройки кошелька ЮMoney (Уведомления настраиваются тут: https://yoomoney.ru/transfer/myservices/http-notification)
$YOOMONEY_WALLET  = "410010000000000";      // Номер твоего кошелька ЮMoney
$YOOMONEY_SECRET  = "ТВОЁ_СЕКРЕТНОЕ_СЛОВО"; // Секретное слово из настроек HTTP-уведомлений ЮMoney

// Настройки RCON твоего Minecraft сервера (из файла server.properties)
$RCON_HOST        = "127.0.0.1";            // IP твоего сервера (без порта)
$RCON_PORT        = 25575;                  // rcon.port из server.properties
$RCON_PASSWORD    = "ТВОЙ_ПАРОЛЬ_RCON";     // rcon.password из server.properties

// =========================================================================
// 💎 СПИСОК ВСЕХ 17 ПРИВИЛЕГИЙ (Коды совпадают с нашим конфигом LuckPerms!)
// =========================================================================
$PRIVILEGES = [
    "prime"       => ["name" => "ПРАЙМ",          "price" => 29,   "color" => "#38bdf8", "desc" => "/fly, /feed, /hat, /workbench, /ec, 5 слотов на /ah"],
    "architect"   => ["name" => "АРХИТЕКТОР",     "price" => 59,   "color" => "#0284c7", "desc" => "Режим /gm 1 (Креатив), /heal, /clear, 7 слотов на /ah"],
    "warden"      => ["name" => "СТРАЖ",          "price" => 99,   "color" => "#6366f1", "desc" => "/vanish, /tp, /invsee, /kick, /tempmute, /repair"],
    "punisher"    => ["name" => "КАРАТЕЛЬ",       "price" => 149,  "color" => "#ef4444", "desc" => "Бессмертие /god, /tempban, /unmute, /repair all, цветной /nick"],
    "titan"       => ["name" => "ТИТАН",          "price" => 199,  "color" => "#a855f7", "desc" => "/speed, /anvil, /tpahere, /jump, /top, сидеть на игроках"],
    "inquisitor"  => ["name" => "ИНКВИЗИТОР",     "price" => 269,  "color" => "#dc2626", "desc" => "/tphere, проверка гриферов /co inspect, /whois, /banlist"],
    "maker"       => ["name" => "ТВОРЕЦ",         "price" => 349,  "color" => "#f59e0b", "desc" => "Флаги региона WorldGuard (PvP, приветствие), /lightning, /fireball"],
    "magnate"     => ["name" => "МАГНАТ",         "price" => 449,  "color" => "#eab308", "desc" => "Объявление /broadcast, /enchant, /exp, /more, вход на полный сервер"],
    "sovereign"   => ["name" => "СУВЕРЕН",        "price" => 590,  "color" => "#f87171", "desc" => "/ban навсегда, /unban, установка варпов /setwarp, смена спавнеров"],
    "root"        => ["name" => "⚡ ROOT ⚡",       "price" => 790,  "color" => "#22d3ee", "desc" => "Доступ к WorldEdit (//wand, //set, //replace, //walls, //copy), /kill"],
    "monarch"     => ["name" => "МОНАРХ",         "price" => 990,  "color" => "#fbbf24", "desc" => "Откат гриферов /co rollback, //sphere, //cyl, /time, иммунитет к муту/кику"],
    "boss"        => ["name" => "ХОЗЯИН СЕРВЕРА", "price" => 1290, "color" => "#b91c1c", "desc" => "/gm 3, изменение чужих инвентарей и эндер-сундуков, иммунитет к бану"],
    "overseer"    => ["name" => "НАДЗИРАТЕЛЬ",    "price" => 1590, "color" => "#4ade80", "desc" => "Прослушка ЛС /socialspy, уведомления всех наказаний, статус стаффа в TAB"],
    "kinetikplus" => ["name" => "✦ KINETIK+ ✦",   "price" => 1990, "color" => "#e879f9", "desc" => "Фирменный RGB-префикс проекта, /kittycannon, /beezooka, 40 лотов /ah"],
    "directorate" => ["name" => "❖ ДИРЕКЦИЯ ❖",   "price" => 2490, "color" => "#10b981", "desc" => "Полный CoreProtect, управление лотами /ah и аренами BedWars"],
    "arbiter"     => ["name" => "⚜ ВЕРШИТЕЛЬ ⚜",  "price" => 3490, "color" => "#facc15", "desc" => "Полный WorldEdit без лимитов, бан по IP, все киты сервера, 50 лотов /ah"],
    "patron"      => ["name" => "💎 МЕЦЕНАТ 💎",   "price" => 4990, "color" => "#06b6d4", "desc" => "ФИНАЛЬНЫЙ СТАТУС: Все возможности доната и максимальный приоритет!"]
];

// =========================================================================
// 🔌 ВСТРОЕННАЯ ФУНКЦИЯ ОТПРАВКИ КОМАНД ПО RCON В MINECRAFT
// =========================================================================
function sendMinecraftRcon($host, $port, $password, $command) {
    $socket = @fsockopen($host, $port, $errno, $errstr, 5);
    if (!$socket) {
        return false;
    }
    stream_set_timeout($socket, 5);

    // 1. Авторизация RCON (Type = 3)
    $authPacket = pack("VV", 1, 3) . $password . "\x00\x00";
    $authPacket = pack("V", strlen($authPacket)) . $authPacket;
    fwrite($socket, $authPacket);

    $respSize = fread($socket, 4);
    if (strlen($respSize) < 4) { fclose($socket); return false; }
    $size = unpack("V", $respSize)[1];
    $respBody = fread($socket, $size);
    $respData = unpack("VreqId/Vtype", substr($respBody, 0, 8));

    if ($respData['reqId'] == -1) {
        fclose($socket);
        return false; // Неверный пароль RCON
    }

    // 2. Отправка самой команды (Type = 2)
    $cmdPacket = pack("VV", 2, 2) . $command . "\x00\x00";
    $cmdPacket = pack("V", strlen($cmdPacket)) . $cmdPacket;
    fwrite($socket, $cmdPacket);

    fread($socket, 4096);
    fclose($socket);
    return true;
}

// =========================================================================
// 🔔 ОБРАБОТЧИК HTTP-УВЕДОМЛЕНИЙ ОТ ЮMONEY (Срабатывает автоматом после оплаты)
// URL для настроек ЮMoney: https://твой-сайт.ru/index.php?webhook=1
// =========================================================================
if (isset($_GET['webhook']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $notification_type = $_POST['notification_type'] ?? '';
    $operation_id      = $_POST['operation_id'] ?? '';
    $amount            = $_POST['amount'] ?? '';
    $withdraw_amount   = floatval($_POST['withdraw_amount'] ?? $amount);
    $currency          = $_POST['currency'] ?? '';
    $datetime          = $_POST['datetime'] ?? '';
    $sender            = $_POST['sender'] ?? '';
    $codepro           = $_POST['codepro'] ?? '';
    $label             = $_POST['label'] ?? '';
    $sha1_hash         = $_POST['sha1_hash'] ?? '';
    $unaccepted        = $_POST['unaccepted'] ?? 'false';

    // 1. Проверяем криптографическую подпись SHA-1 от ЮMoney
    $checkString = "{$notification_type}&{$operation_id}&{$amount}&{$currency}&{$datetime}&{$sender}&{$codepro}&{$YOOMONEY_SECRET}&{$label}";
    $calculatedHash = sha1($checkString);

    if (!hash_equals($calculatedHash, $sha1_hash) || $codepro === 'true' || $unaccepted === 'true') {
        http_response_code(400);
        exit("Invalid signature");
    }

    // 2. Разбираем метку платежа (НикИгрока:код_привилегии)
    if (strpos($label, ':') !== false) {
        list($playerNick, $roleCode) = explode(':', $label, 2);
        $playerNick = preg_replace('/[^a-zA-Z0-9_]/', '', $playerNick);

        if (!empty($playerNick) && isset($PRIVILEGES[$roleCode])) {
            $requiredPrice = $PRIVILEGES[$roleCode]['price'];
            $roleTitle     = $PRIVILEGES[$roleCode]['name'];

            // Проверяем, что оплаченная сумма соответствует цене привилегии (с учётом комиссии ЮMoney до 4%)
            if ($withdraw_amount >= ($requiredPrice * 0.95) || floatval($amount) >= ($requiredPrice * 0.95)) {
                // Выдаём донат через LuckPerms и пишем объявление в чат сервера!
                sendMinecraftRcon($RCON_HOST, $RCON_PORT, $RCON_PASSWORD, "lp user {$playerNick} parent add {$roleCode}");
                sendMinecraftRcon($RCON_HOST, $RCON_PORT, $RCON_PASSWORD, "bc &6&l[МАГАЗИН] &fИгрок &e&l{$playerNick} &fкупил привилегию &d&l[{$roleTitle}]&f! Спасибо за поддержку!");

                // Записываем лог успешной покупки
                file_put_contents(__DIR__ . "/donations_log.txt", date("[Y-m-d H:i:s] ") . "Игрок {$playerNick} купил {$roleCode} ({$withdraw_amount} руб.)\n", FILE_APPEND);
            }
        }
    }

    http_response_code(200);
    exit("OK");
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($SERVER_NAME) ?> — Магазин привилегий</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    background: radial-gradient(circle at top, #1a103c 0%, #090614 100%);
    color: #f1f5f9;
    font-family: 'Segoe UI', system-ui, sans-serif;
    min-height: 100vh;
    padding-bottom: 60px;
  }
  header {
    text-align: center;
    padding: 45px 20px 30px;
    border-bottom: 1px solid rgba(139, 92, 246, 0.25);
  }
  .logo {
    font-size: 2.6rem;
    font-weight: 900;
    letter-spacing: 2px;
    background: linear-gradient(90deg, #8b5cf6, #ec4899, #38bdf8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }
  .ip-badge {
    display: inline-block;
    margin-top: 14px;
    padding: 10px 22px;
    background: rgba(139, 92, 246, 0.15);
    border: 1px solid rgba(139, 92, 246, 0.45);
    border-radius: 50px;
    cursor: pointer;
    font-weight: 600;
    transition: 0.2s;
  }
  .ip-badge:hover {
    background: rgba(139, 92, 246, 0.3);
    transform: translateY(-2px);
  }
  .container {
    max-width: 1100px;
    margin: 35px auto;
    padding: 0 20px;
    display: grid;
    grid-template-columns: 360px 1fr;
    gap: 28px;
  }
  @media (max-width: 860px) {
    .container { grid-template-columns: 1fr; }
  }
  .card-panel {
    background: rgba(19, 14, 38, 0.88);
    border: 1px solid rgba(139, 92, 246, 0.3);
    border-radius: 18px;
    padding: 26px;
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.5);
    height: fit-content;
  }
  h2 {
    font-size: 1.35rem;
    margin-bottom: 18px;
    color: #e2e8f0;
  }
  label {
    display: block;
    font-size: 0.9rem;
    color: #94a3b8;
    margin: 14px 0 6px;
  }
  input, select {
    width: 100%;
    padding: 13px 15px;
    background: #0d091a;
    border: 1px solid rgba(139, 92, 246, 0.4);
    border-radius: 10px;
    color: #fff;
    font-size: 1rem;
    outline: none;
  }
  input:focus, select:focus {
    border-color: #ec4899;
  }
  .buy-btn {
    width: 100%;
    margin-top: 22px;
    padding: 15px;
    border: none;
    border-radius: 12px;
    background: linear-gradient(90deg, #7c3aed, #db2777);
    color: #fff;
    font-size: 1.1rem;
    font-weight: 800;
    cursor: pointer;
    transition: 0.2s;
  }
  .buy-btn:hover {
    opacity: 0.92;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(219, 39, 119, 0.45);
  }
  .priv-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(215px, 1fr));
    gap: 14px;
  }
  .priv-item {
    background: rgba(25, 19, 48, 0.85);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 14px;
    padding: 16px;
    cursor: pointer;
    transition: 0.2s;
    position: relative;
  }
  .priv-item:hover, .priv-item.active {
    border-color: #a855f7;
    background: rgba(139, 92, 246, 0.16);
    transform: translateY(-3px);
  }
  .priv-title {
    font-weight: 800;
    font-size: 1.05rem;
    margin-bottom: 6px;
  }
  .priv-price {
    display: inline-block;
    background: rgba(255, 255, 255, 0.08);
    padding: 4px 10px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.95rem;
    color: #facc15;
    margin-bottom: 8px;
  }
  .priv-desc {
    font-size: 0.83rem;
    color: #cbd5e1;
    line-height: 1.4;
  }
</style>
</head>
<body>

<header>
  <div class="logo"><?= htmlspecialchars($SERVER_NAME) ?></div>
  <p style="color:#94a3b8; margin-top:6px;">Моментальная автоматическая выдача доната после оплаты</p>
  <div class="ip-badge" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($SERVER_IP) ?>'); this.innerText='✅ IP Скопирован: <?= htmlspecialchars($SERVER_IP) ?>'">
    🎮 IP Сервера: <?= htmlspecialchars($SERVER_IP) ?> (Нажми, чтобы скопировать)
  </div>
</header>

<div class="container">
  <!-- ФОРМА ПОКУПКИ ЧЕРЕЗ ЮMONEY -->
  <div class="card-panel">
    <h2>⚡ Покупка привилегии</h2>
    <form method="POST" action="https://yoomoney.ru/quickpay/confirm.xml" id="payForm" onsubmit="return preparePayment()">
      <input type="hidden" name="receiver" value="<?= htmlspecialchars($YOOMONEY_WALLET) ?>">
      <input type="hidden" name="quickpay-form" value="shop">
      <input type="hidden" name="targets" id="ymTargets" value="Покупка привилегии">
      <input type="hidden" name="label" id="ymLabel" value="">
      <input type="hidden" name="sum" id="ymSum" value="29">
      <input type="hidden" name="successURL" value="https://<?= $_SERVER['HTTP_HOST'] ?? 'localhost' ?>">

      <label>Ваш игровой никнейм на сервере:</label>
      <input type="text" id="playerNick" placeholder="Например: KINET1C" required pattern="[a-zA-Z0-9_]{3,16}">

      <label>Выберите привилегию:</label>
      <select id="roleSelect" onchange="updateSelection()">
        <?php foreach ($PRIVILEGES as $code => $info): ?>
          <option value="<?= $code ?>" data-price="<?= $info['price'] ?>" data-name="<?= htmlspecialchars($info['name']) ?>">
            <?= htmlspecialchars($info['name']) ?> — <?= $info['price'] ?> ₽
          </option>
        <?php endforeach; ?>
      </select>

      <label>Способ оплаты:</label>
      <select name="paymentType">
        <option value="AC">💳 Банковская карта (МИР / Visa / MC)</option>
        <option value="PC">🟣 Кошелёк ЮMoney</option>
      </select>

      <button type="submit" class="buy-btn" id="submitBtn">Оплатить 29 ₽</button>
    </form>
    <p style="font-size:0.8rem; color:#64748b; margin-top:14px; text-align:center;">
      Донат выдаётся автоматически в течение 5 секунд после оплаты. Вы можете даже не выходить с сервера!
    </p>
  </div>

  <!-- СЕТКА ПРИВИЛЕГИЙ -->
  <div>
    <h2 style="margin-bottom: 14px;">💎 Доступные привилегии (Нажми для выбора)</h2>
    <div class="priv-grid">
      <?php foreach ($PRIVILEGES as $code => $info): ?>
        <div class="priv-item" id="card-<?= $code ?>" onclick="selectCard('<?= $code ?>')">
          <div class="priv-title" style="color: <?= $info['color'] ?>"><?= htmlspecialchars($info['name']) ?></div>
          <div class="priv-price"><?= $info['price'] ?> ₽</div>
          <div class="priv-desc"><?= htmlspecialchars($info['desc']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script>
function selectCard(code) {
  const select = document.getElementById('roleSelect');
  select.value = code;
  updateSelection();
}

function updateSelection() {
  const select = document.getElementById('roleSelect');
  const code = select.value;
  const opt = select.options[select.selectedIndex];
  const price = opt.getAttribute('data-price');

  document.getElementById('ymSum').value = price;
  document.getElementById('submitBtn').innerText = 'Оплатить ' + price + ' ₽';

  document.querySelectorAll('.priv-item').forEach(el => el.classList.remove('active'));
  const activeCard = document.getElementById('card-' + code);
  if (activeCard) activeCard.classList.add('active');
}

function preparePayment() {
  const nick = document.getElementById('playerNick').value.trim();
  const select = document.getElementById('roleSelect');
  const code = select.value;
  const name = select.options[select.selectedIndex].getAttribute('data-name');

  if (!nick) return false;

  document.getElementById('ymLabel').value = nick + ':' + code;
  document.getElementById('ymTargets').value = 'Привилегия [' + name + '] для игрока ' + nick;
  return true;
}

updateSelection();
</script>

</body>
</html>
