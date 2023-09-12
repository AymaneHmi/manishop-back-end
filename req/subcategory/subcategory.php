<?php

include '../../config/headers.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = filter_var($postdata['title'], FILTER_SANITIZE_STRING);
    $category_id = filter_var($postdata['category'], FILTER_SANITIZE_STRING);
    $sizes = $postdata['sizes'];

    if(empty($title) || empty($category_id)){
        $response = array('error'  => 'Data is requiered!');
        echo json_encode($response);
        exit;
    }

    if(!$conn) {
        $response = array('error'  => 'could not connect to database!');
        echo json_encode($response);
        exit;
    }

    $sizes_json = null;

    if(!empty($sizes)){
        $sizes_json = json_encode($sizes);
    }

    $query = "INSERT INTO subcategories (name , size , category_id) VALUES (? , ? , ?)";
    $stmt = $conn->prepare($query);

    $stmt->bind_param("ssi", $title, $sizes_json, $category_id);

    if(!$stmt->execute()){
        $response = array('error'  => 'could not inset data to database!');
        echo json_encode($response);
        exit;
    }

    $stmt->close();

    $response = array('success' => 'subcategory inserted successfuly.');

    echo json_encode($response);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'];

    if(empty($id)) {
        $response = array('error' => 'id is requiered');
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT * FROM subcategories WHERE subcategory_id = '$id'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        // Fetch the blog
        $subcategory = mysqli_fetch_assoc($result);
        // $category_id = $subcategory['category_id'];

        // $categoryQuery = "SELECT * FROM categories WHERE category_id='$category_id'";
        // $categoryResult = mysqli_query($conn, $categoryQuery);
        // $category = mysqli_fetch_assoc($categoryResult);

        $response = array(
            'id' => $id,
            'name' => $subcategory['name'],
            'category' => $subcategory['category_id'],
            'sizes' => json_decode($subcategory['size'], true),
        );
        
    } else {
        // User with the provided email does not exist, set the variable to false
        $response = array("error" => "category not found");
        echo json_encode($response);
        exit;
    }

    echo json_encode($response);
}

if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $id = filter_var($postdata['id'], FILTER_SANITIZE_STRING);
    $title = filter_var($postdata['title'], FILTER_SANITIZE_STRING);
    $category_id = filter_var($postdata['category'], FILTER_SANITIZE_STRING);
    $sizes = $postdata['sizes'];

    if(empty($id) || empty($title) || empty($category_id)){
        $response = array('error'  => 'Data is requiered!');
        echo json_encode($response);
        exit;
    }

    if(!$conn) {
        $response = array('error'  => 'could not connect to database!');
        echo json_encode($response);
        exit;
    }

    if(empty($sizes)){
        $sizes_json = null;
    } else {
        $sizes_json = json_encode($sizes);
    }

    $query = "UPDATE subcategories SET name=?, category_id=?, size=? WHERE subcategory_id=?";
    $stmt = $conn->prepare($query);

    $stmt->bind_param("sisi", $title, $category_id, $sizes_json, $id);

    if (!$stmt->execute()) {
        $response = array('error' => 'Could not update data in the database!');
        echo json_encode($response);
        exit;
    }

    $stmt->close();

    $response = array('success' => 'Subategory updated successfuly.');

    echo json_encode($response);
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = filter_var($postdata['id'], FILTER_SANITIZE_STRING);

    if(empty($id)) {
        $response = array('error' => 'id is requiered');
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT * FROM subcategories WHERE subcategory_id = '$id'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $subcategory = mysqli_fetch_assoc($result);

        $product_sql = "SELECT * FROM products WHERE subcategory_id = '$id'";
        $product_result = mysqli_query($conn, $product_sql);

        if(mysqli_num_rows($product_result) >= 1){
            echo json_encode(array('error' => 'there is product with this subcategory.'));
            exit;
        }
        // Delete the subcategory from the database
        $delete = "DELETE FROM subcategories WHERE subcategory_id = ?";
        $stmt = mysqli_prepare($conn, $delete);
        mysqli_stmt_bind_param($stmt, "s", $id);
        if (!mysqli_stmt_execute($stmt)) {
            $response = array("error" => "subcategory not deleted");
            echo json_encode($response);
            exit;
        }
        mysqli_stmt_close($stmt);
        
    } else {
        $response = array("error" => "subcategory not found");
        echo json_encode($response);
        exit;
    }

    $response = array('success' => 'subcategory deleted successfuly.');

    echo json_encode($response);
}
