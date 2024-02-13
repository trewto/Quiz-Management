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


