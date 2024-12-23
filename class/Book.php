<?php
class Book {
    private $conn;
    private $table = 'books';

    // Book properties
    private $id;
    private $title;
    private $author;
    private $isbn;
    private $publication_year;
    private $genre;
    private $publisher;
    private $edition;
    private $pages;
    private $language;
    private $description;
    private $cover_image;
    private $stock_quantity;
    private $location;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setTitle($title) { $this->title = $title; }
    public function setAuthor($author) { $this->author = $author; }
    public function setIsbn($isbn) { $this->isbn = $isbn; }
    public function setPublicationYear($year) { $this->publication_year = $year; }
    public function setGenre($genre) { $this->genre = $genre; }
    public function setPublisher($publisher) { $this->publisher = $publisher; }
    public function setEdition($edition) { $this->edition = $edition; }
    public function setPages($pages) { $this->pages = $pages; }
    public function setLanguage($language) { $this->language = $language; }
    public function setDescription($description) { $this->description = $description; }
    public function setCoverImage($cover_image) { $this->cover_image = $cover_image; }
    public function setStockQuantity($quantity) { $this->stock_quantity = $quantity; }
    public function setLocation($location) { $this->location = $location; }

    // Create new book
    public function create() {
        $query = "INSERT INTO " . $this->table . "
                (title, author, isbn, publication_year, genre, publisher, 
                edition, pages, language, description, cover_image, 
                stock_quantity, location) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->author = htmlspecialchars(strip_tags($this->author));
        $this->isbn = htmlspecialchars(strip_tags($this->isbn));
        // ... sanitize other fields ...

        $stmt->bind_param("sssssssssssss", 
            $this->title, $this->author, $this->isbn, 
            $this->publication_year, $this->genre, $this->publisher,
            $this->edition, $this->pages, $this->language, 
            $this->description, $this->cover_image, 
            $this->stock_quantity, $this->location
        );

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Read all books
    public function readAll() {
        $query = "SELECT id, title, author, isbn, genre, stock_quantity, location, cover_image 
                  FROM books 
                  ORDER BY id DESC";
        $result = $this->conn->query($query);
        return $result;
    }

    // Read single book
    public function readOne() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Update book
    public function update() {
        $query = "UPDATE " . $this->table . "
                SET title = ?, author = ?, isbn = ?, publication_year = ?,
                    genre = ?, publisher = ?, edition = ?, pages = ?,
                    language = ?, description = ?, stock_quantity = ?,
                    location = ?";
        
        $params = [
            $this->title, $this->author, $this->isbn,
            $this->publication_year, $this->genre, $this->publisher,
            $this->edition, $this->pages, $this->language,
            $this->description, $this->stock_quantity, $this->location
        ];
        $types = "ssssssssssss";

        // Add cover_image to update if it exists
        if($this->cover_image) {
            $query .= ", cover_image = ?";
            $params[] = $this->cover_image;
            $types .= "s";
        }

        $query .= " WHERE id = ?";
        $params[] = $this->id;
        $types .= "i";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete book
    public function delete() {
        // First get the cover image
        $query = "SELECT cover_image FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        // Delete the actual record
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->id);

        if($stmt->execute()) {
            // If successful, delete the cover image file
            if($row && $row['cover_image'] && file_exists($row['cover_image'])) {
                unlink($row['cover_image']);
            }
            return true;
        }
        return false;
    }

   // Upload cover image
public function uploadCoverImage($file) {
    $target_dir = "uploads/covers/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_filename = time() . '_' . uniqid() . '.' . $file_extension; // Buat nama file unik
    $target_file = $target_dir . $new_filename; // Path lengkap untuk menyimpan file

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        // Simpan hanya nama file, bukan path lengkap
        $this->cover_image = $new_filename;
        return true;
    }
    return false;
}

}