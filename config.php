<?php
session_start();

$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$database = "quiz_db";

$conn = new mysqli($servername, $dbusername, $dbpassword, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "* DB Connected<br>";
}


// Function to check if the user is logged in
function isLoggedIn() {
    if (isset($_SESSION['username']) && isset($_SESSION['key'])) {
        $savedUsername = $_SESSION['username'];
        $savedKey = $_SESSION['key'];
		global $conn; 
        // Retrieve the key from the database based on the username
        $sql = "SELECT id, key1 FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $savedUsername);
        $stmt->execute();
        $stmt->bind_result($id,$keyFromDB);

        if ($stmt->fetch()) {
            // Check if the saved key matches the key from the database
            if ($savedKey === $keyFromDB) {
                return $id;
            }
        }
    }
    return false;
}

function userinfo($id, $field) {
    global $conn;

    // Retrieve the specified field value from the database based on the user's ID
    $sql = "SELECT $field FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($value);

    if ($stmt->fetch()) {
        return $value;
    }

    return null;
}
function changePassword($id, $newPassword) {
    global $conn;

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    // Update the password in the database
    $sql = "UPDATE users SET password = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $hashedPassword, $id);
    $stmt->execute();

    return $stmt->affected_rows > 0;
}

if (isLoggedIn()){
	if (isset($_GET['signout'])) {
		// Unset all session variables
		$_SESSION = array();

		// Destroy the session
		session_destroy();
		header("Location:index.php");
		exit("Successfuly signout");
	}elseif (isset($_GET['changepassword'])) {
        // Handle password change
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newPassword'])) {
            $newPassword = htmlspecialchars($_POST['newPassword']);
            $id = isLoggedIn();

            if (changePassword($id, $newPassword)) {
                echo "Password changed successfully";
            } else {
                echo "Failed to change password";
            }
        } else {
            // Display the password change form
            echo "<h3>Change Password</h3>";
            echo "<form action='?changepassword=true' method='post'>";
            echo "New Password: <input type='password' name='newPassword' required><br>";
            echo "<input type='submit' value='Change Password'>";
            echo "</form>";
        }

        exit();
    }
	
	
	$id = isLoggedIn();
	$username = userinfo($id, "username");
    echo "* Username: $username <br>";
	echo "* Loginuserid :$id ; <a href='?signout=true'>Signout</a> --  <a href='?changepassword=true'>Change Password</a> <br>";
	
	
	exit();
}else{
	


// Check if the user is trying to log in
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $loginUsername = htmlspecialchars($_POST['loginUsername']);
    $loginPassword = htmlspecialchars($_POST['loginPassword']);

    // Retrieve the hashed password from the database based on the username
    $sql = "SELECT id, username, password, key1 FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $loginUsername);
    $stmt->execute();
    $stmt->bind_result($userId, $username, $hashedPassword, $key);

    if ($stmt->fetch()) {
     
        if (password_verify($loginPassword, $hashedPassword)) {
            // Set user information in the session
            $_SESSION['key'] = $key;
            $_SESSION['username'] = $username;

            // Redirect to a secure dashboard page after successful login
            echo "Successful login <br> Please refresh ";
            exit();
        } else {
            $loginError = "Invalid username or password1";
        }
    } else {
        $loginError = "Invalid username or password2";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Index</title>
</head>
<body>

    <h2>Welcome to the Quiz App</h2>

    <!-- Sign-up form -->

    <!-- Sign-in form -->
    <h3>Sign In</h3>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
        <label for="loginUsername">Username:</label>
        <input type="text" name="loginUsername" id="loginUsername">
        <br>
        <label for="loginPassword">Password:</label>
        <input type="password" name="loginPassword" id="loginPassword">
        <br>
        <input type="submit" name="login" value="Sign In">
        <?php if (isset($loginError)) { echo "<p style='color:red;'>$loginError</p>"; } ?>
    </form>

</body>
</html>

<?php } 
