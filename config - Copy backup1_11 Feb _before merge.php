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


function insertQuestion($userId, $questionText) {
    // Assuming $conn is your database connection object
    global $conn;

    // SQL query with placeholders, excluding quiz_id (assuming it's an auto-increment primary key)
    $sql = "INSERT INTO questions (user_id, question_text) VALUES (?, ?)";

    // Prepare the statement
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
      #  die("Error in preparing the statement: " . $conn->error);
      echo("Error in preparing the statement: " . $conn->error);
	  return ; 
    }

    // Bind parameters
	$questionText = htmlspecialchars($questionText, ENT_QUOTES, 'UTF-8');
    $stmt->bind_param("is", $userId, $questionText);

    // Execute the statement
    $result = $stmt->execute();

    if (!$result) {
       # die("Error in executing the statement: " . $stmt->error);
       echo ("Error in executing the statement: " . $stmt->error);
	   return ; 
    }

    // Get the auto-generated question_id
    $questionId = $stmt->insert_id;

    // Close the statement
    $stmt->close();

    // Return the question_id
    return $questionId;
}


function insertAnswer($questionId, $userId, $answerText, $isCorrect) {
    // Assuming $conn is your database connection object
    global $conn;

    // SQL query with placeholders
    $sql = "INSERT INTO answers (question_id, user_id, answer_text, is_correct) VALUES (?, ?, ?, ?)";

    // Prepare the statement
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        // Display the error message
        echo "Error in preparing the statement: " . $conn->error;

        // Return 0 to indicate an error
        return 0;
    }

    // Sanitize user-generated content to prevent SQL injection
    $answerText = htmlspecialchars($answerText, ENT_QUOTES, 'UTF-8');

    // Bind parameters
    $stmt->bind_param("iisi", $questionId, $userId, $answerText, $isCorrect);

    // Execute the statement
    $result = $stmt->execute();

    if (!$result) {
        // Display the error message
        echo "Error in executing the statement: " . $stmt->error;

        // Close the statement
        $stmt->close();

        // Return 0 to indicate an error
        return 0;
    }

    // Get the auto-generated answer_id
    $answerId = $stmt->insert_id;

    // Close the statement
    $stmt->close();

    // Return the answer_id
    return $answerId;
}


