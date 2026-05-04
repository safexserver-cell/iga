<?php
// api/db.php — MySQL Connection
$dbHost = '103.190.92.111';
$dbName = 'internationalgro_iga';
$dbUser = 'internationalgro_iga';
$dbPass = 'internationalgro_iga';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Create tables if they don't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS laureates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            category VARCHAR(100) NOT NULL,
            year VARCHAR(10) NOT NULL,
            description TEXT NOT NULL,
            image_url TEXT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Seed default data only if tables are empty
    $count = $pdo->query("SELECT COUNT(*) FROM laureates")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("
            INSERT INTO laureates (name, category, year, description, image_url) VALUES 
            ('Jonathan Reyes', 'innovation', '2025', 'For redefining artificial intelligence applications in personalized healthcare.', 'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&q=80&w=400'),
            ('Maria Gonzales', 'leadership', '2024', 'For visionary leadership in global supply chain sustainability.', 'https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?auto=format&fit=crop&q=80&w=400'),
            ('Marcus Thorne', 'impact', '2025', 'For expanding clean water access in sub-Saharan Africa.', 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&q=80&w=400'),
            ('David Lee', 'innovation', '2023', 'Pioneering advancements in quantum computing infrastructure.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=400'),
            ('Amelia Croft', 'impact', '2024', 'Advocacy and implementation of global literacy programs for women.', 'https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&q=80&w=400'),
            ('James Wilson', 'leadership', '2025', 'Exceptional corporate governance and ethical leadership.', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=400')
        ");
    }

    $adminCount = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
    if ($adminCount == 0) {
        $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
        $stmt->execute(['admin', $defaultPassword]);
    }

} catch (PDOException $e) {
    die("Database Connection failed: " . $e->getMessage());
}
?>
