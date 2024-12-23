<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <?php
    session_start();
    require_once 'koneksi.php';
    require_once 'class/Book.php';

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $conn = connectDB();
    $book = new Book($conn);
    $books = $book->readAll();
    ?>

    <div class="nav-header">
        <div>
            <h1>Library Management System</h1>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
        </div>
        <form action="proses.php" method="post" style="margin: 0;">
            <input type="hidden" name="action" value="logout">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>

    <div class="container">
        <?php
        if (isset($_SESSION['error'])) {
            echo '<div class="notification error">
                    <span>' . $_SESSION['error'] . '</span>
                    <span class="close" onclick="this.parentElement.style.display=\'none\'">&times;</span>
                  </div>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<div class="notification success">
                    <span>' . $_SESSION['success'] . '</span>
                    <span class="close" onclick="this.parentElement.style.display=\'none\'">&times;</span>
                  </div>';
            unset($_SESSION['success']);
        }
        ?>

        <button onclick="openAddModal()" style="max-width: 200px; margin-bottom: 2rem;">Add New Book</button>

        <table>
            <thead>
                <tr>
                    <th>Cover</th> <!-- Kolom baru untuk gambar -->
                    <th>Title</th>
                    <th>Author</th>
                    <th>ISBN</th>
                    <th>Genre</th>
                    <th>Stock</th>
                    <th>Location</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $books->fetch_assoc()): ?>
                <tr>
                    <td class="book-cover">
                        <?php if (!empty($row['cover_image'])): ?>
                        <img src="uploads/covers/<?php echo htmlspecialchars($row['cover_image']); ?>"
                            alt="Cover <?php echo htmlspecialchars($row['title']); ?>" class="cover-thumbnail">
                        <?php else: ?>
                        <div class="no-cover">No Cover</div>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['author']); ?></td>
                    <td><?php echo htmlspecialchars($row['isbn']); ?></td>
                    <td><?php echo htmlspecialchars($row['genre']); ?></td>
                    <td><?php echo htmlspecialchars($row['stock_quantity']); ?></td>
                    <td><?php echo htmlspecialchars($row['location']); ?></td>
                    <td class="actions">
                        <button class="btn-edit" onclick="openEditModal(<?php echo $row['id']; ?>)">Edit</button>
                        <button class="btn-delete" onclick="confirmDelete(<?php echo $row['id']; ?>)">Delete</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Book Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddModal()">&times;</span>
            <h2>Add New Book</h2>
            <form action="proses.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_book">

                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" name="title" required>
                </div>

                <div class="form-group">
                    <label for="author">Author</label>
                    <input type="text" name="author" required>
                </div>

                <div class="form-group">
                    <label for="isbn">ISBN</label>
                    <input type="text" name="isbn" required>
                </div>

                <div class="form-group">
                    <label for="publication_year">Publication Year</label>
                    <input type="number" name="publication_year" required min="1900" max="2099">
                </div>

                <div class="form-group">
                    <label for="genre">Genre</label>
                    <input type="text" name="genre" required>
                </div>

                <div class="form-group">
                    <label for="publisher">Publisher</label>
                    <input type="text" name="publisher" required>
                </div>

                <div class="form-group">
                    <label for="edition">Edition</label>
                    <input type="text" name="edition">
                </div>

                <div class="form-group">
                    <label for="pages">Pages</label>
                    <input type="number" name="pages" required min="1">
                </div>

                <div class="form-group">
                    <label for="language">Language</label>
                    <input type="text" name="language" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="cover_image">Cover Image</label>
                    <input type="file" name="cover_image" accept="image/*">
                </div>

                <div class="form-group">
                    <label for="stock_quantity">Stock Quantity</label>
                    <input type="number" name="stock_quantity" required min="0">
                </div>

                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" name="location" required>
                </div>

                <button type="submit">Add Book</button>
            </form>
        </div>
    </div>

    <!-- Edit Book Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Book</h2>
            <form action="proses.php" method="post" enctype="multipart/form-data" id="editForm">
                <input type="hidden" name="action" value="edit_book">
                <input type="hidden" name="book_id" id="edit_book_id">

                <!-- Same fields as Add Book form -->
                <div class="form-group">
                    <label for="edit_title">Title</label>
                    <input type="text" name="title" id="edit_title" required>
                </div>

                <div class="form-group">
                    <label for="edit_author">Author</label>
                    <input type="text" name="author" id="edit_author" required>
                </div>

                <div class="form-group">
                    <label for="edit_isbn">ISBN</label>
                    <input type="text" name="isbn" id="edit_isbn" required>
                </div>

                <div class="form-group">
                    <label for="edit_publication_year">Publication Year</label>
                    <input type="number" name="publication_year" id="edit_publication_year" required min="1900"
                        max="2099">
                </div>

                <div class="form-group">
                    <label for="edit_genre">Genre</label>
                    <input type="text" name="genre" id="edit_genre" required>
                </div>

                <div class="form-group">
                    <label for="edit_publisher">Publisher</label>
                    <input type="text" name="publisher" id="edit_publisher" required>
                </div>

                <div class="form-group">
                    <label for="edit_edition">Edition</label>
                    <input type="text" name="edition" id="edit_edition">
                </div>

                <div class="form-group">
                    <label for="edit_pages">Pages</label>
                    <input type="number" name="pages" id="edit_pages" required min="1">
                </div>

                <div class="form-group">
                    <label for="edit_language">Language</label>
                    <input type="text" name="language" id="edit_language" required>
                </div>

                <div class="form-group">
                    <label for="edit_description">Description</label>
                    <textarea name="description" id="edit_description" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="edit_cover_image">Cover Image</label>
                    <input type="file" name="cover_image" id="edit_cover_image" accept="image/*">
                </div>

                <div class="form-group">
                    <label for="edit_stock_quantity">Stock Quantity</label>
                    <input type="number" name="stock_quantity" id="edit_stock_quantity" required min="0">
                </div>

                <div class="form-group">
                    <label for="edit_location">Location</label>
                    <input type="text" name="location" id="edit_location" required>
                </div>

                <button type="submit">Update Book</button>
            </form>
        </div>
    </div>

    <!-- Delete Book Form -->
    <form id="deleteForm" action="proses.php" method="post" style="display: none;">
        <input type="hidden" name="action" value="delete_book">
        <input type="hidden" name="book_id" id="delete_book_id">
    </form>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeDeleteModal()">&times;</span>
            <h2>Delete Book</h2>
            <div class="delete-confirmation">
                <p>Are you sure you want to delete this book? This action cannot be undone.</p>
                <div class="delete-actions">
                    <button onclick="closeDeleteModal()" class="btn-cancel">Cancel</button>
                    <button onclick="submitDelete()" class="btn-delete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    let bookIdToDelete = null;

    function confirmDelete(bookId) {
        bookIdToDelete = bookId;
        document.getElementById('deleteModal').style.display = 'block';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
        bookIdToDelete = null;
    }

    function submitDelete() {
        if (bookIdToDelete) {
            document.getElementById('delete_book_id').value = bookIdToDelete;
            document.getElementById('deleteForm').submit();
        }
    }

    // Modal functions
    function openAddModal() {
        document.getElementById('addModal').style.display = 'block';
    }

    function closeAddModal() {
        document.getElementById('addModal').style.display = 'none';
    }

    function openEditModal(bookId) {
        document.getElementById('editModal').style.display = 'block';
        document.getElementById('edit_book_id').value = bookId;

        // Fetch book details
        fetch('get_book.php?id=' + bookId)
            .then(response => response.json())
            .then(data => {
                document.getElementById('edit_title').value = data.title;
                document.getElementById('edit_author').value = data.author;
                document.getElementById('edit_isbn').value = data.isbn;
                document.getElementById('edit_publication_year').value = data.publication_year;
                document.getElementById('edit_genre').value = data.genre;
                document.getElementById('edit_publisher').value = data.publisher;
                document.getElementById('edit_edition').value = data.edition;
                document.getElementById('edit_pages').value = data.pages;
                document.getElementById('edit_language').value = data.language;
                document.getElementById('edit_description').value = data.description;
                document.getElementById('edit_stock_quantity').value = data.stock_quantity;
                document.getElementById('edit_location').value = data.location;
            });
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }
    // Update fungsi window.onclick untuk mencakup semua modal
    window.onclick = function(event) {
        if (event.target.className === 'modal') {
            event.target.style.display = 'none';
            if (event.target.id === 'deleteModal') {
                bookIdToDelete = null;
            }
        }
    }
    </script>
</body>

</html>