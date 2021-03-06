<?php

include_once "../lib/php/functions.php";

$empty_product = [
	"name"=>"The sea",
	"price"=>"6.5",
	"category"=>"Landscape",
	"description"=>"Look at that beautiful reflection on the sea",
	"thumbnail"=>"images_landscape_med_4.jpg",
	"images"=>"images_landscape_med_4.jpg",
];


// CRUD LOGIC
try {

$conn = makePDOConn();
switch(@$_GET['action']) {
	case "update":
		$statement = $conn->prepare("UPDATE
		`products`
		SET
			`name`=? ,
			`price`=? ,
			`category`=? ,
			`description`=? ,
			`thumbnail`=? ,
			`images`=? ,
			`date_modify`=?
		WHERE `id`=?
		");
        $time = date("Y-m-d H:i:s");
        $statement->bind_param("sdsssssi",$_POST['product-title'],
            $_POST['product-price'],
            $_POST['product-category'],
            $_POST['product-description'],
            $_POST['product-thumbnail'],
            $_POST['product-images'],
            $time,
            $_GET['id']);
        $statement->execute();

		header("location:{$_SERVER['PHP_SELF']}?id={$_GET['id']}");
		break;
	case "create":
        $time = date("Y-m-d H:i:s");

        $statement = $conn->prepare("INSERT INTO
		products
		(
			name,
			price,
			category,
			description,
			thumbnail,
			images,
			date_create,
			date_modify
		)
		VALUES
		(?,?,?,?,?,?,?,?)
		");


        $statement->bind_param("sdssssss",
            $_POST['product-title'],
            $_POST['product-price'],
            $_POST['product-category'],
            $_POST['product-description'],
            $_POST['product-thumbnail'],
            $_POST['product-images'],
            $time,
            $time
        );
        print_r($_POST);
		$statement->execute();
		$id = mysqli_insert_id($conn);
		header("location:{$_SERVER['PHP_SELF']}?id=$id");
		break;
	case "delete":
		$statement = $conn->prepare("DELETE FROM `products` WHERE id=?");
		$statement->bind_param("i",$_GET['id']);
		$statement->execute();
		$id = mysqli_insert_id($conn);
		header("location:{$_SERVER['PHP_SELF']}");
		break;
}


} catch(mysqli_sql_exception $e) {
	die($e->getMessage());
}







// TEMPLATES

function makeListItemTemplate($r,$o) {
return $r.<<<HTML
<div class="itemlist-item display-flex">
	<div class="flex-none">
		<div class="image-square">
			<img src="img/{$o['images']}">
		</div>
	</div>
	<div class="flex-stretch">
		<div><strong>{$o['name']}</strong></div>
		<div><span>{$o['category']}</span></div>
	</div>
	<div class="flex-none display-flex">
		<div><a class="form-button" href="admin/?id={$o['id']}">edit</a></div>
		<div><a class="form-button" href="product_item.php?id={$o['id']}">visit</a></div>
	</div>
</div>
HTML;
}


function makeProductForm($o) {

$id = $_GET['id'];
$addoredit = $id=="new" ? 'Add' : 'Edit';
$createorupdate = $id=="new" ? 'create' : 'update';
$deletebutton = $id=="new" ? '' : <<<HTML
<li class="flex-none"><a href="{$_SERVER['PHP_SELF']}?id=$id&action=delete">Delete</a></li>
HTML;
$images = array_reduce(explode(",",$o['images']),function($r,$p){
	return $r."<img src='img/$p'>";
});

$data_show = $id=="new" ? "" : <<<HTML
<div class="card soft">

<div class="product-main">
	<img src="img/{$o['images']}">
</div>
<div class="product-thumbs">$images</div>

<h2>{$o['name']}</h2>

<div class="form-control">
	<strong>Price</strong>
	<span>{$o['price']}</span>
</div>
<div class="form-control">
	<strong>Category</strong>
	<span>{$o['category']}</span>
</div>
<div class="form-control">
	<strong>Description</strong>
	<span>{$o['description']}</span>
</div>


</div>
HTML;



echo <<<HTML
<nav class="nav-pills">
	<div class="card soft">
	<ul>
		<li class="flex-none"><a href="{$_SERVER['PHP_SELF']}">Back</a></li>
		<li class="flex-stretch"></li>
		$deletebutton
	</ul>
	</div>
</nav>
<form method="post" action="{$_SERVER['PHP_SELF']}?id=$id&action=$createorupdate">
	<div class="grid gap">
		<div class="col-xs-12 col-md-5">
			$data_show
		</div>
		<div class="col-xs-12 col-md-7">
			<div class="card soft">
			<h2>$addoredit Product</h2>
			<div class="form-control">
				<label class="form-label" for="product-title">Name</label>
				<input class="form-input" id="product-title" name="product-title" value="{$o['name']}">
			</div>
			<div class="form-control">
				<label class="form-label" for="product-price">Price</label>
				<input class="form-input" id="product-price" name="product-price" value="{$o['price']}">
			</div>
			<div class="form-control">
				<label class="form-label" for="product-category">Category</label>
				<input class="form-input" id="product-category" name="product-category" value="{$o['category']}">
			</div>
			<div class="form-control">
				<label class="form-label" for="product-description">Description</label>
				<textarea class="form-input" id="product-description" name="product-description" style="height:4em">{$o['description']}</textarea>
			</div>
			<div class="form-control">
				<label class="form-label" for="product-thumbnail">Thumbnail</label>
				<input class="form-input" id="product-thumbnail" name="product-thumbnail" value="{$o['thumbnail']}">
			</div>
			<div class="form-control">
				<label class="form-label" for="product-images">Other Images</label>
				<input class="form-input" id="product-images" name="product-images" value="{$o['images']}">
			</div>

			<div class="form-control">
				<input type="submit" class="form-button" value="Submit">
			</div>
			</div>
		</div>
	</div>
</form>
HTML;

}







// LAYOUT

?><!DOCTYPE html>
<html lang="en">
<head>
	<title>Admin Page</title>
	
	<?php include "../parts/meta.php" ?>
</head>
<body>

	<header class="navbar" class="navbar" style="background: #fff;color: #3a56a4">
		<div class="container display-flex">
			<div class="flex-stretch">
				<h1>Product Admin</h1>
			</div>
			<nav class="nav-flex flex-none">
				<ul>
					<li><a href="index.php">Home</a></li>
					<li><a href="product_list.php">Product List</a></li>
					<li><a href="admin/?id=new">Add New Product</a></li>
				</ul>
			</nav>
		</div>
	</header>

	<div class="container">

			<?php

			$conn = makeConn();

			if(isset($_GET['id'])){

				if($_GET['id']=="new") {
					makeProductForm($empty_product);
				} else {
					$rows = getRows($conn, "SELECT * FROM `products` WHERE `id`='{$_GET['id']}'");
					makeProductForm($rows[0]);
				}


			} else {

			?>
			<div class="card soft">
			<h2>Product List</h2>

			<div class="itemlist">
			<?php

			$rows = getRows($conn, "SELECT * FROM `products`");

			echo array_reduce($rows,'makeListItemTemplate');

			?>
			</div>
			</div>

			<?php 

			}

			?>
	</div>
	
</body>
</html>