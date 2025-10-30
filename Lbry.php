<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php
session_start();

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle login
if (isset($_POST['username']) && isset($_POST['password'])) {
    if ($_POST['username'] === 'admin' && $_POST['password'] === 'admin123') {
        $_SESSION['loggedin'] = true;
    } else {
        echo "<script>alert('Invalid username or password!');</script>";
    }
}

if (!isset($_SESSION['loggedin'])):
?>

<!-- LOGIN SECTION -->
<div class="login-section">
    <h2>Admin Login</h2>
    <form method="POST" action="">
        <input type="text" name="username" placeholder="Username" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <input type="submit" value="Login">
    </form>
</div>

<?php else: ?>

<?php
// Database Connection
$servername = "127.0.0.1:3307";
$username = "root";
$password = "";
$dbname = "library";


$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Create table if not exists
$conn->query("
    CREATE TABLE IF NOT EXISTS books (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        author VARCHAR(255) NOT NULL,
        sold_out DATETIME DEFAULT NULL
    )
");

// Add Book
if (isset($_POST['addBook'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $sql = "INSERT INTO books (title, author) VALUES ('$title', '$author')";
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('New book added successfully!');</script>";
    } else {
        echo "<script>alert('Error adding book.');</script>";
    }
}

// Mark as Sold Out
if (isset($_POST['removeBook'])) {
    $title = $_POST['title'];
    $sql = "UPDATE books SET sold_out = NOW() WHERE title='$title'";
    if ($conn->query($sql) && $conn->affected_rows > 0) {
        echo "<script>alert('Book marked as sold out with timestamp.');</script>";
    } else {
        echo "<script>alert('Book not found.');</script>";
    }
}
?>

<!-- NAVIGATION -->
<nav>
    <button onclick="showSection('addSection')">Add Book</button>
    <button onclick="showSection('removeSection')">Sold Out</button>
    <button onclick="showSection('searchSection')">Search Book</button>
    <button onclick="showSection('viewSection')">View All Books</button>
    <form method="POST" class="logout-form">
        <input type="hidden" name="logout" value="true">
        <input type="submit" value="Logout">
    </form>
</nav>

<!-- ADD BOOK SECTION -->
<section id="addSection" class="active">
    <h2>Add New Book</h2>
    <form method="POST">
        <input type="text" name="title" placeholder="Book Title" required><br><br>
        <input type="text" name="author" placeholder="Author Name" required><br><br>
        <input type="submit" name="addBook" value="Add Book">
    </form>
</section>

<!-- SOLD OUT SECTION -->
<section id="removeSection">
    <h2>Mark Book as Sold Out</h2>
    <form method="POST">
        <input type="text" name="title" placeholder="Book Title" required><br><br>
        <input type="submit" name="removeBook" value="Sold Out">
    </form>
</section>

<!-- SEARCH SECTION -->
<section id="searchSection">
    <h2>Search Book</h2>
    <form method="GET">
        <input type="text" name="query" placeholder="Enter title or author" required>
        <input type="submit" value="Search">
    </form>

    <div class="book-list">
        <?php
        if (isset($_GET['query'])) {
            $query = $_GET['query'];
            $sql = "SELECT * FROM books WHERE (title LIKE '%$query%' OR author LIKE '%$query%')";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                echo "<ul>";
                while ($row = $result->fetch_assoc()) {
                    $status = $row['sold_out'] ? "<span class='sold-out'>Sold Out on " . $row['sold_out'] . "</span>" : "<span class='available'>Available</span>";
                    echo "<li><strong>Title:</strong> {$row['title']} — <em>{$row['author']}</em> — {$status}</li>";
                }
                echo "</ul>";
            } else {
                echo "<p class='not-found'>Book not available!</p>";
            }
        }
        ?>
    </div>
</section>

<!-- VIEW ALL BOOKS SECTION -->
<section id="viewSection">
    <h2>All Books</h2>
    <div class="book-list">
        <?php
        $sql = "SELECT * FROM books";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            echo "<table>";
            echo "<tr><th>Title</th><th>Author</th><th>Status</th><th>Stock Out Date</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$row['title']}</td>";
                echo "<td>{$row['author']}</td>";
                if ($row['sold_out']) {
                    echo "<td class='sold-out'>Sold Out</td>";
                    echo "<td>{$row['sold_out']}</td>";
                } else {
                    echo "<td class='available'>Available</td>";
                    echo "<td>—</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No books in the library yet.</p>";
        }
        ?>
    </div>
</section>

<script>
function showSection(id) {
    document.querySelectorAll("section").forEach(sec => sec.classList.remove("active"));
    document.getElementById(id).classList.add("active");
}
</script>

<?php endif; ?>

</body>
</html>
