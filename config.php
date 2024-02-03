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
    #echo "* DB Connected<br>";
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

function currenturl(){
	return htmlspecialchars($_SERVER['REQUEST_URI']);
}
function changePassword($id, $newPassword) {
	
	if ( isLoggedIn()){
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
}


$pagefunction = [
    'home' => ['homePage', 'Home'],
    #'dashboard' => ['dashboardPage', 'Dashboard'],
    'signout' => ['signoutPage', 'Sign Out'],
    'changepassword' => ['changePasswordPage', 'Change Password'],
    'signin' => ['signinPage', 'Sign In'],
    // Add more pages as needed
];


headerpage();
if (isset($_GET['pagename'])){
	$page = htmlspecialchars($_GET['pagename']);
	if (function_exists($pagefunction[$page][0])) {
        call_user_func($pagefunction[$page][0]);
    } else {
        echo "Invalid pagename.";
    }
}else{
	    homePage();
}
footerpage();
function headerpage(){
    global $pagefunction;

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Quiz App</title>
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
		<style>
            body {
                background-color: #f8f9fa;
				
            }
			.width_adjust{
				
				max-width:800px
			}
           
        </style>
		
    </head>
    <body class="bg-light">
        <nav class="navbar navbar-expand-lg navbar-light bg-light ">
            <div class="container width_adjust">
                <a class="navbar-brand" href="?pagename=home">Quiz App</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                    <ul class="navbar-nav ml-auto">
                        <?php
                        foreach ($pagefunction as $pagename => $pageData) {
                            echo "<li class='nav-item'>";
                            echo "<a class='nav-link' href='?pagename=$pagename'>$pageData[1]</a>";
                            echo "</li>";
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </nav>
    
	<?php
	
	
	 echo "<div class='container mt-5 bg-light width_adjust'>";
?>

<?Php
}

function footerpage(){
	echo "</div>";
	?>
	
	    <!-- Bootstrap JS (optional, if you need it) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
	<?php
	
};

function homePage(){
    if (isLoggedIn()){
        $id = isLoggedIn();
        $username = userinfo($id, "username");
       ;
        echo "<h3>Welcome, $username!</h3>";
        echo "<p>Your user ID is: $id</p>";
        echo "<p><a href='?pagename=signout'>Sign Out</a></p>";
        
    } else {
        header("Location:?pagename=signin");
    }
}

function homePage1(){
	if (isLoggedIn()){
		if (isLoggedIn()){
			
			$id = isLoggedIn();
			$username = userinfo($id, "username");
			echo "* Username: $username <br>";
			echo "* Loginuserid :$id ; <a href='?pagename=signout'>Signout</a> --  <a href='?pagename=changepassword'>Change Password</a> <br>";
			
			
			exit();
		}
	}else{
		
		header("Location:?pagename=signin");
	}
	
	
}

function signoutPage(){
	if (isLoggedIn()){
		$_SESSION = array();

		// Destroy the session
		session_destroy();
		header("Location:index.php");
		exit("Successfuly signout");
	}
}
function changePasswordPage(){
	
	if (!isLoggedIn()){die( "Serious Error");}
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
            echo "<form action='" . currenturl()." ' method='post'>";
            echo "New Password: <input type='password' name='newPassword' required><br>";
            echo "<input type='submit' value='Change Password'>";
            echo "</form>";
        }

        exit();
	
}


function signinPage(){
	if (isLoggedIn()){die("already loggedin");}
	global $conn;
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
			header("location: index.php");
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



    <!-- Sign-up form -->

    <!-- Sign-in form -->
        <h2>Sign In</h2>
        <?php if (isset($loginError)) { echo "<div class='alert alert-danger' role='alert'>$loginError</div>"; } ?>
        <form action="<?php echo currenturl(); ?>" method="post">
            <div class="form-group">
                <label for="loginUsername">Username:</label>
                <input type="text" name="loginUsername" id="loginUsername" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="loginPassword">Password:</label>
                <input type="password" name="loginPassword" id="loginPassword" class="form-control" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary btn-block">Sign In</button>
        </form>
<style>
            body {
                background-color: #f8f9fa;
            }
            .signin-container {
                max-width: 400px;
                margin: auto;
                margin-top: 50px;
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                background-color: #fff;
            }
            .form-group {
                margin-bottom: 20px;
            }
            h2 {
                text-align: center;
                color: #007bff;
            }
            .btn-primary {
                background-color: #007bff;
                border-color: #007bff;
            }
            .btn-primary:hover {
                background-color: #0056b3;
                border-color: #0056b3;
            }
            .alert {
                margin-top: 20px;
            }
        </style>


<?php } 
