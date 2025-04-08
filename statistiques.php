<!DOCTYPE html>
<html lang="fr">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Statistiques de Clics</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
   <div class="container mt-5">
       <div class="row justify-content-center">
           <div class="col-md-6 text-center">
               <div class="card shadow-sm">
                   <div class="card-header bg-primary text-white">
                       <h2>Statistiques de Clics</h2>
                   </div>
                   <div class="card-body">
                       <?php
                       require_once __DIR__ . '/./vendor/autoload.php';
                       require_once 'lib/config.php';

                       use MongoDB\Client;

                       try {
                           $mongoClient = new Client($_ENV['MONGODB_URI']);
                           $database = $mongoClient->selectDatabase('statistiques');
                           $collection = $database->selectCollection('clics');

                           $today = date('Y-m-d');
                           $yesterday = date('Y-m-d', strtotime('-1 day'));

                           $todayDocument = $collection->findOne([
                               'type' => 'site_clics',
                               'date' => $today
                           ]);

                           $yesterdayDocument = $collection->findOne([
                               'type' => 'site_clics',
                               'date' => $yesterday
                           ]);

                           if (!$todayDocument) {
                               $collection->insertOne([
                                   'type' => 'site_clics',
                                   'date' => $today,
                                   'clics' => 1
                               ]);
                               $currentClicks = 1;
                           } else {
                               $updateResult = $collection->updateOne(
                                   ['type' => 'site_clics', 'date' => $today],
                                   ['$inc' => ['clics' => 1]]
                               );
                               $currentClicks = $todayDocument['clics'] + 1;
                           }

                           $yesterdayClicks = $yesterdayDocument ? $yesterdayDocument['clics'] : 0;
                           $clickDifference = $currentClicks - $yesterdayClicks;
                           $percentageDifference = $yesterdayClicks > 0 
                               ? round(($clickDifference / $yesterdayClicks) * 100, 2) 
                               : 0;
                       ?>
                       <h3 class="mb-4">Nombre total de clics</h3>
                       <div class="display-4 text-primary mb-4">
                           <?php echo number_format($currentClicks, 0, ',', ' '); ?>
                       </div>

                       <div class="row">
                           <div class="col-6">
                               <h4>Aujourd'hui</h4>
                               <p class="lead"><?php echo number_format($currentClicks, 0, ',', ' '); ?> clics</p>
                           </div>
                           <div class="col-6">
                               <h4>Hier</h4>
                               <p class="lead"><?php echo number_format($yesterdayClicks, 0, ',', ' '); ?> clics</p>
                           </div>
                       </div>

                       <div class="mt-3 <?php 
                           echo $clickDifference > 0 ? 'text-success' : 
                                ($clickDifference < 0 ? 'text-danger' : 'text-muted');
                       ?>">
                           <?php 
                           if ($clickDifference > 0) {
                               echo "<strong>↑ +$clickDifference clics (+$percentageDifference%)</strong>";
                           } elseif ($clickDifference < 0) {
                               echo "<strong>↓ $clickDifference clics ($percentageDifference%)</strong>";
                           } else {
                               echo "Aucun changement";
                           }
                           ?>
                       </div>

                       <?php
                       } catch (Exception $e) {
                           echo "<div class='alert alert-danger'>Erreur : " . htmlspecialchars($e->getMessage()) . "</div>";
                       }
                       ?>
                   </div>
                   <div class="card-footer text-muted">
                       Dernière mise à jour : <?php echo date('d/m/Y H:i:s'); ?>
                   </div>
               </div>
           </div>
       </div>
   </div>

   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>