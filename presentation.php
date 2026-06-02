<?php
$salle_active = isset($_GET['salle']) ? $_GET['salle'] : '001';

$donnees_salles = [
    '001' => [
        'nom' => 'Horizon',
        'tp' => 'TP 1.2',
        'concept' => 'Comment les objets numériques altèrent-ils notre perception du réel ? À travers trois œuvres interactives, nous explorons la manière dont les technologies influencent notre perception du réel, en transformant notre rapport au corps, à l\'image et à l\'environnement.',
        'oeuvres' => [
            [
                'nom' => 'Bon profil',
                'desc' => 'L\'œuvre explore la vulnérabilité de notre identité numérique en plaçant le visiteur au cœur d\'une mécanique de désinformation instantanée. Le spectateur est invité à prendre une simple photo, pensant capturer un souvenir inoffensif. Cette image est immédiatement détournée par une intelligence artificielle qui génère un deepfake à son insu. En quelques secondes, le visage du visiteur se retrouve propulsé dans des situations absurdes ou compromettantes et publié sur un faux fil d\'actualité de réseaux sociaux.'
            ],
            [
                'nom' => 'Antithèse',
                'desc' => 'Antithèse oppose deux visions de la réalité : une vision déformée, influencée par les réseaux sociaux, les chaînes d\'information et les contenus biaisés, et une vision plus objective basée sur des faits et l\'esprit critique. Grâce à une interaction basée sur la distance du visiteur, l\'installation montre que notre perception peut évoluer selon le point de vue adopté.'
            ],
            [
                'nom' => 'Beauté hors du cadre',
                'desc' => 'L\'œuvre plonge le spectateur dans un espace végétal apaisant où un écran géant imite un smartphone. Ce miroir numérique capte le reflet du visiteur et l\'invite à "swiper" des vidéos d\'abord familières et agréables. Au fil des défilements, les contenus deviennent angoissants, la lumière s\'assombrit et les sons naturels se déforment. Le reflet de l\'utilisateur s\'efface peu à peu, donnant l\'illusion qu\'il est totalement absorbé par l\'écran.'
            ],
        ]
    ],
    '021' => [
        'nom' => 'Societ-e',
        'tp' => 'TP 1.1',
        'concept' => 'Référent de salle : Benjamin RENOLLET.',
        'oeuvres' => [
            [
                'nom' => 'Community',
                'desc' => 'Description à venir.'
            ],
            [
                'nom' => 'Distorsion',
                'desc' => 'L\'œuvre Distorsion explore l\'émancipation de notre identité dans un récit où notre image ne nous appartient plus. Elle montre que, dans l\'univers numérique, notre visage devient une matière que les autres peuvent modifier, détourner ou réinventer. Cette transformation imposée crée une version de nous qui échappe à notre contrôle. Dans Distorsion, cette image altérée est ensuite mise en vente, comme un produit parmi d\'autres, révélant comment la société de consommation s\'approprie jusqu\'à notre identité.'
            ],
        ]
    ],
    '002' => [
        'nom' => 'L\'Envers du Décor',
        'tp' => 'TP 2.1',
        'concept' => 'Comment l\'illusion d\'une société parfaite révèle-t-elle l\'état de la nôtre ? Nous questionnons les façades que la société se construit pour masquer ses contradictions, qu\'il s\'agisse du regard social sur les réseaux, du glamour de la mode ou de la mécanique de la consommation.',
        'oeuvres' => [
            [
                'nom' => 'Tapis Rouge',
                'desc' => 'TAPIS ROUGE est une installation interactive et immersive qui détourne les codes du prestige pour confronter le spectateur aux réalités sociales invisibles de la production industrielle. Un tapis rouge physique invite le public à s\'avancer sous des projecteurs de scène. Le mouvement du visiteur contrôle directement une vidéo projetée sur le mur : d\'abord une ambiance de luxe et de privilège, puis les coulisses de la consommation s\'imposent. En bout de course, l\'image devient grave : pénibilité du travail, insalubrité des usines, épuisement des corps.'
            ],
            [
                'nom' => 'En Direct',
                'desc' => 'Placez-vous devant l\'écran : une caméra vous filme en direct, à la manière d\'un live TikTok ou Instagram. Des commentaires apparaissent automatiquement, générés selon votre distance à l\'écran et vos expressions faciales détectées par reconnaissance d\'image. Trop proche, trop loin, souriant ou neutre — quoi que vous fassiez, vous serez jugé. En Direct met en scène le jugement social permanent que produisent les réseaux sociaux.'
            ],
            [
                'nom' => 'AD-HD (Ad Driven Human Display)',
                'desc' => 'Ad Driven Human Display est une œuvre interactive qui questionne la place de l\'humain dans l\'économie de l\'attention. Face à une interface TikTok, le visiteur fait défiler un flux de contenus promotionnels à l\'aide d\'un grand rouleau physique. Des pop-up publicitaires apparaissent aléatoirement, l\'obligeant à les fermer avec une souris. L\'œuvre révèle une illusion contemporaine : nous pensons contrôler ce que nous regardons, alors que nos gestes et notre attention sont constamment captés par la publicité.'
            ],
        ]
    ],
    '005' => [
        'nom' => 'La Pépinière',
        'tp' => 'TP 2.2',
        'concept' => 'À l\'image de nos rêves, comment le numérique altère-t-il notre perception de la réalité ? Notre exposition immersive vous invite à explorer comment le numérique transforme notre perception du monde. Entre illusions optiques, expériences sensorielles et récits visuels, venez questionner votre propre perception.',
        'oeuvres' => [
            [
                'nom' => 'LOTUS',
                'desc' => 'Une œuvre interactive où la chute d\'Alice au pays des merveilles dans le terrier du lapin devient une expérience sensorielle et participative. En jouant du synthétiseur, les visiteurs transforment en temps réel ce que le public voit à l\'écran : les notes jouées modifient la vitesse, la forme ou les couleurs de la chute. Une deuxième personne peut interagir avec un LFO pour ajouter une couche de modulation, déformant à la fois la sonorité et l\'image.'
            ],
            [
                'nom' => 'E-biscus',
                'desc' => 'E-biscus est une œuvre interactive qui met en avant les illusions de la société concernant la beauté. Un jeune homme s\'endort après avoir liké la photo d\'une inconnue sur Instagram. Dans son rêve, il a rendez-vous avec elle et doit préparer son apparence. Les spectateurs peuvent interagir avec une interface pour le modifier. La rencontre se passe, mais pas comme prévu : ni lui ni elle ne ressemblent à leurs photos. L\'œuvre interroge la différence entre l\'image que l\'on donne sur les réseaux et ce que l\'on est réellement.'
            ],
            [
                'nom' => 'Datura',
                'desc' => '« Datura » est une installation interactive présentant une forêt de séquoias en 3D, projetée sur un grand écran. Deux zones physiques au sol, « Zone Rêve » et « Zone Cauchemar », invitent le spectateur à interagir. Une webcam détecte la répartition des visiteurs entre ces deux zones. Le public, par sa présence physique, vote et influence en temps réel le monde projeté.'
            ],
            [
                'nom' => 'Œuvre 4',
                'desc' => 'Description à venir.'
            ],
        ]
    ],
];

