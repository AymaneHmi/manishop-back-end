<?php

include '../../config/headers.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = filter_var($postdata['title'], FILTER_SANITIZE_STRING);
    $description = filter_var($postdata['description'], FILTER_SANITIZE_STRING);
    $image = $postdata['image'];

    if(empty($title) || empty($description) || empty($image)){
        $response = array( 'error'  => 'Data is requiered!');
        echo json_encode($response);
        exit;
    }

    if(!$conn) {
        $response = array( 'error'  => 'could not connect to database!');
        echo json_encode($response);
        exit;
    }

    $query = "INSERT INTO categories (name , description) VALUES (? , ?)";
    $stmt = $conn->prepare($query);

    $stmt->bind_param("ss", $title, $description);

    if(!$stmt->execute()){
        $response = array( 'error'  => 'could not inset data to database!');
        echo json_encode($response);
        exit;
    }

    $stmt->close();

    $category_id = $conn->insert_id;
    list($type, $data) = explode(';', $image);
    list(, $data) = explode(',', $data);
    $data = base64_decode($data);

    $image_extension = '';
    if (strpos($type, 'image/png') !== false) {
        $image_extension = 'png';
    } elseif (strpos($type, 'image/jpeg') !== false) {
        $image_extension = 'jpg';
    } elseif (strpos($type, 'image/gif') !== false) {
        $image_extension = 'gif';
    }

    $filename = uniqid() . '.' . $image_extension;

    $uploadDirectory = '../../imgs/categories/' . $filename;

    if(!file_put_contents($uploadDirectory, $data)) {
        $response = array( 'error' => 'Failed to upload image');
        echo json_encode($response);
        exit;
    }

    $sql = "UPDATE categories SET image='$filename' WHERE category_id='$category_id'";

    if(!$conn->query($sql)) {
        $response = array( 'error' => 'Failed to update image row in database');
        echo json_encode($response);
        exit;
    }

    $response = array( 'success' => 'category inserted successfuly.');

    echo json_encode($response);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'];

    if(empty($id)) {
        $response = array('error' => 'id is requiered');
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT * FROM categories WHERE category_id = '$id'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $category = mysqli_fetch_assoc($result);
    
        $response = array(
            'id' => $id,
            'name' => $category['name'],
            'description' => $category['description'],
            'image' => [$category['image']],
        );
        
    } else {
        $response = array("error" => "category not found");
        echo json_encode($response);
        exit;
    }

    echo json_encode($response);
}

if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $id = filter_var($postdata['id'], FILTER_SANITIZE_STRING);
    $title = filter_var($postdata['title'], FILTER_SANITIZE_STRING);
    $description = filter_var($postdata['description'], FILTER_SANITIZE_STRING);
    $image = $postdata['upload_image'];
    $delete_image = $postdata['delete_image'];

    if(empty($title) || empty($description) || empty($id)){
        $response = array( 'error'  => 'Data is requiered!');
        echo json_encode($response);
        exit;
    }

    if((empty($delete_image) && $image) || ($delete_image && empty($image))){
        $response = array( 'error'  => 'you have to replace image!');
        echo json_encode($response);
        exit;
    }

    if(!$conn) {
        $response = array( 'error'  => 'could not connect to database!');
        echo json_encode($response);
        exit;
    }

    $query = "UPDATE categories SET name=?, description=? WHERE category_id=?";
    $stmt = $conn->prepare($query);

    $stmt->bind_param("ssi", $title, $description, $id);

    if (!$stmt->execute()) {
        $response = array( 'error' => 'Could not update data in the database!');
        echo json_encode($response);
        exit;
    }

    $stmt->close();

    
    if(!empty($image) && !empty($delete_image)){
        $image_path = '../../imgs/categories/' . $delete_image;
        if (file_exists($image_path)) {
            if(!unlink($image_path)){
                $response = array('error' => 'Could not delete image');
                echo json_encode($response);
                exit;
            }
        } else {
            $response = array('error' => "image(s) couldn't found.");
            echo json_encode($response);
            exit;
        }

        list($type, $data) = explode(';', $image);
        list(, $data) = explode(',', $data);
        $data = base64_decode($data);

        $image_extension = '';
        if (strpos($type, 'image/png') !== false) {
            $image_extension = 'png';
        } elseif (strpos($type, 'image/jpeg') !== false) {
            $image_extension = 'jpg';
        } elseif (strpos($type, 'image/gif') !== false) {
            $image_extension = 'gif';
        }

        $filename = uniqid() . '.' . $image_extension;

        $uploadDirectory = '../../imgs/categories/' . $filename;

        if(!file_put_contents($uploadDirectory, $data)) {
            $response = array( 'error' => 'Failed to upload image');
            echo json_encode($response);
            exit;
        }

        $sql = "UPDATE categories SET image='$filename' WHERE category_id='$id'";

        if(!$conn->query($sql)) {
            $response = array( 'error' => 'Failed to update image row in database');
            echo json_encode($response);
            exit;
        }
    }

    $response = array( 'success' => 'Category updated successfuly.');

    echo json_encode($response);
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = filter_var($postdata['id'], FILTER_SANITIZE_STRING);

    if(empty($id)) {
        $response = array('error' => 'id is requiered');
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT * FROM categories WHERE category_id = '$id'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 0) {
        $response = array("error" => "category not found");
        echo json_encode($response);
        exit;
    }
    
    $category = mysqli_fetch_assoc($result);

    $sql = "SELECT * FROM subcategories WHERE category_id = '$id'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        $response = array('error' => 'there is a subcategory with this category.');
        echo json_encode($response);
        exit;
    }
    
    $filename = $category['image'];
    $filepath = '../../imgs/categories/' . $filename;
    if (file_exists($filepath)) {
        if (!unlink($filepath)) {
            $response = array("error" => "probleme accured when deleting this image");
            echo json_encode($response);
            exit;
        }
        $delete = "DELETE FROM categories WHERE category_id = ?";
        $stmt = mysqli_prepare($conn, $delete);
        mysqli_stmt_bind_param($stmt, "s", $id);
        if (!mysqli_stmt_execute($stmt)) {
            $response = array("error" => "category not deleted");
            echo json_encode($response);
            exit;
        }
        mysqli_stmt_close($stmt);
    } else {
        $response = array("error" => "image not exist to delete");
        echo json_encode($response);
        exit;
    }

    $response = array('success' => 'category deleted successfuly.');

    echo json_encode($response);
}
