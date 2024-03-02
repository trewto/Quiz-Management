<?php
// Function to add meta data and return id
function addMetaData($metaName, $metaValue, $referenceId) {
    global $conn;

    $sql = "INSERT INTO meta_data (meta_name, meta_value, referece_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $metaName, $metaValue, $referenceId);
    $stmt->execute();

    // Return the ID of the inserted row
    return $stmt->insert_id;
}

// Function to update meta value using reference id and meta name
function updateMetaData($metaName, $metaValue, $referenceId) {
    global $conn;

    $sql = "UPDATE meta_data SET meta_value = ? WHERE meta_name = ? AND referece_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $metaValue, $metaName, $referenceId);
    $stmt->execute();

    // Check if any rows were affected by the update
    if ($stmt->affected_rows > 0) {
        return true; // Update successful
    } else {
        return false; // Update failed
    }
}


// Function to delete meta data using meta ID
function deleteMetaDataById($metaId) {
    global $conn;

    $sql = "DELETE FROM meta_data WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $metaId);
    $stmt->execute();

    // Check if any rows were affected by the deletion
    if ($stmt->affected_rows > 0) {
        return true; // Deletion successful
    } else {
        return false; // Deletion failed
    }
}

// Function to get meta data ID using meta name and reference ID
function getMetaDataId($metaName, $referenceId) {
    global $conn;

	#echo $metaName ; 
	#echo $referenceId; 
	
	if(!$referenceId>0){return 0 ; }
	$sql = "SELECT id FROM meta_data WHERE meta_name = ? AND referece_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $metaName, $referenceId);
    $stmt->execute();
    $stmt->bind_result($metaId);
    $stmt->fetch();
    $stmt->close(); // Close the statement


    $sql = "SELECT id FROM meta_data WHERE meta_name = ? AND referece_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $metaName, $referenceId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($metaId);
        $stmt->fetch();
        return $metaId; // Return the meta data ID if found
    } else {
        return false; // Return false if meta data is not found
    }
}


// Function to get meta rows using meta data ID
function getMetaRowsById($metaId) {
    global $conn;

    $sql = "SELECT * FROM meta_data WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $metaId);
    $stmt->execute();
    $result = $stmt->get_result();
    $metaRows = $result->fetch_all(MYSQLI_ASSOC);

    return $metaRows; // Return the meta rows
}

function viewQuestionsPage_s(){
    if (!isLoggedIn()){ 
        die("Serious Error"); 
    }
    
    global $conn;

    $currentPage = isset($_GET['page_num']) ?  htmlspecialchars ($_GET['page_num']) : 1; 
    $questionsPerPage = isset($_GET['per_page']) ?  htmlspecialchars ($_GET['per_page']) : 5;
    $searchCategory = isset($_GET['category']) ? htmlspecialchars ( $_GET['category'] ) : null; // Add this line to get category from URL
    $searchQuestion = isset($_GET['search']) ?  htmlspecialchars ($_GET['search']) : ''; // Add this line to get search query from URL
	
    // Calculate the offset
    $offset = ($currentPage - 1) * $questionsPerPage;

    // Construct the WHERE condition based on the search category
    $whereCondition = "";
    if ($searchCategory == null or empty($searchCategory) ) {
       
	   }else{
			$whereCondition = "AND FIND_IN_SET('$searchCategory', meta_value) > 0 AND meta_name='meta_cat'";
    
		}
	if (!empty($searchQuestion)) {
        $whereCondition .= " AND q.question_text LIKE '%$searchQuestion%'";
    }
    // Retrieve questions for the current page and count total rows
    /*$sql = "SELECT SQL_CALC_FOUND_ROWS q.id, q.question_text FROM meta_data AS md
            JOIN questions AS q ON md.referece_id = q.id
            WHERE 1 $whereCondition GROUP BY q.id  ORDER BY q.created_at DESC 
            LIMIT ?, ?";
	*/
			#$sql = "SELECT SQL_CALC_FOUND_ROWS q.id, q.question_text FROM questions AS q LEFT JOIN meta_data AS md ON md.referece_id = q.id WHERE 1 GROUP BY q.id ORDER BY q.created_at DESC"; 
			
			$sql = "SELECT SQL_CALC_FOUND_ROWS q.id, q.question_text 
        FROM questions AS q 
        LEFT JOIN meta_data AS md ON md.referece_id = q.id 
        WHERE 1 $whereCondition 
        GROUP BY q.id  
        ORDER BY q.created_at DESC 
        LIMIT ?, ?";

			#echo $sql ; 
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $offset, $questionsPerPage);
    $stmt->execute();
    $result = $stmt->get_result();

    // Get total count of rows using SQL_CALC_FOUND_ROWS
    $foundRowsResult = $conn->query("SELECT FOUND_ROWS() AS total");
    $totalCountRow = $foundRowsResult->fetch_assoc();
    $totalCount = $totalCountRow['total'];

    // Calculate total number of pages
    $totalPages = ceil($totalCount / $questionsPerPage);


echo "<div class='container text-center mt-4'>";
echo "<nav aria-label='Page navigation'>";
echo "<ul class='pagination justify-content-center'>";

// Previous button
echo "<li class='page-item'>";
if ($currentPage > 1) {
    echo "<a class='page-link' href='?page_num=" . ($currentPage - 1) . "&pagename=view_questions_s&per_page=$questionsPerPage&category=$searchCategory&search=$searchQuestion'>Previous</a>";
} else {
    echo "<span class='page-link disabled'>Previous</span>";
}
echo "</li>";

