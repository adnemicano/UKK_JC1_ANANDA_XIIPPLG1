<?php
$koneksi = mysqli_connect('localhost', 'root', '', 'ukk2025_todolist');


$search = isset($_GET['search']) ? $_GET['search'] : ''; // ✅ Mencegah Undefined Variable Warning

// Konfigurasi Pagination
$limit = 5; // Jumlah task per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Hitung total task
$total_query = "SELECT COUNT(*) AS total FROM task WHERE task LIKE '%$search%'";
$total_result = mysqli_query($koneksi, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_task = $total_row['total'];
$total_page = ceil($total_task / $limit);



// Tambah task dengan validasi tanggal
if (isset($_POST['add_task'])) {
    $task = $_POST['task'];
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'];
    $today = date("Y-m-d");

    if (!empty($task) && !empty($priority) && !empty($due_date)) {
        if ($due_date < $today) {
            echo "<script>alert('Tanggal tidak boleh sebelum hari ini!'); window.location.href='index.php';</script>";
            exit();
        }

        $query = "INSERT INTO task (task, priority, due_date, status) VALUES ('$task', '$priority', '$due_date', '0')";
        mysqli_query($koneksi, $query);
        echo "<script>alert('Task berhasil ditambahkan'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Task gagal ditambahkan'); window.location.href='index.php';</script>";
    }
}

// Update status task menjadi selesai
// Toggle status task (Undo Selesai ↔ Belum Selesai)
if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];

    // Ambil status saat ini
    $result = mysqli_query($koneksi, "SELECT status FROM task WHERE id = '$id'");
    $row = mysqli_fetch_assoc($result);

    $new_status = $row['status'] == 1 ? 0 : 1; // Jika 1 jadi 0, jika 0 jadi 1

    mysqli_query($koneksi, "UPDATE task SET status = '$new_status' WHERE id = '$id'");
    echo "<script>window.location.href='index.php';</script>";
}

// Edit task - Tampilkan form
$edit_id = isset($_GET['edit']) ? $_GET['edit'] : 0;
$task_to_edit = null;

if ($edit_id > 0) {
    $result = mysqli_query($koneksi, "SELECT * FROM task WHERE id = '$edit_id'");
    $task_to_edit = mysqli_fetch_assoc($result);
}

// Edit task - Proses update
if (isset($_POST['edit_task'])) {
    $id = $_POST['id'];
    $task = $_POST['task'];
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'];
    $today = date("Y-m-d");

    if (!empty($task) && !empty($priority) && !empty($due_date)) {
        if ($due_date < $today) {
            echo "<script>alert('Tanggal tidak boleh sebelum hari ini!'); window.location.href='index.php';</script>";
            exit();
        }

        $query = "UPDATE task SET task='$task', priority='$priority', due_date='$due_date' WHERE id='$id'";
        mysqli_query($koneksi, $query);
        echo "<script>alert('Task berhasil diupdate'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Task gagal diupdate'); window.location.href='index.php';</script>";
    }
}


// Hapus task
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($koneksi, "DELETE FROM task WHERE id = '$id'");
    echo "<script>alert('Task berhasil dihapus'); window.location.href='index.php';</script>";
}

// Filter & Search Query
$search = isset($_GET['search']) ? $_GET['search'] : '';
// Ubah bagian filter & search query
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'priority'; // Ubah default dari 'default' menjadi 'priority'

$query = "SELECT * FROM task WHERE task LIKE '%$search%'";

if ($filter == 'date') {
    $query .= " ORDER BY due_date ASC";
} elseif ($filter == 'priority') {
    $query .= " ORDER BY priority DESC"; // Priority high to low
} else {
    $query .= " ORDER BY status ASC, priority DESC"; // Tambahkan priority DESC untuk default sorting
}

$query .= " LIMIT $start, $limit"; // Tambahkan LIMIT untuk pagination
$result = mysqli_query($koneksi, $query);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To Do List</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>