$pagefunction = [
    'home' => ['homePage', 'Home'],
    'add_new_question' => ['add_new_questionPage', 'AddQ'],
    'view_questions' => ['viewQuestionsPage', 'Questions'],
    'edit_question' => ['editQuestionPage', 'EditQ'],
	'print_question_paper' => ['printQuestionPaperPage', 'Print'],
	'reprint' => ['reprintQuestionPaperPage', 'Reprint'],
    #'dashboard' => ['dashboardPage', 'Dashboard'],
    
    'changepassword' => ['changePasswordPage', 'Change Password'],
    'signin' => ['signinPage', 'Sign In'],
	'signout' => ['signoutPage', 'Sign Out'],
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


function printQuestionPaperPage(){
    if (!isLoggedIn()){ 
        die("Serious Error"); 
    }
    
    global $conn;

    // Retrieve all questions from the database
    $sql = "SELECT id, question_text FROM questions ORDER BY RAND()"; // Retrieve 20 random questions for the A4 page
    $result = $conn->query($sql);
	$qansdataidlist  = "";
    if ($result->num_rows > 0) {
        echo "<h2 class='text-center mb-4' style='font-size: 16px;'>MCQ Question Paper</h2>"; // Adjust font size for the title

        // Add a checkbox for marking correct answers
        echo "<label><input type='checkbox' id='markCorrectAnswers' onchange='toggleBoldAnswers()'> Mark Correct Answers </label>&nbsp;&nbsp;&nbsp;";
        echo "<label><input type='checkbox' id='correctanswerlist' onchange='togglecorrectAnswers()'> Bottom  Answers</label><br><br>";

        $questionCounter = 1; // Initialize question counter
		
		$ques_ans_list = [];
		
        // Loop through each question
        while ($row = $result->fetch_assoc()) {
            $questionId = $row['id'];
            $questionText = $row['question_text'];
			
			
			$qansdataidlist = $qansdataidlist."#". $questionId;
            // Display only the question ID on the right side
            echo "<div style='font-size: 11px; display: flex; justify-content: space-between;'>"; // Use flexbox for a two-column layout
            echo "<div style='width: 70%;'><b>$questionCounter) $questionText</b></div>"; // Reduced font size for question text
			
            // Move [ID:$questionId] to the right side
            echo "<div style='width: 28%; text-align: right;'>[ID:$questionId]</div>";

            echo "</div>";
			$ques_ans_list[$questionCounter] =  [];
            // Retrieve and display answers for the current question
            $answersSql = "SELECT id, answer_text, is_correct FROM answers WHERE question_id = ? ORDER BY RAND()";
            $stmt = $conn->prepare($answersSql);
            $stmt->bind_param("i", $questionId);
            $stmt->execute();
            $stmt->bind_result($answerId, $answerText, $isCorrect);

            echo "<div style='font-size: 11px; display: flex; justify-content: space-between;'>"; // Use flexbox for a two-column layout

            $index = 65; // ASCII code for 'A'

            while ($stmt->fetch()) {
                // Display each answer in one line with alphabetical count (A, B, C, ..., J)
                $alphabeticalCount = chr($index);

                // Add a class to correct answers if the checkbox is checked
                $boldClass = ($isCorrect) ? 'bold-answer' : '';
				if($isCorrect){
					$ques_ans_list[$questionCounter][] = chr($index) ;
				}
                echo "<div style='width: 48%;' class='$boldClass'><span style='margin-right: 10px;'>$alphabeticalCount)</span>";
                echo "$answerText</div>"; // Reduced font size for answer text
                $index++;
				$qansdataidlist = $qansdataidlist."@".$answerId; 
            }
			
			

// Add inline JavaScript to toggle bold class on correct answers


            echo "</div>";
            echo "<br>"; // Add a line break between questions
            $questionCounter++; // Increment question counter
        }
		
		
		echo "<div class='correctanswerlistshow'>";
		foreach ($ques_ans_list as $questionNumber => $answerNumbers) {
            echo "<b> Q-$questionNumber: </b>" . implode(", ", $answerNumbers) . "&nbsp;&nbsp;&nbsp;";
        }
		echo "</div>";
		echo "<textarea class='form-control' readonly>$qansdataidlist</textarea>";		
		
		
		#var_dump($ques_ans_list);
	 echo "<script>
            function toggleBoldAnswers() {
                var checkbox = document.getElementById('markCorrectAnswers');
                var answers = document.querySelectorAll('.bold-answer');

                if (checkbox.checked) {
                    answers.forEach(function (answer) {
                        answer.style.fontWeight = 'bold';
                        
                    });
                } else {
                    answers.forEach(function (answer) {
                        answer.style.fontWeight = 'normal';
                        
                    });
                }
            }
			
			  function togglecorrectAnswers() {
                var checkbox = document.getElementById('correctanswerlist');
                var opt = document.querySelectorAll('.correctanswerlistshow');

                if (checkbox.checked) {
                    opt.forEach(function (opt) {
                        opt.style.display = 'block';
                        
                    });
                } else {
                    opt.forEach(function (opt) {
                        opt.style.display = 'none';
                        
                    });
                }
            }
        </script>";

    } else {
        echo "<p class='text-center mt-5'>No questions found.</p>";
    }
}


function reprintQuestionPaperPage(){
    if (!isLoggedIn()){ 
        die("Serious Error"); 
    }
    
	
	 
	if(!isset($_POST["reprinttext"])){
		?>
	  <form method="post" action="<?php echo currenturl(); ?>">
		<div class="form-group">
		  <label for="exampleTextarea">Your Textarea Label:</label>
		  <textarea class="form-control" name="reprinttext" id="exampleTextarea" rows="8" placeholder="Enter your text here..."></textarea>
		</div>
		<button type="submit" class="btn btn-primary">Submit</button>
	  </form>
		<?php		
	}else{
	?>
	  <form method="post" action="<?php echo currenturl(); ?>">
		<div class="form-group">
		  <label for="exampleTextarea">Your Textarea Label:</label>
		  <textarea class="form-control" name="reprinttext" id="exampleTextarea" rows="2" placeholder="Enter your text here..."></textarea>
		</div>
		<button type="submit" class="btn btn-primary">Submit</button>
	  </form>
		<?php		
	#echo $string = "5@20@17@19@18#4@15@13@14@16#2@5@7@6@8#1@3@1@2@4#3@12@11@9@10";
	$string  = htmlspecialchars($_POST['reprinttext']);
	// Step 1: Split the string using #
	$step1Array = explode("#", $string);
	
	#var_dump($step1Array);
	// Step 2: Initialize the final array
	$finalArray = [];

	// Step 3: Iterate over the results from step 1
	foreach ($step1Array as $step1Item) {
		// Step 4: Split each item using @
		$step2Array = explode("@", $step1Item);

		// Step 5: Add the resulting array to the final array
		$finalArray[] = $step2Array;
	}

	// Display the final array
	#var_dump($finalArray);
	
	
    
	$qansdataidlist  = "";
    if (1==1) {
        echo "<h2 class='text-center mb-4' style='font-size: 16px;'>MCQ Question Paper</h2>"; // Adjust font size for the title

        // Add a checkbox for marking correct answers
        echo "<label><input type='checkbox' id='markCorrectAnswers' onchange='toggleBoldAnswers()'> Mark Correct Answers </label>&nbsp;&nbsp;&nbsp;";
        echo "<label><input type='checkbox' id='correctanswerlist' onchange='togglecorrectAnswers()'> Bottom  Answers</label><br><br>";

        $questionCounter = 1; // Initialize question counter
		
		$ques_ans_list = [];
		
        // Loop through each question
		
		foreach ( $finalArray as $row ) {
        #while ($row = $result->fetch_assoc()) {
            $questionId = $row[0];
			if(getCurrentQuestionText($questionId)){
				$questionText = getCurrentQuestionText($questionId); 
			}else{continue;}
			
			
			$qansdataidlist = $qansdataidlist."#". $questionId;
            // Display only the question ID on the right side
            echo "<div style='font-size: 11px; display: flex; justify-content: space-between;'>"; // Use flexbox for a two-column layout
            echo "<div style='width: 70%;'><b>$questionCounter) $questionText</b></div>"; // Reduced font size for question text
			
            // Move [ID:$questionId] to the right side
            echo "<div style='width: 28%; text-align: right;'>[ID:$questionId]</div>";

            echo "</div>";
			$ques_ans_list[$questionCounter] =  [];
            // Retrieve and display answers for the current question
            
			
			
			
            echo "<div style='font-size: 11px; display: flex; justify-content: space-between;'>"; // Use flexbox for a two-column layout

            $index = 64; // ASCII code for 'A'-1
			
			foreach ( $row as $ansI ) {
				$answerId = $ansI;
				$index++;
				if($index==65){continue;}
				if(getCurrentAnswerDetails($answerId)){
				$ansdetail = getCurrentAnswerDetails($answerId);
				}else{
					
					continue;
				}
				$isCorrect = $ansdetail['is_correct'];
				$answerText = $ansdetail['answer_text'];
				#var_dump($ansdetail);
                // Display each answer in one line with alphabetical count (A, B, C, ..., J)
                $alphabeticalCount = chr($index);

                // Add a class to correct answers if the checkbox is checked
                $boldClass = ($isCorrect) ? 'bold-answer' : '';
				if($isCorrect){
					$ques_ans_list[$questionCounter][] = chr($index) ;
				}
                echo "<div style='width: 48%;' class='$boldClass'><span style='margin-right: 10px;'>$alphabeticalCount)</span>";
                echo "$answerText</div>"; // Reduced font size for answer text
                
				$qansdataidlist = $qansdataidlist."@".$answerId; 
            }
			
			

// Add inline JavaScript to toggle bold class on correct answers


            echo "</div>";
            echo "<br>"; // Add a line break between questions
            $questionCounter++; // Increment question counter
        }
		
		
		echo "<div class='correctanswerlistshow'>";
		foreach ($ques_ans_list as $questionNumber => $answerNumbers) {
            echo "<b> Q-$questionNumber: </b>" . implode(", ", $answerNumbers) . "&nbsp;&nbsp;&nbsp;";
        }
		echo "</div>";
		echo "<textarea class='form-control' readonly>$qansdataidlist</textarea>";		
		
		
		#var_dump($ques_ans_list);
	 echo "<script>
            function toggleBoldAnswers() {
                var checkbox = document.getElementById('markCorrectAnswers');
                var answers = document.querySelectorAll('.bold-answer');

                if (checkbox.checked) {
                    answers.forEach(function (answer) {
                        answer.style.fontWeight = 'bold';
                        
                    });
                } else {
                    answers.forEach(function (answer) {
                        answer.style.fontWeight = 'normal';
                        
                    });
                }
            }
			
			  function togglecorrectAnswers() {
                var checkbox = document.getElementById('correctanswerlist');
                var opt = document.querySelectorAll('.correctanswerlistshow');

                if (checkbox.checked) {
                    opt.forEach(function (opt) {
                        opt.style.display = 'block';
                        
                    });
                } else {
                    opt.forEach(function (opt) {
                        opt.style.display = 'none';
                        
                    });
                }
            }
        </script>";

    } else {
        echo "<p class='text-center mt-5'>No questions found.</p>";
    }
	
	}
}



function viewQuestionsPage(){
    if (!isLoggedIn()){ 
        die("Serious Error"); 
    }
    
    global $conn;

    // Retrieve all questions from the database
    $sql = "SELECT id, question_text FROM questions";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<div class='container mt-4'>";
        echo "<h2 class='text-center mb-4'>Question Paper</h2>";

        // Loop through each question
        while ($row = $result->fetch_assoc()) {
            $questionId = $row['id'];
            $questionText = $row['question_text'];

            // Display the question with an "Edit" link
            echo "<div class='card mb-4'>";
            echo "<div class='card-body'>";
            
            // Question ID and Edit button side by side
            echo "<div class='d-flex justify-content-between align-items-center'>";
                echo "<h5 class='card-title'>Question ID: $questionId</h5>";
                echo "<a href='index.php?pagename=edit_question&id=$questionId' class='btn btn-small btn-primary'>Edit Question</a>";
            echo "</div>";
            
            echo "<p class='card-text'>$questionText</p>";

            // Retrieve and display answers for the current question
            $answersSql = "SELECT id, answer_text, is_correct FROM answers WHERE question_id = ?";
            $stmt = $conn->prepare($answersSql);
            $stmt->bind_param("i", $questionId);
            $stmt->execute();
            $stmt->bind_result($answerId, $answerText, $isCorrect);

            echo "<div class='row'>";
            while ($stmt->fetch()) {
                // Display each answer in two columns
                echo "<div class='col-md-6 mb-2'>";
                echo "<div class='d-flex justify-content-between align-items-center'>";
                echo "<p class='mb-0'>$answerText</p>";

                // Display a green checkmark (✓) for correct answers
                if ($isCorrect) {
                    echo "<span class='badge bg-success'>&#x2713; Correct</span>";
                }

                echo "</div>";
                echo "</div>";
            }
            echo "</div>";

            echo "</div>";
            echo "</div>";
        }

        echo "</div>";
    } else {
        echo "<p class='text-center mt-5'>No questions found.</p>";
    }
}