// Page numbers
echo "<li class='page-item'><span class='page-link'>$currentPage / $totalPages</span></li>";

// Next button
echo "<li class='page-item'>";
if ($currentPage < $totalPages) {
    echo "<a class='page-link' href='?page_num=" . ($currentPage + 1) . "&pagename=view_questions_s&per_page=$questionsPerPage&category=$searchCategory&search=$searchQuestion'>Next</a>";
} else {
    echo "<span class='page-link disabled'>Next</span>";
}
echo "</li>";

echo "</ul>";
echo "</nav>";
echo "</div>";

  
    $currentUrl = $_SERVER['REQUEST_URI'];


	echo "<div class='container mt-4'>";
echo "<form method='get' action='$currentUrl' class='row justify-content-center'>";
echo "<div class='col-md-6 form-group'>";
echo "<label for='pageNumber'>Page Number:</label>";
$totalPages = $totalPages ==0 ? 1 : $totalPages ; 
$currentPage  = $totalPages ==0 ? 1: $currentPage;
echo "<input type='number' id='pageNumber' name='page_num' value='$currentPage' class='form-control' min='1' max='$totalPages'>";
echo "</div>";
echo "<input type='hidden' id='' name='pagename' value='view_questions_s'>";
echo "<div class='col-md-6 form-group'>";
echo "<label for='questionsPerPage'>Questions Per Page:</label>";
echo "<input type='number' id='questionsPerPage' name='per_page' value='$questionsPerPage' class='form-control' min='1'>";
echo "</div>";
echo "<div class='col-md-6 form-group'>";
echo "<label for='searchCategory'>Search Category:</label>";
echo "<input type='text' id='searchCategory' name='category' value='$searchCategory' class='form-control'>";
echo "</div>";
echo "<div class='col-md-6 form-group'>";
echo "<label for='searchQuestion'>Search Question:</label>";
echo "<input type='text' id='searchQuestion' name='question' value='$searchQuestion' class='form-control'>";
echo "</div>";
echo "<div class='col-md-12 form-group'>";
echo "<button type='submit' class='btn btn-primary'>Update</button>";
echo "</div>";
echo "</form>";
echo "</div>";





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
                echo "<a href='index.php?pagename=add_edit&id=$questionId' class='btn btn-small btn-primary'>Edit Question</a>";
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



function insertPaper($userId, $papertitle, $paperValue) {
    global $conn;

    $sql = "INSERT INTO question_papers (title, value, user) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo("Error in preparing the statement: " . $conn->error);
        return false;
    }

    $papertitle = htmlspecialchars($papertitle, ENT_QUOTES, 'UTF-8');
    $paperValue = htmlspecialchars($paperValue, ENT_QUOTES, 'UTF-8');

    $stmt->bind_param("ssi", $papertitle, $paperValue, $userId);

    $result = $stmt->execute();

    if (!$result) {
        echo ("Error in executing the statement: " . $stmt->error);
        return false;
    }

    $paperId = $stmt->insert_id;

    $stmt->close();

    return $paperId;
}


function editPaper($paperId, $newPapertitle, $newPaperValue) {
    global $conn;

    $currentPaper = viewPaper($paperId);

    if ($currentPaper !== null) {
        $currentPapertitle = $currentPaper['title'];
        $currentPaperValue = $currentPaper['value'];

        if ($currentPapertitle != $newPapertitle || $currentPaperValue != $newPaperValue) {
            $sql = "UPDATE question_papers SET title = ?, value = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $newPapertitle, $newPaperValue, $paperId);
            $stmt->execute();

            return $stmt->affected_rows > 0;
        }

        // Values are the same, consider it a successful update
        return true;
    }

    // Paper not found
    return false;
}

function viewPaper($paperId) {
    global $conn;

    $sql = "SELECT * FROM question_papers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $paperId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $paper = $result->fetch_assoc();
        $stmt->close();
        return $paper;
    }

    return null;
}

