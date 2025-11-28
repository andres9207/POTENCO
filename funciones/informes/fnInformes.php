<?php
include '../../data/db.config.php';
include('../../data/conexion.php');
session_start();
error_reporting(E_ALL);
//$login     = isset($_SESSION['persona']);
// cookie que almacena el numero de identificacion de la persona logueada
$usuario   = $_COOKIE['usuarioPo'];
$idUsuario = $_COOKIE['idusuarioPo'];
$perfil    = $_SESSION["perfilPo"];
$IP = $_SERVER['REMOTE_ADDR'];
//include '../../data/validarpermisos.php';
setlocale(LC_TIME, 'es_CO.UTF-8'); 
date_default_timezone_set('America/Bogota');
$fecha  = date("Y/m/d H:i:s");

include ("../../data/validarpermisos.php");

require_once "../../controladores/general.controller.php";

use  PHPMailer\PHPMailer\PHPMailer;
use  PHPMailer\PHPMailer\Exception;
require ('../../PHPMailer-master/src/PHPMailer.php');
require ('../../PHPMailer-master/src/Exception.php');
//require ('../../PHPMailer-master/src/SMTP.php');
//require_once('../../clases/pdf/html2pdf.class.php');
//pg_free_result($conped);

if (!function_exists('obtenerTotalesHorasExtras')) {
    /**
     * Consulta las sumatorias de horas extras por año.
     */
    function obtenerTotalesHorasExtras(PDO $conn, $year = null): array
    {
        $year = ($year === '' || $year === null) ? null : (int)$year;
        $sql = "
        SELECT 
            SUM(ld.lid_hedo) AS hedo,
            SUM(ld.lid_hdf) AS hdf,
            SUM(ld.lid_hedf) AS hedf,
            SUM(ld.lid_heno) AS heno,
            SUM(ld.lid_henf) AS henf,
            SUM(ld.lid_rn) AS rn,
            SUM(ld.lid_hnf) AS hnf,
            SUM(ld.lid_rd) AS rd,
            SUM(ld.lid_permisos) AS are,
            COUNT(DISTINCT CASE WHEN ld.lid_hedo > 0 THEN u.usu_clave_int END) AS hedo_empleados,
            COUNT(DISTINCT CASE WHEN ld.lid_hdf > 0 THEN u.usu_clave_int END) AS hdf_empleados,
            COUNT(DISTINCT CASE WHEN ld.lid_hedf > 0 THEN u.usu_clave_int END) AS hedf_empleados,
            COUNT(DISTINCT CASE WHEN ld.lid_heno > 0 THEN u.usu_clave_int END) AS heno_empleados,
            COUNT(DISTINCT CASE WHEN ld.lid_henf > 0 THEN u.usu_clave_int END) AS henf_empleados,
            COUNT(DISTINCT CASE WHEN ld.lid_rn > 0 THEN u.usu_clave_int END) AS rn_empleados,
            COUNT(DISTINCT CASE WHEN ld.lid_hnf > 0 THEN u.usu_clave_int END) AS hnf_empleados,
            COUNT(DISTINCT CASE WHEN ld.lid_rd > 0 THEN u.usu_clave_int END) AS rd_empleados,
            COUNT(DISTINCT CASE WHEN ld.lid_permisos > 0 THEN u.usu_clave_int END) AS are_empleados,
            COUNT(DISTINCT u.usu_clave_int) AS empleados_total
        FROM 
          tbl_liquidar_dias ld
          JOIN tbl_liquidar l on ld.liq_clave_int = l.liq_clave_int
          JOIN tbl_usuarios u ON u.usu_clave_int = l.usu_clave_int
        ";

        if ($year !== null) {
            $sql .= " WHERE DATE_FORMAT(ld.lid_fecha, '%Y') = :year";
        }

        $stmt = $conn->prepare($sql);

        if ($year !== null) {
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
}

if (!function_exists('normalizarTotalesHorasExtras')) {
    /**
     * Garantiza que los totales tengan valores numéricos y aplica ajustes requeridos.
     */
    function normalizarTotalesHorasExtras(array $rawTotals): array
    {
        $keys = ['hedo', 'hdf', 'hedf', 'heno', 'henf', 'rn', 'hnf', 'rd', 'are'];
        $totals = [];

        foreach ($keys as $key) {
            $value = isset($rawTotals[$key]) ? (float)$rawTotals[$key] : 0;
            $totals[$key] = (int)round($value);
        }

        if ($totals['are'] > 0) {
            $totals['hedo'] -= $totals['are'];
        }

        $employeeCountMap = [
            'hedo_empleados' => 'hedo',
            'hdf_empleados'  => 'hdf',
            'hedf_empleados' => 'hedf',
            'heno_empleados' => 'heno',
            'henf_empleados' => 'henf',
            'rn_empleados'   => 'rn',
            'hnf_empleados'  => 'hnf',
            'rd_empleados'   => 'rd',
            'are_empleados'  => 'are'
        ];
        $empleadosPorTipo = [];
        foreach ($employeeCountMap as $sourceKey => $targetKey) {
            $empleadosPorTipo[$targetKey] = isset($rawTotals[$sourceKey]) ? (int)$rawTotals[$sourceKey] : 0;
        }

        $totals['empleadosPorTipo'] = $empleadosPorTipo;
        $totals['employeeCount'] = isset($rawTotals['empleados_total']) ? (int)$rawTotals['empleados_total'] : null;

        return $totals;
    }
}

if (!function_exists('obtenerTotalesMensualesHorasExtras')) {
    /**
     * Construye un dataset mensual agrupado por mes y año para alimentar Highcharts.
     */
    function obtenerTotalesMensualesHorasExtras(PDO $conn, $year = null): array
    {
        $year = ($year === '' || $year === null) ? null : (int)$year;

        $mesesEs = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        $colorPalette = ['#0d6efd','#6610f2','#6f42c1','#d63384','#dc3545','#fd7e14','#ffc107','#198754','#20c997','#0dcaf0','#17a2b8','#6c757d'];
        $colorIndex = 0;

        $years = [];
        if ($year === null) {
            $sqlYears = "
                SELECT DISTINCT DATE_FORMAT(ld.lid_fecha, '%Y') AS anio
                FROM 
                tbl_liquidar_dias ld
                
                ORDER BY anio
            ";
            $stmtYears = $conn->query($sqlYears);
            if ($stmtYears) {
                while ($row = $stmtYears->fetch(PDO::FETCH_ASSOC)) {
                    $years[] = (int)$row['anio'];
                }
            }
            if (empty($years)) {
                $years[] = (int)date('Y');
            }
        } else {
            $years[] = $year;
        }

        $categories = [];
        for ($month = 1; $month <= 12; $month++) {
            $categories[] = $mesesEs[$month - 1];
        }

        $series = [];
        $annualTotals = [];
        $annualEmployeeCounts = [];
        foreach ($years as $currentYear) {
            $seriesColor = $colorPalette[$colorIndex % count($colorPalette)];
            $colorIndex++;
            $annualTotals[$currentYear] = 0;
            $annualEmployeeCounts[$currentYear] = 0;

            $sqlYearEmployees = "
                SELECT COUNT(DISTINCT u.usu_clave_int) AS empleados
                FROM 
                  tbl_liquidar_dias ld
                  JOIN tbl_liquidar l ON ld.liq_clave_int = l.liq_clave_int
                  JOIN tbl_usuarios u ON u.usu_clave_int = l.usu_clave_int
                WHERE DATE_FORMAT(ld.lid_fecha, '%Y') = :anio
            ";
            $stmtYearEmp = $conn->prepare($sqlYearEmployees);
            $stmtYearEmp->bindParam(':anio', $currentYear, PDO::PARAM_INT);
            $stmtYearEmp->execute();
            $annualEmployeeCounts[$currentYear] = (int)($stmtYearEmp->fetchColumn() ?: 0);
            $seriesData = [];
            for ($month = 1; $month <= 12; $month++) {
                $formattedMonth = sprintf('%02d', $month);
                $mese = $currentYear . '-' . $formattedMonth;
                $sql = "
                    SELECT 
                        SUM(
                            ld.lid_hedo + ld.lid_hdf + ld.lid_hedf + 
                            ld.lid_heno + ld.lid_henf + ld.lid_rn + 
                            ld.lid_hnf + ld.lid_rd
                        ) AS tot,
                        COUNT(DISTINCT u.usu_clave_int) AS empleados
                    FROM 
                      tbl_liquidar_dias ld
                     JOIN tbl_liquidar l on ld.liq_clave_int = l.liq_clave_int
                     JOIN tbl_usuarios u ON u.usu_clave_int = l.usu_clave_int
                    WHERE DATE_FORMAT(ld.lid_fecha, '%Y-%m') = :mese
                ";

                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':mese', $mese, PDO::PARAM_STR);
                $stmt->execute();

                $dattot = $stmt->fetch(PDO::FETCH_ASSOC);
                $total = isset($dattot['tot']) ? (int)round($dattot['tot'], 0) : 0;
                $employeeCount = isset($dattot['empleados']) ? (int)$dattot['empleados'] : 0;
                $annualTotals[$currentYear] += $total;

                $seriesData[] = [
                    'y' => $total,
                    'custom' => [
                        'value' => number_format($total, 0, '', ','),
                        'mes'   => $mese,
                        'empleados' => $employeeCount
                    ]
                ];
            }

            $series[] = [
                'name' => (string)$currentYear,
                'color' => $seriesColor,
                'custom' => ['gender' => 'Mes'],
                'data' => $seriesData
            ];
        }

        return [
            'categories' => $categories,
            'series'     => $series,
            'annualTotals' => $annualTotals,
            'annualEmployeeCounts' => $annualEmployeeCounts
        ];
    }
}

if (!function_exists('obtenerTotalesHorasExtrasPorPeriodo')) {
    /**
     * Retorna los totales de horas extras para un periodo YYYY-MM específico.
     */
    function obtenerTotalesHorasExtrasPorPeriodo(PDO $conn, string $periodo): array
    {
        $periodo = trim($periodo);
        if ($periodo === '' || !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periodo)) {
            return [];
        }

        $sql = "
        SELECT 
            SUM(ld.lid_hedo) AS hedo,
            SUM(ld.lid_hdf) AS hdf,
            SUM(ld.lid_hedf) AS hedf,
            SUM(ld.lid_heno) AS heno,
            SUM(ld.lid_henf) AS henf,
            SUM(ld.lid_rn) AS rn,
            SUM(ld.lid_hnf) AS hnf,
            SUM(ld.lid_rd) AS rd,
            SUM(ld.lid_permisos) AS are,
            COUNT(DISTINCT CASE WHEN ld.lid_hedo > 0 THEN u.usu_clave_int END) AS hedo_empleados,
            COUNT(DISTINCT CASE WHEN ld.lid_hdf > 0 THEN u.usu_clave_int END) AS hdf_empleados,
            COUNT(DISTINCT CASE WHEN ld.lid_hedf > 0 THEN u.usu_clave_int END) AS hedf_empleados,
            COUNT(DISTINCT CASE WHEN ld.lid_heno > 0 THEN u.usu_clave_int END) AS heno_empleados,
            COUNT(DISTINCT CASE WHEN ld.lid_henf > 0 THEN u.usu_clave_int END) AS henf_empleados,
            COUNT(DISTINCT CASE WHEN ld.lid_rn > 0 THEN u.usu_clave_int END) AS rn_empleados,
            COUNT(DISTINCT CASE WHEN ld.lid_hnf > 0 THEN u.usu_clave_int END) AS hnf_empleados,
            COUNT(DISTINCT CASE WHEN ld.lid_rd > 0 THEN u.usu_clave_int END) AS rd_empleados,
            COUNT(DISTINCT CASE WHEN ld.lid_permisos > 0 THEN u.usu_clave_int END) AS are_empleados,
            COUNT(DISTINCT u.usu_clave_int) AS empleados_total
        FROM 
          tbl_liquidar_dias ld
          JOIN  tbl_liquidar l on ld.liq_clave_int = l.liq_clave_int
          JOIN tbl_usuarios u ON u.usu_clave_int = l.usu_clave_int
        WHERE DATE_FORMAT(ld.lid_fecha, '%Y-%m') = :periodo";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':periodo', $periodo, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
}

if (!function_exists('obtenerDetalleEmpleadosPorTipoHora')) {
    /**
     * Retorna el detalle por empleado para un tipo de hora específico.
     */
    function obtenerDetalleEmpleadosPorTipoHora(PDO $conn, string $tipo, ?int $year = null, ?string $periodo = null): array
    {
        $columnMap = [
            'HEDO' => 'ld.lid_hedo',
            'HDF'  => 'ld.lid_hdf',
            'HEDF' => 'ld.lid_hedf',
            'HENO' => 'ld.lid_heno',
            'HENF' => 'ld.lid_henf',
            'RN'   => 'ld.lid_rn',
            'HNF'  => 'ld.lid_hnf',
            'RD'   => 'ld.lid_rd',
            'ARE'  => 'ld.lid_permisos'
        ];

        $tipo = strtoupper(trim($tipo));
        if (!isset($columnMap[$tipo])) {
            return [
                'tipo' => $tipo,
                'total' => 0,
                'empleados' => []
            ];
        }

        $column = $columnMap[$tipo];
        $baseFrom = "
            FROM 
             tbl_liquidar_dias ld
          JOIN tbl_liquidar l on ld.liq_clave_int = l.liq_clave_int
          JOIN tbl_usuarios u ON u.usu_clave_int = l.usu_clave_int";

        $whereParts = [];
        $params = [];

        if ($periodo && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periodo)) {
            $whereParts[] = "DATE_FORMAT(ld.lid_fecha, '%Y-%m') = :periodo";
            $params[':periodo'] = $periodo;
        } elseif ($year !== null) {
            $whereParts[] = "DATE_FORMAT(ld.lid_fecha, '%Y') = :year";
            $params[':year'] = $year;
        }

        $whereSql = '';
        if (!empty($whereParts)) {
            $whereSql = ' AND ' . implode(' AND ', $whereParts);
        }

        $sql = "
            SELECT 
                CONCAT(u.usu_apellido, ' ', u.usu_nombre) AS empleado,
                u.usu_documento AS documento,
                SUM($column) AS total
            $baseFrom
            WHERE 1 = 1 $whereSql
            GROUP BY empleado, documento
            HAVING SUM($column) > 0
            ORDER BY total DESC, empleado ASC";

        $countSql = "
            SELECT 
                COUNT(DISTINCT CASE WHEN $column > 0 THEN u.usu_clave_int END) AS empleados
            $baseFrom
            WHERE 1 = 1 $whereSql";

        $stmt = $conn->prepare($sql);
        foreach ($params as $key => $value) {
            $paramType = ($key === ':year') ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $paramType);
        }
        $stmt->execute();

        $stmtCount = $conn->prepare($countSql);
        foreach ($params as $key => $value) {
            $paramType = ($key === ':year') ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmtCount->bindValue($key, $value, $paramType);
        }
        $stmtCount->execute();
        $employeeCount = (int)($stmtCount->fetchColumn() ?: 0);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $total = 0;
        $detalle = [];

        foreach ($rows as $row) {
            $valor = isset($row['total']) ? (float)$row['total'] : 0;
            if ($valor <= 0) {
                continue;
            }
            $total += $valor;
            $detalle[] = [
                'empleado'  => trim($row['empleado'] ?? ''),
                'documento'=> trim($row['documento'] ?? ''),
                'total'     => (int)round($valor)
            ];
        }

        return [
            'tipo'      => $tipo,
            'total'     => (int)round($total),
            'empleados' => $detalle,
            'employeeCount' => $employeeCount
        ];
    }
}

