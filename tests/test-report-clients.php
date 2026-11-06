<?php
if(PHP_SAPI!=='cli')exit(1);
$root=$argv[1];require $root.'/report-model.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$link=mysqli_connect('127.0.0.1','root',getenv('MYSQL_PWD'),'mkradius');mysqli_set_charset($link,'utf8mb4');
function reportAssert($ok,$message){if(!$ok)throw new RuntimeException('FAIL '.$message);echo 'PASS '.$message."\n";}
// All fixtures shadow production tables only within this connection.
mysqli_query($link,'CREATE TEMPORARY TABLE sis_cliente(id INT,uuid_cliente VARCHAR(50),nome VARCHAR(100),caixa_herm VARCHAR(100),login VARCHAR(64),porta_olt VARCHAR(32),porta_splitter VARCHAR(32),endereco VARCHAR(255),numero VARCHAR(15),bairro VARCHAR(100),armario_olt VARCHAR(100),cli_ativado VARCHAR(1),grupo VARCHAR(50))');
mysqli_query($link,'CREATE TEMPORARY TABLE sis_adicional(id INT,nome VARCHAR(100),caixa_herm VARCHAR(100),username VARCHAR(64),login VARCHAR(64),porta_olt VARCHAR(32),porta_splitter VARCHAR(32),end_endereco VARCHAR(255),end_numero VARCHAR(15),end_bairro VARCHAR(100),armario_olt VARCHAR(100))');
mysqli_query($link,'CREATE TEMPORARY TABLE radacct(radacctid INT AUTO_INCREMENT PRIMARY KEY,username VARCHAR(64),acctstarttime DATETIME,acctstoptime DATETIME NULL)');
mysqli_query($link,"CREATE TEMPORARY TABLE mp_caixa(id INT AUTO_INCREMENT PRIMARY KEY,nome VARCHAR(100),capacidade INT,tipo VARCHAR(20) DEFAULT 'CTO',endereco VARCHAR(100) DEFAULT 'Rua de exemplo')");
mysqli_query($link,"INSERT INTO sis_cliente VALUES(1,'owner-uuid','Titular Fixture','CAIXA 20','titular','PON1','01','Rua Titular','10','Bairro','OLT', 's','A'),(2,'inactive-uuid','Inativo','CAIXA 20','inativo','','02','','','','','n','A'),(3,'other-uuid','Outra','CAIXA 20','outra','','07','','','','','s','B')");
mysqli_query($link,"INSERT INTO sis_adicional VALUES(1,'Extra Online','CAIXA 20','extra_online','titular','PON15','15','Rua Extra','15','Bairro Extra','OLT'),(2,'Extra Offline','CAIXA 20','extra_offline','titular','','14','','','','OLT'),(3,'Sem Porta','CAIXA 20','sem_porta','titular','','','','','','OLT'),(4,'Apenas Adicional','CAIXA EXTRA','extra_only','titular','','02','','','','OLT'),(5,'Inativo','CAIXA 20','extra_inativo','inativo','','03','','','','OLT'),(6,'Conflito','CAIXA 20','extra_conflito','titular','','07','','','','OLT')");
mysqli_query($link,"INSERT INTO mp_caixa(nome,capacidade) VALUES('CAIXA 20',16),('CAIXA EXTRA',4),('CTO VAZIA',8)");
mysqli_query($link,"INSERT INTO radacct(username,acctstarttime,acctstoptime) VALUES('titular','2026-11-05 08:00:00',NULL),('extra_online','2026-11-05 09:00:00',NULL),('extra_offline','2026-11-01 08:00:00','2026-11-01 10:00:00'),('extra_offline','2026-11-04 08:00:00','2026-11-04 10:00:00')");
$all=relCtoLoadReport($link,array('full'=>true,'groups'=>array(),'totals'=>true));
$boxes=array();foreach($all['ctos'] as $b)$boxes[$b['name']]=$b;
reportAssert(count($boxes)===3,'empty registered CTOs included');
$box=$boxes['CAIXA 20'];$s=$box['stats'];
reportAssert($s['connections']===6 && $s['principal']===2 && $s['additional']===4,'principals and additionals counted independently');
reportAssert($s['used']===4 && $s['free']===12 && $s['conflicts']===1,'physical occupied/free ports and duplicate occupancy');
reportAssert($s['withoutPort']===1,'additional without port does not consume capacity');
reportAssert($box['ports'][14]['state']==='used' && $box['ports'][14]['clients']===array('adicional:1'),'additional occupies port 15');
$byKey=array();foreach($box['clients'] as $c)$byKey[$c['key']]=$c;
reportAssert(isset($byKey['cliente:1']) && isset($byKey['adicional:1']),'type plus ID preserves principal and additional');
reportAssert($byKey['adicional:1']['status']==='online' && $byKey['adicional:1']['address']==='Rua Extra n. 15','own additional identity and address');
reportAssert($byKey['adicional:2']['stop']==='2026-11-04 10:00:00','latest offline session from batched history');
reportAssert($boxes['CAIXA EXTRA']['stats']['additional']===1 && $boxes['CTO VAZIA']['stats']['free']===8,'additional-only and empty boxes');
$limited=relCtoLoadReport($link,array('full'=>false,'groups'=>array('A'),'totals'=>true));
$payload=json_encode($limited);
reportAssert(strpos($payload,'other-uuid')===false && strpos($payload,'CTO VAZIA')===false,'group scope excludes other groups and unrelated boxes');
foreach($limited['ctos'] as $b){reportAssert($b['stats']['free']===null,'partial group scope does not claim free ports');foreach($b['ports'] as $p)reportAssert($p['state']!=='free','unseen port remains unknown in partial scope');}
$hidden=relCtoLoadReport($link,array('full'=>true,'groups'=>array(),'totals'=>false));
foreach($hidden['ctos'] as $b)reportAssert($b['stats']===null && !isset($b['capacity']),'aggregate data omitted without totals permission');
$noGroups=relCtoLoadReport($link,array('full'=>false,'groups'=>array(),'totals'=>true));reportAssert(!$noGroups['ctos'],'empty scope returns no clients or boxes');
$search=mysqli_query($link,relCtoClientesQuery($link,'extra_only'));reportAssert(mysqli_num_rows($search)===1,'additional login search retained');
$quote=mysqli_query($link,relCtoClientesQuery($link,"'"));reportAssert(mysqli_num_rows($quote)===0,'apostrophe search remains safe');
mysqli_query($link,'CREATE TEMPORARY TABLE sis_acesso(login VARCHAR(50),cli_grupos VARCHAR(100))');
mysqli_query($link,'CREATE TEMPORARY TABLE sis_perm(usuario VARCHAR(50),nome VARCHAR(50),permissao VARCHAR(3))');
mysqli_query($link,"INSERT INTO sis_acesso VALUES('allowed','full_clientes'),('denied','A')");
mysqli_query($link,"INSERT INTO sis_perm VALUES('allowed','perm_totais','sim'),('denied','perm_numClientes','sim')");
$scope=relCtoScope($link,array('MKA_Usuario'=>'allowed'));reportAssert($scope['full'] && $scope['totals'],'native totals permission enabled');
$scope=relCtoScope($link,array('MKA_Usuario'=>'denied'));reportAssert(!$scope['full'] && !$scope['totals'] && $scope['groups']===array('A'),'native totals key and restricted groups');
reportAssert(!relCtoScope($link,array())['valid'],'missing identity denied');
if(isset($argv[2])){
    $defs=$demoClients=$demoSessions=array();$id=0;
    for($b=1;$b<=12;$b++){
        $name=str_pad($b,3,'0',STR_PAD_LEFT).' CTO '.array('CENTRO','JARDINS','VILA NOVA')[($b-1)%3];
        $defs[]=array('id'=>$b,'nome'=>$name,'tipo'=>$b===5?'CEO':'CTO','endereco'=>'Rua das Flores, '.($b*20),'capacidade'=>16);
        $n=$b===8?0:($b%5)+7;
        for($i=1;$i<=$n;$i++){
            $id++;$extra=$i%4===0;$login=($extra?'adicional_':'cliente_').$b.'_'.$i;$port=$b===3&&$i===$n?2:$i;
            $demoClients[]=array('tipo_registro'=>$extra?'adicional':'cliente','registro_id'=>$id,'uuid_cliente'=>'fixture-'.$id,'nome'=>'Cliente Exemplo '.$id,'caixa_herm'=>$name,'login'=>$login,'porta_olt'=>'PON '.(($b%3)+1),'porta_splitter'=>str_pad($port,2,'0',STR_PAD_LEFT),'endereco'=>'Rua das Flores','numero'=>$b*20+$i,'bairro'=>'Bairro de exemplo','armario_olt'=>$b%2?'OLT CENTRO':'OLT JARDINS','titular_login'=>'cliente_'.$b.'_1');
            $demoSessions[relCtoKey($login)]=array('status'=>$i%5===0?'offline':'online','start'=>'2026-11-05 08:15:00','stop'=>$i%5===0?'2026-11-05 09:20:00':null);
        }
    }
    $demo=relCtoBuildReport($defs,$demoClients,$demoSessions,true,true);$demo['clientExtension']='hhvm';$demo['signalMode']=false;$demo['elapsedMs']=84;
    file_put_contents($argv[2],json_encode($demo,JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT));
}
mysqli_close($link);