<body>
    <div class="container">

        <!-- Form Edit (akan muncul hanya saat mode edit) -->
        <?php if ($task_to_edit): ?>
            <div class="task-form edit-mode">
                <p class="top-text">Edit Task</p>
                <form action="" method="post" class="form">
                    <input type="hidden" name="id" value="<?php echo $task_to_edit['id']; ?>">

                    <label class="form-label">Task</label>
                    <input type="text" name="task" class="form-control" value="<?php echo htmlspecialchars($task_to_edit['task']); ?>" required>

                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-control" required>
                        <option value="1" <?php echo $task_to_edit['priority'] == 1 ? 'selected' : ''; ?>>Low</option>
                        <option value="2" <?php echo $task_to_edit['priority'] == 2 ? 'selected' : ''; ?>>Medium</option>
                        <option value="3" <?php echo $task_to_edit['priority'] == 3 ? 'selected' : ''; ?>>High</option>
                    </select>

                    <label class="form-label">Tanggal</label>
                    <input type="date" name="due_date" class="form-control" value="<?php echo $task_to_edit['due_date']; ?>" required>

                    <button class="btn-submit" name="edit_task">Update Task</button>
                </form>
            </div>
        <?php endif; ?>
        <!-- Form Input -->
        <div class="task-form">
            <p class="top-text">Add a list</p>
            <form action="" method="post" class="form">
                <label class="form-label">Task</label>
                <input type="text" name="task" class="form-control" required>

                <label class="form-label">Priority</label>
                <select name="priority" class="form-control" required>
                    <option value="">--Pilih Prioritas--</option>
                    <option value="1">Low</option>
                    <option value="2">Medium</option>
                    <option value="3">High</option>
                </select>

                <label class="form-label">Tanggal</label>
                <input type="date" name="due_date" class="form-control" id="due_date" required>

                <button class="btn-submit" name="add_task">Submit</button>
            </form>
        </div>

        <!-- Daftar Tugas -->
        <div class="task-list">
            <div class="search-filter-container">
                <form action="" method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Search task..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>" class="search-box">
                    <select name="filter" class="filter-box" onchange="this.form.submit()">
                        <option value="default" <?php if ($filter == 'default') echo 'selected'; ?>>Default</option>
                        <option value="date" <?php if ($filter == 'date') echo 'selected'; ?>>Tanggal Terdekat</option>
                        <option value="priority" <?php if ($filter == 'priority') echo 'selected'; ?>>Prioritas Tertinggi</option>
                    </select>
                    <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                </form>

                <p class="date-today">Today, <?php echo date("F j, Y"); ?></p>
            </div>

            <!-- List Task -->
            <?php if (mysqli_num_rows($result) > 0) { ?>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <div class="task-item">
                        <!-- Kiri: Task & Status -->
                        <div class="task-left">
                            <a href="?toggle=<?php echo $row['id']; ?>">
                                <i class="task-icon <?php echo $row['status'] ? 'fas fa-check-circle check' : 'far fa-circle uncheck'; ?>"></i>
                            </a>
                            <span class="task-name"><?php echo $row['task']; ?></span>
                        </div>

                        <!-- Kanan: Tanggal & Priority -->
                        <!-- Kanan: Tanggal & Priority -->
                        <div class="task-right">
                            <?php
                            $due_date = strtotime($row['due_date']);
                            $today = strtotime(date("Y-m-d"));
                            $is_late = $due_date < $today;
                            ?>
                            <span class="task-date">
                                <?php echo date("d M Y", $due_date); ?>
                                <?php if ($is_late && $row['status'] == 0) { ?>
                                    <span class="text-danger">(Sudah Terlewat)</span>
                                <?php } ?>
                            </span>
                            <span class="task-priority priority-<?php echo strtolower($row['priority']); ?>">
                                <?php
                                echo ($row['priority'] == 1) ? 'Low' : (($row['priority'] == 2) ? 'Medium' : 'High');
                                ?>
                            </span>
                            <a href="?delete=<?php echo $row['id']; ?>">
                                <i class="fas fa-trash trash-icon"></i>
                            </a>

                            <a href="?edit=<?php echo $row['id']; ?>" class="edit-btn">
                                <i class="fas fa-edit edit-icon"></i>
                            </a>
                        </div>

                    </div>


                <?php } ?>
            <?php } ?>

            <!-- Pagination -->
            <div class="pagination">
                <?php if ($page > 1) { ?>
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>&filter=<?php echo $filter; ?>" class="page-link">❮ Prev</a>
                <?php } ?>

                <?php for ($i = 1; $i <= $total_page; $i++) { ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&filter=<?php echo $filter; ?>"
                        class="page-link <?php echo ($page == $i) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php } ?>

                <?php if ($page < $total_page) { ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>&filter=<?php echo $filter; ?>" class="page-link">Next ❯</a>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Task</label>
                            <input type="text" name="task" id="edit_task" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" id="edit_priority" class="form-control" required>
                                <option value="1">Low</option>
                                <option value="2">Medium</option>
                                <option value="3">High</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="due_date" id="edit_due_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="edit_task" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>




    <script>
        let today = new Date().toISOString().split('T')[0];
        document.getElementById("due_date").setAttribute("min", today);
    </script>

    <script>
        // Set minimum date untuk input tanggal
        document.addEventListener('DOMContentLoaded', function() {
            let today = new Date().toISOString().split('T')[0];
            let dueDateInputs = document.querySelectorAll('input[type="date"]');

            dueDateInputs.forEach(input => {
                input.setAttribute('min', today);
            });
        });
    </script>
</body>

</html>