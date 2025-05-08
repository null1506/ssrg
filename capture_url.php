<?php
session_start();
require_once "./functions/database_functions.php";
require_once "./auth.php";
checkLogin();

$title = "Capture URL Screenshot";
require "./template/header.php";
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Capture URL Screenshot</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        .preview-container {
            margin-top: 20px;
            border: 1px solid #ddd;
            padding: 10px;
            display: none;
        }
        #url-frame {
            width: 100%;
            height: 600px;
            border: none;
        }
        .loading {
            display: none;
            text-align: center;
            margin: 20px 0;
        }
        .loading:after {
            content: " .";
            animation: dots 1.5s steps(5, end) infinite;
        }
        @keyframes dots {
            0%, 20% { content: " ."; }
            40% { content: " .."; }
            60% { content: " ..."; }
            80%, 100% { content: " ...."; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2 class="text-center mb-4">Capture URL Screenshot</h2>
                
                <div class="form-group">
                    <label for="url">Enter URL to capture:</label>
                    <div class="input-group">
                        <input type="url" class="form-control" id="url" 
                               placeholder="https://example.com" required>
                        <span class="input-group-btn">
                            <button class="btn btn-primary" id="load-btn">Load URL</button>
                        </span>
                    </div>
                </div>

                <div class="loading" id="loading">
                    Loading page
                </div>

                <div class="preview-container" id="preview-container">
                    <h3>Preview</h3>
                    <iframe id="url-frame" sandbox="allow-same-origin allow-scripts"></iframe>
                    <div class="text-center mt-3">
                        <button id="capture-btn" class="btn btn-success">Capture Screenshot</button>
                        <button id="new-capture-btn" class="btn btn-default">New Capture</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('load-btn').addEventListener('click', function() {
            var url = document.getElementById('url').value;
            if (!url) {
                alert('Please enter a URL');
                return;
            }

            // Show loading
            document.getElementById('loading').style.display = 'block';
            document.getElementById('preview-container').style.display = 'none';

            // Load URL in iframe
            var iframe = document.getElementById('url-frame');
            iframe.onload = function() {
                document.getElementById('loading').style.display = 'none';
                document.getElementById('preview-container').style.display = 'block';
            };
            iframe.src = url;
        });

        document.getElementById('capture-btn').addEventListener('click', function() {
            var iframe = document.getElementById('url-frame');
            html2canvas(iframe.contentDocument.body).then(function(canvas) {
                // Create download link
                var link = document.createElement('a');
                var url = document.getElementById('url').value;
                var filename = url.replace(/^https?:\/\//, '').replace(/[^a-z0-9]/gi, '_').toLowerCase();
                link.download = filename + '.png';
                link.href = canvas.toDataURL();
                link.click();
            });
        });

        document.getElementById('new-capture-btn').addEventListener('click', function() {
            document.getElementById('url').value = '';
            document.getElementById('preview-container').style.display = 'none';
            document.getElementById('url-frame').src = '';
        });
    </script>
</body>
</html>

<?php
require "./template/footer.php";
?> 