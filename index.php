<?php
$koneksi = mysqli_connect('localhost', 'root', '', 'ukk2025_todolist');

// Tambah task
if (isset($_POST['add_task'])) {
    $task = $_POST['task'];
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'];

    if (!empty($task) && !empty($priority) && !empty($due_date)) {
        mysqli_query($koneksi, "INSERT INTO task VALUES ('', '$task', '$priority', '$due_date', '0')");
        echo "<script>alert('Task berhasil ditambahkan'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Task gagal ditambahkan'); window.location.href='index.php';</script>";
    }
}

// Update status task menjadi selesai
if (isset($_GET['complete'])) {
    $id = $_GET['complete'];
    mysqli_query($koneksi, "UPDATE task SET status = '1' WHERE id = '$id'");
    echo "<script>alert('Task berhasil diselesaikan'); window.location.href='index.php';</script>";
}

// Hapus task
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($koneksi, "DELETE FROM task WHERE id = '$id'");
    echo "<script>alert('Task berhasil dihapus'); window.location.href='index.php';</script>";
}

// Menampilkan daftar task
$result = mysqli_query($koneksi, "SELECT * FROM task ORDER BY status ASC, priority DESC, due_date ASC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To Do List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css"> <!-- Panggil CSS Eksternal -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

</head>

<body>
    <div class="container">
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
                <input type="date" name="due_date" class="form-control" required>

                <button class="btn-submit" name="add_task">Submit</button>
            </form>
        </div>

        <!-- Daftar Tugas -->
        <div class="task-list">
            <p class="date-today">Today, <?php echo date("F j, Y"); ?></p>
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) { ?>
                    <div class="task-item">
                        <!-- Status Task -->
                        <a href="?complete=<?php echo $row['id']; ?>">
                            <i class="task-icon <?php echo $row['status'] ? 'fas fa-check-circle check' : 'far fa-circle uncheck'; ?>"></i>
                        </a>

                        <!-- Nama Task -->
                        <span><?php echo $row['task']; ?></span>

                        <!-- Tombol Hapus -->
                        <a href="?delete=<?php echo $row['id']; ?>">
                            <i class="fas fa-trash trash-icon"></i>
                        </a>
                    </div>
            <?php
                }
            } 
            ?>
        </div>
    </div>
</body>

</html>