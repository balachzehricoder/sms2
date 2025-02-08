<?php
include 'header.php';
include 'sidebar.php';
?>

<style>
    .button-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin: 12px 0;
    }

    button {
        flex: 1;
        padding: 14px;
        font-size: 15px;
        color: #ffffff;
        background-color: #007bff;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.3s ease;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    button:hover {
        background-color: #0056b3;
        transform: translateY(-2px);
    }

    button:nth-child(even) {
        background-color: #6c757d;
    }

    button:nth-child(even):hover {
        background-color: #495057;
    }

    @media (max-width: 600px) {
        .button-row {
            flex-direction: column;
        }
    }
</style>

<body>
    <div class="content-body">
        <div class="container-fluid">
            <h4 class="card-title">Fee Reports</h4>

            <?php
            // Define the buttons and their corresponding pages
            $buttonRows = [
                [
                    ['label' => 'Fee Receivable Report', 'url' => 'fee_receivable.php'],
                    ['label' => 'Summarized Fee Report', 'url' => 'summarized_fee.php'],
                ],
                [
                    ['label' => 'Fee Collection Reports', 'url' => 'fee_collection.php'],
                    ['label' => 'Monthly Discount Report', 'url' => 'monthly_discount.php'],
                ],
                [
                    ['label' => 'Special Student Report', 'url' => 'special_student.php'],
                    ['label' => 'Total Monthly Fee Report', 'url' => 'monthly_fee_report'],
                ],
                [
                    ['label' => 'Fee Settings', 'url' => 'fee_settings.php'],
                    ['label' => 'Other Report', 'url' => 'other_report.php'],
                ],
                [
                    ['label' => 'Monthly Advance Report', 'url' => 'monthly_advance.php'],
                    ['label' => 'View Student Installments', 'url' => 'student_installments.php'],
                ],
                [
                    ['label' => 'User Wise Profit & Loss Report', 'url' => 'profit_loss.php']
                ]
            ];
            ?>

            <!-- Loop through each row of buttons -->
            <?php foreach ($buttonRows as $row): ?>
                <div class="button-row">
                    <?php foreach ($row as $button): ?>
                        <button onclick="navigateTo('<?php echo $button['url']; ?>')">
                            <?php echo $button['label']; ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function navigateTo(url) {
            window.location.href = url;
        }
    </script>

    <?php include 'footer.php'; ?>
</body>