if (!function_exists('prepararDatasetPieHorasExtras')) {
    /**
     * Construye el dataset que alimenta el gráfico principal de horas extras.
     */
    function prepararDatasetPieHorasExtras(array $totals): array
    {
        $empleadosPorTipo = $totals['empleadosPorTipo'] ?? [];
        return [
            [
                'name'    => 'HEDO',
                'y'       => $totals['hedo'] ?? 0,
                'opcion'  => 'HEDO',
                'sliced'  => true,
                'selected'=> true,
                'empleados' => $empleadosPorTipo['hedo'] ?? 0
            ],
            ['name' => 'HDF',  'y' => $totals['hdf'] ?? 0,  'opcion' => 'HDF',  'empleados' => $empleadosPorTipo['hdf'] ?? 0],
            ['name' => 'HEDF', 'y' => $totals['hedf'] ?? 0, 'opcion' => 'HEDF', 'empleados' => $empleadosPorTipo['hedf'] ?? 0],
            ['name' => 'HENO', 'y' => $totals['heno'] ?? 0, 'opcion' => 'HENO', 'empleados' => $empleadosPorTipo['heno'] ?? 0],
            ['name' => 'HENF', 'y' => $totals['henf'] ?? 0, 'opcion' => 'HENF', 'empleados' => $empleadosPorTipo['henf'] ?? 0],
            ['name' => 'RN',   'y' => $totals['rn'] ?? 0,   'opcion' => 'RN',   'empleados' => $empleadosPorTipo['rn'] ?? 0],
            ['name' => 'HNF',  'y' => $totals['hnf'] ?? 0,  'opcion' => 'HNF',  'empleados' => $empleadosPorTipo['hnf'] ?? 0],
            ['name' => 'ARE',  'y' => $totals['are'] ?? 0,  'opcion' => 'ARE',  'empleados' => $empleadosPorTipo['are'] ?? 0]
        ];
    }
}

