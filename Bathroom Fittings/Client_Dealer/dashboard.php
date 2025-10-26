<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dealer Dashboard | Product List</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #e0f7fa, #cfd9df);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 0;
    }

    .dashboard {
      width: 90%;
      max-width: 1100px;
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
      padding: 30px;
      animation: fadeIn 0.7s ease-in-out;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    h2 {
      text-align: center;
      font-weight: 600;
      color: #004d61;
      margin-bottom: 25px;
    }

    table {
      border-radius: 15px;
      overflow: hidden;
    }

    thead {
      background: linear-gradient(90deg, #0077b6, #00b4d8);
      color: #fff;
    }

    th,
    td {
      text-align: center;
      vertical-align: middle;
    }

    tbody tr:hover {
      background-color: rgba(0, 183, 255, 0.05);
      transition: all 0.3s ease;
    }

    .status-active {
      background-color: #d4edda;
      color: #155724;
      padding: 5px 10px;
      border-radius: 10px;
      font-weight: 500;
    }

    .status-inactive {
      background-color: #f8d7da;
      color: #721c24;
      padding: 5px 10px;
      border-radius: 10px;
      font-weight: 500;
    }

    #loading {
      display: none;
      text-align: center;
      padding: 40px 0;
    }

    .water-drop {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background: radial-gradient(circle at 30% 30%, #90e0ef, #0077b6);
      animation: drip 1.5s infinite ease-in-out;
      margin: 0 auto;
    }

    @keyframes drip {

      0%,
      100% {
        transform: translateY(0) scale(1);
        opacity: 1;
      }

      50% {
        transform: translateY(10px) scale(0.95);
        opacity: 0.8;
      }
    }

    #refreshBtn {
      background: linear-gradient(90deg, #0096c7, #48cae4);
      border: none;
      color: #fff;
      padding: 10px 20px;
      border-radius: 30px;
      transition: all 0.3s ease;
    }

    #refreshBtn:hover {
      background: linear-gradient(90deg, #48cae4, #0096c7);
      transform: scale(1.05);
    }
  </style>
</head>

<body>
  <div class="dashboard">
    <h2><i class="fa-solid fa-faucet me-2 text-primary"></i>Dealer Dashboard</h2>

    <!-- Water Drop Loader -->
    <div id="loading">
      <div class="water-drop"></div>
      <p class="text-muted mt-3">Fetching the latest products...</p>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle shadow-sm">
        <thead>
          <tr>
            <th>ID</th>
            <th>Product</th>
            <th>Stock</th>
            <th>Price (₹)</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody id="productTableBody">
          <!-- Data will load dynamically -->
        </tbody>
      </table>
    </div>

    <div class="text-center mt-4">
      <button id="refreshBtn"><i class="fa-solid fa-rotate"></i> Refresh</button>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    async function fetchProducts() {
      const tableBody = document.getElementById('productTableBody');
      const loader = document.getElementById('loading');
      loader.style.display = 'block';

      try {
        const response = await fetch('get_products.php');
        const data = await response.json();
        loader.style.display = 'none';

        tableBody.innerHTML = ''; // clear previous data

        if (data.success && data.products.length > 0) {
          data.products.forEach(product => {
            const row = `
              <tr>
                <td>${product.product_id}</td>
                <td>${product.product_name}</td>
                <td>${product.product_count}</td>
                <td>₹${product.product_price}</td>
                <td>
                  <span class="${product.product_active == 1 ? 'status-active' : 'status-inactive'}">
                    ${product.product_active == 1 ? 'Active' : 'Inactive'}
                  </span>
                </td>
              </tr>`;
            tableBody.insertAdjacentHTML('beforeend', row);
          });
        } else {
          tableBody.innerHTML = '<tr><td colspan="5" class="text-muted text-center">No products found.</td></tr>';
        }
      } catch (error) {
        loader.style.display = 'none';
        tableBody.innerHTML = '<tr><td colspan="5" class="text-danger text-center">Error loading data.</td></tr>';
        console.error('Error fetching products:', error);
      }
    }

    // Run once on page load
    window.onload = fetchProducts;

    // Auto-refresh every 5 seconds
    setInterval(fetchProducts, 5000);

    // Manual refresh button
    document.getElementById('refreshBtn').addEventListener('click', fetchProducts);
  </script>
</body>

</html>