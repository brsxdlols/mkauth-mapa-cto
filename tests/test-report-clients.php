<?php
if (PHP_SAPI !== 'cli') exit(1);
$root=$argv[1];require $root.'/report-clients.php';
$link=mysqli_connect('127.0.0.1','root',getenv('MYSQL_PWD'),'mkradius');
mysqli_set_charset($link,'utf8mb4');
function reportAssert($ok,$message){if(!$ok)throw new RuntimeException('FAIL '.$message);echo 'PASS '.$message."\n";}
// All fixtures shadow production tables only within this connection.
mysqli_query($link,'CREATE TEMPORARY TABLE sis_cliente(id INT,uuid_cliente VARCHAR(50),nome VARCHAR(100),caixa_herm VARCHAR(100),login VARCHAR(64),porta_olt VARCHAR(32),porta_splitter VARCHAR(32),endereco VARCHAR(255),numero VARCHAR(15),bairro VARCHAR(100),armario_olt VARCHAR(100),cli_ativado VARCHAR(1))');
mysqli_query($link,'CREATE TEMPORARY TABLE sis_adicional(id INT,nome VARCHAR(100),caixa_herm VARCHAR(100),username VARCHAR(64),login VARCHAR(64),porta_olt VARCHAR(32),porta_splitter VARCHAR(32),end_endereco VARCHAR(255),end_numero VARCHAR(15),end_bairro VARCHAR(100),armario_olt VARCHAR(100))');
mysqli_query($link,'CREATE TEMPORARY TABLE radacct(username VARCHAR(64),acctstarttime DATETIME,acctstoptime DATETIME NULL)');
mysqli_query($link,'CREATE TEMPORARY TABLE mp_caixa(nome VARCHAR(100),capacidade INT)');
mysqli_query($link,"INSERT INTO sis_cliente VALUES(1,'owner-uuid','Titular Fixture','CAIXA 20','titular','PON1','01','Rua Titular','10','Bairro','OLT', 's'),(2,'inactive-uuid','Inativo','CAIXA 20','inativo','','02','','','','','n'),(3,'other-uuid','Outra','CAIXA 20','outra','','07','','','','','s')");
mysqli_query($link,"INSERT INTO sis_adicional VALUES(1,'Extra Online','CAIXA 20','extra_online','titular','PON15','15','Rua Extra','15','Bairro Extra','OLT'),(2,'Extra Offline','CAIXA 20','extra_offline','titular','','14','','','','OLT'),(3,'Sem Porta','CAIXA 20','sem_porta','titular','','','','','','OLT'),(4,'Apenas Adicional','CAIXA EXTRA','extra_only','titular','','02','','','','OLT'),(5,'Inativo','CAIXA 20','extra_inativo','inativo','','03','','','','OLT'),(6,'Conflito','CAIXA 20','extra_conflito','titular','','07','','','','OLT')");
mysqli_query($link,"INSERT INTO mp_caixa VALUES('CAIXA 20',16),('CAIXA EXTRA',4)");
mysqli_query($link,"INSERT INTO radacct VALUES('titular','2026-11-05 08:00:00',NULL),('extra_online','2026-11-05 09:00:00',NULL),('extra_offline','2026-11-01 08:00:00','2026-11-01 10:00:00'),('extra_offline','2026-11-04 08:00:00','2026-11-04 10:00:00')");
$source=file_get_contents($root.'/index.php');
$start=strpos($source,'    $starttime = microtime(true);');$end=strpos($source,'    $endtime = microtime(true);',$start);
$renderer=substr($source,$start,$end-$start);
function renderFixture($link,$renderer,$filter='todos',$search='',$olt=false){
 $filtro=$filter;$cfg_port_olt=$olt;$format='.hhvm';$busca=$search;
 $result=mysqli_query($link,relCtoClientesQuery($link,$search));
 $username_on=$inicio_conn=$final_conn=$tot_olt=array();
 $online=mysqli_query($link,'SELECT username,acctstarttime FROM radacct WHERE acctstoptime IS NULL');
 while($row=mysqli_fetch_assoc($online)){$key=strtolower($row['username']);$username_on[$key]=$row['username'];$inicio_conn[$key]=date('d/m/Y - H:i:s',strtotime($row['acctstarttime']));}
 ob_start();eval($renderer);return ob_get_clean();
}
function rowsAtPort($html,$port){preg_match_all('/<tr[^>]*>(.*?)<\/tr>/s',$html,$rows);$found=array();foreach($rows[1] as $row){preg_match_all('/<td[^>]*>(.*?)<\/td>/s',$row,$cells);if(count($cells[1])===8 && trim(strip_tags($cells[1][0]))==='CAIXA 20' && trim(strip_tags($cells[1][5]))===$port)$found[]=$cells[1];}return $found;}
$all=renderFixture($link,$renderer);
$port=rowsAtPort($all,'15');reportAssert(count($port)===1 && strpos($port[0][2],'extra_online')!==false && strpos($port[0][2],'Adicional')!==false && strpos($port[0][1],'ONLINE')!==false,'additional occupies port 15 with own online identity');
reportAssert(strpos($port[0][2],'owner-uuid')!==false && strpos($port[0][3],'Rua Extra')!==false,'additional uses its own address and links to owner');
reportAssert(count(rowsAtPort($all,'01'))===1 && strpos(rowsAtPort($all,'01')[0][2],'titular')!==false,'principal with same numeric ID remains present');
reportAssert(count(rowsAtPort($all,'07'))===2,'two connections sharing a port remain visible');
reportAssert(strpos($all,'extra_inativo')===false,'inactive owner eligibility preserved');
reportAssert(strpos($all,'CAIXA EXTRA')!==false && strpos($all,'extra_only')!==false,'box containing only an additional is included');
reportAssert(strpos($all,'sem_porta')===false,'additional without port occupies no physical port');
$off=renderFixture($link,$renderer,'off');reportAssert(strpos($off,'extra_offline')!==false && strpos($off,'extra_online')===false && strpos($off,'LIVRE')===false,'offline filter preserved');
reportAssert(strpos($off,'04/11/2026 - 10:00:00')!==false,'latest offline session shown');
$on=renderFixture($link,$renderer,'on');reportAssert(strpos($on,'extra_online')!==false && strpos($on,'extra_offline')===false && strpos($on,'LIVRE')===false,'online filter preserved');
$search=renderFixture($link,$renderer,'todos','extra_only');reportAssert(strpos($search,'extra_only')!==false && strpos($search,'extra_online')===false,'search by additional login');
$apostrophe=renderFixture($link,$renderer,'todos',"'");reportAssert($apostrophe==='','apostrophe in search remains valid SQL');
$olt=renderFixture($link,$renderer,'todos','',true);reportAssert(strpos($olt,'extra_online')!==false && strpos($olt,'PON15')!==false,'alternate OLT-port report includes additional');
mysqli_close($link);
