<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

require_once '../api/db.php';
$message = '';

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Handle Delete
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM laureates WHERE id = ?");
    if ($stmt->execute([$id])) {
        $message = "Laureate deleted successfully.";
    }
}

// Handle Add/Edit
if (isset($_POST['action']) && ($_POST['action'] === 'add' || $_POST['action'] === 'edit')) {
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $year = trim($_POST['year']);
    $description = trim($_POST['description']);
    $image_url = trim($_POST['image_url']);

    if ($name && $category && $year && $description && $image_url) {
        if ($_POST['action'] === 'add') {
            $stmt = $pdo->prepare("INSERT INTO laureates (name, category, year, description, image_url) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $category, $year, $description, $image_url]);
            $message = "Laureate added successfully.";
        } else {
            $stmt = $pdo->prepare("UPDATE laureates SET name=?, category=?, year=?, description=?, image_url=? WHERE id=?");
            $stmt->execute([$name, $category, $year, $description, $image_url, $id]);
            $message = "Laureate updated successfully.";
        }
    } else {
        $message = "All fields are required.";
    }
}

// Fetch all laureates
$stmt = $pdo->query("SELECT * FROM laureates ORDER BY year DESC, id DESC");
$laureates = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | IGA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
        .navbar-admin { background-color: #0B1F3A; }
        .table-wrap { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<nav class="navbar navbar-dark navbar-admin mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#" style="font-family: 'Playfair Display', serif; color: #D4AF37;">IGA Admin Panel</a>
        <div>
            <a href="../winners.html" target="_blank" class="btn btn-outline-light btn-sm me-2">View Site</a>
            <a href="?logout=1" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Manage Laureates</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#laureateModal" onclick="resetForm()">
            <i class="bi bi-plus-lg"></i> Add Laureate
        </button>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="table-wrap table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Year</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($laureates as $l): ?>
                <tr>
                    <td><?= $l['id'] ?></td>
                    <td><img src="<?= htmlspecialchars($l['image_url']) ?>" alt="img" width="40" height="40" class="rounded-circle object-fit-cover"></td>
                    <td class="fw-bold"><?= htmlspecialchars($l['name']) ?></td>
                    <td class="text-capitalize"><?= htmlspecialchars($l['category']) ?></td>
                    <td><?= htmlspecialchars($l['year']) ?></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-secondary" onclick="editLaureate(<?= htmlspecialchars(json_encode($l)) ?>)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this laureate?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $l['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($laureates)): ?>
                <tr>
                    <td colspan="6" class="text-center text-white py-4">No laureates found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="laureateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="modalTitle">Add Laureate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="id" id="formId" value="">
                    
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="name" id="formName" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category" id="formCategory" required>
                                <option value="innovation">Innovation</option>
                                <option value="leadership">Leadership</option>
                                <option value="impact">Social Impact</option>
                                <option value="arts">Arts & Culture</option>
                                <option value="environment">Environmental</option>
                                <option value="education">Education</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Year</label>
                            <input type="number" class="form-control" name="year" id="formYear" required min="2000" max="2100">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Image URL</label>
                        <input type="url" class="form-control" name="image_url" id="formImageUrl" placeholder="https://..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description / Achievement</label>
                        <textarea class="form-control" name="description" id="formDescription" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Laureate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const modal = new bootstrap.Modal(document.getElementById('laureateModal'));
    
    function resetForm() {
        document.getElementById('modalTitle').innerText = 'Add Laureate';
        document.getElementById('formAction').value = 'add';
        document.getElementById('formId').value = '';
        document.getElementById('formName').value = '';
        document.getElementById('formCategory').value = 'innovation';
        document.getElementById('formYear').value = new Date().getFullYear();
        document.getElementById('formImageUrl').value = '';
        document.getElementById('formDescription').value = '';
    }

    function editLaureate(data) {
        document.getElementById('modalTitle').innerText = 'Edit Laureate';
        document.getElementById('formAction').value = 'edit';
        document.getElementById('formId').value = data.id;
        document.getElementById('formName').value = data.name;
        document.getElementById('formCategory').value = data.category;
        document.getElementById('formYear').value = data.year;
        document.getElementById('formImageUrl').value = data.image_url;
        document.getElementById('formDescription').value = data.description;
        modal.show();
    }
</script>
</body>
</html>
