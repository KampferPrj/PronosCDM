<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] != 'admin') {
    header('Location: index.php');
    exit();
}

include('connect.php');

$req = $bdd->query("SELECT id FROM users WHERE login='admin'");
$id_perso = $req->fetch()['id'];

$grp = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

/* Peuplement des 1/8 de finale */
for ($i = 0; $i < 8; $i++) {
    $e1 = $bdd->prepare("SELECT id_e1 FROM paris_0 WHERE id_user=:usr AND grp=:groupe;");
    $e2 = $bdd->prepare("SELECT id_e2 FROM paris_0 WHERE id_user=:usr AND grp=:groupe;");

    if ($i < 4) {
        $e1->execute(array('usr' => $id_perso, 'groupe' => $grp[2*$i]));
        $e2->execute(array('usr' => $id_perso, 'groupe' => $grp[2*$i+1]));
    } else {
        $e1->execute(array('usr' => $id_perso, 'groupe' => $grp[2*($i-4)+1]));
        $e2->execute(array('usr' => $id_perso, 'groupe' => $grp[2*($i-4)]));
    }

    $maj = $bdd->prepare("UPDATE matchs SET team1=:e1, team2=:e2 WHERE groupe=:grp");
    $maj->execute(array('e1' => $e1->fetch()['id_e1'], 'e2' => $e2->fetch()['id_e2'], 'grp' => 'H' . (string)($i+1)));
}

/* Peuplement des 1/4 de finale */
for ($i = 0; $i < 4; $i++) {
    $e1 = $bdd->prepare("SELECT id_e1 FROM paris_0 WHERE id_user=:usr AND grp=:groupe;");
    $e2 = $bdd->prepare("SELECT id_e1 FROM paris_0 WHERE id_user=:usr AND grp=:groupe;");

    $e1->execute(array('usr' => $id_perso, 'groupe' => 'H' . (string)(2*$i+1)));
    $e2->execute(array('usr' => $id_perso, 'groupe' => 'H' . (string)(2*$i+2)));

    $maj = $bdd->prepare("UPDATE matchs SET team1=:e1, team2=:e2 WHERE groupe=:grp");
    $maj->execute(array('e1' => $e1->fetch()['id_e1'], 'e2' => $e2->fetch()['id_e1'], 'grp' => 'Q' . (string)($i+1)));
}

/* Peuplement des 1/2 finales */
for ($i = 0; $i < 2; $i++) {
    $e1 = $bdd->prepare("SELECT id_e1 FROM paris_0 WHERE id_user=:usr AND grp=:groupe;");
    $e2 = $bdd->prepare("SELECT id_e1 FROM paris_0 WHERE id_user=:usr AND grp=:groupe;");

    $e1->execute(array('usr' => $id_perso, 'groupe' => 'Q' . (string)(2*$i+1)));
    $e2->execute(array('usr' => $id_perso, 'groupe' => 'Q' . (string)(2*$i+2)));

    $maj = $bdd->prepare("UPDATE matchs SET team1=:e1, team2=:e2 WHERE groupe=:grp");
    $maj->execute(array('e1' => $e1->fetch()['id_e1'], 'e2' => $e2->fetch()['id_e1'], 'grp' => 'D' . (string)($i+1)));
}

/* Peuplement de la finale */
$e1 = $bdd->prepare("SELECT id_e1 FROM paris_0 WHERE id_user=:usr AND grp='D1';");
$e2 = $bdd->prepare("SELECT id_e1 FROM paris_0 WHERE id_user=:usr AND grp='D2';");

$e1->execute(array('usr' => $id_perso));
$e2->execute(array('usr' => $id_perso));

$maj = $bdd->prepare("UPDATE matchs SET team1=:e1, team2=:e2 WHERE groupe='F0'");
$maj->execute(array('e1' => $e1->fetch()['id_e1'], 'e2' => $e2->fetch()['id_e1']));

/* Peuplement de la petite finale */
$e1 = $bdd->prepare("SELECT id_e2 FROM paris_0 WHERE id_user=:usr AND grp='D1';");
$e2 = $bdd->prepare("SELECT id_e2 FROM paris_0 WHERE id_user=:usr AND grp='D2';");

$e1->execute(array('usr' => $id_perso));
$e2->execute(array('usr' => $id_perso));

$maj = $bdd->prepare("UPDATE matchs SET team1=:e1, team2=:e2 WHERE groupe='F1'");
$maj->execute(array('e1' => $e1->fetch()['id_e2'], 'e2' => $e2->fetch()['id_e2']));

/* Enregistrement du résultat des matchs */

$matchs = $bdd->prepare("SELECT id_match, score1, score2, winner FROM paris_match WHERE id_user=:usr");
$matchs->execute(array('usr' => $id_perso));

while ($match = $matchs->fetch()) {
    $maj = $bdd->prepare("UPDATE matchs SET score1=:s1, score2=:s2, winner=:win, played=1 WHERE id=:id_m");
    $maj->execute(array('s1' => $match['score1'], 's2' => $match['score2'], 'win' => $match['winner'], 'id_m' => $match['id_match']));
}

/* Points des utilisateurs */

$users = $bdd->query("SELECT id FROM users WHERE login != 'admin'");

while ($usr = $users->fetch()) {
    $pts = 0;

    /* Points des matchs */
    $p_matchs = $bdd->prepare("SELECT id_match, score1, score2, winner FROM paris_match WHERE id_user=:usr");
    $p_matchs->execute(array('usr' => $usr['id']));

    while ($p = $p_matchs->fetch()) {
        $r_m = $bdd->prepare("SELECT score1, score2, winner, played FROM matchs WHERE id=:id_m");
        $r_m->execute(array('id_m' => $p['id_match']));

        $m = $r_m->fetch();

        if ($m['played'] == 1) {
            if ($p['winner'] == $m['winner'] && $p['score1'] == $m['score1'] && $p['score2'] == $p['score2']) {
                $pts += 5;
            } elseif ($p['winner'] == $m['winner'] && $p['score1'] - $p['score2'] == $m['score1'] - $m['score2']) {
                $pts += 3;
            } elseif ($p['winner'] == $m['winner']) {
                $pts += 1;
            }
        }
    }

    $maj = $bdd->prepare("UPDATE users SET points=:pts WHERE id=:usr");
    $maj->execute(array('pts' => $pts, 'usr' => $usr['id']));
}

header('Location: index.php');
exit();

?>