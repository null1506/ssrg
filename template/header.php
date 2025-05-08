<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?php echo $title; ?></title>

    <link href="./bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="./bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">
    <link href="./bootstrap/css/jumbotron.css" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
    <style>
        .user-menu {
            position: relative;
            display: inline-block;
        }
        .user-menu-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 4px;
        }
        .user-menu-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        .user-menu-content a:hover {
            background-color: #f1f1f1;
        }
        .user-menu:hover .user-menu-content {
            display: block;
        }
        .user-menu:hover .user-menu-btn {
            background-color: #3e8e41;
        }
    </style>
  </head>

  <body>

    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="index.php">CSE Bookstore</a>
        </div>

        <!--/.navbar-collapse -->
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-right">
              <!-- link to publiser_list.php -->
              <li><a href="publisher_list.php"><span class="glyphicon glyphicon-paperclip"></span>&nbsp; Publisher</a></li>
              <!-- link to books.php -->
              <li><a href="books.php"><span class="glyphicon glyphicon-book"></span>&nbsp; Books</a></li>
              <!-- link to contacts.php -->
              <li><a href="contact.php"><span class="glyphicon glyphicon-phone-alt"></span>&nbsp; Contact</a></li>
              <!-- link to shopping cart -->
              <li><a href="cart.php"><span class="glyphicon glyphicon-shopping-cart"></span>&nbsp; My Cart</a></li>
              <!-- link to search -->
              <li><a href="search_1.php"><span class="glyphicon glyphicon-search"></span>&nbsp; Search</a></li>
              <?php if (isset($_SESSION['user_id'])): ?>
                  <li class="user-menu">
                      <button class="btn btn-success navbar-btn user-menu-btn">
                          <i class="glyphicon glyphicon-user"></i> 
                          <?php echo htmlspecialchars($_SESSION['username']); ?>
                      </button>
                      <div class="user-menu-content">
                          <a href="profile.php">
                              <i class="glyphicon glyphicon-cog"></i> Profile
                          </a>
                          <a href="orders.php">
                              <i class="glyphicon glyphicon-list"></i> My Orders
                          </a>
                          <a href="logout.php">
                              <i class="glyphicon glyphicon-log-out"></i> Logout
                          </a>
                      </div>
                  </li>
              <?php else: ?>
                  <li><a href="login.php">Login</a></li>
                  <li><a href="register.php">Register</a></li>
              <?php endif; ?>
            </ul>
        </div>
      </div>
    </nav>
    <?php
      if(isset($title) && $title == "Index") {
    ?>
    <!-- Main jumbotron for a primary marketing message or call to action -->
    <div class="jumbotron">
      <div class="container">
        <h1>Welcome to online CSE bookstore</h1>
        <p class="lead">This site has been made using PHP with MSSQL (procedure functions)!</p>
        <p>The layout use Bootstrap to make it more responsive. It's just a simple web!</p>
      </div>
    </div>
    <?php } ?>

    <div class="container" id="main">