$salle = $donnees_salles[$salle_active];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>E-LLUSION – Présentation</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'header.php'; ?>

<section class="intro-expo">
  <div class="intro-texte">
    <h1>E-LLUSION</h1>
    <h2>Explication de l'exposition</h2>
    <p><?php echo $salle['concept']; ?></p>
  </div>
  <div class="intro-infos">
    <p><strong>18 et 19 Juin 2026</strong></p>
    <p>IUT de Chambéry - Aile C</p>
    <p>28 Avenue du Lac d'Annecy</p>
    <p>73370 Bourget-du-Lac</p>
  </div>
</section>

<div class="choix-salles">
  <a href="presentation.php?salle=001" class="btn-salle <?php echo ($salle_active == '001') ? 'active' : ''; ?>">Salle 001</a>
  <a href="presentation.php?salle=002" class="btn-salle <?php echo ($salle_active == '002') ? 'active' : ''; ?>">Salle 002</a>
  <a href="presentation.php?salle=005" class="btn-salle <?php echo ($salle_active == '005') ? 'active' : ''; ?>">Salle 005</a>
  <a href="presentation.php?salle=021" class="btn-salle <?php echo ($salle_active == '021') ? 'active' : ''; ?>">Salle 021</a>
</div>

<section class="detail-salle">
  <h3><?php echo $salle['tp']; ?> — <?php echo $salle['nom']; ?></h3>

  <?php foreach ($salle['oeuvres'] as $i => $oeuvre): ?>
    <div class="row-oeuvre <?php echo ($i % 2 != 0) ? 'reverse' : ''; ?>">
      <div class="texte-oeuvre">
        <p><?php echo $oeuvre['desc']; ?></p>
      </div>
      <div class="bloc-image">
        <span class="badge-oeuvre"><?php echo $oeuvre['nom']; ?></span>
        <div class="image-placeholder">photo / carrousel</div>
      </div>
    </div>
  <?php endforeach; ?>

</section>

<?php include 'footer.php'; ?>

</body>
</html>