// Function to get the current question text from the database
function getCurrentQuestionText($questionId) {
    global $conn;

    $sql = "SELECT question_text FROM questions WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $questionId);
    $stmt->execute();
    $stmt->bind_result($currentQuestionText);

    if ($stmt->fetch()) {
        $stmt->close();
        return $currentQuestionText;
    }

    return null; // Handle if the question is not found
}

// Function to get the current answer details from the database
function getCurrentAnswerDetails($answerId) {
    global $conn;

    $sql = "SELECT answer_text, is_correct FROM answers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $answerId);
    $stmt->execute();
    $stmt->bind_result($currentAnswerText, $currentIsCorrect);

    if ($stmt->fetch()) {
        $stmt->close();
        return ['answer_text' => $currentAnswerText, 'is_correct' => $currentIsCorrect];
    }

    return null; // Handle if the answer is not found
}



// Function to edit a question in the database
function editQuestion($questionId, $newQuestionText) {
    global $conn;

    // Retrieve the current question text from the database
    $currentQuestionText = getCurrentQuestionText($questionId); // Replace with your own function

    // Check if the new and current values are different
    if ($currentQuestionText != $newQuestionText) {
        // Update the question text in the database
        $sql = "UPDATE questions SET question_text = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $newQuestionText, $questionId);
        $stmt->execute();

        // Return true even if no rows were affected
       # return true;
		
		
		if ($stmt->error) {
			# "Error editing question: " . $stmt->error;
		}

		return $stmt->affected_rows > 0;
		
		
    }

    // Values are the same, consider it a successful update
    return true;
}

