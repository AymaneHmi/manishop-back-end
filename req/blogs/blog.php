<?php

include '../../config/headers.php';
include '../../config/funcs.php';


$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = filter_var($postdata['title'], FILTER_SANITIZE_STRING);
    $description = filterHtmlTags($postdata['description']);
    $image = $postdata['image'];
    $tags = $postdata['tags'];
    $user_id = filter_var($postdata['userId'], FILTER_SANITIZE_STRING);

    if(empty($title) || empty($description) || empty($image) || empty($tags) || empty($user_id)){
        echo json_encode(array('error' => 'fill all fields.'));
        exit;
    }

    if(!$conn) {
        $response = array('error'  => 'could not connect to postdatabase!');
        echo json_encode($response);
        exit;
    }

    $slug = createSlug($title, 'blogs');
    $tags_json = json_encode($tags);

    $query = "INSERT INTO blogs (title , description, slug, tags, user_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    $stmt->bind_param("ssssi", $title, $description, $slug, $tags_json, $user_id);

    if(!$stmt->execute()){
        $response = array('error'  => 'could not inset postdata to postdatabase!');
        echo json_encode($response);
        exit;
    }

    $stmt->close();

    $blog_id = $conn->insert_id;
    // Extract image postdata from the Base64 string
    list($type, $postdata) = explode(';', $image);
    list(, $postdata) = explode(',', $postdata);
    $postdata = base64_decode($postdata);

    // Determine the image extension based on the image type
    $image_extension = '';
    if (strpos($type, 'image/png') !== false) {
        $image_extension = 'png';
    } elseif (strpos($type, 'image/jpeg') !== false) {
        $image_extension = 'jpg';
    } elseif (strpos($type, 'image/gif') !== false) {
        $image_extension = 'gif';
    } else {
        // Handle other image types if needed
    }

    // Generate a unique filename for each image
    $filename = uniqid() . '.' . $image_extension;

    // Save the image to a directory on the server
    $uploadDirectory = '../imgs/blogs/' . $filename;

    if(!file_put_contents($uploadDirectory, $postdata)) {
        $response = array('error' => 'Failed to upload image');
        echo json_encode($response);
        exit;
    }

    $sql = "UPDATE blogs SET image='$filename' WHERE blog_id='$blog_id'";

    if(!$conn->query($sql)) {
        $response = array('error' => 'Failed to update image row in postdatabase');
        echo json_encode($response);
        exit;
    }

    $response = array('success' => 'blog inserted successfuly.');

    echo json_encode($response);
}

// ------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {

}

// -----------------------------

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    $id = filter_var($postdata['id'], FILTER_SANITIZE_STRING);

    if(empty($id)) {
        $response = array('error' => 'id is requiered');
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT * FROM blogs WHERE blog_id = '$id'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        // Fetch the blog
        $blog = mysqli_fetch_assoc($result);

        $filename = $blog['image'];
        $filepath = '../imgs/blogs/' . $filename;
        if (file_exists($filepath)) {
            if (!unlink($filepath)) {
                $response = array("error" => "probleme accured when deleting this image");
                echo json_encode($response);
                exit;
            }
            // Delete the blog from the postdatabase
            $delete = "DELETE FROM blogs WHERE blog_id = ?";
            $stmt = mysqli_prepare($conn, $delete);
            mysqli_stmt_bind_param($stmt, "s", $id);
            if (!mysqli_stmt_execute($stmt)) {
                // Error
                $response = array("error" => "blog not deleted");
                echo json_encode($response);
                exit;
            }
            mysqli_stmt_close($stmt);
        } else {
            $response = array("error" => "image not exist to delete");
            echo json_encode($response);
            exit;
        }

        
    } else {
        // User with the provided email does not exist, set the variable to false
        $response = array("error" => "blog not found");
        echo json_encode($response);
        exit;
    }

    $response = array('success' => 'blog deleted successfuly.');

    echo json_encode($response);
}

// -----------------------------

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $slug = $_GET['slug'];

    if(empty($slug)) {
        $response = array('error' => 'slug is requiered');
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT * FROM blogs WHERE slug = '$slug'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        // Fetch the blog
        $blog = mysqli_fetch_assoc($result);
        
        $user_id = $blog['user_id'];
        $user_sql = "SELECT * FROM users WHERE user_id='$user_id'";
        $user_result = mysqli_query($conn, $user_sql);
    
        if (!$user_result) {
            http_response_code(500); // Internal Server Error
            echo json_encode(array('error' => 'Failed to query database'));
            exit;
        }
        $user = mysqli_fetch_assoc($user_result);

        $response = array(
            'id' => $blog['blog_id'],
            'title' => $blog['title'],
            'description' => $blog['description'],
            'image' => array($blog['image']),
            'slug' => $blog['slug'],
            'tags' => json_decode($blog['tags'], true),
            'author' => $user['username'],
            'createdAt' => $blog['created_at'],
        );
    } else {
        // User with the provided email does not exist, set the variable to false
        $response = array("error" => "blog not found");
        echo json_encode($response);
        exit;
    }

    echo json_encode($response);
}