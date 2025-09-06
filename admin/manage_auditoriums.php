<?php
session_start();
require_once '../config.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to generate chessboard-style seat map
function generateSeatMap($rows, $cols) {
    $map = [];
    $letters = range('A', 'Z');
    
    for ($i = 0; $i < $rows; $i++) {
        for ($j = 1; $j <= $cols; $j++) {
            $seatId = $letters[$i] . $j;
            $map[$seatId] = [
                'type' => 'regular',
                'status' => 'available'
            ];
        }
    }
    return $map;
}

// Handle auditorium deletion
if (isset($_GET['delete'])) {
    $auditorium_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM auditoriums WHERE auditorium_id = ?");
    $stmt->bind_param("i", $auditorium_id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Auditorium deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting auditorium: " . $conn->error;
    }
    $stmt->close();
    header('Location: manage_auditoriums.php');
    exit;
}

// Handle form submission for adding/editing auditoriums
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $auditorium_id = isset($_POST['auditorium_id']) ? (int)$_POST['auditorium_id'] : 0;
    $cinema_id = (int)$_POST['cinema_id'];
    $name = $conn->real_escape_string($_POST['name']);
    
    // Process seat map configuration
    $rows = (int)$_POST['rows'];
    $cols = (int)$_POST['cols'];
    $capacity = $rows * $cols;
    $seat_map = json_encode([
        'rows' => $rows,
        'cols' => $cols,
        'map' => generateSeatMap($rows, $cols)
    ]);

    if ($auditorium_id > 0) {
        // Update existing auditorium
        $stmt = $conn->prepare("UPDATE auditoriums SET cinema_id = ?, name = ?, capacity = ?, seat_map = ? WHERE auditorium_id = ?");
        $stmt->bind_param("isisi", $cinema_id, $name, $capacity, $seat_map, $auditorium_id);
        $message = "Auditorium updated successfully";
    } else {
        // Insert new auditorium
        $stmt = $conn->prepare("INSERT INTO auditoriums (cinema_id, name, capacity, seat_map) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isis", $cinema_id, $name, $capacity, $seat_map);
        $message = "Auditorium added successfully";
    }
    
    if ($stmt->execute()) {
        $_SESSION['message'] = $message;
    } else {
        $_SESSION['error'] = "Error saving auditorium: " . $conn->error;
    }
    $stmt->close();
    header('Location: manage_auditoriums.php');
    exit;
}

