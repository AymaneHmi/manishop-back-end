<?php

include '../../config/headers.php';
include '../../config/funcs.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = filter_var($postdata['title'], FILTER_SANITIZE_STRING);
    $description = filterHtmlTags($postdata['description']);
    $category_id = filter_var($postdata['category'], FILTER_SANITIZE_STRING);
    $subcategory_id = filter_var($postdata['subcategory'], FILTER_SANITIZE_STRING);
    $price= filter_var($postdata['price'], FILTER_SANITIZE_STRING);
    $available= $postdata['available'];
    $images = $postdata['images'];

    if(empty($title) || empty($description) || empty($category_id) || empty($subcategory_id) || empty($price) || empty($images)){
        $response = array('error'  => 'Data is requiered!');
        echo json_encode($response);
        exit;
    }

    if(!$conn) {
        $response = array('error'  => 'could not connect to database!');
        echo json_encode($response);
        exit;
    }

    $slug = createSlug($title, 'products');
    $is_available = $available ? 1 : 0 ;

    $query = "INSERT INTO products (title , description, category_id, subcategory_id, price, available , slug) VALUES (? , ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    $stmt->bind_param("ssiidis", $title, $description, $category_id, $subcategory_id, $price, $is_available, $slug);

    if(!$stmt->execute()){
        $response = array('error'  => 'could not inset data to database!');
        echo json_encode($response);
        exit;
    }

    $stmt->close();

    $imagesNames = array();

    $product_id = $conn->insert_id;
    foreach ($images as $base64_image) {

        list($type, $data) = explode(';', $base64_image);
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
    
        $uploadDirectory = '../../imgs/products/' . $filename;
    
        if(!file_put_contents($uploadDirectory, $data)) {
            $response = array('error' => 'Failed to upload image');
            echo json_encode($response);
            exit;
        }

        $imagesNames[] = $filename;
    }
    $images_json = json_encode($imagesNames);

    $sql = "UPDATE products SET images='$images_json' WHERE product_id='$product_id'";

    if(!$conn->query($sql)) {
        $response = array('error' => 'Failed to update images row in database');
        echo json_encode($response);
        exit;
    }

    $response = array('success' => 'product inserted successfuly.');

    echo json_encode($response);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'];

    if(empty($id)) {
        $response = array('error' => 'id is requiered');
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT * FROM products WHERE product_id = '$id'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {

        $product = mysqli_fetch_assoc($result);

        $response = array(
            'id' => $id,
            'title' => $product['title'],
            'description' => $product['description'],
            'category' => $product['category_id'],
            'subcategory' => $product['subcategory_id'],
            'price' => $product['price'],
            'images' => json_decode($product['images'], true),
            'available' => ($product['available'] === '1' ? true : false),
        );
        
    } else {
        $response = array("error" => "product not found");
        echo json_encode($response);
        exit;
    }

    echo json_encode($response);
}

