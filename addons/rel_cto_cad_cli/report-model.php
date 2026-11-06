<?php
require_once __DIR__.'/report-clients.php';
function relCtoKey($name) {
    $name=trim((string)$name);
    return function_exists('mb_strtolower') ? mb_strtolower($name,'UTF-8') : strtolower($name);
}
function relCtoPermission($db,$user,$permission) {
    if($user==='')return false;
    $st=$db->prepare("SELECT 1 FROM sis_perm WHERE usuario=? AND nome=? AND permissao='sim' LIMIT 1");
    if(!$st)return false;
    $st->bind_param('ss',$user,$permission);$st->execute();$st->store_result();$ok=$st->num_rows>0;$st->close();return $ok;
}
function relCtoScope($db,$session) {
    $user=!empty($session['MKA_Usuario'])?(string)$session['MKA_Usuario']:(isset($session['MM_Usuario'])?(string)$session['MM_Usuario']:'');
    if($user==='')return array('full'=>false,'groups'=>array(),'valid'=>false,'totals'=>false);
    $st=$db->prepare('SELECT cli_grupos FROM sis_acesso WHERE login=? LIMIT 1');
    if(!$st)return array('full'=>false,'groups'=>array(),'valid'=>false,'totals'=>false);
    $st->bind_param('s',$user);$st->execute();$st->bind_result($raw);$found=$st->fetch();$st->close();
    $groups=$found?array_values(array_filter(array_map('trim',explode(',',(string)$raw)))):array();
    $full=$found && (trim((string)$raw)==='' || in_array('full_clientes',$groups,true));
    return array('full'=>(bool)$full,'groups'=>$groups,'valid'=>(bool)$found,'totals'=>relCtoPermission($db,$user,'perm_totais'));
}
function relCtoSessions($db,$clients) {
    $logins=array();foreach($clients as $c){$login=trim((string)$c['login']);if($login!=='')$logins[$login]=true;}
    $sessions=array();
    foreach(array_chunk(array_keys($logins),100) as $chunk){
        $quoted=array();foreach($chunk as $login)$quoted[]="'".mysqli_real_escape_string($db,$login)."'";
        $in=implode(',',$quoted);
        $sql="SELECT r.username,r.acctstarttime,r.acctstoptime FROM radacct r INNER JOIN (SELECT username,MAX(radacctid) last_id FROM radacct WHERE username IN ($in) GROUP BY username) last_session ON r.radacctid=last_session.last_id";
        $result=mysqli_query($db,$sql);if(!$result)throw new RuntimeException('Falha ao consultar conexões.');
        while($r=mysqli_fetch_assoc($result))$sessions[relCtoKey($r['username'])]=array('status'=>'offline','start'=>$r['acctstarttime'],'stop'=>$r['acctstoptime']);
        $result=mysqli_query($db,"SELECT username,MAX(acctstarttime) started FROM radacct WHERE username IN ($in) AND acctstoptime IS NULL GROUP BY username");
        if(!$result)throw new RuntimeException('Falha ao consultar clientes online.');
        while($r=mysqli_fetch_assoc($result))$sessions[relCtoKey($r['username'])]=array('status'=>'online','start'=>$r['started'],'stop'=>null);
    }
    return $sessions;
}
function relCtoBuildReport($definitions,$clients,$sessions,$full,$totals) {
    $boxes=array();
    foreach($definitions as $d){
        $key=relCtoKey($d['nome']);if($key==='')continue;
        if(isset($boxes[$key])){$boxes[$key]['warnings'][]='Nome cadastrado mais de uma vez';continue;}
        $boxes[$key]=array('key'=>$key,'id'=>(string)$d['id'],'name'=>$d['nome'],'type'=>isset($d['tipo'])?$d['tipo']:'CTO','address'=>isset($d['endereco'])?$d['endereco']:'','capacity'=>max(0,(int)$d['capacidade']),'registered'=>true,'clients'=>array(),'ports'=>array(),'warnings'=>array());
    }
    foreach($clients as $c){
        $key=relCtoKey($c['caixa_herm']);if($key==='')continue;
        if(!isset($boxes[$key]))$boxes[$key]=array('key'=>$key,'id'=>null,'name'=>trim($c['caixa_herm']),'type'=>'','address'=>'','capacity'=>0,'registered'=>false,'clients'=>array(),'ports'=>array(),'warnings'=>array('CTO não cadastrada'));
        $session=isset($sessions[relCtoKey($c['login'])])?$sessions[relCtoKey($c['login'])]:array('status'=>'offline','start'=>null,'stop'=>null);
        $raw=trim((string)$c['porta_splitter']);$port=ctype_digit($raw) && (int)$raw>0?(int)$raw:null;
        $boxes[$key]['clients'][]=array('key'=>$c['tipo_registro'].':'.$c['registro_id'],'type'=>$c['tipo_registro'],'name'=>$c['nome'],'login'=>$c['login'],'owner'=>$c['titular_login'],'uuid'=>$c['uuid_cliente'],'port'=>$port,'rawPort'=>$raw,'signal'=>$c['porta_olt'],'address'=>trim($c['endereco'].' n. '.$c['numero']),'neighborhood'=>$c['bairro'],'olt'=>$c['armario_olt'],'status'=>$session['status'],'start'=>$session['start'],'stop'=>$session['stop']);
    }
    $result=array();
    foreach($boxes as $box){
        if(!$full && !$box['clients'])continue;
        $stats=array('connections'=>count($box['clients']),'principal'=>0,'additional'=>0,'online'=>0,'offline'=>0,'capacity'=>$box['capacity'],'used'=>0,'free'=>$full && $box['capacity']>0?0:null,'conflicts'=>0,'withoutPort'=>0,'outsideCapacity'=>0);
        $assigned=array();
        foreach($box['clients'] as $c){
            $stats[$c['type']==='adicional'?'additional':'principal']++;
            $stats[$c['status']==='online'?'online':'offline']++;
            if($c['port']===null){$stats['withoutPort']++;continue;}
            if($c['port']>$box['capacity']){$stats['outsideCapacity']++;continue;}
            if(!isset($assigned[$c['port']]))$assigned[$c['port']]=array();
            $assigned[$c['port']][]=$c['key'];
        }
        for($p=1;$p<=$box['capacity'];$p++){
            $keys=isset($assigned[$p])?$assigned[$p]:array();
            $state=count($keys)>1?'conflict':(count($keys)?'used':($full?'free':'unknown'));
            if($keys)$stats['used']++;
            if($state==='conflict')$stats['conflicts']++;
            if($state==='free')$stats['free']++;
            $box['ports'][]=array('number'=>$p,'state'=>$state,'clients'=>$keys);
        }
        if($stats['conflicts'])$box['warnings'][]='Mais de uma conexão na mesma porta';
        if($stats['outsideCapacity'])$box['warnings'][]='Há portas fora da capacidade cadastrada';
        if(!$box['capacity'])$box['warnings'][]='Capacidade não definida';
        $box['warnings']=array_values(array_unique($box['warnings']));
        $box['stats']=$totals?$stats:null;
        unset($box['capacity']);
        $result[]=$box;
    }
    usort($result,function($a,$b){return strnatcasecmp($a['name'],$b['name']);});
    return array('canTotals'=>(bool)$totals,'fullScope'=>(bool)$full,'ctos'=>$result,'updatedAt'=>date('c'));
}
function relCtoLoadReport($db,$scope) {
    $clients=array();$r=mysqli_query($db,relCtoClientesQuery($db,'',$scope));
    if(!$r)throw new RuntimeException('Falha ao consultar clientes.');
    while($row=mysqli_fetch_assoc($r)){if(trim((string)$row['caixa_herm'])!=='')$clients[]=$row;}
    $defs=array();$r=mysqli_query($db,'SELECT id,nome,tipo,endereco,capacidade FROM mp_caixa ORDER BY nome,id');
    if(!$r)throw new RuntimeException('Falha ao consultar caixas.');
    while($row=mysqli_fetch_assoc($r))$defs[]=$row;
    return relCtoBuildReport($defs,$clients,relCtoSessions($db,$clients),$scope['full'],$scope['totals']);
}
