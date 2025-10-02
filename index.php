<?php
    $fileName = 'data.json';
    if (file_exists($fileName)) {
        $jsonString = file_get_contents($fileName);
        $topics = json_decode($jsonString);
    } else {
        $topics = [];
    }

    $time = date("Y-m-d H:i:s");

    // Új téma hozzáadása
    if (isset($_POST['action']) && $_POST['action'] == 'add') {
        $lastId = 0;
        if (!empty($topics)) {
            $lastItem = end($topics);
            $lastId = $lastItem->id;
        }

        array_push($topics, (object)[
            "id" => $lastId + 1,
            "name" => $_POST['topic'],
            "time" => $time,
            "comments" => [] // Kommentek kezdeti üres tömbként
        ]);
        $jsonString = json_encode($topics, JSON_PRETTY_PRINT);
        file_put_contents($fileName, $jsonString);
    }

    // Téma törlése
    if (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['check'])) {
        $id = $_POST['id'];
        foreach ($topics as $key => $topic) {
            if ($topic->id == $id) break;
        }
        array_splice($topics, $key, 1);
        $jsonString = json_encode($topics, JSON_PRETTY_PRINT);
        file_put_contents($fileName, $jsonString);
    }

    // Komment hozzáadása
    if (isset($_POST['action']) && $_POST['action'] == 'comment' && isset($_POST['comment']) && isset($_POST['name']) && isset($_POST['topic_id'])) {
        $comment = $_POST['comment'];
        $name = $_POST['name'];
        $topic_id = $_POST['topic_id'];
        
        // Komment hozzáadása a megfelelő témához
        foreach ($topics as &$topic) {
            if ($topic->id == $topic_id) {
                $topic->comments[] = (object)[
                    'comment' => $comment,
                    'name' => $name,
                    'time' => $time,
                ];
                break;
            }
        }

        // Visszaírjuk a módosított adatokat a JSON fájlba
        $jsonString = json_encode($topics, JSON_PRETTY_PRINT);
        file_put_contents($fileName, $jsonString);
    }
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum</title>
</head>
<body>
    <?php
    if (!isset($_GET['topicID'])) {
        echo '<h1>Témák:</h1><ol>';
        foreach ($topics as $value) {
            echo '<li><a href="index.php?topicID=' . $value->id . '">' . $value->name . '</a> ' . $value->time . '<br><br>
            <form method="post">
                <input type="hidden" name="id" value="' . $value->id . '">
                <input type="hidden" name="action" value="delete">
                <input type="checkbox" name="check">
                <input type="submit" value="Törlés">
            </form>';
        }
        echo '</ol>';
    } else {
        $id = $_GET['topicID'];
        foreach ($topics as $key => $topic) {
            if ($topic->id == $id) break;
        }
        $topic = $topics[$key];

        echo '<h1>' . $topic->name . '</h1>';
        echo '<p>' . $topic->time . '</p>';
        
        // Kommentek megjelenítése
        echo '<h2>Kommentek:</h2>';
        if (empty($topic->comments)) {
            echo '<p>Nincsenek kommentek.</p>';
        } else {
            echo '<ul>';
            foreach ($topic->comments as $comment) {
                echo '<li><strong>' . htmlspecialchars($comment->name) . '</strong>: ' . htmlspecialchars($comment->comment) . ' - ' . $comment->time . '</li>';
            }
            echo '</ul>';
        }

        // Komment űrlap
        echo '<h2>Új komment</h2>';
        echo '<form method="POST">
                <input type="hidden" name="action" value="comment">
                <input type="hidden" name="topic_id" value="' . $topic->id . '">
                <label for="name">Név:</label>
                <input type="text" name="name" placeholder="Neved..." required><br><br>
                <textarea name="comment" placeholder="Írd meg a kommentet..." cols="70" rows="8" required></textarea><br>
                <input type="submit" value="Kommentelés">
              </form>';
        
        echo '<a href="index.php">Vissza a témákhoz</a>';
    }
    ?>

    <!-- Téma hozzáadása -->
    <h2>Új téma hozzáadása</h2>
    <form method="POST">
        <input type="hidden" name="action" value="add">
        <input type="text" name="topic" placeholder="Téma neve..." required>
        <input type="submit" value="Add">
    </form>

</body>
</html>
