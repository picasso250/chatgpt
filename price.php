<?php
// Define the given values
$pricePer1000TokensUSD = 0.004;//0.0010; // Price in USD for 1000 tokens
$exchangeRateUSDToCNY = 7.3;    // Exchange rate from USD to CNY (Chinese Yuan)
$charactersPer1000Tokens = 650;  // Number of characters for 1000 tokens

// Calculate the price in CNY for 1000 tokens
$pricePer1000TokensCNY = $pricePer1000TokensUSD * $exchangeRateUSDToCNY;

// Calculate the number of characters that can be obtained for 1 CNY
$charactersPer1CNY = $charactersPer1000Tokens / $pricePer1000TokensCNY;
echo"charactersPer1CNY=$charactersPer1CNY\n";

// 以上是成本价，我们要收取一定比例
$pricePerCharacter = $charactersPer1CNY / 1.4;

// Calculate characters for different amounts
$amounts = [10, 21, 60, 120];
foreach ($amounts as $amount) {
    $characters = $amount * $pricePerCharacter;
    echo "For ¥{$amount}, you can obtain approximately {$characters} characters.\n";
}


function calculateCharactersForAmounts($amounts) {
    // Define the given values
    $pricePer1000TokensUSD = 0.004;
    $exchangeRateUSDToCNY = 7.3;
    $charactersPer1000Tokens = 650;

    // Calculate the price in CNY for 1000 tokens
    $pricePer1000TokensCNY = $pricePer1000TokensUSD * $exchangeRateUSDToCNY;

    // Calculate the number of characters that can be obtained for 1 CNY
    $charactersPer1CNY = $charactersPer1000Tokens / $pricePer1000TokensCNY;

    // Calculate the price per character
    $pricePerCharacter = $charactersPer1CNY / 1.4;

    // Calculate characters for different amounts
    $result = [];
    foreach ($amounts as $amount) {
        $characters = $amount * $pricePerCharacter;
        $result[] = (int) $characters; // Convert to integer
    }

    return $result;
}

// Example usage
$amounts = [10, 21, 60, 120];
$resultArray = calculateCharactersForAmounts($amounts);

// Output the result
print_r($resultArray);
?>