function Question_Maker_page(){
	
	
	
	$_POST['cat'] = isset($_POST['cat'])? $_POST['cat']: []; 
	$_POST['numbr'] = isset($_POST['numbr']) ? $_POST['numbr'] : [];
	
	   global $conn;
	
	
	$questiondata = [];
	$msg =[];
	foreach ( $_POST['cat'] as $no => $cat ) {
		
		#ech=o $no = =( $no ) ; 
		
		$whereCondition = "AND FIND_IN_SET('$cat', meta_value) > 0 AND meta_name='meta_cat'";

		 $sql = "SELECT SQL_CALC_FOUND_ROWS q.id, q.question_text 
        FROM questions AS q 
        LEFT JOIN meta_data AS md ON md.referece_id = q.id 
        WHERE 1 $whereCondition 
        GROUP BY q.id  
        ORDER BY RAND()
        LIMIT 0, ?";
	
		$no = isset($_POST['numbr'][$no])?$_POST['numbr'][$no]: 0 ;  	
		
			#echo $sql ; 
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("i",$no );
		$stmt->execute();
		$result = $stmt->get_result();
		#echo $result->num_rows  ; 
		#echo "<br>";
		$r = [];
		if ($result->num_rows > 0) {
			$r = [];
			// Loop through each question
			while ($row = $result->fetch_assoc()) {
				
				
				$questionId = $row['id'];
				$questiondata[] = $questionId;
				#echo "<br>";
				$r[]= $questionId ; 
			}
		}
		if(!count($r)==0){
		$msg[] = $cat. " => " . implode(",",$r)." ";;
		}
		
	}
	$_POST['questionids'] = isset($_POST['questionids']) ?$_POST['questionids'] : [];
#	echo "---";
#	echo "<br>";
	$r = [];
	$p = [];
	foreach($_POST['questionids'] as $no => $id ){
		
		if(getCurrentQuestionText($id)){
			#echo $id;
			$questiondata[] = intval($id) ; 
			#"<br>";
			$p[] = $id ;
			
		}else{
			$r[]  = "$id";
		}
		
	}
	if (!empty($r)){
		$msg[]= 'Query is done. Subject Code Wise Conditions are converted into indivisual';
		
	}
	if (!empty($p)){
		$msg[] = "Ind => ".implode(",", $p ) ; 
	}
	if (!empty($r)){
		$msg[] = "Error=> ". implode(",", $r ) ; 
	}
	
	
	#var_dump($questiondata);
	# implode('#',$questiondata);
	$edit_mode = 0 ; 
	
	$paper_id = 0 ; 
	if(isset($_POST['submit']) ){
		if ( isset($_POST['paper_id']) ){
			
			$paper_id = viewPaper($_POST['paper_id']) ? $_POST['paper_id'] : 0 ; 
			
			if ($paper_id ) {
				$edit_mode=1 ;
				
			}else{
				
				echo "Error; Invalid Paper Id"  ;
			}
			
			
		}else{
			#not edit mode ;
			
		}
		if ( $edit_mode ==  0 ) {
			if(isset($_POST['papername']) && !empty($questiondata)){
				
				$data  =implode('#',$questiondata)  ; 
				$paper_id = insertPaper(isLoggedIn(),$_POST['papername'], $data) ; 
				$new_data = viewPaper($paper_id); 
				$questiondata = explode("#" , $new_data['value']);
				
				/////Transfer To Another Page
				$edit_mode = 1 ; 
				#var_dump($new_data);
			}else{
				echo "Error Empty title or Data" ; 
			}
		}else if($edit_mode == 1){
			// Editing 
			$data  =implode('#',$questiondata)  ; 
			$e  = editPaper($paper_id, $_POST['papername'], $data);
			$new_data = viewPaper($paper_id); 
			$questiondata = explode("#" , $new_data['value']);
			if ($e) {echo "edited";}
			else{echo "failed to edit ";}
			
		}
	}else if (isset($_GET['paper_id'])){
			$paper_id = viewPaper($_GET['paper_id']) ? $_GET['paper_id'] : 0 ; 
			
			if ($paper_id ) {
				$edit_mode=2 ;
				$new_data = viewPaper($paper_id); 
				$questiondata = explode("#" , $new_data['value']);
			}else{
				
				echo "Error; Invalid Paper Id/GET"  ;
			}
			
		
		
	}
	//echo currenturl(); 
	
	if(isset($_POST['papername']) && isset($_POST['Preview']) &&isset($_POST['paper_id'])){
		$paper_title  = htmlspecialchars($_POST['papername']);;
		$paper_id =intval($_POST['paper_id']);
	}else{
		$paper_title = $edit_mode != 0 ?$new_data['title'] : "";
	}
	
	echo "Question Maker" ; 
	
	$msg = implode("<br>", $msg ) ; 
	
	if($edit_mode!=2 & !empty($msg)){
		echo "<div class='alert alert-success' role='alert'>$msg</div>";
	}
	if($edit_mode!=0){
		echo "Paper id $paper_id";
	}
	    #echo "<form action='" . currenturl() . "' method='post'>";
	    echo "<form action='?pagename=question_maker' method='post'>";

	echo "<div class='form-group mb-2'>					
                        <div class='row align-items-center'>	
                            <div class='col-md-12 align-items-center'>
                                <div class='input-group'>
									
                                    <input type='text' value='$paper_title' placeholder='Question Paper Name' name='papername' id='papername'  class='form-control answer-input' required>
                                   
                                </div>
                            </div>
                        </div>
                    </div>"; 
	echo "<h2>Subject Code Wise</h2>";
	echo "<div class='form-group mt-3 col-md-12'>";
    echo "</div>";
	if($edit_mode!=0) {
		echo "<input type='hidden' name='paper_id' value='$paper_id'>";
	}
	echo "<button type='button' id='addAnswerField' class='btn btn-secondary'>Add </button>";
	echo "<h2>Indivisual</h2>";
	$indivIndex = 0 ; 
	$r = [];
	
	
	/// 

	
	
	if(isset($questiondata)){
		
		foreach($questiondata as $no => $qid) {
			echo " <div class='form-group mb-2'>					
                        <div class='row align-items-center'>	
                            <div class='col-md-12 align-items-center'>
                                <div class='input-group'>
									
                                    <input type='text' value='$qid' placeholder='Question ID' name='questionids[]' id='questionids$indivIndex'  class='form-control answer-input' >
                                   
                                </div>
                            </div>
                        </div>
                    </div>";
					$indivIndex++; 
			
		}
	}


	
	echo "<div class='indiv2 col-md-12'>";
    echo "</div>";

	echo "<button type='button' id='adindiv2' class='btn btn-secondary'>Add </button><br>";
	/*
	echo "<div class='mt-2'>";
	

						
	echo "<div class='col-md-6 align-items-center'>";
	echo "<button type='submit' name='submit' class='btn btn-primary'>Submit</button> ";
	echo "</div>";
	echo "<div class='col-md-6 align-items-center'>";
	echo "<button type='submit' name='Preview' class='btn btn-primary'>Preview</button>";
	echo "</div>";
	
	
	echo "</div>";
	*/
	
	echo "<div class='mt-2'>";

echo "<div class='row'>";
	echo "<div class='col-md-6 text-left'>";
	echo "<button type='submit' name='submit' class='btn btn-primary'>Submit</button>";
	echo "</div>";
	echo "<div class='col-md-6 text-right'>";
		echo "<div class='d-grid gap-2 d-md-flex justify-content-md-end'>";
; 
			echo "<button type='submit' name='Preview' class='btn btn-primary'>Preview</button>";
		echo "</div>";
	echo "</div>";
echo "</div>";



	echo " </form>";
  echo "<script>
        document.addEventListener('DOMContentLoaded', function () {
            var answerIndex = $indivIndex; // Set to the next available index
            var indivIndex = 0; // Set to the next available index
            
            document.getElementById('addAnswerField').addEventListener('click', function () {
                var newAnswerField = createAnswerField(answerIndex);
                document.querySelector('.form-group.mt-3').insertAdjacentHTML('beforebegin', newAnswerField);
                answerIndex++;
            });
			
			document.getElementById('adindiv2').addEventListener('click', function () {
                var newindivField = createindivField(answerIndex);
                document.querySelector('.indiv2').insertAdjacentHTML('beforebegin', newindivField);
                answerIndex++;
            });
			

            function createAnswerField(index) {
                return `
                    <div class='form-group mb-2 mt-2 col-md-12'>					
                        <div class='row align-items-center'>	
                            <div class='col-md-12 align-items-center'>
                                <div class='input-group'>
									
									<div class='col-md-6'>
                                    <input type='number' placeholder='Subject Code' name='cat[]' id='answer".'${index}'."'  class='form-control answer-input' >
                                    </div>
									
									<div class='col-md-6'>
									<input type='number' placeholder='Number of Question' name='numbr[]' id='answer".'${index}'."'  class='form-control answer-input' >
									</div>
								</div>
                            </div>
                        </div>
                    </div>`;
            }
			function createindivField(index) {
                return `
                    <div class='form-group mb-2'>					
                        <div class='row align-items-center'>	
                            <div class='col-md-12 align-items-center'>
                                <div class='input-group'>
									
                                    <input type='text' placeholder='Question ID' name='questionids[]' id='questionids".'${index}'."'  class='form-control answer-input' >
                                   
                                </div>
                            </div>
                        </div>
                    </div>`;
            }
        });
    </script>";
	#var_dump($_POST) ; 
}