if (!function_exists('renderSeccionGraficosHorasExtras')) {
    /**
     * Renderiza la sección HTML + JS de los gráficos empleando los datos preparados.
     */
    function renderSeccionGraficosHorasExtras($year, array $totals, array $monthlyData): void
    {
        $titleYear = ($year === null || $year === '') ? 'Todos los Años' : $year;
        $pieData = prepararDatasetPieHorasExtras($totals);

        $totalsJson   = json_encode($totals, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $pieDataJson  = json_encode($pieData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $monthsJson   = json_encode($monthlyData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        ?>
        <div class="col-md-6">
            <!-- LINE CHART -->
            <div class="card card-info">
                <div class="card-header bg-blue hide">
                    <h3 class="card-title">Horas Extras</h3>

                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                        </button>

                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-end align-items-center mb-2">
                        <label for="graficoHorasExtrasTipo" class="mb-0 mr-2">Tipo de grafico:</label>
                        <select id="graficoHorasExtrasTipo" class="form-control form-control-sm w-auto">
                            <option value="pie">Torta</option>
                            <option value="column">Barras</option>
                        </select>
                    </div>
                    <div class="chart">
                        <div id="divordenesestado"></div>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
        <div class="col-md-6">
            <!-- LINE CHART -->
            <div class="card card-info">
                <div class="card-header bg-blue hide">
                     <h3 class="card-title hide">Total Anual</h3>

                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                        </button>

                    </div>
                </div>
                <div class="card-body">
                    <div class="chart">
                            <div id="divhorasanuales"></div>                       
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
        <div class="col-md-12 mt-3">
            <div class="card card-info">
                <div class="card-header bg-blue hide">
                     <h3 class="card-title hide">Tiempo Extra <?php echo $titleYear; ?></h3>
                  
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <div id="divhoraspormes"></div>
                    </div>
                </div>
            </div>
        </div>
        <div id="detalleHorasExtrasOverlay" class="grafico-detalle-overlay">
            <div class="grafico-detalle-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0" id="detalleHorasExtrasTitulo">Detalle</h5>
                    <button type="button" class="btn btn-sm btn-secondary" id="detalleHorasExtrasCerrar">Cerrar</button>
                </div>
                <div id="detalleHorasExtrasContenido"></div>
            </div>
        </div>
        <style>
        .grafico-detalle-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 2050;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .grafico-detalle-overlay.show {
            display: flex;
        }
        .grafico-detalle-card {
            background: #fff;
            width: 100%;
            max-width: 900px;
            max-height: 90vh;
            overflow-y: auto;
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.2);
        }
        </style>
        <script>
        (function () {
        const horasExtras = <?php echo $totalsJson; ?>;
        const pieSeries = <?php echo $pieDataJson; ?>;
        const horasPorMes = <?php echo $monthsJson; ?>;
        const monthCategories = horasPorMes.categories || [];
        const monthSeries = horasPorMes.series || [];
        const annualTotals = horasPorMes.annualTotals || {};
        const annualEmployeeCounts = horasPorMes.annualEmployeeCounts || {};
        const yearFilter = <?php echo ($year === null ? 'null' : (int)$year); ?>;
        const informesEndpoint = 'funciones/informes/fnInformes.php';
        const graficoHorasExtrasTipo = document.getElementById('graficoHorasExtrasTipo');
        const graficoHorasExtrasColors = ['#fd7e14', '#ffc107', '#28a745', '#17a2b8', '#dc3545', '#6f42c1'];
        const overlay = document.getElementById('detalleHorasExtrasOverlay');
        const overlayTitle = document.getElementById('detalleHorasExtrasTitulo');
        const overlayContent = document.getElementById('detalleHorasExtrasContenido');
        const overlayClose = document.getElementById('detalleHorasExtrasCerrar');
        const horaLabels = {
            HEDO: 'Hora extra diurna ordinaria',
            HDF: 'Hora extra diurna festiva',
            HEDF: 'Hora extra dominical festiva',
            HENO: 'Hora extra nocturna ordinaria',
            HENF: 'Hora extra nocturna festiva',
            RN: 'Recargo nocturno',
            HNF: 'Recargo nocturno festivo',
            ARE: 'Permisos / ARE'
        };
        const horaFieldMap = {
            HEDO: 'hedo',
            HDF: 'hdf',
            HEDF: 'hedf',
            HENO: 'heno',
            HENF: 'henf',
            RN: 'rn',
            HNF: 'hnf',
            ARE: 'are'
        };

        const sanitizeNumber = (value) => {
            const parsed = Number(value);
            return Number.isFinite(parsed) ? parsed : 0;
        };

        const formatNumber = (value) => {
            return new Intl.NumberFormat('es-CO').format(sanitizeNumber(value));
        };

        const escapeHtml = (value = '') => {
            return String(value).replace(/[&<>"']/g, (char) => {
                const entities = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
                return entities[char] || char;
            });
        };

        const setOverlayContent = (title, html) => {
            if (!overlay) {
                return;
            }
            overlayTitle.textContent = title || 'Detalle';
            overlayContent.innerHTML = html;
            overlay.classList.add('show');
        };

        const showLoadingOverlay = (title) => {
            setOverlayContent(title || 'Consultando...', '<div class="text-center py-4"><span class="spinner-border spinner-border-sm mr-2"></span>Consultando...</div>');
        };

        const hideOverlay = () => {
            if (overlay) {
                overlay.classList.remove('show');
            }
        };

        if (overlayClose) {
            overlayClose.addEventListener('click', hideOverlay);
        }
        if (overlay) {
            overlay.addEventListener('click', function (event) {
                if (event.target === overlay) {
                    hideOverlay();
                }
            });
        }

        const postInformes = async (payload) => {
            if (!window.fetch) {
                throw new Error('El navegador no soporta fetch.');
            }
            const response = await fetch(informesEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: new URLSearchParams(payload)
            });
            const text = await response.text();
            try {
                return JSON.parse(text);
            } catch (error) {
                throw new Error('No se pudo interpretar la respuesta del servidor.');
            }
        };

        const buildTotalesHtml = (totals = {}, options = {}) => {
            const normalizeCount = (value) => {
                if (value === undefined || value === null) {
                    return null;
                }
                return sanitizeNumber(value);
            };
            const employeeCountFromTotals = normalizeCount(totals.employeeCount);
            const metaCount = normalizeCount(options.employeeCount);
            const employeeCount = metaCount !== null ? metaCount : employeeCountFromTotals;
            const employeeBreakdown = totals.empleadosPorTipo || {};
            const header = employeeCount !== null
                ? `<p class="mb-2"><strong>Empleados con registros:</strong> ${formatNumber(employeeCount)}</p>`
                : '';
            const rows = Object.keys(horaLabels).map((key) => {
                const field = horaFieldMap[key] || key.toLowerCase();
                const valor = formatNumber(totals[field] || 0);
                const empleadosValor = formatNumber(employeeBreakdown[field] || 0);
                return `<tr><td class="font-weight-bold align-middle">${key}</td><td>${horaLabels[key]}</td><td class="text-right">${valor}</td><td class="text-right">${empleadosValor}</td></tr>`;
            }).join('');
            return `${header}<div class="table-responsive"><table class="table table-sm table-bordered mb-0"><thead><tr><th>Código</th><th>Concepto</th><th class="text-right">Total</th><th class="text-right">Empleados</th></tr></thead><tbody>${rows}</tbody></table></div>`;
        };

        const buildDetalleTipoHtml = (data) => {
            const totalHoras = formatNumber(data.total || 0);
            const employeeCount = (data.employeeCount === undefined || data.employeeCount === null)
                ? (data.empleados || []).length
                : sanitizeNumber(data.employeeCount);
            const registros = data.empleados || [];
            if (!registros.length) {
                return `<p class="mb-0">No se encontraron empleados con registros para este concepto.</p>`;
            }
            const filas = registros.map((row, idx) => {
                const nombre = escapeHtml(row.empleado || '');
                const doc = escapeHtml(row.documento || '');
                const valor = formatNumber(row.total || 0);
                return `<tr><td>${idx + 1}</td><td>${nombre}</td><td>${doc}</td><td class="text-right">${valor}</td></tr>`;
            }).join('');

            return `
                <p class="mb-1"><strong>Total ${escapeHtml(data.tipo || '')}:</strong> ${totalHoras} horas</p>
                <p class="mb-2"><strong>Empleados con registros:</strong> ${formatNumber(employeeCount)}</p>
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Empleado</th>
                                <th>Documento</th>
                                <th class="text-right">Horas</th>
                            </tr>
                        </thead>
                        <tbody>${filas}</tbody>
                    </table>
                </div>`;
        };

        const mostrarDetallePeriodo = async (periodo, etiquetaFallback) => {
            if (!periodo) {
                return;
            }
            showLoadingOverlay('Detalle mensual');
            try {
                const data = await postInformes({ opcion: 'DETALLE_GRAFICO_PERIODO', periodo });
                const titulo = data.title || etiquetaFallback || 'Detalle mensual';
                const contenido = buildTotalesHtml(data.totals || {}, { employeeCount: data.employeeCount });
                setOverlayContent(titulo, contenido);
            } catch (error) {
                setOverlayContent('Error', `<div class="alert alert-danger mb-0">${escapeHtml(error.message)}</div>`);
            }
        };

        const mostrarDetalleAnio = async (anio) => {
            const payload = { opcion: 'DETALLE_GRAFICO_ANIO' };
            if (anio !== undefined && anio !== null && anio !== '') {
                payload.anio = anio;
            }
            showLoadingOverlay('Detalle anual');
            try {
                const data = await postInformes(payload);
                const titulo = data.title || 'Detalle anual';
                const contenido = buildTotalesHtml(data.totals || {}, { employeeCount: data.employeeCount });
                setOverlayContent(titulo, contenido);
            } catch (error) {
                setOverlayContent('Error', `<div class="alert alert-danger mb-0">${escapeHtml(error.message)}</div>`);
            }
        };

        const mostrarDetalleTipoHora = async (tipo, context = {}) => {
            if (!tipo) {
                return;
            }
            const payload = { opcion: 'DETALLE_GRAFICO_TIPO', tipo };
            if (context.periodo) {
                payload.periodo = context.periodo;
            } else if (context.anio !== undefined && context.anio !== null && context.anio !== '') {
                payload.anio = context.anio;
            } else if (yearFilter !== null) {
                payload.anio = yearFilter;
            }
            const tituloBase = `Detalle ${tipo}`;
            showLoadingOverlay(tituloBase);
            try {
                const data = await postInformes(payload);
                const contenido = buildDetalleTipoHtml(data || {});
                setOverlayContent(tituloBase, contenido);
            } catch (error) {
                setOverlayContent('Error', `<div class="alert alert-danger mb-0">${escapeHtml(error.message)}</div>`);
            }
        };

        Object.keys(horasExtras).forEach((key) => {
            const value = horasExtras[key];
            if (typeof value === 'number' || typeof value === 'string') {
                horasExtras[key] = sanitizeNumber(value);
            }
        });

        pieSeries.forEach((point) => {
            point.y = sanitizeNumber(point.y);
            point.empleados = (point.empleados === undefined || point.empleados === null) ? null : sanitizeNumber(point.empleados);
        });

        monthSeries.forEach((serie) => {
            serie.data = serie.data.map((point) => {
                const custom = { ...(point.custom || {}) };
                custom.empleados = (custom.empleados === undefined || custom.empleados === null)
                    ? null
                    : sanitizeNumber(custom.empleados);
                return {
                    ...point,
                    y: sanitizeNumber(point.y),
                    custom
                };
            });
        });

        const annualCategories = Object.keys(annualTotals).sort((a, b) => Number(a) - Number(b));
        const annualSeriesData = annualCategories.map((yearKey) => {
            const empleadosRaw = annualEmployeeCounts[yearKey];
            const empleados = (empleadosRaw === undefined || empleadosRaw === null) ? null : sanitizeNumber(empleadosRaw);
            return {
                name: yearKey,
                y: sanitizeNumber(annualTotals[yearKey]),
                custom: { empleados }
            };
        });

        Highcharts.setOptions({
            lang:{
                downloadCSV: 'Descargar  CSV',
                downloadJPEG: 'Download Imagen JPG',
                downloadPDF: 'Descargar PDF',
                downloadPNG: 'Descargar Imagen PNG',
                downloadSVG: 'Descargar SVG',
                downloadXLS:'Descargar XLS',
                exitFullscreen:"Salir de pantalla completa",
                loading:'Cargando...',
                noData:'No hay datos',
                printChart:'Imprimir grafico',
                resetZoom:'Reiniciar zoom',
                viewFullscreen:'Ver en pantalla completa'
            }
        });

        const renderGraficoHorasExtras = (chartType = 'pie') => {
            const isPie = chartType === 'pie';
            const dataset = pieSeries.map((point) => ({ ...point }));
            const basePointEvents = {
                click: function () {
                    const tipo = this.options?.opcion || this.options?.name || this.name;
                    mostrarDetalleTipoHora(tipo, { anio: yearFilter });
                }
            };

            const options = {
                chart: {
                    type: isPie ? 'pie' : 'column',
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false
                },
                title: {
                    text: 'Horas Extras  - <?php echo $titleYear; ?>'
                },
                colors: graficoHorasExtrasColors
            };

            if (isPie) {
                options.tooltip = {
                    formatter: function () {
                        const empleados = this.point?.options?.empleados ?? this.point?.empleados;
                        const empleadosTxt = Number.isFinite(empleados)
                            ? `<br/>Empleados: <b>${formatNumber(empleados)}</b>`
                            : '';
                        const porcentaje = typeof this.point.percentage === 'number'
                            ? this.point.percentage.toFixed(1)
                            : '0.0';
                        return `${this.series.name}: <b>${porcentaje}%</b><br/>Total: <b>${formatNumber(this.point.y)}</b>${empleadosTxt}`;
                    }
                };
                options.accessibility = {
                    point: {
                        valueSuffix: '%'
                    }
                };
                options.plotOptions = {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                        },
                        colors: graficoHorasExtrasColors,
                        point: {
                            events: basePointEvents
                        }
                    }
                };
                options.series = [{
                    name: 'Porcentaje',
                    colorByPoint: true,
                    data: dataset
                }];
            } else {
                options.xAxis = {
                    categories: dataset.map((point) => point.name),
                    labels: {
                        autoRotation: 0
                    },
                    type: 'category'
                };
                options.yAxis = {
                    title: {
                        text: 'Total Horas'
                    }
                };
                options.tooltip = {
                    formatter: function () {
                        const empleados = this.point?.options?.empleados ?? this.point?.empleados;
                        const empleadosTxt = Number.isFinite(empleados)
                            ? `<br/>Empleados: <b>${formatNumber(empleados)}</b>`
                            : '';
                        return `Total: <b>${formatNumber(this.point.y)}</b>${empleadosTxt}`;
                    }
                };
                options.plotOptions = {
                    column: {
                        borderWidth: 0,
                        colorByPoint: true,
                        cursor: 'pointer',
                        pointPadding: 0.1,
                        point: {
                            events: basePointEvents
                        }
                    }
                };
                options.series = [{
                    name: 'Total',
                    data: dataset.map((point) => ({
                        name: point.name,
                        y: point.y,
                        opcion: point.opcion,
                        empleados: point.empleados
                    }))
                }];
            }

            Highcharts.chart('divordenesestado', options);
        };

        renderGraficoHorasExtras(graficoHorasExtrasTipo ? graficoHorasExtrasTipo.value : 'pie');
        if (graficoHorasExtrasTipo) {
            graficoHorasExtrasTipo.addEventListener('change', function (event) {
                renderGraficoHorasExtras(event.target.value);
            });
        }

        Highcharts.chart('divhoraspormes', {
            title: {
                text: 'Tiempo Extra Mes - <?php echo $titleYear; ?>'
            },
            chart: {
                type: 'column'
            },
            colors: ['#28E', '#4A0'],
            xAxis: [{
                categories: monthCategories,
                labels: {
                    autoRotation: 0
                },
                type: 'category'
            }],
            accessibility: {
                point: {
                    descriptionFormatter: function (point) {
                        return (
                            point.options.custom.value + ' en el ' + point.series.options.custom.gender + ' ' + point.name + '.'
                        );
                    }
                }
            },
            tooltip: {
                shared: true,
                formatter: function () {
                    const header = `<strong>${this.x || ''}</strong><br />`;
                    const points = (this.points || []).map((point) => {
                        const customValue = point.point?.custom?.value || point.y;
                        const empleadosRaw = point.point?.custom?.empleados;
                        const empleados = (empleadosRaw === undefined || empleadosRaw === null)
                            ? null
                            : sanitizeNumber(empleadosRaw);
                        const empleadosTxt = empleados !== null ? ` (Empleados: ${formatNumber(empleados)})` : '';
                        return `${point.series.name}: ${customValue}${empleadosTxt}`;
                    });
                    return header + points.join('<br />');
                }
            },
            plotOptions: {
                series: {
                    borderWidth: 0,
                    pointPadding: 0.1,
                    groupPadding: 0.1,
                    cursor: 'pointer',
                    point: {
                        events: {
                            click: function () {
                                const periodo = this.options?.custom?.mes;
                                const etiqueta = `${this.category} - ${this.series?.name || ''}`;
                                mostrarDetallePeriodo(periodo, etiqueta);
                            }
                        }
                    }
                }
            },
            series: monthSeries
        });

        Highcharts.chart('divhorasanuales', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'Total Horas Extra por Año'
            },
            xAxis: {
                categories: annualCategories
            },
            yAxis: {
                title: {
                    text: 'Total Horas'
                }
            },
            tooltip: {
                formatter: function () {
                    const empleados = this.point?.custom?.empleados;
                    const empleadosTxt = (empleados === null || empleados === undefined)
                        ? ''
                        : `<br/>Empleados: <b>${formatNumber(empleados)}</b>`;
                    return `<strong>${this.x}</strong><br/>Total: <b>${formatNumber(this.y)}</b>${empleadosTxt}`;
                }
            },
            plotOptions: {
                column: {
                    colorByPoint: true,
                    cursor: 'pointer',
                    point: {
                        events: {
                            click: function () {
                                const anio = this.category;
                                mostrarDetalleAnio(anio);
                            }
                        }
                    }
                }
            },
            series: [{
                name: 'Año',
                data: annualSeriesData
            }]
        });
        })();
        </script>
        <div id="divordenestado" class="col-md-12"></div>
        <?php
    }
}

