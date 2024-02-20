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
/*
	echo "<div class='container text-center mt-4'>";
	echo "<div class='btn-group' role='group'>";
	if ($currentPage > 1) {
		echo "<a href='?page_num=" . ($currentPage - 1) . "&pagename=view_questions_s&per_page=$questionsPerPage&category=$searchCategory&search=$searchQuestion' class='btn btn-primary'>Previous</a> ";
	}
	
		echo "<div class='mt-2' class='btn btn-primary'>$currentPage / $totalPages</div>";

	if ($currentPage < $totalPages) {
		echo "<a href='?page_num=" . ($currentPage + 1) . "&pagename=view_questions_s&per_page=$questionsPerPage&category=$searchCategory&search=$searchQuestion' class='btn btn-primary'>Next</a>";
	}
	echo "</div>";
	echo "</div>";
*/


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

/*
    echo "<div class='text-center mt-4'>";
    echo "<form method='get' action='$currentUrl'>";
    echo "<label for='pageNumber'>Page Number:</label>";
    echo "<input type='number' id='pageNumber' name='page_num' value='$currentPage' min='1' max='$totalPages'>";
    echo "<input type='hidden' id='' name='pagename' value='view_questions_s'>";
    echo "<label for='questionsPerPage'>Questions Per Page:</label>";
    echo "<input type='number' id='questionsPerPage' name='per_page' value='$questionsPerPage' min='1'>";
	
    echo "<br><label for='searchCategory'>Search Category:</label>"; // Added label for search category
    # echo "<label for='searchCategory'>Search Category:</label>";

	echo "<input type='text' id='searchCategory' name='category' value='$searchCategory'>"; // Added input box for search category
	 echo "<label for='searchQuestion'>Search Category:</label>";
	    echo "<input type='text' id='searchQuestion' name='search' value='$searchQuestion'>"; // Added input box for search category
    echo "<button type='submit' class='btn btn-primary'>Update</button>";
    echo "</form>";
    echo "</div>";
*/

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
