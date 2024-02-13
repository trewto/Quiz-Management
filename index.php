<?php
include ("config.php");

#$userPassword = "password";
#echo $hashedPassword = password_hash($userPassword, PASSWORD_BCRYPT);

/*
$p2= '$2y$10$kzpGQ5bFdrXIG4LesTqyMOAZedZJ3D04ilQiSYXvrtcRWClQJmRVy';

    $p1 = htmlspecialchars("password");
    echo  password_hash($p1, PASSWORD_BCRYPT);

if( password_verify($p1,$p2)){
	echo "h1";	
}else{
	echo "h2";
} */


#$plainPassword = "password";
//Hash the password using BCRYPT algorithm
#echo $hashedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);

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

?>