$p121 = isset($permisosUsuario[121]) ?? 0;
$p122 = isset($permisosUsuario[122]) ?? 0;
$p38  = isset($permisosUsuario[38]) ?? 0;

$opcion = $_POST['opcion'];

 if ($opcion == "CONTADORHOME") {
    $poraprobar = 0;
    $aprobadas = 0;
    $liquidadas = 0;
    $cancelada = 0;
    $rechazada = 0;
    $poraprobarliq = 0;
    $porfacturar = 0;
    $facturadas = 0;
    $pendienteobra = 0;
    $poraprobarobra = 0;
    // Filtro dinámico por tipo
    $wh = "";
    if ($p121 > 0 || $p122 > 0) {
        $wh .= " AND (";
        if ($p121 > 0) $wh .= "liq_tipo = 1";
        if ($p121 > 0 && $p122 > 0) $wh .= " OR ";
        if ($p122 > 0) $wh .= "liq_tipo = 2";
        $wh .= ")";
    } else {
        $wh .= " AND liq_tipo NOT IN (1, 2)";
    }

    // Contadores principales de jornada
    $sqlJornada = "
        SELECT 
            (SELECT COUNT(jor_clave_int) FROM tbl_jornada WHERE jor_estado = 0) AS PORAPROBAR,
            (SELECT COUNT(jor_clave_int) FROM tbl_jornada WHERE jor_estado = 1) AS APROBADAS,
            (SELECT COUNT(jor_clave_int) FROM tbl_jornada WHERE jor_estado = 2) AS LIQUIDADAS
    ";
    $stmtJor = $conn->query($sqlJornada);
    if ($stmtJor) {
        $dat = $stmtJor->fetch(PDO::FETCH_ASSOC);
        $poraprobar = $dat['PORAPROBAR'];
        $aprobadas  = $dat['APROBADAS'];
        $liquidadas = $dat['LIQUIDADAS'];
    }

    // Contadores de liquidaciones
    $sqlLiq = "
        SELECT
            (SELECT COUNT(liq_clave_int) FROM tbl_liquidar WHERE liq_estado = 1 $wh) AS PORAPROBARLIQUIDACION,
            (SELECT COUNT(liq_clave_int) FROM tbl_liquidar WHERE liq_estado = 2 $wh) AS PORFACTURAR,
            (SELECT COUNT(liq_clave_int) FROM tbl_liquidar WHERE liq_estado = 3 $wh) AS FACTURADAS,
            (SELECT COUNT(liq_clave_int) FROM tbl_liquidar WHERE liq_estado = 4 $wh) AS RECHAZADAS
    ";
    $stmtLiq = $conn->query($sqlLiq);
    if ($stmtLiq) {
        $dat = $stmtLiq->fetch(PDO::FETCH_ASSOC);
        $poraprobarliq = $dat['PORAPROBARLIQUIDACION'];
        $porfacturar   = $dat['PORFACTURAR'];
        $facturadas    = $dat['FACTURADAS'];
        $rechazada     = $dat['RECHAZADAS'];
    }

    // Liquidaciones pendientes por obra
    $sqlPenObra = "
        SELECT COUNT(DISTINCT l.liq_clave_int) AS can
        FROM tbl_liquidar l
        JOIN tbl_liquidar_dias_obra ld ON ld.liq_clave_int = l.liq_clave_int
        WHERE l.liq_tipo = 2 AND l.lio_clave_int <= 0
    ";
    $stmtPenObra = $conn->query($sqlPenObra);
    if ($stmtPenObra) {
        $dat = $stmtPenObra->fetch(PDO::FETCH_ASSOC);
        $pendienteobra = $dat['can'];
    }

    // Obras pendientes por aprobar
    $sqlAproObra = "SELECT COUNT(DISTINCT l.lio_clave_int) AS can FROM tbl_liquidar_obras l WHERE lio_estado = 1";
    $stmtAproObra = $conn->query($sqlAproObra);
    if ($stmtAproObra) {
        $dat = $stmtAproObra->fetch(PDO::FETCH_ASSOC);
        $poraprobarobra = $dat['can'];
    }

    $datos[] = array(
        "poraprobar"      => $poraprobar,
        "aprobadas"       => $aprobadas,
        "liquidadas"      => $liquidadas,
        "cancelada"       => $cancelada,
        "rechazada"       => $rechazada,
        "poraprobarliq"   => $poraprobarliq,
        "facturadas"      => $facturadas,
        "porfacturar"     => $porfacturar,
        "pendienteobra"   => $pendienteobra,
        "poraprobarobra"  => $poraprobarobra
    );

    echo json_encode($datos);
}