// Function to edit an answer in the database
function editAnswer($answerId, $newAnswerText, $isCorrect) {
    global $conn;

    // Retrieve the current answer details from the database
    $currentAnswerDetails = getCurrentAnswerDetails($answerId); // Replace with your own function

    // Check if the new and current values are different
    if ($currentAnswerDetails['answer_text'] != $newAnswerText || $currentAnswerDetails['is_correct'] != $isCorrect) {
        // Update the answer text and correctness in the database
        $sql = "UPDATE answers SET answer_text = ?, is_correct = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $newAnswerText, $isCorrect, $answerId);
        $stmt->execute();

        // Return true even if no rows were affected
        if ($stmt->error) {
			echo "Error editing answer with ID $answerId: " . $stmt->error;
		}

		return $stmt->affected_rows > 0;
    }

    // Values are the same, consider it a successful update
    return true;
}


function add_new_questionPage(){
	if (!isLoggedIn()){die( "Serious Error");}
    global $conn;

    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
        $questionText = htmlspecialchars($_POST['questionText']);
        $answers = $_POST['answers']; // An array containing answer texts
        $correctAnswers = isset($_POST['correctAnswers']) ? $_POST['correctAnswers'] : [];

        // Insert the question into the database
        $userId = isLoggedIn();
        $questionId = insertQuestion($userId, $questionText);

        if ($questionId) {
            // Insert the answers into the database
            foreach ($answers as $index => $answerText) {
				if (!empty($answerText)){
					$isCorrect = in_array($index, $correctAnswers) ? 1 : 0;
					insertAnswer($questionId, $userId, $answerText, $isCorrect);
				}else{
					echo "<div class='alert alert-danger' role='alert'>Empty Answer Removed</div>"; 
				}
            }
			echo "<div class='alert alert-success' role='alert'>Question and answers added successfully!</div>";
        } else {
            echo "Error adding question.";
        }
    }

    // Display the form
    echo "<h2>Add New Question</h2>";
    echo "<form action='" . currenturl() . "' method='post'>";
    echo "<div class='form-group'>";
    echo "<label for='questionText'>Question Text:</label>";
    echo "<textarea name='questionText' id='questionText' class='form-control' required></textarea>";
    echo "</div>";

