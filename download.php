<?php

if(isset($_GET['file'])) {
    $filename = 'files/' . basename($_GET['file']) . ".tmp";
    if (file_exists($filename)) {
        header('Content-Description: File Transfer');
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="Minimized DFA.txt"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filename));
        readfile($filename);
        exit;
    }
    else {
        header("HTTP/1.0 404 Not Found");
        echo "<h1>404 Not Found</h1>";
        echo "The file you requested not found on server!";
        exit();
    }
}
else {
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 Not Found</h1>";
    echo "The file you requested not found on server!";
    exit();
}
?>