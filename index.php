<?php
// Define the path to Apache's sites-available directory
$apacheConfPath = '/etc/apache2/sites-available/';

// Open the directory and get all the .conf files (excluding defaults)
$confFiles = array_filter(scandir($apacheConfPath), function($file) {
    return preg_match('/\.conf$/', $file) && !in_array($file, ['00-default.conf', 'default-ssl.conf', 'template.conf']);
});

$sites = [];
$confContent = ''; // Will hold the content of the selected config file

// Handle editing a configuration file
if (isset($_GET['edit'])) {
    $file = $_GET['edit'];  // This is the filename of the config to edit
    $confFilePath = $apacheConfPath . $file;

    if (file_exists($confFilePath)) {
        // Read the content of the selected config file
        $confContent = file_get_contents($confFilePath);
    } else {
        $confContent = 'Error: File does not exist.';
    }
}

// Handle saving the edited config file
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save'])) {
    $domain = $_POST['domain'];
    $newContent = $_POST['config_content'];

    // Ensure the file path ends with .conf
    $confFilePath = $apacheConfPath . $domain;
    if (substr($confFilePath, -5) !== '.conf') {
        $confFilePath .= '.conf'; // Append .conf if not present
    }

    if (file_put_contents($confFilePath, $newContent)) {
        // Reload Apache after saving
        shell_exec("sudo systemctl reload apache2");
        $confContent = 'File successfully updated and Apache reloaded!';
    } else {
        $confContent = 'Error: Unable to save the file.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Apache Virtual Hosts</title>
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
            min-height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 900px;
            box-sizing: border-box;
        }
        h1 {
            text-align: center;
            color: #FF5722;
            margin-bottom: 30px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #FFEBEE;
        }
        td a {
            color: #FF5722;
            text-decoration: none;
            font-weight: bold;
        }
        td a:hover {
            text-decoration: underline;
            color: #FF7043;
        }
        textarea {
            width: 100%;
            height: 300px;
            font-family: monospace;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            box-sizing: border-box;
            margin-top: 10px;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            background-color: #FFEBEE;
            border: 1px solid #FFCCBC;
            border-radius: 4px;
            color: #333;
            text-align: center;
        }
        .btn {
            background-color: #FF5722;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        .btn:hover {
            background-color: #FF7043;
        }
        .create-btn-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .create-btn-container .btn {
            width: auto;
            padding: 12px 30px;
            font-size: 16px;
            margin-top: 20px;
        }
        .edit-container {
            display: none;
            margin-top: 20px;
        }
        .close-btn {
            background-color: #FF7043;
            border: none;
            color: white;
            padding: 6px 12px;
            cursor: pointer;
            border-radius: 4px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>JoPanel</h1>
        <h2>Apache Virtual Hosts</h2>
        <p><strong>⭐ = SSL-enabled VHost</strong></p>
        <div class="create-btn-container">
            <a href="create.php">
                <button class="btn">Create New Site</button>
            </a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Domain</th>
                    <th>Document Root</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($confFiles as $file):
                    $fileContent = file_get_contents($apacheConfPath . $file);
                    preg_match('/ServerName\s+([^\s]+)/', $fileContent, $domainMatches);
                    preg_match('/DocumentRoot\s+"?([^\s]+)"?/', $fileContent, $docRootMatches);
                    $isSSL = strpos($fileContent, '<VirtualHost *:443>') !== false;
                    ?>
                    <?php if (isset($domainMatches[1]) && isset($docRootMatches[1])): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($domainMatches[1]) . ($isSSL ? ' ⭐' : ''); ?></td>
                            <td><?php echo htmlspecialchars($docRootMatches[1]); ?></td>
                            <td>
                                <a href="javascript:void(0);" onclick="toggleEditForm('<?php echo $file; ?>')">Edit</a>
                            </td>
                        </tr>
                        <tr id="edit-<?php echo $file; ?>" class="edit-container">
                            <td colspan="3">
                                <form method="post">
                                    <input type="hidden" name="domain" value="<?php echo htmlspecialchars($file); ?>">
                                    <textarea name="config_content"><?php echo htmlspecialchars(file_get_contents($apacheConfPath . $file)); ?></textarea><br>
                                    <button type="submit" name="save" class="btn">Save Changes</button>
                                    <button type="button" class="close-btn" onclick="toggleEditForm('<?php echo $file; ?>')">Close</button>
                                </form>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function toggleEditForm(file) {
            var formRow = document.getElementById('edit-' + file);
            var isVisible = formRow.style.display === 'table-row';

            // Toggle visibility of the form
            formRow.style.display = isVisible ? 'none' : 'table-row';
        }
    </script>
</body>
</html>