else if($opcion=="GRAFICO" and $p38>0)
{
    setlocale(LC_ALL,"es_ES.utf8","es_ES","esp");

    $yearInput = $_POST['ano'] ?? '';
    $year = ($yearInput === "") ? null : (int)$yearInput;

    $rawTotals = obtenerTotalesHorasExtras($conn, $year);
    $normalizedTotals = normalizarTotalesHorasExtras($rawTotals);
    $monthlyData = obtenerTotalesMensualesHorasExtras($conn, $year);

    renderSeccionGraficosHorasExtras($year, $normalizedTotals, $monthlyData);
}
else if($opcion=="DETALLE_GRAFICO_PERIODO" and $p38>0)
{
    header('Content-Type: application/json');
    $periodo = $_POST['periodo'] ?? '';
    if ($periodo === '' || !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periodo)) {
        echo json_encode(['error' => 'Periodo inválido.']);
        exit;
    }
    $rawTotals = obtenerTotalesHorasExtrasPorPeriodo($conn, $periodo);
    $employeeCount = isset($rawTotals['empleados_total']) ? (int)$rawTotals['empleados_total'] : 0;
    $normalized = normalizarTotalesHorasExtras($rawTotals);
    $pieData = prepararDatasetPieHorasExtras($normalized);

    $mesLabel = '';
    if (preg_match('/^(\d{4})-(\d{2})$/', $periodo, $matches)) {
        $mesesEs = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        $mesIndex = (int)$matches[2];
        $mesNombre = $mesesEs[$mesIndex - 1] ?? '';
        $mesLabel = trim($mesNombre . ' ' . $matches[1]);
    }

    echo json_encode([
        'periodo' => $periodo,
        'title' => $mesLabel !== '' ? ('Detalle ' . $mesLabel) : 'Detalle Mensual',
        'totals' => $normalized,
        'pieData' => $pieData,
        'employeeCount' => $employeeCount
    ]);
    exit;
}
else if($opcion=="DETALLE_GRAFICO_ANIO" and $p38>0)
{
    header('Content-Type: application/json');
    $yearInput = $_POST['anio'] ?? '';
    if ($yearInput !== '' && !preg_match('/^\d{4}$/', $yearInput)) {
        echo json_encode(['error' => 'Año inválido.']);
        exit;
    }
    $year = ($yearInput === '' ? null : (int)$yearInput);
    $rawTotals = obtenerTotalesHorasExtras($conn, $year);
    $normalized = normalizarTotalesHorasExtras($rawTotals);
    $pieData = prepararDatasetPieHorasExtras($normalized);

    echo json_encode([
        'anio'   => $year,
        'title'  => $year ? ('Detalle ' . $year) : 'Detalle Todos los Años',
        'totals' => $normalized,
        'pieData'=> $pieData,
        'employeeCount' => $normalized['employeeCount'] ?? null
    ]);
    exit;
}
else if($opcion=="DETALLE_GRAFICO_TIPO" and $p38>0)
{
    header('Content-Type: application/json');
    $tipo = $_POST['tipo'] ?? '';
    if ($tipo === '') {
        echo json_encode(['error' => 'Tipo de hora inválido.']);
        exit;
    }
    $anio = $_POST['anio'] ?? null;
    $periodo = $_POST['periodo'] ?? null;
    $year = ($anio === '' || $anio === null) ? null : (int)$anio;

    $detalle = obtenerDetalleEmpleadosPorTipoHora($conn, $tipo, $year, $periodo);

    echo json_encode([
        'tipo' => $detalle['tipo'],
        'total' => $detalle['total'],
        'empleados' => $detalle['empleados'],
        'employeeCount' => $detalle['employeeCount'],
        'anio' => $year,
        'periodo' => $periodo
    ]);
    exit;
}
else if($opcion=="FILTROSCOMPENSATORIOS")
{
    ?>
     <div class="col-md-4">
        <label for="CRUDINFORMES('LISTACOMPENSATORIOS')">Empleado</label>
        <select id="busempleado" multiple name="busempleado" class="form-control form-control-sm selectpicker" onchange= "CRUDINFORMES('LISTACOMPENSATORIOS')"  data-actions-box="true">
       
        <?php
            $selectEmp = new General();
            $selectEmp -> cargarEmpleados("");
        ?>
        </select>       
    </div>
    <?php
    echo "<script>INICIALIZARCONTENIDO();</script>";
    echo "<script>CRUDINFORMES('LISTACOMPENSATORIOS');</script>";
}
else if($opcion=="LISTACOMPENSATORIOS")
{
    ?>
    <div class="col-md-12">
        <div id="smartwizard" class="mt-2">
            <ul class="nav">
            
                <li><a class="nav-link" data-empleado = "<?php echo $id;?>" data-target="#step-0">Nomina</a></li>
                <li><a class="nav-link" data-empleado = "<?php echo $id;?>" data-target="#step-1">Logistica</a></li>
                
            </ul>          
            <div class="tab-content">
                <div id="step-0" class="tab-pane invoice table-responsive" role="tabpanel">
                <button type="button" class="btn btn-sm btn-default" onclick="CRUDINFORMES('EXPORTARCOMPENSATORIO',1,1)" >Exportar Por Año<i class="fas fa-file-excel text-success"></i></button>
                <button type="button" class="btn btn-sm btn-default" onclick="CRUDINFORMES('EXPORTARCOMPENSATORIO',1,2)" >Exportar Por Mes<i class="fas fa-file-excel text-success"></i></button>
                    <table id="tbCompensatorios" class="table table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th class="dt-head-center align-middle bg-terracota">Empleado</th>
                                <th class="dt-head-center align-middle bg-terracota">Generados</th>
                                <th class="dt-head-center align-middle bg-terracota">Compensatorios</th>
                                <th class="dt-head-center align-middle bg-terracota">Por Mes</th>
                                <th class="dt-head-center align-middle bg-terracota">Por Año</th>
                                <th class="dt-head-center align-middle bg-terracota">Tomados</th>
                                <th class="dt-head-center align-middle bg-terracota">Remunerados</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div> 
                <div id="step-1" class="tab-pane invoice table-responsive" role="tabpanel">
                    <table id="tbCompensatoriosObra" class="table table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th class="dt-head-center align-middle bg-terracota">Empleado</th>
                                <th class="dt-head-center align-middle bg-terracota">Generados</th>
                                <th class="dt-head-center align-middle bg-terracota">Compensatorios</th>
                        
                                <th class="dt-head-center align-middle bg-terracota">Tomados</th>
                                <th class="dt-head-center align-middle bg-terracota">Remunerados</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                            
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>                         
            </div>
        </div>        
    </div>
    <script src="jsdatatable/informes/jscompensatorios.js?<?php echo time();?>"></script>
    <?php
}
else if($opcion=="FILTROSLIMITE")
{
    $gen = new General();
    ?>
     <div class="col-md-6">
        <label for="">Empleado</label>
        <select  id="selempleado" class="form-control form-control-sm selectpicker" onchange= "CRUDGENERAL('CARGARSEMANAS', 'selsemana', 'selmes', 'selano', 'JORNADA','','selempleado')" required data-parsley-error-message="Seleccionar empleado" data-parsley-errors-container="#msn-error1" >
        <option value=""></option>
        <?php
          
            $gen -> cargarEmpleados($emp);
        ?>
        </select>
        <span id="msn-error1"></span>
    </div>
    <div class="col-md-2">
        <label for="selano">Año</label>
        <select  name="selano" id="selano" class="form-control form-control-sm selectpicker" onchange="CRUDGENERAL('CARGARSEMANAS', 'selsemana', 'selmes', 'selano', 'JORNADA','','selempleado')"   data-live-search="true" >
            <?php
            //onchange="CRUDGENERAL('CARGARMES','','selmes','selano','','','selempleado')"           
            $gen -> cargarAnos(2020,date('Y'),$ano,"DESC",1);
            ?>

        </select>
    </div>
    <div class="col-md-3">
        <label for="selsemana">Semana</label>
        <select <?php echo $disinp;?> name="selsemana" id="selsemana" class="form-control form-control-sm selectpicker" onchange="CRUDINFORMES('LISTALIMITE', '')" required data-parsley-error-message="Seleccionar semana"  data-parsley-errors-container="#msn-error2" data-live-search="true" data-id="<?php echo $sem;?>">
        <?php 
        $sf = $semanaactual;        
        $gen->cargarSemanas(date('Y'),'',2,$sf,$sem,"DESC",1,$emp);    
        ?>
        </select>
        <span id="msn-error2"></span>
    </div>
    <?php
    echo "<script>INICIALIZARCONTENIDO();</script>";
    echo "<script>CRUDINFORMES('LISTALIMITE');</script>";
}
else if($opcion=="LISTALIMITE")
{
    ?>
    <div class="col-md-12">
        <div id="smartwizard" class="mt-2">
            <ul class="nav">
            
                <li><a class="nav-link" data-empleado = "<?php echo $id;?>" data-target="#step-0">Nomina</a></li>
                <li><a class="nav-link" data-empleado = "<?php echo $id;?>" data-target="#step-1">Logistica</a></li>
                
            </ul>          
            <div class="tab-content">
                
                <div id="step-0" class="tab-pane invoice table-responsive" role="tabpanel">
                   
                    <table id="tbLimites" class="table table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th class="dt-head-center align-middle bg-terracota">Año</th>
                                <th class="dt-head-center align-middle bg-terracota">Semana</th>
                                <th class="dt-head-center align-middle bg-terracota">Desde</th>
                                <th class="dt-head-center align-middle bg-terracota">Hasta</th>
                                <th class="dt-head-center align-middle bg-terracota">Empleado</th>
                                <th class="dt-head-center align-middle bg-terracota">Total Extras</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div> 
                <div id="step-1" class="tab-pane invoice table-responsive" role="tabpanel">
                    <table id="tbLimitesObra" class="table table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th class="dt-head-center align-middle bg-terracota">Año</th>
                                <th class="dt-head-center align-middle bg-terracota">Semana</th>
                                <th class="dt-head-center align-middle bg-terracota">Desde</th>
                                <th class="dt-head-center align-middle bg-terracota">Hasta</th>
                                <th class="dt-head-center align-middle bg-terracota">Empleado</th>
                                <th class="dt-head-center align-middle bg-terracota">Total Extras</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>                         
            </div>
        </div>        
    </div>
    <script src="jsdatatable/informes/jslimites.js?<?php echo time();?>"></script>
    <?php
}
else if($opcion=="FILTROSCONTABILIDAD")
{
    $selectEmp = new General();
    $sql = "SELECT DISTINCT liq_inicio, liq_fin 
    FROM tbl_liquidar 
    ORDER BY liq_inicio DESC 
    LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $datfec = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($datfec) {
        $des = $datfec['liq_inicio'];
        $has = $datfec['liq_fin'];
    }


    ?>
     <div class="col-md-4">
        <label for="busempleado">Empleado</label>
        <select id="busempleado" multiple name="busempleado" class="form-control form-control-sm selectpicker" onchange= "CRUDINFORMES('LISTACONTABILIDAD')"  data-actions-box="true">       
        <?php           
            $selectEmp -> cargarEmpleados("");
        ?>
        </select>       
    </div>
    <div class="col-md-2">
        <label for="busfrecuencia">Frecuencia</label>
        <select id="busfrecuencia" name="busfrecuencia" class="form-control form-control-sm selectpicker" onchange= "CRUDINFORMES('LISTACONTABILIDAD')"  data-actions-box="true">       
            <option value="1">Por Rango de Fecha</option>
            <option value="2">Por periodo Liquidado</option>
        </select>       
    </div>
    <div class="col-md-2 hide" id="divperiodos">
        <label for="busperiodos">Periodos Liquidados</label>
        <select id="busperiodos" name="busperiodos" class="form-control form-control-sm selectpicker" onchange= "CRUDINFORMES('LISTACONTABILIDAD')"  data-actions-box="true">       
        <?php           
            $selectEmp -> cargarPeriodosLiquidados("");
        ?>
        </select>       
    </div>
    <div class="col-6 col-sm-6 col-md-2" id="divdesde">
        <label for="busdesde">Desde</label>
        <input type="date" name="busdesde" id="busdesde" class="form-control form-control-sm" onchange="CRUDINFORMES('LISTACONTABILIDAD', '') " required data-parsley-error-message="Seleccionar fecha desde" value="<?php echo $des;?>">
    </div>
    <div class="col-6 col-sm-6 col-md-2" id="divhasta">
        <label for="bushasta">Hasta</label>
        <input type="date" name="bushasta" id="bushasta" class="form-control form-control-sm" onchange="CRUDINFORMES('LISTACONTABILIDAD', '')" required data-parsley-error-message="Seleccionar fecha hasta" value="<?php echo $has;?>">
    </div>
    <div class="col-md-2">
        <label for="bustipo">Tipo Informe</label>
        <select id="bustipo" name="bustipo" class="form-control form-control-sm selectpicker" onchange= "CRUDINFORMES('LISTACONTABILIDAD')"  data-actions-box="true">       
        <option value="1">Horas</option>
        <option value="2">Alimentación</option>
        </select>       
    </div>
    <div class="col-2 col-sm-2 col-md-2">
        <br>
        <button type="button" class="btn btn-sm btn-default" onclick="CRUDINFORMES('EXPORTARCONTABILIDAD')" >Exportar <i class="fas fa-file-excel text-success"></i></button>
    </div>
    <script>
        $('#busfrecuencia').on('change',function(){
            var fre = $(this).val();
            if(fre==2)
            {
                $('#divperiodos').removeClass('hide');
                $('#divdesde').addClass('hide');
                $('#divhasta').addClass('hide');
            }
            else
            {
                $('#divperiodos').addClass('hide');
                $('#divdesde').removeClass('hide');
                $('#divhasta').removeClass('hide');
            }
        })
    </script>
    <?php
  
    echo "<script>CRUDINFORMES('LISTACONTABILIDAD');</script>";
    echo "<script>INICIALIZARCONTENIDO();</script>";
}
else if($opcion=="LISTACONTABILIDAD")
{
    $tip = $_POST['tip'];
    $des = $_POST['des'];
    $has = $_POST['has'];
    
    if($des=="" || $has=="")
    {
        echo "<div class='alert alert-info mt-1'>Seleccionar rango de fechas</div>";
    }
    else
    if($tip==1)
    {
        ?>
        <table id="tbContabilidad" class="table table-bordered table-striped table-valign-middle" style="font-size:12px">
            <thead>
                <tr>
                    <th class="dt-head-center" rowspan="2">EMPLEADO</th>
                    <th class="dt-head-center" rowspan="2">CEDULA</th>
                    <th class="dt-head-center" rowspan="2">BASICO</th>
                    <th class="dt-head-center p-0 bg-white">002</th>
                    <th class="dt-head-center p-0 bg-white">004</th>
                    <th class="dt-head-center p-0 bg-white">014</th>
                    <th class="dt-head-center p-0 bg-white">003</th>
                    <th class="dt-head-center p-0 bg-white">005</th>
                    <th class="dt-head-center p-0 bg-white">006</th>
                    <th class="dt-head-center p-0 bg-white">013</th>
                    <th class="dt-head-center p-0 bg-white">008</th>
                    <th class="dt-head-center p-0 bg-white">010</th>

                    <th class="dt-head-center bg-purple"  rowspan="2">TOTAL HORAS</th>  
                    <th class="dt-head-center bg-purple"  rowspan="2">TOTAL HORAS</th>  
                    <th class="dt-head-center bg-purple"  rowspan="2">HORAS A AJUSTAR</th>  
                    <th class="dt-head-center bg-purple"  rowspan="2">HORAS AJUSTADAS</th>  
                    <th class="dt-head-center bg-green"  rowspan="2">HORAS FESTIVA COMO BONIFICACION</th>  
                    <th class="dt-head-center bg-green"  rowspan="2">HORAS ORDINARIAS COMO BONIFICACION</th>  

                    <th class="dt-head-center p-0 bg-white">002</th>
                    <th class="dt-head-center p-0 bg-white">010</th>
                    <th class="dt-head-center p-0 bg-white">006</th>
                    <th class="dt-head-center p-0 bg-white">008</th>
                    <th class="dt-head-center p-0 bg-white">003</th>
                    <th class="dt-head-center p-0 bg-white">005</th>
                    <th class="dt-head-center p-0 bg-white">004</th>
                    <th class="dt-head-center p-0 bg-white">013</th>
                    <th class="dt-head-center p-0 bg-white">014</th>
                
                    <th class="dt-head-center p-0 bg-white">002</th>
                    <th class="dt-head-center p-0 bg-white">006</th>
                    <th class="dt-head-center p-0 bg-white">008</th>
                    <th class="dt-head-center p-0 bg-white">004</th>
                    <th class="dt-head-center p-0 bg-white">014</th>
                    <th class="dt-head-center p-0 bg-white">003</th>
                    <th class="dt-head-center p-0 bg-white">013</th>
                    <th class="dt-head-center p-0 bg-white">005</th>
                    <th class="dt-head-center p-0 bg-white">010</th>
                            
                    <th class="dt-head-center p-0 bg-secondary" rowspan="2">VALOR TOTAL SIN DEDUCCIONES</th>
                    
                    <th class="dt-head-center bg-green"  rowspan="2">VALOR HORA EXTRA COMO BONIFICACION</th>  
                    <th class="dt-head-center bg-green"  rowspan="2">VALOR EXTRA DIURNA FESTIVA COMO BONIFICACION</th>  
                    <th class="dt-head-center bg-green"  rowspan="2">TOTAL BONIFICACION</th>  
                </tr>
                <tr>
                    <th class="dt-head-center bg-blue-dark">VALOR EXTRA ORDINARIA</th>
                    <th class="dt-head-center bg-blue-dark">VALOR EXTRA DIURNAS FESTIVA</th>
                    <th class="dt-head-center bg-blue-dark">VALOR DIURNA FESTIVA</th>
                    <th class="dt-head-center bg-blue-dark">VALOR EXTRA NOCTURNA ORDINARIA</th>
                    <th class="dt-head-center bg-blue-dark">VALOR EXTRA NOCTURNA FESTIVA</th>
                    <th class="dt-head-center bg-blue-dark">VALOR RECARGO NOCTURNO</th>
                    <th class="dt-head-center bg-blue-dark">VALOR RECARGO DOMINICAL</th>
                    <th class="dt-head-center bg-blue-dark">VALOR RECARGO NOCTURNO DOMINICAL </th>
                    <th class="dt-head-center bg-blue-dark">VALOR RECARGO EXTRAS ORDINARIA</th> 

                                

                    <th class="dt-head-center bg-terracota">TOTAL EXTRAS ORDINARIAS LABORADAS</th>
                    <th class="dt-head-center bg-terracota">TOTAL RECARGO EXTRAS ORDINARIAS</th>
                    <th class="dt-head-center bg-terracota">TOTAL RECARGO NOCTURNO</th>
                    <th class="dt-head-center bg-terracota">TOTAL RECARGO NOCTURNAS DOMINICAL</th>
                    <th class="dt-head-center bg-terracota">TOTAL EXTRAS NOCTURNAS</th>
                    <th class="dt-head-center bg-terracota">TOTAL EXTRAS NOCTURNAS FESTIVA</th>
                    <th class="dt-head-center bg-terracota">TOTAL EXTRAS DIURNAS FESTIVAS</th>
                    <th class="dt-head-center bg-terracota">TOTAL RECARGO DOMINICAL</th>
                    <th class="dt-head-center bg-terracota">TOTAL EXTRAS FESTIVAS</th> 

                    <th class="dt-head-center bg-blue-dark">VALOR EXTRAS ORDINARIAS</th>
                    <th class="dt-head-center bg-blue-dark">VALOR RECARGO NOCTURNO</th>
                    <th class="dt-head-center bg-blue-dark">VALOR RECARGO NOCTURNO DOMINICAL</th>
                    <th class="dt-head-center bg-blue-dark">VALOR EXTRAS FESTIVAS</th>
                    <th class="dt-head-center bg-blue-dark">VALOR DIURNA FESTIVAS</th>
                    <th class="dt-head-center bg-blue-dark">VALOR EXTRAS NOCTURNO</th>
                    <th class="dt-head-center bg-blue-dark">VALOR RECARGO DOMINICAL SEMANA</th>
                    <th class="dt-head-center bg-blue-dark">VALOR EXTRAS DOMINICAL NOCTURNO</th>
                    <th class="dt-head-center bg-blue-dark">VALOR RECARGO EXTRAS ORDINARIAS</th> 
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>

                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>

                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>

                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>

                    <th></th>

                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
        <script src="jsdatatable/informes/jscontabilidad.js?v=1"></script>
        <?php
    }
    else
    {

        $emp = "";
        $emp1 = "''";
        if(isset($_POST['emp']))
        {
            $emp = $_POST['emp'];
            $emp =  implode(', ', (array)$emp) ; 
            if($emp==""){$emp1="''";}else {$emp1=$emp;}
        }

        $sql = "
            SELECT 
                CONCAT(u.usu_apellido, ' ', u.usu_nombre) AS nom, 
                ld.lid_fecha AS fec, 
                SUM(ld.lid_val_alimentacion) AS alimentacion
            FROM tbl_usuarios u
            JOIN tbl_liquidar l ON l.usu_clave_int = u.usu_clave_int
            JOIN tbl_liquidar_dias ld ON ld.liq_clave_int = l.liq_clave_int
            WHERE 
                l.liq_inicio = :des 
                AND l.liq_fin = :has 
                AND ld.lid_alimentacion = 1 
                AND (
                    u.usu_clave_int IN ($emp1) 
                    OR :emp IS NULL 
                    OR :emp = ''
                )
            GROUP BY u.usu_nombre, u.usu_apellido, ld.lid_fecha
            ORDER BY nom, ld.lid_fecha
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':des', $des);
        $stmt->bindParam(':has', $has);
        $stmt->bindParam(':emp', $emp); // <- cuidado: solo si $emp es escalar

        $stmt->execute();

        ?>
        <table id="tbAlimentacion" class="table table-bordered table-striped table-valign-middle" style="font-size:12px">
        <thead>
            <tr>
                <th class="dt-head-center bg-terracota">EMPLEADO</th>
                <th class="dt-head-center bg-terracota">FECHA</th>
                <th class="dt-head-center bg-terracota">VALOR</th>
            </tr>
        </thead>
        <?php
        while ($dat = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $fec = $dat['fec'];
            $nom = $dat['nom'];
            $ali = $dat['alimentacion'];

            ?>
            <tr>
                <td><?php echo strtoupper($nom);?></td>
                <td><?php echo $fec;?></td>
                <td>$ <?php echo number_format($ali, 0,',','.'); ?></td>
                
            </tr>
            <?php
        }
        ?>
        <tfoot>
            <tr>
                <th></th>
                <th></th>
                <th></th>
               
            </tr>
        </tfoot>
        </table>
        <script src="jsdatatable/informes/jsalimentacion.js?<?php echo time();?>"></script>
        <?php
    }
}
else if($opcion=="FILTROSHORAS")
{
    $reg = new General();
    $datr = $reg->fnReglas();
    //reg_hor_mes hmes, reg_hor_semana hsemana, reg_lim_extras limex, reg_lim_extras_semana limexsemana
    $hmes = $datr['hmes'];
    $hsemana = $datr['hsemana'];
    $limex = $datr['limex']; // LIMITE DIA
    $limexsemana = $datr['limexsemana']; // LIMITE SEMANA 

    ?>
     <div class="col-md-2 hide">
        <label for="seltipo">Tipo:</label>
        <select  name="seltipo" id="seltipo" class="form-control form-control-sm selectpicker" onchange="CRUDLIQUIDAR('CARGAREMPLEADO', '')" <?php echo $disa;?>>

            <?php if($p121>0){ ?> <option value="1" <?php if($tip==1){ echo "selected"; }?>>Por empleado</option><?php } ?>
            <?php if($p122>0){ ?> <option value="2" <?php if($tip==2){ echo "selected"; }?>>Por Obra</option><?php } ?>
        </select>
    </div>
    <div class="col-md-4">
        <label for="busempleado">Empleado</label>
        <select id="busempleado" multiple name="busempleado" class="form-control form-control-sm selectpicker" onchange= "CRUDINFORMES('LISTAHORAS')"  data-actions-box="true">    
        <?php           
            $reg -> cargarEmpleados("");
        ?>
        </select>       
    </div>
    <div class="col-6 col-sm-6 col-md-2">
        <label for="busdesde">Desde:</label>
        <input type="date" name="busdesde" id="busdesde" class="form-control form-control-sm" onchange="CRUDINFORMES('LISTAHORAS', '') " required data-parsley-error-message="Seleccionar fecha desde" value="">
    </div>
    <div class="col-6 col-sm-6 col-md-2">
        <label for="bushasta">Hasta:</label>
        <input type="date" name="bushasta" id="bushasta" class="form-control form-control-sm" onchange="CRUDINFORMES('LISTAHORAS', '')" required data-parsley-error-message="Seleccionar fecha hasta" value="">
    </div>
    <div class="col-2 col-sm-2 col-md-2 hide">
        <br>
        <button type="button" class="btn btn-sm btn-default" onclick="CRUDINFORMES('EXPORTARHORAS')" >Exportar <i class="fas fa-file-excel text-success"></i></button>
    </div>
    <div class="col-md-12">
        <div class="row mt-1">
            <div class="col-md-3 bg-danger p-2 text-white">Supera las <strong><?php echo $hmes;?> horas</strong> mes</div>
            <div class="col-md-3 bg-danger p-2 text-white bg-opacity-75">Supera el limite de <strong><?php echo $hsemana;?> horas</strong>   semana</div>
            <div class="col-md-3 bg-danger p-2 text-dark bg-opacity-50">Supera el limite de <strong><?php echo $limexsemana;?> horas</strong> extras semana</div>
            <div class="col-md-3 bg-danger p-2 text-dark bg-opacity-25">Supera el limite de <strong><?php echo $limex;?> horas</strong>  extras dia</div>
        </div>
        <div id="smartwizard" class="mt-2">
            <ul class="nav">
                <li><a class="nav-link" data-empleado = "" data-target="#step-0">Limite Horas Dia</a></li>
                <li><a class="nav-link" data-empleado = "" data-target="#step-1">Limite Horas Semana</a></li>
                <li><a class="nav-link" data-empleado = "" data-target="#step-2">Limite Horas Mes</a></li>
            </ul>          
            <div class="tab-content">
                <div id="step-0" class="tab-pane invoice table-responsive" role="tabpanel"></div> 
                <div id="step-1" class="tab-pane invoice table-responsive" role="tabpanel"></div> 
                <div id="step-2" class="tab-pane invoice table-responsive" role="tabpanel"></div>  
            </div>
        </div>
        <script>
            localStorage.setItem('step','0');
            $('#smartwizard').smartWizard({
                selected: 0, // Initial selected step, 0 = first step
                theme: 'arrows', // theme for the wizard, related css need to include for other than default theme
                justified: false, // Nav menu justification. true/false
                darkMode: false, // Enable/disable Dark Mode if the theme supports. true/false
                autoAdjustHeight: false, // Automatically adjust content height
                cycleSteps: false, // Allows to cycle the navigation of steps
                backButtonSupport: true, // Enable the back button support
                enableURLhash: true, // Enable selection of the step based on url hash
                transition: {
                    animation: 'none', // Effect on navigation, none/fade/slide-horizontal/slide-vertical/slide-swing
                    speed: '400', // Transion animation speed
                    easing:'' // Transition animation easing. Not supported without a jQuery easing plugin
                },
                toolbarSettings: {
                    toolbarPosition: 'top', // none, top, bottom, both
                    toolbarButtonPosition: 'right', // left, right, center
                    showNextButton: false, // show/hide a Next button
                    showPreviousButton: false, // show/hide a Previous button
                    /* toolbarExtraButtons: [
                        $('<button type="button"></button>').text('Generar Liquidación')
                                .addClass('btn btn-success')
                                .on('click', function(){ 
                                    var emp = localStorage.getItem('emp');
                                    CRUDLIQUIDAR('GENERARPROFORMA','',emp);
                                                                
                        })
                    ]*/ // Extra buttons to show on toolbar, array of jQuery input/buttons elements
                },
                anchorSettings: {
                    anchorClickable: true, // Enable/Disable anchor navigation
                    enableAllAnchors: true, // Activates all anchors clickable all times
                    markDoneStep: true, // Add done state on navigation
                    markAllPreviousStepsAsDone: false, // When a step selected by url hash, all previous steps are marked done
                    removeDoneStepOnNavigateBack: false, // While navigate back done step after active step will be cleared
                    enableAnchorOnDoneStep: true // Enable/Disable the done steps navigation
                },
                keyboardSettings: {
                    keyNavigation: false, // Enable/Disable keyboard navigation(left and right keys are used if enabled)
                    keyLeft: [37], // Left key code
                    keyRight: [39] // Right key code
                },
                lang: { // Language variables for button
                    next: 'Siguiente',
                    previous: 'Anterior'
                },
                disabledSteps: [], // Array Steps disabled
                errorSteps: [], // Highlight step with errors
                hiddenSteps: [] // Hidden steps
            });

            $("#smartwizard").on("stepContent", function(e, anchorObject, stepIndex, stepDirection) {
                var elmForm = "step-" + stepIndex;
                localStora('step', stepIndex);
                CRUDINFORMES('LISTAHORAS');           
                //$("#smartwizard"+ d.Id).smartWizard("loader", "show");     
            });
        </script>
    </div>
    <?php
    echo "<script>CRUDINFORMES('LISTAHORAS');</script>";
    echo "<script>INICIALIZARCONTENIDO();</script>";
}
else if($opcion=="LISTAHORAS")
{
    $opc = $_POST['opc'];
    $tit = ($opc==0?"DIA":($opc==1?"SEMANA":"MES"));
    ?>

    
    <table id="tbHoras_<?php echo $opc;?>" class="table table-bordered table-striped table-valign-middle" style="font-size:12px">
        <thead>
            <tr>
                <th class="dt-head-center" rowspan="2">EMPLEADO</th>
                <th class="dt-head-center" rowspan="2">CEDULA</th>
                <th class="dt-head-center" rowspan="2"><?php echo $tit;?></th>
                <th class="dt-head-center" rowspan="2">RANGO</th>
                <th  class="dt-head-center p-0 bg-blue-dark" rowspan="2">TOTAL HORAS LABORADAS</th>
                <th class="dt-head-center p-0 bg-white">002</th>
                <th class="dt-head-center p-0 bg-white">010</th>
                <th class="dt-head-center p-0 bg-white">006</th>
                <th class="dt-head-center p-0 bg-white">008</th>
                <th class="dt-head-center p-0 bg-white">003</th>
                <th class="dt-head-center p-0 bg-white">005</th>
                <th class="dt-head-center p-0 bg-white">004</th>
                <th class="dt-head-center p-0 bg-white">013</th>
                <th class="dt-head-center p-0 bg-white">014</th>                
                <th  class="dt-head-center p-0 bg-secondary" rowspan="2">TOTAL EXTRAS</th>
            </tr>
            <tr>
                <th class="dt-head-center bg-terracota">HEDO</th>
                <th class="dt-head-center bg-terracota">AJ REC EXTRA</th>
                <th class="dt-head-center bg-terracota">RN</th>
                <th class="dt-head-center bg-terracota">HNF</th>
                <th class="dt-head-center bg-terracota">HENO</th>
                <th class="dt-head-center bg-terracota">HENF</th>
                <th class="dt-head-center bg-terracota">HEDF</th>
                <th class="dt-head-center bg-terracota">RD</th>
                <th class="dt-head-center bg-terracota">HDF</th>                
        </thead>
        <tfoot>
            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        </tfoot>
    </table>
    <script src="jsdatatable/informes/jshoras.js?<?php echo time();?>"></script>
    <?php
}

