<?php
session_start();
if (!isset($_SESSION['login'])) {
    header('Location: index.php');
    exit();
}
?>

<html>
<head>
<title>Les matchs | Pronostics coupe du monde 2018</title>
    <link href='https://fonts.googleapis.com/css?family=Mina'
    rel='stylesheet'>
    <link href='https://fonts.googleapis.com/css?family=Open+Sans'
    rel='stylesheet'>
    <link href='style.css' rel='stylesheet' type='text/css'>
</head>
<body>
    <div align="left">
        <font style="font-family: 'Mina'; font-size: 20px;"><a href="index.php"><b>Pronostics CDM 2018</b></a></font>
    </div>
    <div align="right">
        <font style="font-family: 'Open Sans'; font-size: 20px;"><a href="logout.php">Déconnexion</a></font>
    </div><br/>
    <div align="center">
        <?php
        include('connect.php');

        $req = $bdd->query("SELECT matchs_q.id AS id_match, DATE_FORMAT(date, '%d/%m, %Hh%i') AS date, matchs_q.groupe, eq1.pays AS e1, eq2.pays AS e2 FROM matchs_q JOIN teams eq1 ON eq1.id = matchs_q.team1 JOIN teams eq2 ON eq2.id = matchs_q.team2 WHERE date > NOW() ORDER BY date ASC");
        ?>
        <font style="font-family: 'Open Sans'; font-size: 30px;"><b>Dans la cabane de Madame Irma</b><br/><br/></font>
    </div>
    <table width="100%" align="center">
        <tr>
            <td width="20%" align="center">
                <font style="font-family: 'Open Sans'; font-size: 15px;"><b>Matchs individuels</b></font><br/><br/>
            </td>
            <td width="20%" align="center">
                <font style="font-family: 'Open Sans'; font-size: 15px;"><a href="general.php">Toute la compétition</a></font><br/><br/>
            </td>
            <td width="20%" align="center">
                <font style="font-family: 'Open Sans'; font-size: 15px;"><a href="">Paris divers</a></font><br/><br/>
            </td>
        </tr>
    </table>
    <table width="100%" align="center">
        <tr>
            <td width="20%" align="center">
                <font style="font-family: 'Open Sans'; font-size: 25px;">Les matchs à venir</font><br/><br/>
            </td>
        </tr>
        <?php
        $i = 0;
        while ($item = $req->fetch()) {
            $pari = $bdd->prepare("SELECT score1, score2 FROM paris_q JOIN users ON users.id = paris_q.id_user WHERE id_match=:play AND users.login=:usr");
            $pari->execute(array('play' => $item['id_match'], 'usr' => $_SESSION['login']));
            $res = $pari->fetch();
            if ($i % 4 == 0) {?>
            <tr>
            <?php
            }?>
                <td width="20%" align="center">
                    <font style="font-family: 'Open Sans'; font-size: 15px;"><?php echo $item['e1'] . ' — ' . $item['e2'];?></font><br/>
                    <font style="font-family: 'Open Sans'; font-size: 10px;">-- / --</font><br/>
                    <font style="font-family: 'Open Sans'; font-size: 10px;"><?php echo $item['date'];?></font><br/>
                    <font style="font-family: 'Open Sans'; font-size: 10px;"><b>
                        <?php
                        if (!$res) {
                            echo '<a href="poules.php?id=' . $item['id_match'] . '">AUCUN PARI POUR L\'INSTANT</a>';
                        } else {
                            echo '<a href="poules.php?id=' . $item['id_match'] . '">VOUS PRÉVOYEZ : ' . $res['score1'] . '-' . $res['score2'] . '</a>';
                        }
                        ?>
                    </b></font><br/><br/><br/>
                </td><?php
            $i += 1;
        }
        ?>
    </table>
</body>
</html>