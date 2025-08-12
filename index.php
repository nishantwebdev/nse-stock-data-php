<?php
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/nseindia.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Default symbols as a string (comma or newline separated)
$defaultSymbols = implode("\n", [
    'ADANIENT', 'ADANIPORTS', 'ASIANPAINT', 'AXISBANK', 'BAJAJ-AUTO', 'BAJFINANCE', 'BAJAJFINSV',
    'BPCL', 'BHARTIARTL', 'BRITANNIA', 'CIPLA', 'COALINDIA', 'DIVISLAB', 'DRREDDY', 'EICHERMOT',
    'GRASIM', 'HCLTECH', 'HDFCBANK', 'HDFCLIFE', 'HEROMOTOCO', 'HINDALCO', 'HINDUNILVR',
    'ICICIBANK', 'ITC', 'INDUSINDBK', 'INFY', 'JSWSTEEL', 'KOTAKBANK', 'LT', 'M&M', 'MARUTI',
    'NTPC', 'NESTLEIND', 'ONGC', 'POWERGRID', 'RELIANCE', 'SBILIFE', 'SBIN', 'SUNPHARMA', 'TCS',
    'TATACONSUM', 'TATAMOTORS', 'TATASTEEL', 'TECHM', 'TITAN', 'UPL', 'ULTRACEMCO', 'WIPRO',
    'LTIM', 'HINDPETRO'
]);

$symbolsInput = isset($_POST['symbols']) ? $_POST['symbols'] : $defaultSymbols;
$symbols = array_filter(array_map('trim', preg_split('/[\s,]+/', $symbolsInput)));

$downloadLink = '';
$errors = [];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nse = new NseIndia();
        $stocks = [];
        foreach ($symbols as  $index => $symbol) {
            try {
                $equityData = $nse->getEquityPriceByDate($symbol, new DateTime());
                $derivativeData = $nse->getDerivativeData($symbol);
                if (!$derivativeData) {
                    throw new Exception("No data found for symbol: $symbol");
                }
                $dates = array_keys($derivativeData);
                //convert dates to DateTime objects from 28-Aug-2025 format
                $currentMonth = new DateTime($dates[0]);
                $nextMonth = new DateTime($dates[1]);
                $nextAfterMonth = new DateTime($dates[2]);
                if($index == 0) {
                    $headers = [
                        'NAME',
                        'Equity',
                        $currentMonth->format('M-Y'),
                        $nextMonth->format('M-Y'),
                        'Diff ( ' . $currentMonth->format('M') . ' - Equity)',
                        'Diff ( ' . $nextMonth->format('M') . ' - Equity)',
                        '%-'.$currentMonth->format('M'),
                        '%-'.$nextMonth->format('M'),
                        'DIFF ( %'.$nextMonth->format('M').' - %'.$currentMonth->format('M').')'
                    ];
                }
                $stocks[] = [
                    $symbol,
                    $equityData,
                    $derivativeData[$dates[0]],
                    $derivativeData[$dates[1]]
                ];
            } catch (Exception $e) {
                $errors[] = "Symbol <b>$symbol</b>: " . htmlspecialchars($e->getMessage());
                $stocks[] = [$symbol, 'Error', 'Error', 'Error'];
            }
            sleepMilliseconds(500);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Future Derivatives Data');
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray($stocks, null, 'A2');

        for ($row = 2; $row <= count($symbols) + 1; $row++) {
            $sheet->setCellValue("E$row", "=C$row-B$row");
            $sheet->setCellValue("F$row", "=D$row-B$row");
            $sheet->setCellValue("G$row", "=(E$row*100)/C$row");
            $sheet->setCellValue("H$row", "=(F$row*100)/D$row");
            $sheet->setCellValue("I$row", "=H$row-G$row");
        }
        foreach (range('A', 'I') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $dir = __DIR__ . '/src/assets/spreadsheet';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $filename = 'future_derivatives_data_' . date('Y_m_d_His') . '.xlsx';
        $filepath = $dir . '/' . $filename;
        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);

        $downloadLink = "<a href='src/assets/spreadsheet/$filename' download class='mt-6 inline-block px-6 py-3 bg-green-600 text-white rounded-lg shadow hover:bg-green-700 transition'>⬇️ Download the spreadsheet</a>";
    }
} catch (Throwable $e) {
    $errors[] = "Fatal error: " . htmlspecialchars($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NSE Derivatives Data Export</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="apple-touch-icon" sizes="180x180" href="src/assets/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="src/assets/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="src/assets/favicons/favicon-16x16.png">
    <link rel="manifest" href="src/assets/favicons/site.webmanifest">

</head>
<body class="bg-gradient-to-br from-blue-50 to-blue-200 min-h-screen flex items-center justify-center px-2">
    <div class="bg-white rounded-xl shadow-lg my-6 p-6 sm:p-8 md:p-10 max-w-lg w-full text-center">
        <h1 class="text-2xl sm:text-3xl font-bold mb-6 text-blue-700">NSE Future Derivatives Data Export</h1>
        <form method="post" id="exportForm" class="space-y-6" >
            <label for="symbols" class="block text-left font-semibold text-gray-700 mb-2">Stock Symbols (comma or newline separated)</label>
            <textarea id="symbols" name="symbols" rows="8" class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 p-3 text-base resize-y transition" required><?= htmlspecialchars($symbolsInput) ?></textarea>
            <button id="generateBtn" type="submit" class="w-full px-8 py-4 bg-blue-600 text-white text-lg font-semibold rounded-lg shadow hover:bg-blue-700 transition flex items-center justify-center gap-2">
                <span>📊</span> <span>Generate Spreadsheet</span>
            </button>
        </form>
        <?php if ($downloadLink): ?>
            <div class="mt-8 animate-fade-in">
                <p class="text-green-700 text-base sm:text-lg font-medium mb-2">Spreadsheet created successfully!</p>
                <?= $downloadLink ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="mt-8 p-4 bg-red-100 border border-red-300 text-red-700 rounded-lg text-left animate-fade-in">
                <div class="font-semibold mb-2">Some errors occurred:</div>
                <ul class="list-disc pl-5">
                    <?php foreach ($errors as $err): ?>
                        <li><?= $err ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
    <style>
        @keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }
        .animate-fade-in { animation: fade-in 1s; }
    </style>
    <script>
        document.getElementById('exportForm').addEventListener('submit', function() {
            var btn = document.getElementById('generateBtn');
            btn.disabled = true;
            btn.textContent = 'Processing...';
            btn.classList.add('opacity-60', 'cursor-not-allowed');
        });
    </script>
</body>
</html>
