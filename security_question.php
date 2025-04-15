<?php
session_start();
include 'db.php'; // if you use a database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['U_Email'];

    // Check if the user exists
    $stmt = $conn->prepare("SELECT security_question, security_answer FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($question, $answer);
        $stmt->fetch();

        // Save for later comparison
        $_SESSION['email'] = $email;
        $_SESSION['security_answer'] = $answer;

        echo "<form method='POST' action='reset_password.php'>
                <label>$question</label>
                <input type='text' name='user_answer' required>
                <button type='submit'>Submit Answer</button>
              </form>";
    } else {
        echo "Email not found.";
    }

    $stmt->close();
    $conn->close();
}
?>
