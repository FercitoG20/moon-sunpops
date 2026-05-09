<link rel="stylesheet" href="inicio/inicio.css">

<div class="dashboard-inicio">
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Ventas Hoy</h3>
            <p>$1,850.00</p>
        </div>
        <div class="stat-card">
            <h3>Paletas Vendidas</h3>
            <p>124</p>
        </div>
        <div class="stat-card">
            <h3>Nuevos Clientes</h3>
            <p>18</p>
        </div>
    </div>

    <div class="charts-calc-container">
        
        <div class="chart-section">
            <h2>Ventas de la Semana</h2>
            <div class="chart-wrapper">
                <canvas id="ventasChart"></canvas>
            </div>
        </div>

        <div class="calc-section">
            <h2>Calculadora de Ganancias</h2>
            <form id="calcForm">
                <div class="input-box">
                    <label>Costo de Producción ($)</label>
                    <input type="number" id="costo" step="0.01" required>
                </div>
                <div class="input-box">
                    <label>Precio de Venta ($)</label>
                    <input type="number" id="precio" step="0.01" required>
                </div>
                <div class="input-box">
                    <label>Cantidad Vendida</label>
                    <input type="number" id="cantidad" required>
                </div>
                <button type="submit" class="btn-calc">Calcular</button>
            </form>
            <div id="calcResult" class="result-box"></div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="inicio/inicio.js"></script>