function viewQuestionPapernav(){
    if (!isLoggedIn()){ 
        die("Serious Error"); 
    }
    
    global $conn;

    $currentPage = isset($_GET['page_num']) ?  htmlspecialchars ($_GET['page_num']) : 1; 
    $questionsPerPage = isset($_GET['per_page']) ?  htmlspecialchars ($_GET['per_page']) : 5;
    $searchTitle = isset($_GET['search']) ?  htmlspecialchars ($_GET['search']) : ''; // Add this line to get search query from URL
    
    // Calculate the offset
    $offset = ($currentPage - 1) * $questionsPerPage;

    // Construct the WHERE condition based on the search title
    $whereCondition = "";
    if (!empty($searchTitle)) {
        $whereCondition .= " WHERE title LIKE '%$searchTitle%'";
    }
    
    // Retrieve questions for the current page and count total rows
    $sql = "SELECT SQL_CALC_FOUND_ROWS * 
            FROM question_papers 
            $whereCondition
            ORDER BY timestamp DESC 
            LIMIT ?, ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $offset, $questionsPerPage);
    $stmt->execute();
    $result = $stmt->get_result();

    // Get total count of rows using SQL_CALC_FOUND_ROWS
    $foundRowsResult = $conn->query("SELECT FOUND_ROWS() AS total");
    $totalCountRow = $foundRowsResult->fetch_assoc();
    $totalCount = $totalCountRow['total'];

    // Calculate total number of pages
    $totalPages = ceil($totalCount / $questionsPerPage);

    echo "<div class='container text-center mt-4'>";
    echo "<nav aria-label='Page navigation'>";
    echo "<ul class='pagination justify-content-center'>";

    // Previous button
    echo "<li class='page-item'>";
    if ($currentPage > 1) {
        echo "<a class='page-link' href='?page_num=" . ($currentPage - 1) . "&pagename=view_question_paper_nav&per_page=$questionsPerPage&search=$searchTitle'>Previous</a>";
    } else {
        echo "<span class='page-link disabled'>Previous</span>";
    }
    echo "</li>";

    // Page numbers
    echo "<li class='page-item'><span class='page-link'>$currentPage / $totalPages</span></li>";

    // Next button
    echo "<li class='page-item'>";
    if ($currentPage < $totalPages) {
        echo "<a class='page-link' href='?page_num=" . ($currentPage + 1) . "&pagename=view_question_paper_nav&per_page=$questionsPerPage&search=$searchTitle'>Next</a>";
    } else {
        echo "<span class='page-link disabled'>Next</span>";
    }
    echo "</li>";

    echo "</ul>";
    echo "</nav>";
    echo "</div>";
 $Search = isset($_GET['search'])? htmlspecialchars($_GET['search']): "";
    // Display search form
	echo "<div class='container mt-4'>";