// Answer fields
for ($i = 0; $i <= 3; $i++) {
    echo "<div class='form-group mb-2 mt-2'>";
    echo "<div class='row align-items-center'>"; // Added 'align-items-center' class

    // Input field column
    echo "<div class='col-md-10 align-items-center'>";
    echo "<div class='input-group'>";
    echo "<input type='text' name='answers[]' id='answer$i' class='form-control answer-input'>";
    echo "</div>";
    echo "</div>";

    // Checkbox column
	echo "<div class='col-md-2 form-check align-items-center'>";
    echo "<div class='form-check-input-container'>";
    echo "<input class='form-check-input form-check-input-lg' type='checkbox' name='correctAnswers[]' value='$i' id='correctAnswerCheckbox$i'>";
    echo "<label class='form-check-label' for='correctAnswerCheckbox$i'> Correct</label>";
    echo "</div>";
    echo "</div>";

    echo "</div>"; // Close row
    echo "</div>"; // Close form-group
}
	echo "<div class='form-group mt-3'>";
    echo "</div>";
	
	    echo "<button type='button' id='addAnswerField' class='btn btn-secondary'>Add Answer</button>";

    #echo "<input type='text' name='answers[]' id='answer$i' class='form-control answer-input'>";




    // Add JavaScript to dynamically add answer input fields
    echo "<script>
        document.addEventListener('DOMContentLoaded', function () {
            var answerIndex = 4; // Set to the next available index
            
            document.getElementById('addAnswerField').addEventListener('click', function () {
                var newAnswerField = createAnswerField(answerIndex);
                document.querySelector('.form-group.mt-3').insertAdjacentHTML('beforebegin', newAnswerField);
                answerIndex++;
            });

            function createAnswerField(index) {
                return `
                    <div class='form-group mb-2 mt-2'>
					
                        <div class='row align-items-center'>
						
						
						
                            <div class='col-md-10 align-items-center'>
                                <div class='input-group'>
                                    <input type='text' name='answers[]' id='answer".'${index}'."' value='answer".'${index}'."' class='form-control answer-input' >
                                </div>
                            </div>
							
							
                            <div class='col-md-2 form-check align-items-center'>
                                <div class='form-check-input-container'>
								
										<input class='form-check-input form-check-input-lg' type='checkbox' name='correctAnswers[]' value=".'${index}'." id='correctAnswerCheckbox".'${index}'."' >
										
										<label class='form-check-label' for='correctAnswerCheckbox".'${index}'."'> Correct</label>
									
								</div>
                            </div>
							
							
							
							
                        </div>
                    </div>`;
            }
        });
    </script>";
	
	
