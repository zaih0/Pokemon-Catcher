<?php
// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=pokemondb', 'root', 'root');

// Array mapping Pokémon IDs to their respective sprite filenames
$pokemonSprites = [
    1 => '1.png', // Bulbasaur
    2 => '2.png', // Ivysaur
    3 => '3.png', // Venusaur
    4 => '4.png', // Charmander
    5 => '5.png', // Charmeleon
    6 => '6.png', // Charizard
    7 => '7.png', // Squirtle
    8 => '8.png', // Wartortle
    9 => '9.png', // Blastoise
    10 => '10.png', // Caterpie
    11 => '11.png', // Metapod
    12 => '12.png', // Butterfree
    13 => '13.png', // Weedle
    14 => '14.png', // Kakuna
    15 => '15.png', // Beedrill
    16 => '16.png', // Pidgey
    17 => '17.png', // Pidgeotto
    18 => '18.png', // Pidgeot
    19 => '19.png', // Rattata
    20 => '20.png', // Raticate
    21 => '21.png', // Spearow
    22 => '22.png', // Fearow
    23 => '23.png', // Ekans
    24 => '24.png', // Arbok
    25 => '25.png', // Pikachu
    26 => '26.png', // Raichu
    27 => '27.png', // Sandshrew
    28 => '28.png', // Sandslash
    29 => '29.png', // Nidoran♀
    30 => '30.png', // Nidorina
    31 => '31.png', // Nidoqueen
    32 => '32.png', // Nidoran♂
    33 => '33.png', // Nidorino
    34 => '34.png', // Nidoking
    35 => '35.png', // Clefairy
    36 => '36.png', // Clefable
    37 => '37.png', // Vulpix
    38 => '38.png', // Ninetales
    39 => '39.png', // Jigglypuff
    40 => '40.png', // Wigglytuff
    41 => '41.png', // Zubat
    42 => '42.png', // Golbat
    43 => '43.png', // Oddish
    44 => '44.png', // Gloom
    45 => '45.png', // Vileplume
    46 => '46.png', // Paras
    47 => '47.png', // Parasect
    48 => '48.png', // Venonat
    49 => '49.png', // Venomoth
    50 => '50.png', // Diglett
    51 => '51.png', // Dugtrio
    52 => '52.png', // Meowth
    53 => '53.png', // Persian
    54 => '54.png', // Psyduck
    55 => '55.png', // Golduck
    56 => '56.png', // Mankey
    57 => '57.png', // Primeape
    58 => '58.png', // Growlithe
    59 => '59.png', // Arcanine
    60 => '60.png', // Poliwag
    61 => '61.png', // Poliwhirl
    62 => '62.png', // Poliwrath
    63 => '63.png', // Abra
    64 => '64.png', // Kadabra
    65 => '65.png', // Alakazam
    66 => '66.png', // Machop
    67 => '67.png', // Machoke
    68 => '68.png', // Machamp
    69 => '69.png', // Bellsprout
    70 => '70.png', // Weepinbell
    71 => '71.png', // Victreebel
    72 => '72.png', // Tentacool
    73 => '73.png', // Tentacruel
    74 => '74.png', // Geodude
    75 => '75.png', // Graveler
    76 => '76.png', // Golem
    77 => '77.png', // Ponyta
    78 => '78.png', // Rapidash
    79 => '79.png', // Slowpoke
    80 => '80.png', // Slowbro
    81 => '81.png', // Magnemite
    82 => '82.png', // Magneton
    83 => '83.png', // Farfetch'd
    84 => '84.png', // Doduo
    85 => '85.png', // Dodrio
    86 => '86.png', // Seel
    87 => '87.png', // Dewgong
    88 => '88.png', // Grimer
    89 => '89.png', // Muk
    90 => '90.png', // Shellder
    91 => '91.png', // Cloyster
    92 => '92.png', // Gastly
    93 => '93.png', // Haunter
    94 => '94.png', // Gengar
    95 => '95.png', // Onix
    96 => '96.png', // Drowzee
    97 => '97.png', // Hypno
    98 => '98.png', // Krabby
    99 => '99.png', // Kingler
    100 => '100.png', // Voltorb
    101 => '101.png', // Electrode
    102 => '102.png', // Exeggcute
    103 => '103.png', // Exeggutor
    104 => '104.png', // Cubone
    105 => '105.png', // Marowak
    106 => '106.png', // Hitmonlee
    107 => '107.png', // Hitmonchan
    108 => '108.png', // Lickitung
    109 => '109.png', // Koffing
    110 => '110.png', // Weezing
    111 => '111.png', // Rhyhorn
    112 => '112.png', // Rhydon
    113 => '113.png', // Chansey
    114 => '114.png', // Tangela
    115 => '115.png', // Kangaskhan
    116 => '116.png', // Horsea
    117 => '117.png', // Seadra
    118 => '118.png', // Goldeen
    119 => '119.png', // Seaking
    120 => '120.png', // Staryu
    121 => '121.png', // Starmie
    122 => '122.png', // Mr. Mime
    123 => '123.png', // Scyther
    124 => '124.png', // Jynx
    125 => '125.png', // Electabuzz
    126 => '126.png', // Magmar
    127 => '127.png', // Pinsir
    128 => '128.png', // Tauros
    129 => '129.png', // Magikarp
    130 => '130.png', // Gyarados
    131 => '131.png', // Lapras
    132 => '132.png', // Ditto
    133 => '133.png', // Eevee
    134 => '134.png', // Vaporeon
    135 => '135.png', // Jolteon
    136 => '136.png', // Flareon
    137 => '137.png', // Porygon
    138 => '138.png', // Omanyte
    139 => '139.png', // Omastar
    140 => '140.png', // Kabuto
    141 => '141.png', // Kabutops
    142 => '142.png', // Aerodactyl
    143 => '143.png', // Snorlax
    144 => '144.png', // Articuno
    145 => '145.png', // Zapdos
    146 => '146.png', // Moltres
    147 => '147.png', // Dratini
    148 => '148.png', // Dragonair
    149 => '149.png', // Dragonite
    150 => '150.png', // Mewtwo
    151 => '151.png'  // Mew
];

// Base path to the sprites directory
$basePath = 'images/pokemon/';

// Prepare the SQL statement
$stmt = $pdo->prepare("UPDATE pokemon SET pokemon_sprite = :sprite WHERE id = :id");

// Iterate through each Pokémon and update the sprite path
foreach ($pokemonSprites as $id => $sprite) {
    $spritePath = $basePath . $sprite;
    $stmt->execute([':sprite' => $spritePath, ':id' => $id]);
}

echo "Pokémon sprites have been updated successfully.";
?>
