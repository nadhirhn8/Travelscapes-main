<?php
// Database connection parameters
$servername = "localhost"; 
$username = "root";        
$password = "";            
$dbname = "travelscapes";  

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize filter variables
$selectedRegions = isset($_POST["region"]) ? $_POST["region"] : [];
$selectedSeasons = isset($_POST["season"]) ? $_POST["season"] : [];
$selectedDays = isset($_POST["days"]) ? $_POST["days"] : [];

// Build the base SQL query with placeholders for filters
$sql = "SELECT * FROM cities WHERE 1";

// Dynamic SQL conditions
$params = [];
$types = "";

if (!empty($selectedRegions) && !in_array("All", $selectedRegions)) {
    $placeholders = implode(",", array_fill(0, count($selectedRegions), "?"));
    $sql .= " AND region IN ($placeholders)";
    $params = array_merge($params, $selectedRegions);
    $types .= str_repeat("s", count($selectedRegions));
}

if (!empty($selectedSeasons) && !in_array("All", $selectedSeasons)) {
    $placeholders = implode(",", array_fill(0, count($selectedSeasons), "?"));
    $sql .= " AND season IN ($placeholders)";
    $params = array_merge($params, $selectedSeasons);
    $types .= str_repeat("s", count($selectedSeasons));
}

if (!empty($selectedDays) && !in_array("All", $selectedDays)) {
    $placeholders = implode(",", array_fill(0, count($selectedDays), "?"));
    $sql .= " AND days IN ($placeholders)";
    $params = array_merge($params, $selectedDays);
    $types .= str_repeat("i", count($selectedDays));
}

// Prepare the statement
$stmt = $conn->prepare($sql);

// Bind parameters if needed
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

// Execute the query
$stmt->execute();
$result = $stmt->get_result();

// Fetch data into an array
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Close the statement
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/cities.css">
    <title>Travel Packages</title>
    <script>
        function toggleDropdown(filterName) {
            const dropdown = document.getElementById(filterName + "Dropdown");
            dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
        }
    </script>
</head>
<body>
<div class="navbar">
    <span class="logo">Travelscapes</span>
</div>
<div class="content-container">
    <h1>Travel Packages</h1>
    <form method="post">
        <div class="filter-box">
            <!-- Region Filter -->
            <div class="custom-dropdown">
                <span onclick="toggleDropdown('region')">Region</span>
                <div id="regionDropdown" class="custom-dropdown-content">
                    <?php
                    $regions = ["All", "North", "South", "East", "West", "Central", "North-East"];
                    foreach ($regions as $region) {
                        $checked = in_array($region, $selectedRegions) ? "checked" : "";
                        echo "<label><input type='checkbox' name='region[]' value='$region' $checked>$region</label>";
                    }
                    ?>
                </div>
            </div>

            <!-- Season Filter -->
            <div class="custom-dropdown">
                <span onclick="toggleDropdown('season')">Season</span>
                <div id="seasonDropdown" class="custom-dropdown-content">
                    <?php
                    $seasons = ["All", "Winter", "Summer", "Monsoon", "Spring", "Autumn"];
                    foreach ($seasons as $season) {
                        $checked = in_array($season, $selectedSeasons) ? "checked" : "";
                        echo "<label><input type='checkbox' name='season[]' value='$season' $checked>$season</label>";
                    }
                    ?>
                </div>
            </div>

            <!-- Days Filter -->
            <div class="custom-dropdown">
                <span onclick="toggleDropdown('days')">Days</span>
                <div id="daysDropdown" class="custom-dropdown-content">
                    <?php
                    $daysOptions = ["All", 3, 5, 7];
                    foreach ($daysOptions as $days) {
                        $checked = in_array($days, $selectedDays) ? "checked" : "";
                        echo "<label><input type='checkbox' name='days[]' value='$days' $checked>$days</label>";
                    }
                    ?>
                </div>
            </div>
        </div>
        <input type="submit" value="Apply Filters">
    </form>

    <table border="1">
        <tr>
            <th>City ID</th>
            <th>City</th>
            <th>Region</th>
            <th>Season</th>
            <th>Days</th>
            <th>Cost</th>
            <th>Action</th>
        </tr>
        <?php
        foreach ($data as $row) {
            echo "<tr>";
            echo "<td>{$row['cityid']}</td>";
            echo "<td>{$row['city']}</td>";
            echo "<td>{$row['region']}</td>";
            echo "<td>{$row['season']}</td>";
            echo "<td>{$row['days']}</td>";
            echo "<td>TND {$row['cost']}</td>";
            echo "<td><a href='viewjourney.php?city_id={$row['cityid']}' class='view-button'>View Journey</a></td>";
            echo "</tr>";
        }
        ?>
    </table>
</div>
<footer>
    <p>&copy; 2024 Travelscapes. All rights reserved.</p>
</footer>
</body>
</html>
