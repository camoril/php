<?php
declare(strict_types=1);

// Configuration
$pageTitle = "Calculadora de Préstamos - CAMORIL";

// Default values
$loan_amount       = isset($_GET["loan_amount"])       ? (float)$_GET["loan_amount"]       : 150000.00;
$loan_length       = isset($_GET["loan_length"])       ? (float)$_GET["loan_length"]       : 1.0;
$annual_interest   = isset($_GET["annual_interest"])   ? (float)$_GET["annual_interest"]   : 7.0;
$pay_periodicity   = isset($_GET["pay_periodicity"])   ? (int)$_GET["pay_periodicity"]     : 12;
$amortization_type = isset($_GET["amortization_type"]) ? $_GET["amortization_type"]        : 'french';

$periodos = [
    52 => 'Semanal',
    26 => 'Quincenal',
    12 => 'Mensual',
    6  => 'Bimestral',
    4  => 'Trimestral',
    2  => 'Semestral',
    1  => 'Anual'
];

$systems = [
    'french'   => 'Francés (Cuota Constante)',
    'german'   => 'Alemán (Amortización Constante)',
    'american' => 'Americano (Cuota Final)'
];

$error = null;
$results = null;
$amortization = [];

// Validation & Calculation
if (isset($_GET['action'])) {
    if ($loan_amount <= 0) {
        $error = "La cantidad del préstamo debe ser mayor a cero.";
    } elseif ($loan_length <= 0) {
        $error = "La duración del préstamo debe ser mayor a cero.";
    } elseif ($annual_interest <= 0) {
        $error = "El interés anual debe ser mayor a cero.";
    } else {
        // Common Calculation Variables
        $total_periods     = (int)($loan_length * $pay_periodicity);
        $interest_percent  = $annual_interest / 100;
        $period_interest   = $interest_percent / $pay_periodicity;
        
        $current_balance     = $loan_amount;
        $total_paid          = 0.0;
        $total_interest_paid = 0.0;
        
        // Initialize Summary Variables
        $summary_payment = 0.0;
        $summary_label   = "Pago Periódico";

        // Amortization Logic Switch
        switch ($amortization_type) {
            case 'german':
                // German System: Constant Principal Amortization
                // The principal part of the payment is fixed. Interest varies.
                $fixed_principal = $loan_amount / $total_periods;
                $summary_label   = "Primer Pago";

                for ($period = 1; $period <= $total_periods; $period++) {
                    $c_interest  = $current_balance * $period_interest;
                    $c_principal = $fixed_principal;

                    // Adjust last principal to avoid rounding errors
                    if ($period == $total_periods) {
                        $c_principal = $current_balance;
                    }

                    $c_payment = $c_principal + $c_interest;
                    $current_balance -= $c_principal;

                    // Capture first payment for summary
                    if ($period === 1) $summary_payment = $c_payment;

                    $total_paid          += $c_payment;
                    $total_interest_paid += $c_interest;

                    $amortization[] = [
                        'period'    => $period,
                        'payment'   => $c_payment,
                        'interest'  => $c_interest,
                        'principal' => $c_principal,
                        'balance'   => max(0, $current_balance)
                    ];
                }
                break;

            case 'american':
                // American System: Interest Only, Principal at the end
                $c_interest_payment = $loan_amount * $period_interest;
                $summary_label      = "Pago de Interés";
                $summary_payment    = $c_interest_payment;

                for ($period = 1; $period <= $total_periods; $period++) {
                    $c_interest  = $c_interest_payment;
                    $c_principal = 0.0;

                    // Full principal payment at the very end
                    if ($period == $total_periods) {
                        $c_principal = $loan_amount;
                    }

                    $c_payment = $c_principal + $c_interest;
                    $current_balance -= $c_principal;

                    $total_paid          += $c_payment;
                    $total_interest_paid += $c_interest;

                    $amortization[] = [
                        'period'    => $period,
                        'payment'   => $c_payment,
                        'interest'  => $c_interest,
                        'principal' => $c_principal,
                        'balance'   => max(0, $current_balance)
                    ];
                }
                break;

            case 'french':
            default:
                // French System: Constant Total Payment
                // P = (Pv*R) / (1 - (1+R)^(-n))
                if ($period_interest > 0) {
                    $c_period_payment = $loan_amount * ($period_interest / (1 - pow((1 + $period_interest), -($total_periods))));
                } else {
                    $c_period_payment = $loan_amount / $total_periods;
                }
                
                $summary_payment = $c_period_payment;

                for ($period = 1; $period <= $total_periods; $period++) {
                    $c_interest  = $current_balance * $period_interest;
                    $c_principal = $c_period_payment - $c_interest;
                    $current_balance -= $c_principal;
                    
                    // Fix floating point drift for last payment
                    if ($period == $total_periods && abs($current_balance) < 1) {
                        $c_principal += $current_balance;
                        $current_balance = 0;
                    }
                    
                    $c_payment = $c_principal + $c_interest;

                    $total_paid          += $c_payment;
                    $total_interest_paid += $c_interest;

                    $amortization[] = [
                        'period'    => $period,
                        'payment'   => $c_payment,
                        'interest'  => $c_interest,
                        'principal' => $c_principal,
                        'balance'   => max(0, $current_balance)
                    ];
                }
                break;
        }

        $results = [
            'payment_label'   => $summary_label,
            'monthly_payment' => $summary_payment,
            'total_paid'      => $total_paid,
            'total_interest'  => $total_interest_paid,
            'total_principal' => $loan_amount
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            light: '#456ae5',
                            DEFAULT: '#2c4cb5',
                            dark: '#1a3a9a',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen font-sans text-gray-800">

    <div class="container mx-auto px-4 py-8 max-w-6xl">
        
        <!-- Header -->
        <header class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-brand-dark mb-2">Calculadora de Préstamos</h1>
            <p class="text-gray-600">Planifica tus pagos y visualiza tu tabla de amortización</p>
        </header>

        <?php if ($error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm" role="alert">
                <p class="font-bold">Error</p>
                <p><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Form Section -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-6">
                    <h2 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">Parámetros</h2>
                    <form action="" method="GET" class="space-y-4">
                        
                        <div>
                            <label for="loan_amount" class="block text-sm font-medium text-gray-700 mb-1">Cantidad Préstamo ($)</label>
                            <input type="number" step="0.01" name="loan_amount" id="loan_amount" value="<?= htmlspecialchars((string)$loan_amount) ?>" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring focus:ring-brand focus:ring-opacity-50 p-2 border" required>
                        </div>

                        <div>
                            <label for="loan_length" class="block text-sm font-medium text-gray-700 mb-1">Plazo (Años)</label>
                            <input type="number" step="0.1" name="loan_length" id="loan_length" value="<?= htmlspecialchars((string)$loan_length) ?>" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring focus:ring-brand focus:ring-opacity-50 p-2 border" required>
                        </div>

                        <div>
                            <label for="annual_interest" class="block text-sm font-medium text-gray-700 mb-1">Interés Anual (%)</label>
                            <input type="number" step="0.01" name="annual_interest" id="annual_interest" value="<?= htmlspecialchars((string)$annual_interest) ?>" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring focus:ring-brand focus:ring-opacity-50 p-2 border" required>
                        </div>

                        <div>
                            <label for="pay_periodicity" class="block text-sm font-medium text-gray-700 mb-1">Frecuencia de Pago</label>
                            <select name="pay_periodicity" id="pay_periodicity" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring focus:ring-brand focus:ring-opacity-50 p-2 border">
                                <?php foreach ($periodos as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $pay_periodicity == $value ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="amortization_type" class="block text-sm font-medium text-gray-700 mb-1">Sistema de Amortización</label>
                            <select name="amortization_type" id="amortization_type" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring focus:ring-brand focus:ring-opacity-50 p-2 border">
                                <?php foreach ($systems as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $amortization_type == $value ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="pt-4">
                            <button type="submit" name="action" value="calculate" 
                                class="w-full bg-brand hover:bg-brand-dark text-white font-bold py-2 px-4 rounded transition duration-150 ease-in-out shadow">
                                Calcular
                            </button>
                        </div>
                    </form>
                    <div class="mt-6 text-center text-xs text-gray-400">
                        Generado por <a href="#" class="hover:text-brand">CAMORIL</a>
                    </div>
                </div>
            </div>

            <!-- Results Section -->
            <div class="lg:col-span-2">
                <?php if ($results): ?>
                    
                    <!-- Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
                            <div class="text-sm text-gray-500"><?= htmlspecialchars($results['payment_label']) ?></div>
                            <div class="text-2xl font-bold text-gray-800">$<?= number_format($results['monthly_payment'], 2) ?></div>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
                            <div class="text-sm text-gray-500">Total Intereses</div>
                            <div class="text-2xl font-bold text-gray-800">$<?= number_format($results['total_interest'], 2) ?></div>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-purple-500">
                            <div class="text-sm text-gray-500">Total a Pagar</div>
                            <div class="text-2xl font-bold text-gray-800">$<?= number_format($results['total_paid'], 2) ?></div>
                        </div>
                    </div>

                    <!-- Amortization Table -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-medium text-gray-900">Tabla de Amortización</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periodo</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Pago Total</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Interés</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Capital</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($amortization as $row): ?>
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                                <?= $row['period'] ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-bold">
                                                $<?= number_format($row['payment'], 2) ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 text-right">
                                                $<?= number_format($row['interest'], 2) ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 text-right">
                                                $<?= number_format($row['principal'], 2) ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right font-mono">
                                                $<?= number_format($row['balance'], 2) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="bg-white rounded-lg shadow p-12 text-center text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-lg">Ingresa los datos del préstamo y presiona "Calcular" para ver los resultados.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>
