<?php
include 'db_connection.php'; // Include your database connection

// Fetch categories for the dropdown
$categories = $conn->query("SELECT * FROM categories");

// Handle data request for graphs
if (isset($_GET['chart_data'])) {
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
    $selected_categories = isset($_GET['categories']) ? explode(',', $_GET['categories']) : [];

    $chart_sql = "SELECT DATE(sales.sale_date) AS sale_date, categories.category_name, 
                  SUM(sales.total_amount - (products.cost * sales.quantity_sold)) AS total_profit 
                  FROM sales 
                  JOIN products ON sales.product_id = products.id 
                  JOIN categories ON products.category_id = categories.id";

    // Apply filters
    $where_clauses = [];
    if ($start_date && $end_date) {
        $where_clauses[] = "sales.sale_date BETWEEN '$start_date' AND '$end_date'";
    }
    if (!empty($selected_categories) && !in_array('all', $selected_categories)) {
        $category_ids = implode(',', array_map('intval', $selected_categories));
        $where_clauses[] = "products.category_id IN ($category_ids)";
    }

    if (!empty($where_clauses)) {
        $chart_sql .= " WHERE " . implode(' AND ', $where_clauses);
    }

    $chart_sql .= " GROUP BY sale_date, categories.category_name ORDER BY sale_date";

    $chart_result = $conn->query($chart_sql);
    $chart_data = [];
    while ($row = $chart_result->fetch_assoc()) {
        $chart_data[] = [
            'sale_date' => $row['sale_date'],
            'category_name' => $row['category_name'],
            'total_profit' => (float)$row['total_profit']
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($chart_data);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profit Analysis Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #1a4b8c;
            --primary-light: #2c5aa0;
            --secondary-color: #eef3fc;
            --accent-color: #3b82f6;
            --success-color: #10b981;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --border-color: #e5e7eb;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --hover-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            display: flex;
            background: var(--secondary-color);
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: var(--primary-color);
            color: white;
            padding: 20px;
            height: 100vh;
            position: fixed;
            }

            .logo-container {
            text-align: center;
            padding: 20px 0;
            margin-bottom: 30px;
            border-bottom: 1px solid white;
            }

            .logo-container img {
            width: 100px;
            border-radius: 50%;
            }

            .sidebar-menu {
            list-style: none;
            }

            .sidebar-menu li {
            margin-bottom: 15px;
            }

            .sidebar-menu a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 5px;
            transition: 0.3s;
            }

            .sidebar-menu a:hover {
            background: #2c5aa0;
            box-shadow: 0 4px 8px var(--shadow-color);
            }

            .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
            }
            
            .sidebar-menu a.active {
            background: #2c5aa0;
            box-shadow: 0 4px 8px var(--shadow-color);
            }

            h1 {
            text-align: center;
            color: var(--blue-color);
            font-size: 28px;    
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--blue-color);
            display: inline-block;
        }

        .content-wrapper {
            flex: 1;
            margin-left: 270px;
            padding: 2rem;
        }

        .dashboard-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.875rem;
            font-weight: 600;
            color: var(--text-dark);
            text-align: center;
            position: relative;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
            
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background-color: var(--primary-color);
            border-radius: 2px;
        }

        .filter-card {
            background: white;
            border-radius: 1rem;
            box-shadow: var(--card-shadow);
            padding: 1.5rem;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .filter-card:hover {
            box-shadow: var(--hover-shadow);
            transform: translateY(-2px);
        }

        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-dark);
        }

        select, 
        input[type="date"] {
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            color: var(--text-dark);
            background-color: white;
            transition: all 0.2s;
        }

        select[multiple] {
            height: 120px;
        }

        select:focus,
        input:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: white;
            background-color: var(--primary-color);
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn:hover {
            background-color: var(--primary-light);
            transform: translateY(-1px);
        }

        .btn i {
            font-size: 1rem;
        }

        .chart-container {
            background: white;
            border-radius: 1rem;
            box-shadow: var(--card-shadow);
            padding: 1.5rem;
            margin-top: 2rem;
            transition: all 0.3s ease;
        }

        .chart-container:hover {
            box-shadow: var(--hover-shadow);
        }

        canvas {
            width: 100% !important;
            max-height: 400px;
        }

        @media (max-width: 768px) {
            .content-wrapper {
                margin-left: 0;
                padding: 1rem;
            }

            .filter-form {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
            <div class="logo-container">
                <img src="/Robert-2.0/image/R.B.png" alt="RB Group of Companies">
            </div>
            <ul class="sidebar-menu">
                <li><a href=../dashboard.html><i class="fas fa-chart-line"></i><span>Dashboard</span></a></li>
                <li><a href="../attendance/attendance_management.php"><i class="fas fa-clipboard-list"></i><span>Attendance</span></a></li>
                <li><a href="../Employee/employee.html"><i class="fas fa-users"></i><span>Employee</span></a></li>
                <li><a href="solditem.php"><i class="fas fa-chart-line"></i><span>Daily Sales</span></a></li>
                <li><a href="selling.php"><i class="fas fa-file-invoice"></i><span>Billing</span></a></li>
                <li><a href="index.php"><i class="fas fa-warehouse"></i><span>Warehouse</span></a></li>
                <li><a href="../laser account/ledger_management.php"><i class="fas fa-book"></i><span>Ledger Account</span></a></li>
                <li><a href="../Gatepass/process_gatepass.php"><i class="fas fa-book"></i><span>Gatepass</span></a></li>
                <li><a href="profit_graphs.php" class="active"><i class="fas fa-chart-bar"></i><span>Reports</span></a></li>
            </ul>
        </div>

    <div class="content-wrapper">
        <div class="dashboard-header">
            <h1 class="page-title">Profit Analysis Dashboard</h1>
        </div>
        
        <div class="filter-card">
            <form id="filterForm" class="filter-form">
                <div class="form-group">
                    <label for="categories">
                        <i class="fas fa-tags"></i> Categories
                    </label>
                    <select name="categories" id="categories" multiple>
                        <option value="all">All Categories</option>
                        <?php while ($category = $categories->fetch_assoc()): ?>
                            <option value="<?= $category['id'] ?>"><?= $category['category_name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="start_date">
                        <i class="fas fa-calendar-alt"></i> Start Date
                    </label>
                    <input type="date" name="start_date" id="start_date">
                </div>
                
                <div class="form-group">
                    <label for="end_date">
                        <i class="fas fa-calendar-alt"></i> End Date
                    </label>
                    <input type="date" name="end_date" id="end_date">
                </div>
                
                <div class="form-group">
                    <label for="graphType">
                        <i class="fas fa-chart-bar"></i> Graph Type
                    </label>
                    <select id="graphType">
                        <option value="bar">Bar Graph</option>
                        <option value="line">Line Graph</option>
                        <option value="pie">Pie Chart</option>
                    </select>
                </div>
                
                <button type="button" id="applyFilter" class="btn">
                    <i class="fas fa-filter"></i> Apply Filter
                </button>
            </form>
        </div>

        <div class="chart-container">
            <canvas id="profitChart"></canvas>
        </div>
    </div>
        
    <script>
        const filterForm = document.getElementById('filterForm');
        const applyFilterButton = document.getElementById('applyFilter');

        let profitChart;

        applyFilterButton.addEventListener('click', () => {
            const categories = Array.from(document.getElementById('categories').selectedOptions).map(option => option.value);
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const graphType = document.getElementById('graphType').value;

            // Fetch data for charts
            fetch(`profit_graphs.php?chart_data=1&start_date=${startDate}&end_date=${endDate}&categories=${categories.join(',')}`)
                .then(response => response.json())
                .then(data => {
                    renderProfitChart(data, graphType);
                })
                .catch(err => console.error('Error fetching chart data:', err));
        });

        function renderProfitChart(data, type) {
            const labels = [...new Set(data.map(item => item.sale_date))];
            const categories = [...new Set(data.map(item => item.category_name))];
            const datasets = categories.map(category => ({
                label: category,
                data: labels.map(date => {
                    const item = data.find(d => d.sale_date === date && d.category_name === category);
                    return item ? item.total_profit : 0;
                }),
                backgroundColor: getRandomColor(),
                borderColor: getRandomColor(),
                borderWidth: 1,
                tension: 0.4,
            }));

            if (profitChart) {
                profitChart.destroy();
            }

            const ctx = document.getElementById('profitChart').getContext('2d');
            profitChart = new Chart(ctx, {
                type: type,
                data: { labels, datasets },
                options: { responsive: true, scales: { y: { beginAtZero: true } } },
            });
        }

        function getRandomColor() {
            return `#${Math.floor(Math.random() * 16777215).toString(16)}`;
        }
    </script>
</body>
</html>
