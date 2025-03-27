<?php
// Database connection
$host = 'localhost';
$username = 'root';
$password = 'root';
$dbname = 'pokemondb';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: {$conn->connect_error}");
}

session_start();

$message = "";

// === FETCH A RANDOM POKEMON IF NONE SELECTED ===
if (!isset($_SESSION['current_pokemon'])) {
    $sql = "SELECT * FROM pokemon ORDER BY RAND() LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $pokemon = $result->fetch_assoc();
        $_SESSION['current_pokemon'] = $pokemon;
        $_SESSION['current_hp'] = $pokemon['hp'];
    } else {
        $message = "No Pokémon found in the database.";
    }
}

$currentPokemon = $_SESSION['current_pokemon'] ?? null;
$currentHP = $_SESSION['current_hp'] ?? 0;
$maxHP = $currentPokemon['hp'] ?? 1;

// === HANDLE ACTIONS ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $currentPokemon) {

    // ATTACK
    if (isset($_POST['attack'])) {
        $damage = rand(20, 50);
        $currentHP -= $damage;

        if ($currentHP <= 0) {
            $currentHP = 0;
            $_SESSION['current_hp'] = $currentHP;
            $message = htmlspecialchars($currentPokemon['name']) . " fainted!";

            // Clear current Pokémon and reload for next one
            unset($_SESSION['current_pokemon']);
            unset($_SESSION['current_hp']);

            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $_SESSION['current_hp'] = $currentHP;
            $message = "You attacked " . htmlspecialchars($currentPokemon['name']) . " for {$damage} damage!";
        }
    }

    // CATCH
    if (isset($_POST['catch'])) {
        $thresholdHP = $maxHP * 0.25;

        if ($currentHP <= $thresholdHP) {
            $catchChance = 100; // Guaranteed catch
        } else {
            $catchChance = $currentPokemon['legendary'] ? 10 : 50;
        }

        $randomNumber = rand(1, 100);

        if ($randomNumber <= $catchChance) {
            // Add to pokedex table
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

            // Clear current Pokémon and reload for next one
            unset($_SESSION['current_pokemon']);
            unset($_SESSION['current_hp']);

            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $message = htmlspecialchars($currentPokemon['name']) . " escaped!";
        }
    }

    // NEXT
    if (isset($_POST['next'])) {
        $message = "You skipped " . htmlspecialchars($currentPokemon['name']) . ".";

        // Clear current Pokémon and reload for next one
        unset($_SESSION['current_pokemon']);
        unset($_SESSION['current_hp']);

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Calculate HP percent for health bar
$hpPercent = ($maxHP > 0) ? ($currentHP / $maxHP) * 100 : 0;
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
            cursor: pointer;
        }

        img {
            height: 200px;
        }
    </style>
</head>
<body>

<?php if ($message): ?>
    <p><strong><?php echo $message; ?></strong></p>
<?php endif; ?>

<?php if ($currentPokemon): ?>
    <h1><?php echo htmlspecialchars($currentPokemon['name']); ?></h1>
    <img class="pokemon-display" src="pokemon-gifs/<?php echo htmlspecialchars($currentPokemon['name']); ?>.gif"
         alt="<?php echo htmlspecialchars($currentPokemon['name']); ?>">

    <?php
        $sql = "SELECT * FROM pokemon WHERE name = 'greninja-active'";
        $result = $conn->query($sql); 
        $DisplayPokemon = $result;
    ?>
<?php if ($DisplayPokemon->num_rows > 0): ?>
    <?php while ($row = $DisplayPokemon->fetch_assoc()): ?>
        <img class="pokemon-display-bottom" src="pokemon-back-gifs/<?php echo htmlspecialchars($row['name']); ?> (1).gif" 
        alt="<?php echo htmlspecialchars($row['name']); ?>">
    <?php endwhile; ?>
