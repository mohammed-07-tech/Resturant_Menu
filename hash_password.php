<?php
/**
 * Script pour générer des mots de passe hachés
 * Utilisez ce script pour créer des mots de passe sécurisés pour vos administrateurs
 */

// Fonction pour générer un hash de mot de passe
function generatePasswordHash($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Fonction pour vérifier un mot de passe
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Si le script est appelé directement, générer des exemples
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    echo "<h2>Générateur de mots de passe pour Restaurant Le Gourmet</h2>";
    
    // Mots de passe de démonstration
    $passwords = [
        'admin123',
        'restaurant2024',
        'legourmet123',
        'admin_secure'
    ];
    
    echo "<h3>Mots de passe hachés :</h3>";
    echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
    echo "<tr><th style='padding: 10px;'>Mot de passe</th><th style='padding: 10px;'>Hash (à insérer en DB)</th></tr>";
    
    foreach ($passwords as $password) {
        $hash = generatePasswordHash($password);
        echo "<tr>";
        echo "<td style='padding: 10px; font-weight: bold;'>" . htmlspecialchars($password) . "</td>";
        echo "<td style='padding: 10px; font-family: monospace; font-size: 12px;'>" . htmlspecialchars($hash) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<h3>Requête SQL pour insérer l'utilisateur admin :</h3>";
    $adminHash = generatePasswordHash('admin123');
    echo "<pre style='background: #f4f4f4; padding: 15px; border-radius: 5px;'>";
    echo "INSERT INTO utilisateurs (username, password) VALUES \n";
    echo "('admin', '" . $adminHash . "');";
    echo "</pre>";
    
    echo "<h3>Instructions :</h3>";
    echo "<ol>";
    echo "<li>Copiez le hash généré ci-dessus</li>";
    echo "<li>Exécutez la requête SQL dans votre base de données</li>";
    echo "<li>Utilisez 'admin' comme nom d'utilisateur et 'admin123' comme mot de passe</li>";
    echo "<li><strong>Changez le mot de passe par défaut en production !</strong></li>";
    echo "</ol>";
    
    echo "<h3>Test de vérification :</h3>";
    $testHash = $adminHash;
    $testPassword = 'admin123';
    $isValid = verifyPassword($testPassword, $testHash);
    
    echo "<p>Test avec le mot de passe 'admin123' : ";
    echo $isValid ? "<span style='color: green; font-weight: bold;'>✓ VALIDE</span>" : "<span style='color: red; font-weight: bold;'>✗ INVALIDE</span>";
    echo "</p>";
    
    // Formulaire pour générer un nouveau hash
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
        $newPassword = $_POST['new_password'];
        if (!empty($newPassword)) {
            $newHash = generatePasswordHash($newPassword);
            echo "<h3>Nouveau mot de passe généré :</h3>";
            echo "<p><strong>Mot de passe :</strong> " . htmlspecialchars($newPassword) . "</p>";
            echo "<p><strong>Hash :</strong> <code>" . htmlspecialchars($newHash) . "</code></p>";
            echo "<pre style='background: #f4f4f4; padding: 15px; border-radius: 5px;'>";
            echo "INSERT INTO utilisateurs (username, password) VALUES \n";
            echo "('votre_nom_utilisateur', '" . $newHash . "');";
            echo "</pre>";
        }
    }
    
    echo "<h3>Générer un nouveau mot de passe :</h3>";
    echo "<form method='post' style='margin: 20px 0;'>";
    echo "<input type='text' name='new_password' placeholder='Entrez votre nouveau mot de passe' style='padding: 10px; width: 300px; margin-right: 10px;'>";
    echo "<input type='submit' value='Générer Hash' style='padding: 10px 20px; background: #ff6b35; color: white; border: none; border-radius: 5px; cursor: pointer;'>";
    echo "</form>";
    
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<strong>⚠️ Important :</strong> Supprimez ce fichier après avoir configuré vos utilisateurs pour des raisons de sécurité !";
    echo "</div>";
}
?>