echo "<form method='get' action='".$_SERVER['PHP_SELF']."' class='row justify-content-center'>";
echo "<div class='col-md-6 form-group'>";
echo "<label for='pageNumber'>Page Number:</label>";
$totalPages = $totalPages ==0 ? 1 : $totalPages ; 
$currentPage  = $totalPages ==0 ? 1: $currentPage;
echo "<input type='number' id='pageNumber' name='page_num' value='$currentPage' class='form-control' min='1' max='$totalPages'>";
echo "</div>";
echo "<input type='hidden' id='' name='pagename' value='view_question_paper_nav'>";
echo "<div class='col-md-6 form-group'>";
echo "<label for='questionsPerPage'>Questions Per Page:</label>";
echo "<input type='number' id='questionsPerPage' name='per_page' value='$questionsPerPage' class='form-control' min='1'>";
echo "</div>";

echo "<div class='col-md-6 form-group'>";
echo "<label for='searchQuestion'>Search Question:</label>";
echo "<input type='text' id='searchQuestion' name='search' value='$Search' class='form-control'>";
echo "</div>";
echo "<div class='col-md-12 form-group'>";
echo "<button type='submit' class='btn btn-primary'>Update</button>";
echo "</div>";
echo "</form>";
echo "</div>";



    if ($result->num_rows > 0) {
        echo "<div class='container mt-4'>";
        echo "<h2 class='text-center mb-4'>Question Paper</h2>";

        // Loop through each question
        while ($row = $result->fetch_assoc()) {
            $paper_id = $row['id'];
            $paperTitle = $row['title'];
            $paperValue = $row['value'];
            $paperUser = $row['user'];
            $paperTimestamp = $row['timestamp'];

            // Display the question
            echo "<div class='card mb-4'>";
            echo "<div class='card-body'>";
            echo "<h5 class='card-title'>$paperTitle</h5>";
            echo "<p class='card-text'>Id: $paper_id</p>";
            echo "<p class='card-text'>Value: $paperValue</p>";
            echo "<p class='card-text'>User: $paperUser</p>";
            echo "<p class='card-text'>Timestamp: $paperTimestamp</p>";
            echo "<a href='index.php?pagename=question_maker&paper_id=$paper_id'>Edit</a>";
            echo " <a href='index.php?pagename=reprint&paper_id=$paper_id'>View</a>";
            echo " <a href='index.php?pagename=evaluate&paper_id=$paper_id'>Exam_link</a>";
            echo "</div>";
            echo "</div>";
        }

        echo "</div>";
    } else {
        echo "<p class='text-center mt-5'>No questions found.</p>";
    }
}

function qry_from_paper_id($paper_id){
	global $conn;
			if (viewPaper($paper_id)){
				$p_id = intval ( $paper_id ) ; 
				#var_dump( viewPaper($p_id));
				 $st1 = viewPaper($p_id)['value'];
				$qs = explode("#",$st1);
				echo "<br>";
				$ee1 = [];
				foreach($qs as $q_id){
					$ee2_ans= [];
					$ee2_ans[] = $q_id;
					
					
					#$answersSql = "SELECT id FROM answers WHERE question_id = ? ORDER BY RAND()";
					$answersSql = "SELECT id FROM answers WHERE question_id = ?";
					$stmt = $conn->prepare($answersSql);
					$stmt->bind_param("i", $q_id);
					$stmt->execute();
					$stmt->bind_result($answerId);


					while ($stmt->fetch()) {
						#echo $answerId."@";  
						$ee2_ans[] = $answerId;
						##echo $answerId ; 
						#echo "<br>";echo "<br>";
					}
					 $ee1[] = implode("@",$ee2_ans);
					#echo "<br>";
				}
				return $qry = trim(implode ("#",$ee1));
				# var_dump($qry);
			}else{
				return false ; 
				
			}
}

function suffle_qry($qry) {
    $string = trim($qry);
    $step1Array = explode("#", $string);
    $finalArray = [];
    foreach ($step1Array as $step1Item) {
        $step2Array = explode("@", $step1Item);
		if(!empty($step2Array)){
			$q_id = $step2Array[0];
		}
		array_shift($step2Array);
        shuffle($step2Array);
        array_unshift($step2Array,$q_id);
		#array_unshift($step2Array, array_shift($step2Array)); // Move the first element to the front
        $finalArray[] = implode("@", $step2Array);
    }
    shuffle($finalArray);
    return implode("#", $finalArray);
}