// Add JavaScript to handle the validation styling
echo "<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Delegate the click event to a parent element that exists when the page loads
        document.addEventListener('click', function (event) {
            var checkbox = event.target.closest('.form-check-input');
            if (checkbox) {
                var inputId = 'answer' + checkbox.value;
                var inputField = document.getElementById(inputId);

                if (checkbox.checked) {
                    inputField.classList.add('is-valid');
                } else {
                    inputField.classList.remove('is-valid');
                }
            }
        });
    });
</script>
";

    echo "<button type='submit' name='submit' class='btn btn-primary'>Add Question</button>";
    echo "</form>";
}




function editQuestionPage() {
    if (!isLoggedIn()) { die("Serious Error"); }
    global $conn;

    // Initialize $stmt
    $stmt = null;

    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
        $newQuestionText = htmlspecialchars($_POST['questionText']);
        $questionId = intval($_POST['questionId']);

        // Edit the question
        if (editQuestion($questionId, $newQuestionText)) {
           # echo "Question edited successfully!";
			echo "<div class='alert alert-success' role='alert'>Question edited successfully!</div>";
        } else {
            #echo "Error editing question.";
        }

        // Close the statement for retrieving the question text
        if ($stmt) {
            $stmt->close();
        }

        // Edit each answer
        foreach ($_POST['answers'] as $index => $answerText) {
            $answerId = intval($index);
            $isCorrect = isset($_POST['correctAnswers'][$index]) ? 1 : 0;
			
			if (!empty($answerText)){
				if (!editAnswer($answerId, $answerText, $isCorrect)) {
				   # echo "Error editing answer with ID: $answerId";
				}
			}else{
				#remove the answer
				removeAnswerById($answerId);
				#echo "<div class='alert alert-danger' role='alert'>Empty Answer Removed</div>"; 
			}
        }
		##var_dump($_POST['newanswers']);
		#var_dump($_POST['correctnewAnswers']);
		$_POST['newanswers'] = isset($_POST['newanswers'])?$_POST['newanswers'] : [];
		$_POST['correctnewAnswers'] = isset($_POST['correctnewAnswers'])?$_POST['correctnewAnswers'] : [];
		$correctnewAnswers = $_POST['correctnewAnswers'];
		##var_dump($_POST['newanswers'] );
		#var_dump($_POST['correctnewAnswers'] );
		foreach ($_POST['newanswers'] as $index => $answerText) {
            $answerId = intval($index);
            #$isCorrect = isset($_POST['correctnewAnswers'][$index]) ? 1 : 0;
			$isCorrect = in_array($index, $correctnewAnswers) ? 1 : 0;
			#addAnswerField
			$userId = isLoggedIn();
			if (!empty($answerText)){
				if (!insertAnswer($questionId, $userId, $answerText, $isCorrect)) {
				   # echo "Error editing answer with ID: $answerId";
				}
			}else{
				#remove the answer
				#removeAnswerById($answerId);
				#echo "<div class='alert alert-danger' role='alert'>Empty Answer Removed</div>"; 
			}
        }
    }

    // Retrieve the question details from the database
    if (isset($_GET['id'])) {
        $questionId = intval($_GET['id']);

        $questionSql = "SELECT question_text FROM questions WHERE id = ?";
        $stmt = $conn->prepare($questionSql);
        $stmt->bind_param("i", $questionId);
        $stmt->execute();
        $stmt->bind_result($questionText);

        if ($stmt->fetch()) {
            // Display the form to edit the question
            echo "<h2>Edit Question</h2>";
            echo "<form action='" . currenturl() . "' method='post'>";#
            echo "<input type='hidden' name='questionId' value='$questionId'>";
				echo "<div class='form-group'>";###
				echo "<label for='questionText'>Question Text:</label>";
				echo "<textarea name='questionText' id='questionText' class='form-control' required>$questionText</textarea>";
				echo "</div>";##form-group

            // Close the statement for retrieving the question text
            $stmt->close();

            // Retrieve and display answers for the current question
            $answersSql = "SELECT id, answer_text, is_correct FROM answers WHERE question_id = ? ORDER BY `answers`.`id` ASC";
            $stmt = $conn->prepare($answersSql);
            $stmt->bind_param("i", $questionId);
            $stmt->execute();
            $stmt->bind_result($answerId, $answerText, $isCorrect);

            echo "<h3>Edit Answers</h3>";
			$count_ans= 0;
            while ($stmt->fetch()) {
				$count_ans = $count_ans + 1; 
                // Display each answer with an input field
                echo "<div class='form-group mb-2 mt-2'>";
                echo "<div class='row align-items-center'>"; // Added 'align-items-center' class

                // Input field column
                echo "<div class='col-md-10 align-items-center'>";
                #echo "<label for='answer$answerId' class='form-label'>Answer $answerId:</label>";
                echo "<div class='input-group'>";
                echo "<input type='text' name='answers[$answerId]' id='answer$answerId' class='form-control answer-input " . ($isCorrect ? "is-valid" : "") . "' value='$answerText'>";
                echo "</div>";
                echo "</div>";

                // Checkbox column
					echo "<div class='col-md-2 form-check align-items-center'>";
					echo "<div class='form-check-input-container'>";
					echo "<input class='form-check-input form-check-input-lg' type='checkbox' name='correctAnswers[$answerId]' value='$answerId' id='correctAnswerCheckbox$answerId' " . ($isCorrect ? "checked" : "") . ">";
					echo "<label class='form-check-label' for='correctAnswerCheckbox$answerId'> Correct</label>";
					echo "</div>";
					echo "</div>";
				echo "</div>";
					echo "</div>";	
		

			}
				echo "<div class='form-group mt-3 mb-3'>";
				echo "<button type='button' id='addAnswerField' class='btn btn-secondary'>Add Answer</button>";
				echo "</div>";

               
			/*	echo "<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.form-check-input').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                var inputId = 'answer' + checkbox.value;
                var inputField = document.getElementById(inputId);

                if (checkbox.checked) {
                    inputField.classList.add('is-valid');
                } else {
                    inputField.classList.remove('is-valid');
                }
            });
        });
    });
</script>"; */
echo "<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Delegate the click event to a parent element that exists when the page loads
        document.addEventListener('click', function (event) {
            var checkbox = event.target.closest('.form-check-input');
            if (checkbox) {
                var inputId = 'answer' + checkbox.value;
                var inputField = document.getElementById(inputId);

                if (checkbox.checked) {
                    inputField.classList.add('is-valid');
                } else {
                    inputField.classList.remove('is-valid');
                }
            }
        });
    });
