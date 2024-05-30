<?php
session_start();
if (!isset($_SESSION['user_info']['username'])) {
    header('Location: ../../login/home.html');
    exit;
}

$username = $_SESSION['user_info']['username'];
$conn = new mysqli('localhost', 'root', '', 'olcs');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch client_id based on username
$query = "SELECT referenced_id FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $client_id = $row['referenced_id'];
} else {
    echo "<script>alert('Error fetching client ID.');</script>";
    exit;
}

$stmt->close();

// Function to get lawyer_id based on document type
function getLawyerId($conn, $documentType) {
    $query = "SELECT lawyer_id FROM lawyers WHERE department = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $documentType);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['lawyer_id'];
    } else {
        return 5; // Default to generalist department if no specific lawyer found
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['prepareDocument'])) {
        $documentType = $_POST['documentType'];
        $request_desc = "Preparation of " . $documentType;
        $lawyer_id = getLawyerId($conn, $documentType);

        $stmt = $conn->prepare("INSERT INTO request (client_id, lawyer_id, document_name, request) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $client_id, $lawyer_id, $documentType, $request_desc);

        if ($stmt->execute()) {
            echo "<script>alert('Document type $documentType is requested for preparation and recorded in the database.');</script>";
        } else {
            echo "<script>alert('Error recording request in the database: " . $stmt->error . "');</script>";
        }

        $stmt->close();
    }

    if (isset($_POST['reviewDocument'])) {
        if (isset($_FILES['documentUpload']) && $_FILES['documentUpload']['size'] > 0) {
            $fileTmpPath = $_FILES['documentUpload']['tmp_name'];
            $fileName = $_FILES['documentUpload']['name'];
            $uploadDir = 'documents/';
            $dest_path = $uploadDir . $fileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $stmt = $conn->prepare("INSERT INTO document (client_id, document_name, description, location) VALUES (?, ?, ?, ?)");
                $description = "Uploaded document for review";
                $stmt->bind_param("isss", $client_id, $fileName, $description, $dest_path);

                if ($stmt->execute()) {
                    $lawyer_id = 5; // Generalist department
                    $stmt = $conn->prepare("INSERT INTO request (client_id, lawyer_id, document_name, request) VALUES (?, ?, ?, ?)");
                    $request_desc = "Review of " . $fileName;
                    $stmt->bind_param("iiss", $client_id, $lawyer_id, $fileName, $request_desc);

                    if ($stmt->execute()) {
                        echo "<script>alert('The consultant will get to it and reach out later. File uploaded and recorded in the database successfully.');</script>";
                    } else {
                        echo "<script>alert('Error recording request in the database: " . $stmt->error . "');</script>";
                    }
                } else {
                    echo "<script>alert('Error recording document in the database: " . $stmt->error . "');</script>";
                }

                $stmt->close();
            } else {
                echo "<script>alert('There was an error uploading the file.');</script>";
            }
        } else {
            $stmt = $conn->prepare("SELECT document_name FROM document WHERE client_id = ?");
            $stmt->bind_param("i", $client_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $documents = [];
                while ($row = $result->fetch_assoc()) {
                    $documents[] = $row['document_name'];
                }

                $documentsList = implode("\\n", $documents);
                echo "<script>alert('Select a file or choose from existing documents:\\n$documentsList');</script>";
            } else {
                echo "<script>alert('No files in the database. Please choose a file first.');</script>";
            }

            $stmt->close();
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prepare Document</title>
    <link rel="stylesheet" type="text/css" href="index.css">
    <style>
        .form-container {
            display: flex;
            justify-content: space-between;
            margin: 20px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .form-section {
            width: 48%;
        }
        .form-section form {
            display: flex;
            flex-direction: column;
        }
        .form-section label,
        .form-section select,
        .form-section input[type="file"],
        .form-section button {
            margin-bottom: 10px;
            padding: 10px;
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-section button {
            background-color: #1aa3ff;
            color: white;
            border: none;
            cursor: pointer;
        }
        .form-section button:hover {
            background-color: #007acc;
        }
    </style>
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

    <div class="form-container">
        <div class="form-section">
            <form id="documentForm" action="" method="POST">
                <label for="documentType">Select Document Type:</label>
                <select name="documentType" id="documentType" required>
                    <option value="Contract">Contract</option>
                    <option value="Lease Agreement">Lease Agreement</option>
                    <option value="Employment Agreement">Employment Agreement</option>
                    <option value="Non-Disclosure Agreement (NDA)">Non-Disclosure Agreement (NDA)</option>
                    <option value="Power of Attorney">Power of Attorney</option>
                    <option value="Will">Will</option>
                    <option value="Partnership Agreement">Partnership Agreement</option>
                    <option value="Memorandum of Understanding (MOU)">Memorandum of Understanding (MOU)</option>
                    <option value="Corporate Resolution">Corporate Resolution</option>
                    <option value="Compliance Document">Compliance Document</option>
                </select>
                <button type="submit" name="prepareDocument">Request Prepared Document</button>
            </form>
        </div>

        <div class="form-section">
            <form id="reviewForm" action="" method="POST" enctype="multipart/form-data">
                <label for="documentUpload">Upload Document:</label>
                <input type="file" name="documentUpload" id="documentUpload">
                <button type="submit" name="reviewDocument">Request Review</button>
            </form>
        </div>
    </div>

    <button class="logout-btn" onclick="logout()">Logout</button>
    <footer>
        <p class="footer-copyright">&copy; 2024 O-Lcs Platform. All rights reserved.</p>
    </footer>

    <script>
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

        document.getElementById('reviewForm').addEventListener('submit', function (event) {
            const fileInput = document.getElementById('documentUpload');
            if (fileInput.files.length === 0) {
                alert('Please select a file to upload for review.');
                event.preventDefault();
            }
        });

        window.addEventListener('scroll', () => {
            const header = document.querySelector('header');
            header.classList.toggle('scrolled', window.scrollY > 0);
        });
    </script>
</body>
</html>
