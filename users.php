<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'fundas');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission (Add or Update)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $birthdate = $_POST['birthdate'];
    $location = $_POST['location'];
    $spouse = $_POST['spouse'];
    $story = $_POST['story'];
    $memories = $_POST['memories'];
    $message = $_POST['message'];
    $achivements = $_POST['achivements'] ?? ''; // Handle missing input

    if ($id) {
        // Update record
        $sql = "UPDATE users SET name=?, email=?, contact=?, birthdate=?, location=?, spouse=?, story=?, memories=?, message=?, achivements=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssssi", $name, $email, $contact, $birthdate, $location, $spouse, $story, $memories, $message, $achivements, $id);
    } else {
        // Insert new record
        $sql = "INSERT INTO users (name, email, contact, birthdate, location, spouse, story, memories, message, achivements) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssss", $name, $email, $contact, $birthdate, $location, $spouse, $story, $memories, $message, $achivements);
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'User saved successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $stmt->error]);
    }
    $stmt->close();
    exit();
}

// Handle Delete Request (Soft Delete)
if (isset($_GET['delete'])) {
    $deleteId = $_GET['delete'];
    $sql = "UPDATE users SET is_deleted=1 WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $deleteId);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'User deleted successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $stmt->error]);
    }
    $stmt->close();
    exit();
}
// Handle Edit Request
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    $sql = "SELECT * FROM users WHERE id = ? AND is_deleted = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $editRecord = $result->fetch_assoc();
        echo json_encode($editRecord);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User not found!']);
    }
    $stmt->close();
    exit();
}

// Fetch users (excluding soft-deleted)
$sql = "SELECT * FROM users WHERE is_deleted = 0";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
</head>
<body>
<div class="container mt-5">
    <h2>User Management</h2>
    <a href="logout.php" class="btn btn-danger mb-3">Logout</a>
    <button class="btn btn-primary mb-3" id="addUserButton">Add User</button>

    <div id="userFormContainer" style="display: none;">
   <!--  <button type="button" id="closeForm" class="btn btn-secondary mt-3 float-end">
    <i class="fas fa-times"></i> Close
    </button> -->

        <h4 id="formTitle">Add New User</h4>
        <form method="POST" id="userForm" action="">
            <input type="hidden" name="id" id="userId">
            <div class="row">
                <div class="col-md-6">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="contact">Contact</label>
                    <input type="text" id="contact" name="contact" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="birthdate">Birth Date</label>
                    <input type="date" id="birthdate" name="birthdate" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="spouse">Spouse</label>
                    <input type="text" id="spouse" name="spouse" class="form-control">
                </div>
                
                <div class="col-md-6">
                <label for="story">Story</label>
                <textarea id="story" name="story" class="form-control"></textarea>
            </div>
            <div class="col-md-6">
                <label for="memories">Memories</label>
                <textarea id="memories" name="memories" class="form-control"></textarea>
            </div>
            <div class="col-md-6">
                <label for="message">Message</label>
                <textarea id="message" name="message" class="form-control"></textarea>
            </div>
            <div class="col-md-6">
                <label for="achivements">Achievements</label>
                <textarea id="achivements" name="achivements" class="form-control"></textarea>
            </div>

            </div>
            <button type="submit" class="btn btn-primary mt-3" id="formSubmitBtn">Save</button>
            <button type="button" id="closeForm" class="btn btn-secondary mt-3">Close</button>
        </form>
    </div>

    <table id="usersTable" class="table table-bordered mt-5">
        <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Contact</th>
            <th>Birth Date</th>
            <th>Location</th>
            <th>Spouse</th>
            <th>Story</th>
            <th>Memories</th>
            <th>Message</th>
            <th>Achievements</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['name'] ?></td>
                <td><?= $row['email'] ?></td>
                <td><?= $row['contact'] ?></td>
                <td><?= $row['birthdate'] ?></td>
                <td><?= $row['location'] ?></td>
                <td><?= $row['spouse'] ?></td>
                <td><?= $row['story'] ?></td>
                <td><?= $row['memories'] ?></td>
                <td><?= $row['message'] ?></td>
                <td><?= $row['achivements'] ?></td>
                <td>
                    <button class="btn btn-warning btn-sm edit-user" data-id="<?= $row['id'] ?>">Edit</button>
                    <button class="btn btn-danger btn-sm delete-user" data-id="<?= $row['id'] ?>">Delete</button>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>


<script>
    $(document).ready(function () {
    // Initialize CKEditor for each textarea with a corresponding ID
    const editors = ['story', 'memories', 'message', 'achivements'];

    editors.forEach(function(field) {
        CKEDITOR.replace(field, {
            toolbar: [
                { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline'] },
                { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Alignment'] },
                { name: 'insert', items: ['CodeSnippet'] }
            ],
            removeButtons: '',
            removePlugins: 'table,forms',
            imageUploadUrl: '/path/to/upload/image', // Adjust the upload URL
            imageCrop: true,  // Enable image cropping
            codeSnippet_theme: 'default'
        });
    });


    $('#usersTable').DataTable();

    $('.delete-user').on('click', function () {
        const userId = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.get(`?delete=${userId}`, function (response) {
                    const result = JSON.parse(response);
                    Swal.fire({
                        title: result.status === 'success' ? 'Deleted!' : 'Error',
                        text: result.message,
                        icon: result.status
                    }).then(() => {
                        if (result.status === 'success') location.reload();
                    });
                });
            }
        });
    });

    $('#addUserButton').on('click', function () {
        $('#formTitle').text('Add New User');
        $('#userForm')[0].reset();
        $('#userId').val('');
        $('#userFormContainer').show();
    });

    $('.edit-user').on('click', function () {
        const userId = $(this).data('id');
        $.get(`?edit=${userId}`, function (response) {
            const user = JSON.parse(response);
            if (user.status === 'error') {
                Swal.fire('Error', user.message, 'error');
            } else {
                $('#formTitle').text('Edit User');
                $('#name').val(user.name);
                $('#email').val(user.email);
                $('#contact').val(user.contact);
                $('#birthdate').val(user.birthdate);
                $('#location').val(user.location);
                $('#spouse').val(user.spouse);
                CKEDITOR.instances.story.setData(user.story);
                CKEDITOR.instances.memories.setData(user.memories);
                CKEDITOR.instances.message.setData(user.message);
                CKEDITOR.instances.achivements.setData(user.achivements);
                $('#userId').val(user.id);
                $('#userFormContainer').show();
            }
        });
    });

    $('#closeForm').on('click', function () {
        $('#userFormContainer').hide();
    });

    // Handling the form submission with CKEditor data
    $('#userForm').on('submit', function (e) {
        e.preventDefault();

        // Retrieve data from CKEditor instances
        const story = CKEDITOR.instances.story.getData();
        const memories = CKEDITOR.instances.memories.getData();
        const message = CKEDITOR.instances.message.getData();
        const achivements = CKEDITOR.instances.achivements.getData();

        // Update form data with CKEditor data
        $('#story').val(story);
        $('#memories').val(memories);
        $('#message').val(message);
        $('#achivements').val(achivements);

        const formData = $(this).serialize();

        $.post('', formData, function (response) {
            const result = JSON.parse(response);
            Swal.fire({
                title: result.status === 'success' ? 'Success!' : 'Error',
                text: result.message,
                icon: result.status
            }).then(() => {
                if (result.status === 'success') location.reload();
            });
        });
    });
});

</script>
</body>
</html>
