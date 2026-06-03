<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) { header("Location: ../login.php"); exit(); }
include '../includes/db.php';

$id = intval($_GET['id'] ?? 0);
$v  = null;
if ($id) {
    $stmt = $conn->prepare("SELECT * FROM visitors WHERE id = ?");
    $stmt->execute([$id]);
    $v = $stmt->fetch();
}
if (!$v) { die("Visitor not found."); }
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<title>Visitor Badge</title>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Helvetica Neue',Arial,sans-serif; background:#f1f5f9; display:flex; align-items:center; justify-content:center; min-height:100vh; }

    .badge {
        width: 320px;
        background: #ffffff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 40px rgba(0,0,0,0.15);
    }

    .badge-header {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        padding: 20px 24px 16px;
        color: white;
    }
    .badge-header .system-name {
        font-size: 9px; font-weight: 900; letter-spacing: 4px;
        text-transform: uppercase; opacity: 0.7; margin-bottom: 4px;
    }
    .badge-header .visitor-label {
        font-size: 11px; font-weight: 700; opacity: 0.8; letter-spacing: 1px;
    }

    .badge-body { padding: 20px 24px; }

    .visitor-name {
        font-size: 22px; font-weight: 900; color: #0f172a;
        letter-spacing: -0.5px; margin-bottom: 4px;
    }
    .tracking-id {
        font-size: 11px; font-weight: 700; color: #2563eb;
        font-family: monospace; letter-spacing: 2px;
        background: #eff6ff; display: inline-block;
        padding: 3px 8px; border-radius: 6px;
        border: 1px solid #bfdbfe;
    }

    .divider { border: none; border-top: 1px solid #e2e8f0; margin: 16px 0; }

    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px; }
    .info-item label {
        font-size: 8px; font-weight: 700; color: #94a3b8;
        text-transform: uppercase; letter-spacing: 2px; display: block; margin-bottom: 3px;
    }
    .info-item span { font-size: 12px; font-weight: 700; color: #334155; }

    .qr-section { text-align: center; padding: 12px 0 4px; }
    .qr-section #badge-qr { display: flex; justify-content: center; }
    .qr-section #badge-qr img, .qr-section #badge-qr canvas { border-radius: 10px; }

    .badge-footer {
        background: #f8fafc; padding: 12px 24px;
        border-top: 1px solid #e2e8f0; text-align: center;
    }
    .time-row { font-size: 11px; font-weight: 700; color: #475569; }
    .time-row span { color: #2563eb; }

    @media print {
        body { background: white; }
        .badge { box-shadow: none; border: 1px solid #e2e8f0; }
        .no-print { display: none !important; }
    }
</style>
</head>
<body>
<div>
    <div class="badge">
        <div class="badge-header">
            <div class="system-name">Visit Track</div>
            <div class="visitor-label">Visitor Pass</div>
        </div>

        <div class="badge-body">
            <div class="visitor-name"><?php echo htmlspecialchars($v['full_name']); ?></div>
            <div class="tracking-id"><?php echo htmlspecialchars($v['tracking_id']); ?></div>

            <hr class="divider">

            <div class="info-grid">
                <div class="info-item">
                    <label>Host</label>
                    <span><?php echo htmlspecialchars($v['host_name']); ?></span>
                </div>
                <div class="info-item">
                    <label>Purpose</label>
                    <span><?php echo htmlspecialchars($v['purpose']); ?></span>
                </div>
                <?php if (!empty($v['vehicle_details'])): ?>
                <div class="info-item" style="grid-column:span 2">
                    <label>Vehicle</label>
                    <span><?php echo htmlspecialchars($v['vehicle_details']); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="qr-section">
                <div id="badge-qr"></div>
            </div>
        </div>

        <div class="badge-footer">
            <div class="time-row">
                <span><?php echo date('h:i A', strtotime($v['arrival_time'])); ?></span>
                &nbsp;→&nbsp;
                <span><?php echo date('h:i A', strtotime($v['departure_time'])); ?></span>
            </div>
            <div style="font-size:9px;color:#94a3b8;margin-top:4px;letter-spacing:1px;">
                <?php echo date('l, M d Y'); ?>
            </div>
        </div>
    </div>

    <!-- Print button — يختفي عند الطباعة -->
    <div class="no-print" style="text-align:center;margin-top:20px;">
        <button onclick="window.print()"
            style="background:#2563eb;color:white;border:none;padding:12px 32px;border-radius:12px;font-weight:900;font-size:13px;cursor:pointer;letter-spacing:1px;">
            Print Badge
        </button>
        <button onclick="window.close()"
            style="background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;padding:12px 24px;border-radius:12px;font-weight:700;font-size:13px;cursor:pointer;margin-left:8px;">
            Close
        </button>
    </div>
</div>

<script>
    new QRCode(document.getElementById('badge-qr'), {
        text: '<?php echo addslashes($v['tracking_id']); ?>',
        width: 120, height: 120,
        colorDark: '#0f172a', colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H
    });
    // طباعة تلقائية بعد تحميل الـ QR
    window.onload = () => setTimeout(() => window.print(), 500);
</script>
</body>
</html>