</script>
";
    // Add JavaScript to dynamically add answer input fields
    echo "<script>
        document.addEventListener('DOMContentLoaded', function () {
            var answerIndex =$count_ans //0; // Set to the next available index
            
            document.getElementById('addAnswerField').addEventListener('click', function () {
                var newAnswerField = createAnswerField(answerIndex);
                document.querySelector('.form-group.mt-3').insertAdjacentHTML('beforebegin', newAnswerField);
                answerIndex++;
            });

            function createAnswerField(index) {
                return `
                    <div class='form-group mb-2 mt-2'>
					
                        <div class='row align-items-center'>
						
						
						
                            <div class='col-md-10 align-items-center'>
                                <div class='input-group'>
                                    <input type='text' name='newanswers[]' id='answer".'${index}'."' class='form-control answer-input' >
                                </div>
                            </div>
							
							
                            <div class='col-md-2 form-check align-items-center'>
                                <div class='form-check-input-container'>
								
										<input class='form-check-input form-check-input-lg' type='checkbox' name='correctnewAnswers[]' value=".'${index}'." id='answer".'${index}'."' >
										
										<label class='form-check-label' for='answer".'${index}'."'> Correct</label>
									
								</div>
                            </div>
							
							
							
							
                        </div>
                    </div>`;
            }
        });
    </script>";

   
            

            echo "<button type='submit' name='submit' class='btn btn-primary'>Update Question</button>";
			
            echo "</form>";#
        } else {
            echo "<p>Question not found.</p>";
        }
    } else {
        echo "<p>Question ID not provided.</p>";
    }
}