#function evaluateFun(){}
function evaluateFun() {
    if (!isLoggedIn()){ 
        die("Serious Error"); 
    }
    global $conn ; 
	$currenturl = currenturl();
	
  
  
  $paper_id = isset($_GET['paper_id'])  ?$_GET['paper_id']: 0; 
  
  
  
  
  
  $qry ="";
  if ($paper_id) {
	
	  if(viewEvaluationRowIdByUserIdAndPaperId(isLoggedIn(),$paper_id)){
		  $eva_id = viewEvaluationRowIdByUserIdAndPaperId(isLoggedIn(),$paper_id);
		  $eva_row =  viewEvaluationRow($eva_id);
		  $qry = $eva_row['Qdata'];;
	  }
     else if (isset($_POST['reprinttext'])){
		 $qry = htmlspecialchars($_POST['reprinttext']);
	 }else{
		 $qry = suffle_qry(qry_from_paper_id($paper_id));
	 }
  }else if(isset($_GET['watch'])){
	  $watch_id  = intval($_GET['watch']) ; 
	  $watch = viewEvaluationRow($watch_id); 
	  
	  if($watch) {
	  $qry = $watch['Qdata'] ; 
	 # $answers = $watch['Adata'] ; 
	  $eva_id  = $watch_id ; 
	  $eva_row = $watch ; 
	  }
	 $mode = 1;  
	
	  
  }
  else{
	 
	 if (isset($_POST['reprinttext'])){
		 $qry = htmlspecialchars($_POST['reprinttext']);
	 }else{
		  echo $form =  "<form method='post' class='printable-hidden' action='$currenturl'>
	<div class='form-group '>
	  <label for='exampleTextarea'>Your Textarea Label:</label>
	  <textarea class='form-control' name='reprinttext' id='exampleTextarea' rows='2' placeholder='Enter your text here...'></textarea>
	</div>
	  
	<button type='submit' class='btn btn-primary'>Submit</button>
	</form>" ;
	  echo $form =  "<form method='get' class='printable-hidden' action='$currenturl'>
	<div class='form-group '>
	
	  <input type='hidden' name='pagename' value='evaluate'>
	  <input type='text' name='paper_id' class='form-control answer-input' placeholder='Paper Id '>
	</div>
	  
	<button type='submit' class='btn btn-primary'>Submit</button>
	</form>" ;
		 
		 
	 }
	  
  }
  
  echo "<br>";
	
	if(isset($_POST['answers'])){
		$answers =   $_POST['answers'];
	}
	else if( isset($eva_id)){
		$answers = json_decode($eva_row['Adata'],true);
		#var_dump($answers);
	}else{
		$answers = [];
	}
	
	$mode = isset($_POST['submit'])? 1 : 0 ;
	if(isset($eva_id)){$mode = 1 ;}
	if(isset($_GET['watch'])){$mode=1;}
	
	 "<div class='mb-3'>
  <textarea class='form-control' id='exampleFormControlTextarea1' rows='3'>$qry</textarea>
</div>" ;
 "<div class='mb-3'>
  <textarea class='form-control' id='exampleFormControlTextarea1' rows='3'>".json_encode($answers)."</textarea>
</div>" ;
	$a = evaluate_paper($qry,$answers,$mode);
	$result = [$a[1] ,$a[2] ,$a[3]];
	if($paper_id  && isset($_POST['submit'])){
		if(!isset($eva_id)){
			#create new row;
			$insert_id = insertEvaluationRow(isLoggedIn(), $paper_id, $qry, json_encode($answers), json_encode($result)); 
			echo "insert id is $insert_id" ; 
		}else{
			#update again the result
			echo json_encode($answers) ; 
			$update = modifyEvaluationRow($eva_id, isLoggedIn(), $paper_id, $qry, json_encode($answers), json_encode($result));
			if( $update){
			echo "evaluation Updated###"; 
			}
		}
	}
	#var_dump($_POST) ;
	#var_dump($a ) ;
	if( isset($eva_id) or isset($_POST['submit'])){
		echo "<div class='alert alert-success' role='alert'>" ; 
		echo "Correct Ans: ".$a[1]; 
		echo "</div>";
		echo "<div class='alert alert-danger' role='alert'>" ;
		echo "Wrong Ans: ".$a[2] ; 
		echo "</div>";
		echo "<div class='alert alert-warning' role='alert'>" ;
		echo "Not Touched: ".$a[3] ; 
		echo "</div>";
		}
	
	echo $a[0] ;
	
	
}

function evaluate_paper($qry,$answers=[] ,$evaluatemode=0) {
	 
	$num_positive = 0 ; 
	$num_negative = 0 ; 
	$num_not_answer = 0 ; 
	$printdata ="";
    if ($qry) {

        $questions = explode("#", $qry);
        $printdata .=  "<form method='post'>";
		$printdata .= "<input type='hidden' name='reprinttext' value='$qry'>";
        foreach ($questions as $question) {
            $data = explode("@", $question);
            $questionId = array_shift($data);
            
			
             $isdisabled= $evaluatemode==1? "disabled": "";
			
			if(getCurrentQuestionText($questionId)){
				 
				$printdata .=  "<div class='card'>";
				$printdata .=  "<div class='card-body'>";
				$printdata .=  "<p>Question ID: $questionId</p>";
				
				$printdata .=  '  <div class="card-title">';
				
				$printdata .=  $questionText = getCurrentQuestionText($questionId); 
				$printdata .=  ' </div>';
				#$printdata .=  "<br>" ; 
				#$mk = 1 ; 
				$pos_ans = 0 ;
				$neg_ans = 0 ;
				$ckbutnot_ans = 0 ; 
				foreach ($data as $answerId) {
					
					$ansdetail = getCurrentAnswerDetails($answerId);
					if($ansdetail){
						// Retrieve answer text based on answer ID
						$answerText = $ansdetail['answer_text'];
						$isCorrect = $ansdetail['is_correct'];
						$checked = isset($answers[$questionId][$answerId]) ? "checked " : "";
						$printdata .=  "<input type='checkbox' class='form-check-input' id='ck-$questionId-$answerId' name='answers[$questionId][$answerId]' value='$answerId'  $checked  $isdisabled>
						<label for='ck-$questionId-$answerId'>$answerText</label>" ;
						
						if($isCorrect && $evaluatemode){
							#$printdata .=  " (This is correct Answer)";
							$printdata .= ' <span class="badge rounded-pill text-bg-success"> Right Answer</span>';
							
						}
						
						
						if($isCorrect && isset($answers[$questionId][$answerId])){
							$pos_ans = 1; 
							
						}
						#$printdata .= $questionId."-".$answerId;
						if($isCorrect && !isset($answers[$questionId][$answerId])){
							#echo $neg_ans =  1 ; // you can do negative marking too; 
							#$post_ans = 0 ; 
							#$printdata .= "Correct but not answered";
							$ckbutnot_ans= 1 ; 
						}
						
						if(!$isCorrect && isset($answers[$questionId][$answerId]))
						{
							 #$pos_ans = 0 ; 
							 $neg_ans =  1 ; // you can do negative marking too; 
							 $printdata .= ' <span class="badge rounded-pill text-bg-danger"> Wrong Answer</span>';
						}
						
						
						
							$printdata .=  "<br>"; 
					}	
				}
				
				#if($pos_ans= 1 && $ckbutnot_ans==1){
				#	$pos_ans = 0 ; 
				#	$neg_ans = 1 ;
				#}
				if($pos_ans == 1 && $ckbutnot_ans==1){
					$post_ans = 0 ; 
					$neg_ans = 1; 
				}
				if($pos_ans == 0 && $neg_ans == 0 ){
					$num_not_answer += 1; 
				}
				 $num_positive += ($neg_ans)==0 ? $pos_ans: 0;
				 $num_negative += $neg_ans;
				$printdata .=  "</div>";
				$printdata .=  "</div>";
				
			}
			
            
        }
		if(!$evaluatemode){
			
			$printdata .=  "<div class='d-grid gap-2 col-6 mx-auto'><button type='submit' class='btn btn-success' name='submit'>Submit</button></div>";
		}
        $printdata .=  "</form>";
    } else {
        $printdata .=  "Error: Paper not found.";
    }
	
	$re = []; 
	$re[] = $printdata;
	$re[] = $num_positive;
	$re[] = $num_negative;
	$re[] = $num_not_answer;
	$re[] = $qry;
	$re[] = $answers;
	
	return $re;
}


