<?php
session_start();

//
include "../includes/template.php";
include "../includes/config.php";


$customer = $_SESSION['id_login'];
$order = $_SESSION['id_login'];

if (!isset($_SESSION['mysesi']) && !isset($_SESSION['mytype'])=='customer')
{
    echo "<script>window.location.assign('LoginAndReg.php')</script>";
}
?>
<?php


include("admin/php/myFunctions.php");
@mysql_connect("localhost","root","") or die("Could not connect to database");
@mysql_select_db("bookstore") or die("Could not select database");

$displayImages = "";

if((isset($_GET['cat']) ? $_GET['cat'] : '') == "children")
    $sqlSelProd = @mysql_query("select * from tblproduct where prod_cat = '$_GET[cat]'") or die(mysql_error());
else if((isset($_GET['cat']) ? $_GET['cat'] : '') == "Horror")
    $sqlSelProd = @mysql_query("select * from tblproduct where prod_cat = '$_GET[cat]'") or die(mysql_error());
else if((isset($_GET['cat']) ? $_GET['cat'] : '') == "Thriller")
    $sqlSelProd = @mysql_query("select * from tblproduct where prod_cat = '$_GET[cat]'") or die(mysql_error());
else
    $sqlSelProd = @mysql_query("select * from tblproduct") or die(mysql_error());

if(mysql_num_rows($sqlSelProd) >= 1){
    while($getProdInfo = mysql_fetch_array($sqlSelProd)){
        $prodNo = $getProdInfo["prod_no"];
        $prodID = $getProdInfo["prod_id"];
        $prodName = $getProdInfo["prod_name"];
        $prodPrice = $getProdInfo["prod_price"];

        $displayImages .= '<div class="col col_14 product_gallery">
            <a href="productdetail.php?prodid='.$prodID.'"><img src="images/product/'.$prodNo.'.jpg" alt="Product '.$prodNo.'" width="170" height="150" /></a>
            <h3>'.$prodName.'</h3>
            <p class="product_price">R '.$prodPrice.'</p>
            <a href="shoppingcart.php?prodid='.$prodID.'" class="add_to_cart">Add to Cart</a></div>';
    }
}

?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <!-- Responsive code -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Great selling Book Store</title>
    <link href="css/slider.css" rel="stylesheet" type="text/css" />

    <link rel="stylesheet" type="text/css" href="css/ddsmoothmenu.css" />

    <link rel="stylesheet" type="text/css" href="css/styles.css" />

    <script language="javascript" type="text/javascript">

        function clearText(field)
        {
            if (field.defaultValue == field.value) field.value = '';
            else if (field.value == '') field.value = field.defaultValue;
        }
    </script>

</head>

<body id="home">

<div id="main_wrapper">
    <div id="main_header">
        <div id="site_title"><h1><a href="#" rel="nofollow">Book Store</a></h1></div>

        <div id="header_right">
            <div id="main_search">
                <form action="products.php" method="get" name="search_form">

                    <input type="text" value="Search" name="keyword" onfocus="clearText(this)" onblur="clearText(this)" class="txt_field" />
                    <input type="submit" name="Search" value="" alt="Search" id="searchbutton" title="Search" class="sub_btn"  />
                    <p>Welcome, <?php echo $_SESSION['mysesi'] ?></p> <a href="logout.php"class="btn btn-primary" role="button">Log Out</a>
                    <?php echo $customer ?>
                    <?php echo $order ?>
                </form>
            </div>
        </div> <!-- END -->
    </div> <!-- END of header -->

    <div id="main_menu" class="ddsmoothmenu">
        <ul>
            <li><a href="index.php" class="selected">Home</a></li>
            <li><a href="products.php">Books</a></li>
            <li><a href="shoppingcart.php">Cart</a></li>
            <li><a href="checkout.php">Checkout</a></li>
            <li><a href="about.php">About</a></li>
        </ul>
        <br style="clear: left" />
    </div> <!-- end of menu -->

    <div id="main_middle">
        <img src="images/image_book.png" alt="Image 01" width="500" height="170" />
        <h1>Great Selling book Store</h1>
        <p><a href="#" rel="nofollow" target="_parent">Great Selling book Store</a> is a country wide book store.</p>
        <a href="index.php" class="buy_now">Browse All books</a>
    </div> <!-- END of middle -->

    <div id="main_top"></div>
    <div id="main">
        <div id="sidebar">
            <h3>Categories</h3>
            <ul class="sidebar_menu">
                <li><a href="index.php?cat=children">Children</a></li>
                <li><a href="index.php?cat=Horror">Horror</a></li>
                <li><a href="index.php?cat=Thriller">Thriller</a></li>
            </ul>
        </div> <!-- END of sidebar -->

        <div id="content">
            <h2>Products</h2>
            <?php echo $displayImages; ?>
        </div> <!-- END of content -->
        <div class="cleaner"></div>
    </div> <!-- END of main -->

    <div id="main_footer">
        <div class="cleaner h40"></div>
        <center>
            Copyright © 2048 DigitalNinja
        </center>
    </div> <!-- END of footer -->

</div>


<script type='text/javascript' src='js/logging.js'></script>
</body>
    </html>
