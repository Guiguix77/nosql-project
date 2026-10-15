<?php



$db = __DIR__ . '/sets.db';
$pdo = new PDO('sqlite:' . $db);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sql = file_get_contents(__DIR__ . '/execute.sql');
$pdo->exec("PRAGMA foreign_keys = ON;");
$pdo->exec($sql);
$pdo->beginTransaction();

//On import les THEMES
if (($f = fopen(__DIR__ . '/../csv/themes.csv', 'r')) !== false) {
  fgetcsv($f);
  $st = $pdo->prepare("INSERT INTO themes(id,name,parent_id)
                       VALUES(?,?,?)
                       ON CONFLICT(id) DO UPDATE SET name=excluded.name,parent_id=excluded.parent_id");
  while ($r = fgetcsv($f)) {
    if (!$r || $r[0]==='') continue;
    $st->execute([(int)$r[0], trim($r[1]??''), ($r[2]===''?null:(int)$r[2])]);
  }
  fclose($f);
}

//On import les SETS
if (($f = fopen(__DIR__ . '/../csv/sets.csv', 'r')) !== false) {
  fgetcsv($f);
  $st = $pdo->prepare("INSERT INTO sets(set_num,name,year,theme_id,num_parts,img_url)
                       VALUES(?,?,?,?,?,?)
                       ON CONFLICT(set_num) DO UPDATE SET
                         name=excluded.name,year=excluded.year,theme_id=excluded.theme_id,
                         num_parts=excluded.num_parts,img_url=excluded.img_url");
  while ($r = fgetcsv($f)) {
    if (!$r || trim($r[0]??'')==='') continue;
    $st->execute([
      trim($r[0]), trim($r[1]??''),
      ($r[2]===''?null:(int)$r[2]),
      ($r[3]===''?null:(int)$r[3]),
      ($r[4]===''?null:(int)$r[4]),
      trim($r[5]??'')
    ]);
  }
  fclose($f);
}

$pdo->commit();
echo "OK\n";



?>
