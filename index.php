
<?php
// Database connection
$host = 'localhost';
$username = 'root'; // Change if necessary
$password = 'root'; // Change if necessary
$dbname = 'pokemondb';

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: {$conn->connect_error}");
}

// Initialize session to keep track of current Pokémon
session_start();
if (!isset($_SESSION['current_index'])) {
    $_SESSION['current_index'] = 0;
}

// Fetch Pokémon data in random order only once per session
if (!isset($_SESSION['pokemon_list'])) {
    $sql = "SELECT * FROM pokemon ORDER BY RAND()";
    $result = $conn->query($sql);

    // Convert result to an array
    $pokemonList = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if ($row['legendary'] == 1) {
                $pokemonList[] = $row;
            } else {
                $pokemonList[] = $row;
                $pokemonList[] = $row; // Duplicate to increase chance
            }
        }
    }

    $_SESSION['pokemon_list'] = $pokemonList;
    $_SESSION['pokemon_hp'] = array_map(function($pokemon) {
        return $pokemon['hp']; // Initial current HP is full HP
    }, $pokemonList);
}

$pokemonList = $_SESSION['pokemon_list'];
$currentIndex = $_SESSION['current_index'];
$totalPokemon = count($pokemonList);
$currentPokemon = $pokemonList[$currentIndex] ?? null;

// Current HP for the Pokémon
$currentHP = $_SESSION['pokemon_hp'][$currentIndex] ?? 0;
$maxHP = $currentPokemon['hp'] ?? 0;

$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ATTACK action
    if (isset($_POST['attack'])) {
        $damage = rand(20, 50);
        $currentHP -= $damage;

        if ($currentHP <= 0) {
            $currentHP = 0;

            $message = htmlspecialchars($currentPokemon['name']) . " fucking died! Moving on to the next Pokémon.";

            // Move to the next Pokémon
            $_SESSION['current_index'] = ($currentIndex + 1) % $totalPokemon;
            $currentIndex = $_SESSION['current_index'];

            // Set up next Pokémon
            $currentPokemon = $pokemonList[$currentIndex] ?? null;
            $currentHP = $currentPokemon['hp'] ?? 0;
            $_SESSION['pokemon_hp'][$currentIndex] = $currentHP;
            $maxHP = $currentHP;

        } else {
            $_SESSION['pokemon_hp'][$currentIndex] = $currentHP;
            $message = "You attacked " . htmlspecialchars($currentPokemon['name']) . " for {$damage} damage!";
        }
    }

    // CATCH action
    if (isset($_POST['catch'])) {
        if ($currentPokemon) {
            $thresholdHP = $maxHP * 0.25;

            if ($currentHP <= $thresholdHP) {
                $catchChance = 100;
            } else {
                $catchChance = $currentPokemon['legendary'] ? 10 : 50;
            }

            $randomNumber = rand(1, 100);

            if ($randomNumber <= $catchChance) {
                // Successful catch!
                $stmt = $conn->prepare("INSERT INTO pokedex (name, hp, attack, defense, pokemon_sprite, legendary) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param(
                    "siiisi",
                    $currentPokemon['name'],
                    $currentPokemon['hp'],
                    $currentPokemon['attack'],
                    $currentPokemon['defense'],
                    $currentPokemon['pokemon_sprite'],
                    $currentPokemon['legendary']
                );
                $stmt->execute();
                $stmt->close();

                $message = "You caught " . htmlspecialchars($currentPokemon['name']) . "!";

                // Move to the next Pokémon
                $_SESSION['current_index'] = ($currentIndex + 1) % $totalPokemon;
            } else {
                $message = "The Pokémon escaped!";
                $_SESSION['current_index'] = ($currentIndex + 1) % $totalPokemon;
            }

            // Update currentIndex and HP for new Pokémon
            $currentIndex = $_SESSION['current_index'];
            $currentPokemon = $pokemonList[$currentIndex] ?? null;
            $currentHP = $currentPokemon['hp'] ?? 0;
            $_SESSION['pokemon_hp'][$currentIndex] = $currentHP;
            $maxHP = $currentHP;
        }
    }

    // NEXT action
    if (isset($_POST['next'])) {
        $_SESSION['current_index'] = ($currentIndex + 1) % $totalPokemon;
        $currentIndex = $_SESSION['current_index'];
        $currentPokemon = $pokemonList[$currentIndex] ?? null;
        $currentHP = $currentPokemon['hp'] ?? 0;
        $_SESSION['pokemon_hp'][$currentIndex] = $currentHP;
        $maxHP = $currentHP;
    }
}

// Calculate HP percentage for health bar
$hpPercent = $maxHP > 0 ? ($currentHP / $maxHP) * 100 : 0;
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/index.css">

    <title>Pokemon Catcher</title>
    <style>
        .health-bar-container {
            width: 300px;
            background-color: #ddd;
            border: 1px solid #333;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .health-bar {
            height: 25px;
            background-color: <?php echo $hpPercent > 50 ? 'green' : ($hpPercent > 25 ? 'orange' : 'red'); ?>;
            width: <?php echo $hpPercent; ?>%;
            border-radius: 5px;
            transition: width 0.3s ease;
        }
        button {
            margin: 5px;
            padding: 10px 15px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <?php if ($message): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>

    <?php if ($currentPokemon): ?>
        <h1><?php echo htmlspecialchars($currentPokemon['name']); ?></h1>
        <img src="pokemon-gifs/<?php echo htmlspecialchars($currentPokemon['name']); ?>.gif" alt="<?php echo htmlspecialchars($currentPokemon['name']); ?>" style="height:200px;">
        <p><?php echo $currentPokemon['legendary'] ? 'This is a Legendary Pokémon!' : 'This is a normal Pokémon.'; ?></p>

        <div class="health-bar-container">
            <div class="health-bar"></div>
        </div>
        <p>HP: <?php echo $currentHP; ?> / <?php echo $maxHP; ?></p>

        <div class="topframe">
            <div id="top-speaker"></div>
        </div>
        <div class="middle-part">
            <div id="stripe1"></div>
            <div id="stripe2"></div>
        </div>
        <div class="bottomframe">

        </div>

        <form method="post">
            <button type="submit" name="attack">Attack Pokémon</button>
            <button type="submit" name="catch">Catch Pokémon</button>
            <button type="submit" name="next">Next Pokémon</button>
        </form>
    <?php else: ?>
        <p>No Pokémon found in the database.</p>
    <?php endif; ?>
</body>
</html>

<?php
$conn->close();
?>