// Function to remove a question by question ID
function removeQuestionById($questionId) {
    global $conn;

    // Remove question from the questions table
    $sql = "DELETE FROM questions WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $questionId);

    if ($stmt->execute()) {
        // Remove corresponding answers from the answers table
        removeAnswersByQuestionId($questionId);

        echo "Question removed successfully.";
    } else {
        echo "Error removing question: " . $conn->error;
    }
}

// Function to remove answers by question ID
function removeAnswersByQuestionId($questionId) {
    global $conn;

    // Remove answers from the answers table
    $sql = "DELETE FROM answers WHERE question_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $questionId);

    if ($stmt->execute()) {
        echo "Answers removed successfully.";
    } else {
        echo "Error removing answers: " . $conn->error;
    }
}

// Function to remove an answer by answer ID
function removeAnswerById($answerId) {
    global $conn;

    // Remove answer from the answers table
    $sql = "DELETE FROM answers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $answerId);

    if ($stmt->execute()) {
       # echo "Answer removed successfully.";
	   return 1; 
    } else {
       return  "Error removing answer: " . $conn->error;
    }
}

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
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

		<style>
            body {
                background-color: #f8f9fa;
				
            }
			.width_adjust{
				
				max-width:800px
			}
			
			/* Custom CSS for input boxes */
			input[type="text"],
			input[type="password"],
			textarea {
				margin-top: 0.5rem !important;
				margin-bottom: 0.5rem !important;
			}@media print {
    /* Add styles for print layout */
    .row {
        display: flex;
        flex-wrap: wrap;
    }

    .col-md-6 {
        width: 50%;
        box-sizing: border-box;
    }
}


           
        </style>
		
    </head>
    <body class="bg-light">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
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
