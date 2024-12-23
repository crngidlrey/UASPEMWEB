<?php
session_start();
require_once 'koneksi.php';
require_once 'class/Book.php';

// Function to sanitize input
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Handle all process actions
if (isset($_POST['action'])) {
    $conn = connectDB();
    $action = $_POST['action'];

    switch ($action) {
        case 'register':
            handleRegistration($conn);
            break;
        case 'login':
            handleLogin($conn);
            break;
        case 'add_book':
            handleAddBook($conn);
            break;
        case 'edit_book':
            handleEditBook($conn);
            break;
        case 'delete_book':
            handleDeleteBook($conn);
            break;
        case 'logout':
            handleLogout();
            break;
        default:
            $_SESSION['error'] = "Invalid action";
            header("Location: login.php");
            break;
    }
    $conn->close();
}

// Handle user registration
function handleRegistration($conn) {
    $required_fields = [
        'nim', 'username', 'email', 'full_name', 'date_of_birth',
        'gender', 'phone_number', 'address', 'major', 'entry_year',
        'password', 'confirm_password'
    ];

    // Check required fields
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $_SESSION['error'] = "All fields are required";
            header("Location: register.php");
            exit();
        }
    }

    // Validate password match
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $_SESSION['error'] = "Passwords do not match";
        header("Location: register.php");
        exit();
    }

    // Sanitize input
    $nim = sanitize($_POST['nim']);
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $full_name = sanitize($_POST['full_name']);
    $date_of_birth = sanitize($_POST['date_of_birth']);
    $gender = sanitize($_POST['gender']);
    $phone_number = sanitize($_POST['phone_number']);
    $address = sanitize($_POST['address']);
    $major = sanitize($_POST['major']);
    $entry_year = sanitize($_POST['entry_year']);
    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if username or email already exists
    $check_query = "SELECT * FROM students WHERE username = ? OR email = ? OR nim = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("sss", $username, $email, $nim);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Username, email, or NIM already exists";
        header("Location: register.php");
        exit();
    }

    // Handle profile image upload
    $profile_image = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $target_dir = "uploads/profiles/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($file_extension, $allowed_types)) {
            $_SESSION['error'] = "Only JPG, JPEG, PNG & GIF files are allowed";
            header("Location: register.php");
            exit();
        }

        $profile_image = $target_dir . time() . '_' . basename($_FILES['profile_image']['name']);
        
        if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $profile_image)) {
            $_SESSION['error'] = "Error uploading profile image";
            header("Location: register.php");
            exit();
        }
    }

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO students (nim, username, email, full_name, date_of_birth, 
                           gender, phone_number, address, major, entry_year, password, profile_image) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("ssssssssssss", $nim, $username, $email, $full_name, $date_of_birth, 
                      $gender, $phone_number, $address, $major, $entry_year, $hashed_password, $profile_image);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Registration successful! Please login.";
        header("Location: login.php");
    } else {
        $_SESSION['error'] = "Registration failed: " . $stmt->error;
        header("Location: register.php");
    }
    $stmt->close();
}

// Handle user login
function handleLogin($conn) {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;

    $stmt = $conn->prepare("SELECT * FROM students WHERE username = ? OR nim = ? OR email = ?");
    $stmt->bind_param("sss", $username, $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['user_type'] = 'student';

            // Set remember me cookie if checked
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + (86400 * 30), "/"); // 30 days
                
                // Store token in database (you'll need to add a remember_token column)
                $update_stmt = $conn->prepare("UPDATE students SET remember_token = ? WHERE id = ?");
                $update_stmt->bind_param("si", $token, $user['id']);
                $update_stmt->execute();
            }

            header("Location: cms.php");
        } else {
            $_SESSION['error'] = "Invalid password";
            header("Location: login.php");
        }
    } else {
        $_SESSION['error'] = "User not found";
        header("Location: login.php");
    }
    $stmt->close();
}

// Handle adding new book
function handleAddBook($conn) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $book = new Book($conn);
    
    // Validate and sanitize input
    $required_fields = [
        'title', 'author', 'isbn', 'publication_year', 'genre',
        'publisher', 'pages', 'language', 'stock_quantity', 'location'
    ];

    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $_SESSION['error'] = "All required fields must be filled";
            header("Location: cms.php");
            exit();
        }
        $value = sanitize($_POST[$field]);
        $setter = 'set' . str_replace('_', '', ucwords($field, '_'));
        $book->$setter($value);
    }

    // Optional fields
    if (isset($_POST['edition'])) {
        $book->setEdition(sanitize($_POST['edition']));
    }
    if (isset($_POST['description'])) {
        $book->setDescription(sanitize($_POST['description']));
    }

    // Handle cover image upload
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
        if (!$book->uploadCoverImage($_FILES['cover_image'])) {
            $_SESSION['error'] = "Error uploading cover image";
            header("Location: cms.php");
            exit();
        }
    }

    if ($book->create()) {
        $_SESSION['success'] = "Book added successfully!";
    } else {
        $_SESSION['error'] = "Error adding book";
    }
    header("Location: cms.php");
}

// Handle editing book
function handleEditBook($conn) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    if (!isset($_POST['book_id'])) {
        $_SESSION['error'] = "Book ID is required";
        header("Location: cms.php");
        exit();
    }

    $book = new Book($conn);
    $book->setId(sanitize($_POST['book_id']));

    // Validate and sanitize input (similar to add book)
    $required_fields = [
        'title', 'author', 'isbn', 'publication_year', 'genre',
        'publisher', 'pages', 'language', 'stock_quantity', 'location'
    ];

    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $_SESSION['error'] = "All required fields must be filled";
            header("Location: cms.php");
            exit();
        }
        $value = sanitize($_POST[$field]);
        $setter = 'set' . str_replace('_', '', ucwords($field, '_'));
        $book->$setter($value);
    }

    // Optional fields
    if (isset($_POST['edition'])) {
        $book->setEdition(sanitize($_POST['edition']));
    }
    if (isset($_POST['description'])) {
        $book->setDescription(sanitize($_POST['description']));
    }

    // Handle cover image upload
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
        if (!$book->uploadCoverImage($_FILES['cover_image'])) {
            $_SESSION['error'] = "Error uploading cover image";
            header("Location: cms.php");
            exit();
        }
    }

    if ($book->update()) {
        $_SESSION['success'] = "Book updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating book";
    }
    header("Location: cms.php");
}

// Handle deleting book
function handleDeleteBook($conn) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    if (!isset($_POST['book_id'])) {
        $_SESSION['error'] = "Book ID is required";
        header("Location: cms.php");
        exit();
    }

    $book = new Book($conn);
    $book->setId(sanitize($_POST['book_id']));

    if ($book->delete()) {
        $_SESSION['success'] = "Book deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting book";
    }
    header("Location: cms.php");
}

// Handle logout
function handleLogout() {
    // Clear session
    session_start();
    session_unset();
    session_destroy();
    
    // Clear remember me cookie
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    header("Location: login.php");
    exit();
}
?>