<?php endif; ?>

    <p><?php echo $currentPokemon['legendary'] ? 'This is a Legendary Pokémon!' : 'This is a normal Pokémon.'; ?></p>

        <div class="health-bar-container">
            <div class="health-bar"></div>
        </div>
        <p>HP: <?php echo $currentHP; ?> / <?php echo $maxHP; ?></p>

        <div class="topframe">
            <div id="top-speaker"></div>
            <div id="top-screen">
                <img class="pokemon-battle" src=assets/pokemon-battle.jpeg alt="pokemon-battle">
            </div>
               <img class="speaker1" src="assets/speaker-3ds.png" alt="speaker1">
                <img class="speaker2" src="assets/speaker-3ds.png" alt="speaker2">
        </div>
        <div class="middle-part">
            <div id="stripe1"></div>
            <div id="stripe2"></div>
        </div>
        <div class="bottomframe">
            <div id="bottom-screen"></div>
                <div id="joystick"></div>
                <div id="joystick-center"></div>
                <div id="button1"></div>
                <div id="button2"></div>
                <div id="button3"></div>
                <div id="button4"></div>
                <img id="big-button"src="assets/button-3ds.png" alt="">
                
                <div id="power-button"></div>
                <div id="power">power</div>
                <div class="bottom-part">
                    <div id="bottom-rectangle"></div>
                    <div id="bottom-stripe"></div>
                    <div id="bottom-stripe2"></div>
                </div>
        
        </div>
       

    <form method="post">
        <button type="submit" name="attack" class="attack">Attack Pokémon</button>
        <button type="submit" name="catch">Catch Pokémon</button>
        <button type="submit" name="next">Next Pokémon</button>
    </form>

    <button type="button" id="pokedex-button">View Pokedex</button>

    <div id="pokedex-menu" style="display: none; position: fixed; top: 10%; right: 10px; width: 300px; height: 80%; background-color: lightblue; border: 2px solid #333; border-radius: 10px; overflow-y: auto; z-index: 1000; padding: 10px;">
        <h2 style="text-align: center; font-family: Arial, sans-serif;">Pokedex</h2>
        <button type="button" id="close-pokedex" style="position: absolute; top: 5px; right: 10px; background-color: red; color: white; border: none; border-radius: 5px; padding: 5px 10px; cursor: pointer;">X</button>
        <div id="pokedex-content" style="font-family: Arial, sans-serif;">
            <?php
            $sql = "SELECT * FROM pokedex";
            $result = $conn->query($sql);

            if ($result->num_rows > 0): ?>
                <ul style="list-style: none; padding: 0;">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <li style="margin-bottom: 15px; border-bottom: 1px solid #ccc; padding-bottom: 10px;">
                            <img src="pokemon-gifs/<?php echo htmlspecialchars($row['name']); ?>.gif" alt="<?php echo htmlspecialchars($row['name']); ?>" style="height: 50px; vertical-align: middle;">
                            <strong><?php echo htmlspecialchars($row['name']); ?></strong><br>
                            HP: <?php echo $row['hp']; ?> | Attack: <?php echo $row['attack']; ?> | Defense: <?php echo $row['defense']; ?><br>
                            <?php echo $row['legendary'] ? '<span style="color: gold;">Legendary Pokémon</span>' : 'Normal Pokémon'; ?>
                        </li>
                    <?php endwhile; ?>
                </ul>
        </div>
    </div>
            <?php else: ?>
                <p>No Pokémon in your Pokedex yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.getElementById('pokedex-button').addEventListener('click', function () {
            document.getElementById('pokedex-menu').style.display = 'block';
        });

        document.getElementById('close-pokedex').addEventListener('click', function () {
            document.getElementById('pokedex-menu').style.display = 'none';
        });

        // Function to dynamically update the Pokedex content
        function updatePokedex() {
            fetch('fetch_pokedex.php')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('pokedex-content').innerHTML = data;
                })
                .catch(error => console.error('Error fetching Pokedex:', error));
        }

        // Call updatePokedex after catching a Pokémon
        document.querySelector('form').addEventListener('submit', function (event) {
            if (event.target.name === 'catch') {
                setTimeout(updatePokedex, 500); // Delay to ensure database update
            }
        });
    </script>

<?php else: ?>
    <p>No Pokémon available.</p>
<?php endif; ?>

</body>
</html>

<?php
$conn->close();
?>