// Get all auditoriums with related data
$auditoriums = $conn->query("
    SELECT a.auditorium_id, a.name as auditorium_name, a.capacity, a.seat_map,
           c.name as cinema_name
    FROM auditoriums a
    JOIN cinemas c ON a.cinema_id = c.cinema_id
    ORDER BY c.name, a.name");

// Get auditorium for editing if ID is provided
$edit_auditorium = null;
if (isset($_GET['edit'])) {
    $auditorium_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM auditoriums WHERE auditorium_id = ?");
    $stmt->bind_param("i", $auditorium_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $edit_auditorium = $result->fetch_assoc();
        // Decode seat map for editing
        if ($edit_auditorium['seat_map']) {
            $seat_map = json_decode($edit_auditorium['seat_map'], true);
            $edit_auditorium['rows'] = $seat_map['rows'] ?? 10;
            $edit_auditorium['cols'] = $seat_map['cols'] ?? 15;
        }
    } else {
        $_SESSION['error'] = "Auditorium not found";
    }
    $stmt->close();
}

// Include header
include __DIR__ . '/header.php';

// Get cinemas for dropdowns
$cinemas = $conn->query("SELECT cinema_id, name FROM cinemas ORDER BY name");
?>

<div class="admin-container">
    <?php include __DIR__ . '/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1 class="admin-title">Manage Auditoriums</h1>
            <button onclick="document.getElementById('auditorium-form').style.display='block'" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Auditorium
            </button>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message">
                <?= $_SESSION['message'] ?>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error">
                <?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Auditorium Form (hidden by default) -->
        <div id="auditorium-form" class="auditorium-form" style="<?= $edit_auditorium ? 'display:block' : 'display:none' ?>">
            <h2><?= $edit_auditorium ? 'Edit Auditorium' : 'Add New Auditorium' ?></h2>
            <form method="POST" action="manage_auditoriums.php" id="auditoriumForm">
                <?php if ($edit_auditorium): ?>
                    <input type="hidden" name="auditorium_id" value="<?= $edit_auditorium['auditorium_id'] ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="cinema_id">Cinema</label>
                        <select id="cinema_id" name="cinema_id" class="form-control" required>
                            <option value="">Select Cinema</option>
                            <?php 
                            $cinemas->data_seek(0);
                            while ($cinema = $cinemas->fetch_assoc()): ?>
                            <option value="<?= $cinema['cinema_id'] ?>" 
                                <?= $edit_auditorium && $edit_auditorium['cinema_id'] == $cinema['cinema_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cinema['name']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" class="form-control"
                               value="<?= $edit_auditorium ? htmlspecialchars($edit_auditorium['name']) : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="rows">Rows</label>
                        <input type="number" id="rows" name="rows" class="form-control" 
                               min="1" max="26" value="<?= $edit_auditorium ? ($edit_auditorium['rows'] ?? 10) : 10 ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="cols">Seats per Row</label>
                        <input type="number" id="cols" name="cols" class="form-control" 
                               min="1" max="50" value="<?= $edit_auditorium ? ($edit_auditorium['cols'] ?? 15) : 15 ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="capacity">Capacity</label>
                        <input type="number" id="capacity" name="capacity" class="form-control" readonly
                               value="<?= $edit_auditorium ? $edit_auditorium['capacity'] : '' ?>">
                    </div>
                </div>
                
                <!-- Seat Map Preview -->
                <div class="seat-map-preview">
                    <h3>Seat Map Preview</h3>
                    <div class="screen">SCREEN</div>
                    <div id="seatMapContainer"></div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" 
                            onclick="document.getElementById('auditorium-form').style.display='none'">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <?= $edit_auditorium ? 'Update Auditorium' : 'Add Auditorium' ?>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Auditoriums List -->
        <table class="data-table">
            <thead>
                <tr>
                    <th>Cinema</th>
                    <th>Name</th>
                    <th>Capacity</th>
                    <th>Seat Map</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($auditorium = $auditoriums->fetch_assoc()): 
                    $seat_map = $auditorium['seat_map'] ? json_decode($auditorium['seat_map'], true) : null;
                ?>
                <tr>
                    <td><?= htmlspecialchars($auditorium['cinema_name']) ?></td>
                    <td><?= htmlspecialchars($auditorium['auditorium_name']) ?></td>
                    <td><?= $auditorium['capacity'] ?></td>
                    <td>
                        <?php if ($seat_map): ?>
                            <?= $seat_map['rows'] ?> rows × <?= $seat_map['cols'] ?> seats
                        <?php else: ?>
                            Not configured
                        <?php endif; ?>
                    </td>
                    <td class="actions">
                        <a href="manage_auditoriums.php?edit=<?= $auditorium['auditorium_id'] ?>" class="btn btn-outline">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="manage_auditoriums.php?delete=<?= $auditorium['auditorium_id'] ?>" class="btn btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this auditorium?')">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add JavaScript for seat map preview -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update seat map preview when rows/cols change
    const rowsInput = document.getElementById('rows');
    const colsInput = document.getElementById('cols');
    const capacityInput = document.getElementById('capacity');
    const seatMapContainer = document.getElementById('seatMapContainer');
    
    function updateSeatMap() {
        const rows = parseInt(rowsInput.value) || 0;
        const cols = parseInt(colsInput.value) || 0;
        const capacity = rows * cols;
        
        // Update capacity
        capacityInput.value = capacity;
        
        // Generate seat map HTML
        let html = '<div class="seat-map">';
        const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');
        
        for (let i = 0; i < rows && i < letters.length; i++) {
            html += `<div class="seat-row"><span class="row-label">${letters[i]}</span>`;
            for (let j = 1; j <= cols; j++) {
                html += `<div class="seat" data-seat="${letters[i]}${j}"></div>`;
            }
            html += '</div>';
        }
        
        html += '</div>';
        seatMapContainer.innerHTML = html;
    }
    
    // Initial update
    updateSeatMap();
    
    // Add event listeners
    rowsInput.addEventListener('change', updateSeatMap);
    colsInput.addEventListener('change', updateSeatMap);
    rowsInput.addEventListener('input', updateSeatMap);
    colsInput.addEventListener('input', updateSeatMap);
    
    // If editing, scroll to form
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('edit')) {
        document.getElementById('auditorium-form').scrollIntoView({ behavior: 'smooth' });
    }
});
</script>

<style>
.seat-map-preview {
    margin: 20px 0;
    padding: 15px;
    background: #f5f5f5;
    border-radius: 5px;
}

.screen {
    text-align: center;
    padding: 10px;
    margin-bottom: 20px;
    background: #333;
    color: white;
    font-weight: bold;
    border-radius: 3px;
}

.seat-map {
    display: flex;
    flex-direction: column;
    gap: 10px;
    align-items: center;
}

.seat-row {
    display: flex;
    gap: 5px;
    align-items: center;
}

.row-label {
    width: 20px;
    text-align: center;
    font-weight: bold;
    font-size: 12px;
}

.seat {
    width: 25px;
    height: 25px;
    background: #4CAF50;
    border-radius: 3px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    color: white;
}

.seat:hover {
    background: #45a049;
}

.message {
    background: #d4edda;
    color: #155724;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 4px;
}

.error {
    background: #f8d7da;
    color: #721c24;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 4px;
}
</style>

<?php
// Close database connection
$conn->close();
?>