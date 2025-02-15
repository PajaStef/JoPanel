<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $domain = $_POST['domain'];
    $doc_root = $_POST['doc_root'];
    $email = $_POST['email']; // Get email for Certbot
    $use_redirect = isset($_POST['use_redirect']) ? true : false; // Check if redirect checkbox is checked

    if (empty($domain) || empty($doc_root)) {
        die("Error: Missing domain or document root");
    }
    
    $apacheConfPath = "/etc/apache2/sites-available/";
    $templatePath = "/etc/apache2/sites-available/template.conf"; // Path to the template file
    $confFile = "$apacheConfPath$domain.conf";

    // Check if document root exists, if not, create it
    if (!is_dir($doc_root)) {
        mkdir($doc_root, 0755, true); // Create folder with 755 permissions
        shell_exec("sudo chown -R www-data:www-data $doc_root"); // Set correct ownership
    }

    // Read template file
    $confContent = file_get_contents($templatePath);
    
    // Replace placeholders
    $confContent = str_replace("{{DOMAIN}}", $domain, $confContent);
    $confContent = str_replace("{{DOC_ROOT}}", $doc_root, $confContent);

    // Save new config file
    file_put_contents($confFile, $confContent);
    
    shell_exec("sudo /usr/sbin/a2ensite $domain.conf");
    shell_exec("sudo systemctl reload apache2");
    
    $message = "<div class='message'>Site <strong>$domain</strong> created";
    if (!empty($email)) {
        $certbotCommand = "sudo /usr/bin/certbot --apache -d $domain --non-interactive --agree-tos -m $email";
        if ($use_redirect) {
            $certbotCommand .= " --redirect";
        }
        shell_exec($certbotCommand);
        $message .= " and SSL enabled with Certbot for <strong>$email</strong>.";
        $message .= $use_redirect ? " HTTP to HTTPS redirect is enabled." : " HTTP to HTTPS redirect is disabled.";
    } else {
        $message .= " without SSL.";
    }
    $message .= "</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Apache Virtual Host</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background-color: #f4f7f6; 
            color: #333; 
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        form { 
            display: flex;
            flex-direction: column;
        }
        label {
            font-size: 14px;
            color: #555;
            margin-bottom: 6px;
        }
        input[type="text"], input[type="email"] { 
            padding: 10px; 
            font-size: 14px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            margin-bottom: 18px;
        }
        input[type="submit"] { 
            background-color: #FF5722; /* Orange color */
            color: white; 
            border: none; 
            padding: 12px 20px; 
            font-size: 16px; 
            border-radius: 4px; 
            cursor: pointer; 
            transition: background-color 0.3s;
        }
        input[type="submit"]:hover {
            background-color: #FF7043; /* Darker orange on hover */
        }
        .link-container {
            text-align: center;
            margin-top: 20px;
        }
        .link-container a {
            color: #FF5722; /* Orange color */
            text-decoration: none;
            font-size: 14px;
        }
        .link-container a:hover {
            text-decoration: underline;
        }
        .message {
            background-color: #e8f5e9; /* Light green background */
            color: #2e7d32; /* Dark green text */
            padding: 12px;
            border-radius: 5px;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin-top: 15px;
            border: 1px solid #a5d6a7; /* Slight border for visibility */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create Apache Virtual Host</h1>
        <form method="post">
            <label for="domain">Domain Name:</label>
            <input type="text" id="domain" name="domain" required>

            <label for="doc_root">Document Root:</label>
            <input type="text" id="doc_root" name="doc_root" required>
            
            <label for="email">Email for Certbot (Leave empty for no SSL):</label>
            <input type="text" id="email" name="email" placeholder="youremail@example.com">
            
            <label>
                <input type="checkbox" id="use_redirect" name="use_redirect" checked>
                Use redirect (automatic redirect from http:// to https://)?
            </label>
            
            <input type="submit" value="Create Site">
        </form>
        <div class="link-container">
            <a href="index.php">List of Domains</a>
        </div>
        <?php if (isset($message)) echo $message; ?>
    </div>
</body>
</html>
