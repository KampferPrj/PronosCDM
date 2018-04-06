<?php
session_start();
if (!isset($_SESSION['login'])) {
    header('Location: index.php');
    exit();
}
?>

<html>
<head>
<title>Résultats de la phase de groupes | Pronostics coupe du monde 2018</title>
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
        ?>
        <font style="font-family: 'Open Sans'; font-size: 30px;"><b>Voilà ce qui va se passer...</b><br/><br/></font>
    </div>
    <?php
    $grp = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');
    $req = $bdd->query("SELECT id FROM users WHERE login='" . $_SESSION['login'] . "'");
    $id_perso = $req->fetch()['id'];
    ?>
    <table width="100%" align="center">
        <tr>
            <td width="20%" align="center">
                <font style="font-family: 'Open Sans'; font-size: 15px;"><a href="predictions.php">Matchs individuels</a></font><br/>
            </td>
            <td width="20%" align="center">
                <font style="font-family: 'Open Sans'; font-size: 15px;"><b>Toute la compétition</b></font><br/>
            </td>
            <td width="20%" align="center">
                <font style="font-family: 'Open Sans'; font-size: 15px;"><a href="">Paris divers</a></font><br/>
            </td>
        </tr>
    </table>
    <table width="90%" align="center">
        <form action="general.php" method="post">
        <?php
        $pays = $bdd->query("SELECT pays FROM teams ORDER BY groupe, id");
        for ($i = 0; $i < count($grp); $i++) {
            $msg = '';
            $slc1 = '0';
            $slc2 = '0';
            $eq = array();
            for ($j = 0; $j < 4; $j++) {
                $eq[] = $pays->fetch()['pays'];
            }
            if ($i == 0 || $i == 4) {
                echo '<tr>';
            }
            $bet = $bdd->prepare("SELECT id_pari, id_e1, id_e2 FROM paris_0 WHERE id_user=:usr AND grp=:groupe");
            $bet->execute(array('usr' => $id_perso, 'groupe' => $grp[$i]));
            $data = $bet->fetch();
            if ($data) {
                $slc1 = $data['id_e1'];
                $slc2 = $data['id_e2'];
            }
            if (isset($_POST[$grp[$i] . '1']) && isset($_POST[$grp[$i] . '2'])) {
                $e1 = $_POST[$grp[$i] . '1'];
                $e2 = $_POST[$grp[$i] . '2'];
                if ($e1 == $e2 && $e1 != '0') {
                    $msg = 'Attention ! Les deux équipes doivent être différentes.';
                } else {
                    $arr = array();
                    for ($j = 0; $j < 4; $j++) {
                        $arr[] = (string)($i * 4 + $j + 1);
                    }
                    $arr[] = '0';
                    if (in_array($e1, $arr) && in_array($e2, $arr)) {
                        $slc1 = $e1;
                        $slc2 = $e2;
                        if (!$data) {
                            $req = $bdd->prepare("INSERT INTO paris_0(id_user, grp, id_e1, id_e2) VALUES(:usr, :groupe, :eq1, :eq2)");
                            $req->execute(array('usr' => $id_perso, 'groupe' => $grp[$i], 'eq1' => $e1, 'eq2' => $e2));
                            $msg = 'Votre choix a été pris en compte.';
                        } else {
                            $req = $bdd->prepare("UPDATE paris_0 SET id_e1=:eq1, id_e2=:eq2 WHERE id_user=:usr AND grp=:groupe");
                            $req->execute(array('eq1' => $e1, 'eq2' => $e2, 'usr' => $id_perso, 'groupe' => $grp[$i]));
                            $msg = 'Votre choix a été pris en compte.';
                        }
                    } else {
                        $msg = 'Un problème a été rencontré.';
                    }
                }
            }
            ?>
            <td width="20%" align="center">
                <font style="font-family: 'Open Sans'; font-size: 20px;">Groupe <?php echo ' ' . $grp[$i];?></font><br/>
                <font style="font-family: 'Open Sans'; font-size: 15px;">1<sup>er</sup> du groupe</font>
                <select name=<?php echo '"' . $grp[$i] . '1"';?>>
                    <option value="0">--</option>
                    <?php
                    for ($j = 0; $j < 4; $j++) {
                        if ((string)($i * 4 +$j + 1) == $slc1) {
                            echo '<option value="' . (string)($i * 4 + $j + 1) . '" selected>' . $eq[$j] . '</option>';
                        } else {
                            echo '<option value="' . (string)($i * 4 + $j + 1) . '">' . $eq[$j] . '</option>';
                        }
                    }?>
                </select><br/>
                <font style="font-family: 'Open Sans'; font-size: 15px;">2<sup>e</sup> du groupe</font>
                <select name=<?php echo '"' . $grp[$i] . '2"';?>>
                    <option value="0">--</option>
                    <?php
                    for ($j = 0; $j < 4; $j++) {
                        if ((string)($i * 4 +$j + 1) == $slc2) {
                            echo '<option value="' . (string)($i * 4 + $j + 1) . '" selected>' . $eq[$j] . '</option>';
                        } else {
                            echo '<option value="' . (string)($i * 4 + $j + 1) . '">' . $eq[$j] . '</option>';
                        }
                    }?>
                </select><br/>
                <font style="font-family: 'Open Sans'; font-size: 10px;"><?php echo $msg;?></font>
            </td>
            <?php
            if ($i == 3 || $i == 7) {
                echo '</tr>';
            }
        }
        ?>
        <tr>
            <td width="10%" align="center">
                <br/>
                <input type="submit" value="Valider"/><br/><br/><br/>
            </td><br/>
        </tr>
    </form>
    </table>
    <table width="90%" align="center">
        <tr>
            <td width="15%" align="center">
                <font style="font-family: 'Open Sans'; font-size: 15px;"><b>La phase de groupes</b></font><br/>
            </td>
            <td width="15%" align="center">
                <font style="font-family: 'Open Sans'; font-size: 15px;"><a href="general8.php">Les huitièmes de finale</a></font><br/>
            </td>
            <td width="15%" align="center">
                <font style="font-family: 'Open Sans'; font-size: 15px;"><a href="general4.php">Les quarts de finale</a></font><br/>
            </td>
            <td width="15%" align="center">
                <font style="font-family: 'Open Sans'; font-size: 15px;"><a href="general2.php">Les demi-finales</a></font><br/>
            </td>
            <td width="15%" align="center">
                <font style="font-family: 'Open Sans'; font-size: 15px;"><a href="petitefinale.php">La petite finale</b></font><br/>
            </td>
            <td width="15%" align="center">
                <font style="font-family: 'Open Sans'; font-size: 15px;"><a href="general1.php">La finale</a></font><br/>
            </td>
</body>
</html>