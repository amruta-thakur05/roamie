<?php
// raah_bot.php - The intelligence behind Roamie's Bot

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $userMsg = strtolower(trim($_POST['message']));
    $response = "";

    // 1. Simple Keyword-Based Logic (The "Brain")
    if (str_contains($userMsg, 'hi') || str_contains($userMsg, 'hello')) {
        $response = "Namaste! I'm Raah. I can help you find stays, cabs, or guides in India. What are you looking for today?";
    } 
    elseif (str_contains($userMsg, 'surat')) {
        $response = "Ah, the Diamond City 💎! In Surat, don’t miss Dumas Beach 🌊 or the delicious Locho at Jani's 😋. Food + vibes = perfect combo! Need a cab, guide or rentals to get around? just search the category.";
    } 
    elseif (str_contains($userMsg, 'rajasthan')) {
    $response = "Royal vibes incoming! 👑 Rajasthan is all about grand forts, deserts, and feeling like a king/queen. Don’t skip the sunsets in Jaisalmer! Need a cab, guide or rentals to get around? just search the category.";
}
elseif (str_contains($userMsg, 'delhi')) {
    $response = "Welcome to Delhi — where history meets chaos (and amazing food 😋)! From India Gate to Chandni Chowk, there's a lot to explore. Need a cab, guide or rentals to get around? just search the category.";
}
elseif (str_contains($userMsg, 'jaipur')) {
    $response = "Pink City calling! 🌸 Jaipur has palaces, forts, and shopping that might empty your wallet 😄 Don’t miss Hawa Mahal! Need a cab, guide or rentals to get around? just search the category.";
}
elseif (str_contains($userMsg, 'mumbai')) {
    $response = "Welcome to Mumbai — fast life, big dreams, and vada pav on repeat! 🌆 Catch the Marine Drive sunset, it hits different. Need a cab, guide or rentals to get around? just search the category.";
}
elseif (str_contains($userMsg, 'kerala')) {
    $response = "Time to slow down 😌 Kerala’s backwaters, greenery, and chill vibes are pure therapy. Houseboat ride = must! Need a cab, guide or rentals to get around? just search the category.";
}
elseif (str_contains($userMsg, 'rishikesh')) {
    $response = "Adventure + peace combo unlocked! 🧘‍♂️ Rishikesh has rafting, yoga, and vibes that fix your mood instantly. Need a cab, guide or rentals to get around? just search the category.";
}
elseif (str_contains($userMsg, 'haridwar')) {
    $response = "Spiritual mode ON 🙏 Haridwar’s Ganga Aarti is something you’ll never forget. Pure goosebumps moment! Need a cab, guide or rentals to get around? just search the category.";
}
    elseif (str_contains($userMsg, 'cab') || str_contains($userMsg, 'taxi') || str_contains($userMsg, 'car')) {
        $response = "Roamie has verified local partners for Cabs. You can find them in the 'Cabs' category on our homepage!";
    } 
    elseif (str_contains($userMsg, 'stay') || str_contains($userMsg, 'hotel') || str_contains($userMsg, 'villa')) {
        $response = "Looking for a place to crash? Check our 'Stays' section. We have everything from boutique hotels to cozy homestays.";
    } 
    elseif (str_contains($userMsg, 'price') || str_contains($userMsg, 'cost')) {
        $response = "Prices vary by service, but we ensure local rates without the 'tourist tax'. Check individual listings for exact pricing!";
    } 
    elseif (str_contains($userMsg, 'help') || str_contains($userMsg, 'support')) {
        $response = "I'm here! You can also reach our human support team at support@roamie.com or via the 'Contact Us' section in the footer.";
    } 
    else {
        // Fallback for unknown queries
        $response = "That's a great question! I'm still learning, but you can explore our 'Tours & Attractions' section to find your way, or try asking about 'cabs' or 'stays'.";
    }

    echo $response;
} else {
    echo "Direct access not allowed.";
}
?>