##evaluation database function 

function insertEvaluationRow($user_id, $p_id, $Qdata, $Adata, $result) {
    global $conn;

    $stmt = $conn->prepare("INSERT INTO evaluation (user_id, paper_id, Qdata, Adata, result) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $user_id, $p_id, $Qdata, $Adata, $result);
    
    if ($stmt->execute()) {
        return $stmt->insert_id; // Return the ID of the inserted row
    } else {
        return false; // Insertion failed
    }
}
function viewEvaluationRowIdByUserIdAndPaperId($uid, $p_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT id FROM evaluation WHERE user_id=? AND paper_id = ?");
    $stmt->bind_param("ii", $uid, $p_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id']; // Return the ID of the first row
    } else {
        return false; // Row not found
    }
}

function viewEvaluationRow($id) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM evaluation WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc(); // Return the row as an associative array
    } else {
        return false; // Row not found
    }
}
function modifyEvaluationRow($id, $user_id, $p_id, $Qdata, $Adata, $result) {
    global $conn;

    $stmt = $conn->prepare("UPDATE evaluation SET user_id = ?, paper_id = ?, Qdata = ?, Adata = ?, result = ? WHERE id = ?");
    $stmt->bind_param("iisssi", $user_id, $p_id, $Qdata, $Adata, $result, $id);
    
    if ($stmt->execute()) {
        return true; // Return true if update was successful
    } else {
        // Log error message
        error_log("Failed to update evaluation row: " . $stmt->error);

        return false; // Return false indicating update failure
    }
}


