<?php
ob_start();
include '../includes/config.php';

// Set timezone to East Africa Time (Nairobi)
date_default_timezone_set('Africa/Nairobi');

// Get current database name from config (assuming $dbname is in config.php)
if (isset($dbname)) {
        $database = $dbname;
} else {
        $dbResult = mysqli_query($conn, "SELECT DATABASE() AS db");
        $dbRow    = mysqli_fetch_assoc($dbResult);
        $database = $dbRow['db'] ?? 'Unknown_Database';
}

// Get all table names from the database
$tables = array();
$sql = "SHOW TABLES";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_row($result)) {
    $tables[] = $row[0];
}

$sqlScript = "-- Database Backup\n";
$sqlScript .= "-- Database: `$database`\n";
$sqlScript .= "-- Backup Date: " . date('Y-m-d H:i:s') . "\n\n";

// Include table structure, data, triggers, and events
foreach ($tables as $table) {
    // Prepare SQL script for creating table structure
    $query = "SHOW CREATE TABLE `$table`";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_row($result);

    $sqlScript .= "\n\n" . str_replace("CREATE TABLE", "CREATE TABLE IF NOT EXISTS", $row[1]) . ";\n\n";

    // Prepare SQL script for dumping data for each table
    $query = "SELECT * FROM `$table`";
    $result = mysqli_query($conn, $query);

    $columnCount = mysqli_num_fields($result);
    while ($row = mysqli_fetch_row($result)) {
        $sqlScript .= "INSERT INTO `$table` VALUES(";
        for ($j = 0; $j < $columnCount; $j++) {
            if (isset($row[$j])) {
                $sqlScript .= "'" . mysqli_real_escape_string($conn, $row[$j]) . "'";
            } else {
                $sqlScript .= "NULL";
            }
            if ($j < ($columnCount - 1)) {
                $sqlScript .= ", ";
            }
        }
        $sqlScript .= ");\n";
    }

    $sqlScript .= "\n";
}

// Include triggers
$triggerQuery = "SHOW TRIGGERS";
$triggerResult = mysqli_query($conn, $triggerQuery);

if ($triggerResult->num_rows > 0) {
    while ($trigger = mysqli_fetch_assoc($triggerResult)) {
        $sqlScript .= "\nDELIMITER ;;\n";
        $sqlScript .= "CREATE TRIGGER `" . $trigger['Trigger'] . "` " . $trigger['Timing'] . " " . $trigger['Event'] .
            " ON `" . $trigger['Table'] . "` FOR EACH ROW " . $trigger['Statement'] . ";;\n";
        $sqlScript .= "DELIMITER ;\n\n";
    }
}

// Include events
$eventQuery = "SHOW EVENTS";
$eventResult = mysqli_query($conn, $eventQuery);

if ($eventResult->num_rows > 0) {
    while ($event = mysqli_fetch_assoc($eventResult)) {
        $eventCreateQuery = "SHOW CREATE EVENT `" . $event['Name'] . "`";
        $eventCreateResult = mysqli_query($conn, $eventCreateQuery);
        $eventCreateRow = mysqli_fetch_row($eventCreateResult);

        $sqlScript .= "\n\n" . $eventCreateRow[3] . ";\n";
    }
}

if (!empty($sqlScript)) {
    // Specify the backup directory path relative to the script's location
    $backup_dir = dirname(__FILE__) . "/../backup/database/";

    // Ensure backup directory exists
    if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0777, true);
    }

    // Save the SQL script to a backup file
    $backup_file_name = $backup_dir . $database . '_backup_' . date('Y-m-d_H-i-s') . '.sql';
    $fileHandler = fopen($backup_file_name, 'w+');

    // Check if the file was opened successfully
    if ($fileHandler === false) {
        echo "Failed to open the backup file for writing.";
        exit;
    }

    fwrite($fileHandler, $sqlScript);
    fclose($fileHandler);

    // Redirect to admin_dashboard.php after 4 seconds
    header("refresh:4;url=../dashboard/admin_dashboard.php");

    // Output success message
    echo '<div style="color: green; background-color:  #DAF7A6; height: 40px; padding: 20px; margin-left: 20px; margin-top: 30px; font-size: 18px;">Backup saved successfully at: ' . $backup_file_name . '</div>';

} else {
    echo "No tables, triggers, or events found in the database.";
}

?>
