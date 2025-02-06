<?php
include 'header.php';
include 'sidebar.php';
?>
<style>
    .button-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
    }
    
    .button {
        flex: 1 1 calc(25% - 10px);
        padding: 14px;
        font-size: 15px;
        color: #ffffff;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.3s ease;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        text-align: center;
    }
    
    .blue { background-color: #3b82f6; }
    .blue:hover { background-color: #2563eb; }
    
    .dark-blue { background-color: #1e3a8a; }
    .dark-blue:hover { background-color: #1e40af; }
    
    @media (max-width: 768px) {
        .button { flex: 1 1 calc(50% - 10px); }
    }
    
    @media (max-width: 480px) {
        .button { flex: 1 1 100%; }
    }
</style>

<body>
    <div class="content-body">
        <div class="container-fluid">
            <div class="button-container">
                <button class="button blue">Fee Receivable Report</button>
                <button class="button blue">Summarized Fee Report</button>
                <button class="button dark-blue">Fee Collection Reports</button>
                <button class="button blue">Monthly Discount Report</button>
                <button class="button dark-blue">Special Student Report</button>
                <button class="button dark-blue">Total Monthly Fee Report</button>
                <button class="button blue">Fee Settings</button>
                <button class="button dark-blue">Other Report</button>
                <button class="button dark-blue">Monthly Advance Report</button>
                <button class="button dark-blue">View Student Installments</button>
                <button class="button dark-blue">User Wise Profit & Loss Report</button>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
