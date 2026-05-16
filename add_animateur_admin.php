<?php
session_start();
// Vérifier que l'admin est bien connecté ici...
require 'db_connect.php'; // Votre fichier de connexion à la base
require 'fpdf.php'; // Inclure la librairie FPDF

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['creer_animateur'])) {
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $email = htmlspecialchars($_POST['email']);
    
    // 1. Générer un mot de passe aléatoire de 8 caractères
    $random_password = bin2hex(random_bytes(4)); 
    
    // 2. Hacher le mot de passe pour la base de données
    $hashed_password = password_hash($random_password, PASSWORD_DEFAULT);
    
    // 3. Insérer dans la base de données
    $stmt = $pdo->prepare("INSERT INTO animateurs (nom, prenom, email, password, must_change_password) VALUES (?, ?, ?, ?, 1)");
    
    try {
        $stmt->execute([$nom, $prenom, $email, $hashed_password]);
        
        // 4. Génération du PDF avec les identifiants
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        
        $pdf->Cell(0, 10, utf8_decode('Bienvenue dans la Pastorale des Jeunes !'), 0, 1, 'C');
        $pdf->Ln(10);
        
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, utf8_decode("Bonjour $prenom $nom,"), 0, 1);
        $pdf->MultiCell(0, 10, utf8_decode("Voici tes identifiants pour te connecter à l'Espace Animateurs. Lors de ta première connexion, il te sera demandé de créer ton propre mot de passe."));
        $pdf->Ln(10);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, utf8_decode("Lien de connexion : http://localost/espace-animateurs"), 0, 1);
        $pdf->Cell(0, 10, utf8_decode("Email (Identifiant) : " . $email), 0, 1);
        $pdf->Cell(0, 10, utf8_decode("Mot de passe provisoire : " . $random_password), 0, 1);
        
        // Télécharger le PDF directement
        $pdf->Output('D', "Identifiants_$prenom.pdf");
        exit;
        
    } catch(PDOException $e) {
        $erreur = "Erreur : Cet email existe peut-être déjà.";
    }
}
?>

<h2>Ajouter un nouvel animateur</h2>
<?php if(isset($erreur)) echo "<p style='color:red;'>$erreur</p>"; ?>
<form method="POST" action="">
    <input type="text" name="nom" placeholder="Nom" required>
    <input type="text" name="prenom" placeholder="Prénom" required>
    <input type="email" name="email" placeholder="Email" required>
    <button type="submit" name="creer_animateur">Créer le compte et télécharger le PDF</button>
</form>