function deleteEvaluationRow($id) {
    global $conn;

    $stmt = $conn->prepare("DELETE FROM evaluation WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    return $stmt->execute(); // Return true if deletion was successful
}
function viewEvaluationResults() {
    if (!isLoggedIn()) { 
        die("Serious Error"); 
    }
    
    global $conn;

    $currentPage = isset($_GET['page_num']) ? htmlspecialchars($_GET['page_num']) : 1; 
    $resultsPerPage = isset($_GET['per_page']) ? htmlspecialchars($_GET['per_page']) : 5;
    $searchPaperId = isset($_GET['search_paper_id']) ? htmlspecialchars($_GET['search_paper_id']) : '';
    $searchUserId = isset($_GET['search_user_id']) ? htmlspecialchars($_GET['search_user_id']) : '';

    // Calculate the offset
    $offset = ($currentPage - 1) * $resultsPerPage;

    // Construct the WHERE condition based on search parameters
    $whereCondition = "";
    if (!empty($searchPaperId)) {
        $whereCondition .= " AND paper_id = '$searchPaperId'";
    }
    if (!empty($searchUserId)) {
        $whereCondition .=  " AND user_id = '$searchUserId'";
    }

    // Retrieve evaluation results for the current page and count total rows
    $sql = "SELECT SQL_CALC_FOUND_ROWS * 
            FROM evaluation WHERE 1=1
            $whereCondition
            ORDER BY timestamp DESC 
            LIMIT ?, ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $offset, $resultsPerPage);
    $stmt->execute();
    $result = $stmt->get_result();

    // Get total count of rows using SQL_CALC_FOUND_ROWS
    $foundRowsResult = $conn->query("SELECT FOUND_ROWS() AS total");
    $totalCountRow = $foundRowsResult->fetch_assoc();
    $totalCount = $totalCountRow['total'];

    // Calculate total number of pages
    $totalPages = ceil($totalCount / $resultsPerPage);

    // Display pagination and search form
    echo "<div class='container text-center mt-4'>";
    echo "<nav aria-label='Page navigation'>";
    echo "<ul class='pagination justify-content-center'>";

    // Previous button
    echo "<li class='page-item'>";
    if ($currentPage > 1) {
        echo "<a class='page-link' href='?page_num=" . ($currentPage - 1) . "&pagename=view_evaluation_results&per_page=$resultsPerPage&search_paper_id=$searchPaperId&search_user_id=$searchUserId'>Previous</a>";
    } else {
        echo "<span class='page-link disabled'>Previous</span>";
    }
    echo "</li>";

    // Page numbers
    echo "<li class='page-item'><span class='page-link'>$currentPage / $totalPages</span></li>";

    // Next button
    echo "<li class='page-item'>";
    if ($currentPage < $totalPages) {
        echo "<a class='page-link' href='?page_num=" . ($currentPage + 1) . "&pagename=view_evaluation_results&per_page=$resultsPerPage&search_paper_id=$searchPaperId&search_user_id=$searchUserId'>Next</a>";
    } else {
        echo "<span class='page-link disabled'>Next</span>";
    }
    echo "</li>";

    echo "</ul>";
    echo "</nav>";
    echo "</div>";

    // Display search form
    echo "<div class='container mt-4'>";
    echo "<form method='get' action='".$_SERVER['PHP_SELF']."' class='row justify-content-center'>";
	
	echo "<div class='col-md-6 form-group'>";
echo "<label for='pageNumber'>Page Number:</label>";
$totalPages = $totalPages ==0 ? 1 : $totalPages ; 
$currentPage  = $totalPages ==0 ? 1: $currentPage;
echo "<input type='number' id='pageNumber' name='page_num' value='$currentPage' class='form-control' min='1' max='$totalPages'>";
echo "</div>";
echo "<input type='hidden' id='' name='pagename' value='view_evaluation_results'>";
echo "<div class='col-md-6 form-group'>";
echo "<label for='questionsPerPage'>Result Per Page:</label>";
echo "<input type='number' id='questionsPerPage' name='per_page' value='$resultsPerPage' class='form-control' min='1'>";
echo "</div>";



    echo "<div class='col-md-6 form-group'>";
    echo "<label for='searchPaperId'>Search Paper ID:</label>";
    echo "<input type='text' id='searchPaperId' name='search_paper_id' value='$searchPaperId' class='form-control'>";
    echo "</div>";
    echo "<div class='col-md-6 form-group'>";
    echo "<label for='searchUserId'>Search User ID:</label>";
    echo "<input type='text' id='searchUserId' name='search_user_id' value='$searchUserId' class='form-control'>";
    echo "</div>";
    echo "<div class='col-md-12 form-group'>";
    echo "<button type='submit' class='btn btn-primary'>Search</button>";
    echo "</div>";
    echo "</form>";
    echo "</div>";

    // Display evaluation results
    if ($result->num_rows > 0) {
        echo "<div class='container mt-4'>";
        echo "<h2 class='text-center mb-4'>Evaluation Results</h2>";

       if ($result->num_rows > 0) {
    echo "<div class='container mt-4'>";
    echo "<h2 class='text-center mb-4'>Evaluation Results</h2>";
    echo "<div class='table-responsive'>";
    echo "<table class='table table-striped'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>Eval. ID</th>";
    echo "<th>User ID</th>";
    echo "<th>Paper ID</th>";
    #echo "<th>Qdata</th>";
   # echo "<th>Adata</th>";
    echo "<th>Result</th>";
    echo "<th>Timestamp</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";

    // Loop through each evaluation result
    while ($row = $result->fetch_assoc()) {
        $evaluationId = $row['id'];
        $userId = $row['user_id'];
        $paperId = $row['paper_id'];
        $Qdata = $row['Qdata'];
        $Adata = $row['Adata'];
        $resultData = $row['result'];
        $timestamp = $row['timestamp'];

        // Display each row as a table row
        echo "<tr>";
        echo "<td>$evaluationId <a href= '?pagename=evaluate&watch=$evaluationId' >View</a></td>";
        echo "<td>$userId</td>";
        echo "<td>$paperId</td>";
        #echo "<td>$Qdata</td>";
       # echo "<td>$Adata</td>";
        echo "<td>$resultData</td>";
        echo "<td>$timestamp</td>";
        echo "</tr>";
    }

    echo "</tbody>";
    echo "</table>";
    echo "</div>"; // Close table-responsive
    echo "</div>"; // Close container
} else {
    echo "<p class='text-center mt-5'>No evaluation results found.</p>";
}


        echo "</div>";
    } else {
        echo "<p class='text-center mt-5'>No evaluation results found.</p>";
    }
}




// Function to generate a unique token
function generateToken() {
    return bin2hex(random_bytes(32)); // Generate a 32-byte random token
}

// Function to add token to form
function addTokenToForm($formName='form') {
    $token1 = generateToken();
    $token2 = generateToken();
    $_SESSION[$token2 . '_token'] = $token1; // Store token in session for verification
    $a =  "<input type='hidden' name='{$formName}_token' value='$token1'>";
    $a .= "<input type='hidden' name='$token1' value='$token2'>";
	return $a ;
}

// Function to verify token on form submission

// Function to verify token on form submission
function verifyToken($formName='form') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tokenName = $formName . '_token';
		
        if (isset($_POST[$tokenName])) {
             $token1 = $_POST[$tokenName];
		  $token2 = isset($_POST[$token1]) ? $_POST[$token1] : null;
		  #var_dump($_POST);
#var_dump($_SESSION);
            if ($token2 && isset($_SESSION[$token2 . '_token'])) {
				#echo 222222222;
                $sessionToken = $_SESSION[$token2 . '_token'];
                
                if ($token1 === $sessionToken) {
                    // Token is valid, proceed with form submission
					 unset($_SESSION[$token2 . '_token']);
                    return true;
                }
            }
			
        }
        // Token is invalid or missing
		
        return false;
    }
    // Not a POST request, no need to verify token
    return true;
}
