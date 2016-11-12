<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Automata Minimization</title>

    <!-- Bootstrap and theme -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="js/html5shiv.min.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->

    <link rel="icon" href="favicon.png" type="image/png">

    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="back">
    <a href="javascript:void()" class="glyphicon glyphicon-share-alt"></a>
</div>
<div class="container">
    <header>
        <h1 class="title">Automata Minimization</h1>
    </header>
    <section class="upload">
        <form id="upload" action="ajax.php" method="post" enctype="multipart/form-data">
            <i class="upload-image glyphicon glyphicon-cloud-upload"></i>
            <h2 class="upload-heading">Drop Your DFA File Here</h2>
            <h3 class="upload-heading">-OR-</h3>
            <input type="file" name="file" id="file-select">
            <label class="select-label" for="file-select">Choose file</label>
            <input type="submit" value="Upload" id="submit">
        </form>
    </section>

    <section class="result">
        <a href="">Download Minimized text file</a>
        <div id="cy"></div>
    </section>

    <section id="error">
        <h2>Error:</h2>
        <p class="message"></p>
    </section>

    <section id="loading">
        <div class="cssload-container">
            <div class="cssload-loading"><i></i><i></i></div>
            <h4 class="loading-text">Loading...</h4>
        </div>
</section>
</div>
<footer>
    <div class="container">
        <div class="copyright">By Hossein Sadeghi &amp; Mehrdad Ashtari</div>
        <div class="info">Theory of Language and Automata Project . 1395 . Teacher: Mirzaei</div>
        </div>
</footer>

<!-- jQuery -->
<script src="js/jquery.min.js"></script>
<!-- Bootstrap.js -->
<script src="js/bootstrap.min.js"></script>
<!-- Graph Library-->
<script src="js/cytoscape.js"></script>
<!-- Custom Javascript -->
<script src="js/script.js"></script>
</body>
</html>