if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $id = filter_var($postdata['id'], FILTER_SANITIZE_STRING);
    $title = filter_var($postdata['title'], FILTER_SANITIZE_STRING);
    $description = filterHtmlTags($postdata['description']);
    $category = filter_var($postdata['category'], FILTER_SANITIZE_STRING);
    $subcategory = filter_var($postdata['subcategory'], FILTER_SANITIZE_STRING);
    $price = filter_var($postdata['price'], FILTER_SANITIZE_STRING);
    $available = $postdata['available'];
    $upload_images = $postdata['upload_images'];
    $delete_images = $postdata['delete_images'];

    if(empty($id) || empty($title) || empty($description) || empty($category) || empty($subcategory) || empty($price)){
        $response = array('error'  => 'Data is requiered!');
        echo json_encode($response);
        exit;
    }

    if(!$conn) {
        $response = array('error'  => 'could not connect to database!');
        echo json_encode($response);
        exit;
    }
    
    $is_available = $available ? 1 : 0 ;

    $sql = "SELECT * FROM products WHERE product_id = '$id'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {

        $product = mysqli_fetch_assoc($result);

        $images = json_decode($product['images'], true);
    
        $query = "UPDATE products SET title=?, description=?, category_id=?, subcategory_id=?, price=?, available=? WHERE product_id=?";
        $stmt = $conn->prepare($query);
    
        $stmt->bind_param("sssssii", $title, $description, $category, $subcategory, $price, $is_available, $id);
    
        if (!$stmt->execute()) {
            $response = array('error' => 'Could not update data in the database!');
            echo json_encode($response);
            exit;
        }
    
        $stmt->close();
    
    
        if(!empty($upload_images)){
            foreach($upload_images as $image) {

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
                
                $uploadDirectory = '../../imgs/products/' . $filename;

                if(!file_put_contents($uploadDirectory, $data)) {
                    $response = array('error' => 'Failed to upload image');
                    echo json_encode($response);
                    exit;
                }
                
                $images[] = $filename;
            }

            $images_json = json_encode($images);
            $query = "UPDATE products SET images=? WHERE product_id=?";
            $stmt = $conn->prepare($query);
        
            $stmt->bind_param("si", $images_json, $id);
        
            if (!$stmt->execute()) {
                $response = array('error' => 'Could not update data in the database!');
                echo json_encode($response);
                exit;
            }
        
            $stmt->close();
        }
    
        if(!empty($delete_images)){
            foreach ($delete_images as $image) {
                $image_path = '../../imgs/products/' . $image;
                if (file_exists($image_path)) {
                    if(!unlink($image_path)){
                        $response = array('error' => 'Could not delete image');
                        echo json_encode($response);
                        exit;
                    }
                    $images = array_filter($images, function ($img) use ($image) {
                        return $img !== $image;
                    });
                } else {
                    $response = array('error' => "image(s) couldn't found.");
                    echo json_encode($response);
                    exit;
                }
            }
            $images_json = json_encode(array_values($images));
            $query = "UPDATE products SET images=? WHERE product_id=?";
            $stmt = $conn->prepare($query);
        
            $stmt->bind_param("si", $images_json, $id);
        
            if (!$stmt->execute()) {
                $response = array('error' => 'Could not update data in the database!');
                echo json_encode($response);
                exit;
            }
        
            $stmt->close();
        }
        
    } else {
        $response = array("error" => "product not found");
        echo json_encode($response);
        exit;
    }

    $response = array('success' => 'Product updated successfuly.');

    echo json_encode($response);
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = filter_var($postdata['id'], FILTER_SANITIZE_STRING);

    if(empty($id)) {
        $response = array('error' => 'id is requiered');
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT * FROM products WHERE product_id = '$id'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $product = mysqli_fetch_assoc($result);

        $images = json_decode($product['images'], true);

        foreach($images as $image){
            $filename = $image;
            $filepath = '../../imgs/products/' . $filename;
            if (file_exists($filepath)) {
                if (!unlink($filepath)) {
                    $response = array("error" => "probleme accured when deleting this image");
                    echo json_encode($response);
                    exit;
                }
            } else {
                $response = array("error" => "image not exist to delete");
                echo json_encode($response);
                exit;
            }
        }

        $delete = "DELETE FROM products WHERE product_id = ?";
        $stmt = mysqli_prepare($conn, $delete);
        mysqli_stmt_bind_param($stmt, "s", $id);
        if (!mysqli_stmt_execute($stmt)) {
            $response = array("error" => "product not deleted");
            echo json_encode($response);
            exit;
        }
        $delete_cart = "DELETE FROM carts WHERE product_id = ?";
        $stmt_cart = mysqli_prepare($conn, $delete_cart);
        mysqli_stmt_bind_param($stmt, "s", $id);
        if (!mysqli_stmt_execute($stmt)) {
            $response = array("error" => "carts not deleted");
            echo json_encode($response);
            exit;
        }
        $delete_favorite = "DELETE FROM favorites WHERE product_id = ?";
        $stmt_favorite = mysqli_prepare($conn, $delete_favorite);
        mysqli_stmt_bind_param($stmt, "s", $id);
        if (!mysqli_stmt_execute($stmt)) {
            $response = array("error" => "favorites not deleted");
            echo json_encode($response);
            exit;
        }
        mysqli_stmt_close($stmt);
        
    } else {
        $response = array("error" => "product not found");
        echo json_encode($response);
        exit;
    }

    $response = array('success' => 'product deleted successfuly.');

    echo json_encode($response);
}