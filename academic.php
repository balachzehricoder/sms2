<?php
require_once 'confiq.php';
require_once 'header.php';
require_once 'sidebar.php';
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

        <!-- Breadcrumb -->
        <div class="row page-titles mx-0">
            <div class="col p-md-0">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0)">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">
                        <a href="javascript:void(0)">Examination &amp; Academic</a>
                    </li>
                </ol>
            </div>
        </div>
        <!-- End Breadcrumb -->

        <div class="container-fluid">
            <?php
            /**
             * Define buttons in an array for better maintainability.
             * Each sub-array represents one row of buttons.
             */
            $buttonRows = [
                [
                    ['label' => 'Create / View Exams',         'url' => 'exams.php'],
                    ['label' => 'Manage Subjects',             'url' => 'manage_subjects.php'],
                ],
                [
                    ['label' => 'Assign Subjects To Class',    'url' => 'class_subjects.php'],
                    ['label' => 'Teachers Subject Allocation', 'url' => 'teacher_subjects.php'],
                ],
                [
                    ['label' => 'Subject-Wise Grades',                 'url' => 'grades.php'],
                    ['label' => 'Assign Marks',             'url' => 'grade_settings.php'],
                ],
                [
                    ['label' => 'Academic Performance Criteria', 'url' => 'performance_criteria.php'],
                    ['label' => 'Academic Performance Heads',    'url' => 'performance_heads.php'],
                    ['label' => 'Teacher Performance',    'url' => 'teacher_performance'],
                ],
                [
                    ['label' => 'Add Academic Performance', 'url' => 'add_performance.php'],
                    ['label' => 'Signature Settings',         'url' => 'signature_settings.php'],
                ],
                [
                    ['label' => 'Students Marksheets', 'url' => 'student_marksheet'],
                    ['label' => 'Classwise Marksheets', 'url' => 'class_report'],
                ],
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
        // Reusable function for navigation
        function navigateTo(url) {
            window.location.href = url;
        }
    </script>

    <?php require_once 'footer.php'; ?>
</body>