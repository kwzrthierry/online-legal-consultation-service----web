<?php
session_start();
if (!isset($_SESSION['user_info']['username'])) {
    header('Location: ../../login/home.html');
    exit;
}

$username = $_SESSION['user_info']['username'];
$conn = mysqli_connect('localhost', 'root', '', 'olcs');

$query = "SELECT referenced_id FROM users WHERE username = '$username'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$referenced_id = $row['referenced_id'];

$query = "SELECT first_name, last_name FROM clients WHERE client_id = '$referenced_id'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$firstname = $row['first_name'];
$lastname = $row['last_name'];
$fullname = $firstname . " " . $lastname;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['upload'])) {
        $document_name = mysqli_real_escape_string($conn, $_POST['document_name']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $file = $_FILES['file'];

        $allowed_types = ['pdf', 'docx', 'xlsx'];
        $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);

        if (in_array($file_ext, $allowed_types)) {
            $location = 'documents/' . basename($file['name']);
            if (move_uploaded_file($file['tmp_name'], $location)) {
                $query = "INSERT INTO document (client_id, document_name, description, location) VALUES ('$referenced_id', '$document_name', '$description', '$location')";
                if (mysqli_query($conn, $query)) {
                    echo "<script>alert('Document uploaded successfully');</script>";
                    header("Location: documents.php");
                    exit();
                } else {
                    echo "<script>alert('Error uploading document');</script>";
                }
            } else {
                echo "<script>alert('Error moving file');</script>";
            }
        } else {
            echo "<script>alert('Invalid file type');</script>";
        }
    } elseif (isset($_POST['delete'])) {
        $document_id = $_POST['document_id'];

        $query = "SELECT location FROM document WHERE document_id = '$document_id' AND client_id = '$referenced_id'";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);

        if ($row) {
            // Document exists in the database, proceed with deletion
            $location = $row['location'];

            // Delete from the database first
            $delete_query = "DELETE FROM document WHERE document_id = '$document_id'";
            if (mysqli_query($conn, $delete_query)) {
                // Deletion from the database successful, now delete from local disk
                if (unlink($location)) {
                    // File deletion from local disk successful
                    echo "<script>alert('Document deleted successfully');</script>";
                } else {
                    // File deletion from local disk failed
                    echo "<script>alert('Error deleting file from disk');</script>";
                }
            } else {
                // Error deleting document from the database
                echo "<script>alert('Error deleting document from database');</script>";
            }
        } else {
            // Document not found in the database
            echo "<script>alert('Document not found');</script>";
        }
    }

}

$documents_query = "SELECT * FROM document WHERE client_id = '$referenced_id'";
$documents_result = mysqli_query($conn, $documents_query);
$documents = [];
while ($row = mysqli_fetch_assoc($documents_result)) {
    $documents[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legal Services Platform</title>
    <link rel="stylesheet" type="text/css" href="document.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <h2><span style="color: #1aa3ff;">O -</span>LCS Platform</h2>
            </div>
            <ul class="nav-links">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="documents.php" class="nav-link">Documents</a></li>
                <li><a href="chat.php" class="nav-link">Messages</a></li>
                <li><a href="profile.php" class="nav-link">Profile</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="left">
            <h2>Upload Document</h2>
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="document_name">Document Name</label>
                    <input type="text" id="document_name" name="document_name" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="file">Choose File</label>
                    <input type="file" id="file" name="file" required>
                    <small>Allowed types: pdf, docx, xlsx</small>
                </div>
                <button type="submit" name="upload">Upload Document</button>
            </form>
        </div>

        <div class="right">
            <h2>Manage Documents</h2>
            <?php if (empty($documents)): ?>
                <p>No documents available</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>Document Name</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                    <?php foreach ($documents as $document): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($document['document_name']); ?></td>
                            <td><?php echo htmlspecialchars($document['description']); ?></td>
                            <td>
                                <button onclick="viewDocument('<?php echo $document['location']; ?>')">View</button>
                                <form method="post" onsubmit="return confirm('Are you sure you want to delete this document?');">
                                    <input type="hidden" name="document_id" value="<?php echo $document['document_id']; ?>">
                                    <button type="submit" name="delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>


        <div id="popup" class="popup">
            <div class="popup-content">
                <span id="popup-close">&times;</span>
                <iframe id="document-viewer" src="" width="80%" height="80%"></iframe>
            </div>
        </div>


    <button class="logout-btn" onclick="logout()">Logout</button>
    <footer>
        <p class="footer-copyright">&copy; 2024 O-Lcs Platform. All rights reserved.</p>
    </footer>
    <script>
        function viewDocument(location) {
            const popup = document.getElementById('popup');
            const viewer = document.getElementById('document-viewer');
            popup.style.display = 'block';
            viewer.src = location;
        }

        document.getElementById('popup-close').onclick = function() {
            document.getElementById('popup').style.display = 'none';
            document.getElementById('document-viewer').src = '';
        };

        window.onclick = function(event) {
            const popup = document.getElementById('popup');
            if (event.target == popup) {
                popup.style.display = 'none';
                document.getElementById('document-viewer').src = '';
            }
        };

        function logout() {
            const confirmLogout = confirm("Are you sure you want to logout?");
            if (confirmLogout) {
                const xhr = new XMLHttpRequest();
                xhr.open('GET', '../logout.php', true);
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        window.location.href = "../../index.html";
                    } else {
                        console.error('Error:', xhr.statusText);
                    }
                };
                xhr.onerror = function () {
                    console.error('Network error');
                };
                xhr.send();
            }
        }

        window.addEventListener('scroll', () => {
            const header = document.querySelector('header');
            header.classList.toggle('scrolled', window.scrollY > 0);
        });
    </script>
</body>
</html>
