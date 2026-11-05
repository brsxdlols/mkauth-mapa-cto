<!-- 
    BUSCA INTELIGENTE
    AUTOR: ALEFF MEYKSON
    CONTATO: (82) 9 8748-1848
    EMAIL: meyknho@gmail.com  
    @AJF TELECOM - TODOS DIREITOS RESERVADOS.
-->
<?php
// INCLUE FUNCOES DE ADDONS -----------------------------------------------------------------------
include('addons.class.php');

// VERIFICA SE O USUARIO ESTA LOGADO --------------------------------------------------------------
session_name('mka');
if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['MKA_Logado'])) exit('Acesso negado... <a href="/admin/login.php">Fazer Login</a>');
?>
<!DOCTYPE html>
<?php
    if(isset($_SESSION['MM_Usuario'])){
        echo '<html lang="pt-BR">'; // Fix versão antiga MK-AUTH
    }else{
        echo '<html lang="pt-BR" class="has-navbar-fixed-top">';
    }
?>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta charset="utf-8">
        <title>MK - AUTH :: <?php echo $Manifest->{'name'}." - V ". $Manifest->{'version'};  ?></title>

        <link href="../../estilos/mk-auth.css" rel="stylesheet" type="text/css" />
        <link href="../../estilos/font-awesome.css" rel="stylesheet" type="text/css" />

        <script src="js/jquery-3.6.0.min.js"></script>
        <script src="../../scripts/mk-auth.js"></script>

        <style type="text/css">


    

        #form_graf { width: 100%; }
        #form_graf input { padding: 10px; font-size:18px; width:100%; min-width: 80px;}
        #form_graf select { padding: 10px; font-size:18px;  }
        #btn_buscar { padding: 10px; font-size:18px;  background: Navy; color:#FFF; border: 1.5px solid Navy; }
        
        .buscar {  width: 100%; }
        .conn_cliente {width: 100%;}
        .linha_titulo {font-weight: bold; text-align:center; background: Navy; color: #FFF; padding: 20px; font-size: 13px; }

        .linha_sub_titulo {font-weight: bold; text-align:center; background: lightblue; color: #111; padding: 20px; font-size: 13px; }

        .linha_resultados { font-size: 13px; }
        .rel-cto-adicional { display:inline-block;margin-left:4px;padding:1px 4px;border-radius:3px;background:#e0e7ff;color:#1e40af;font-size:10px;font-weight:bold; }
        .linha_resultados:nth-last-child(2n) {background:#E0E0F8;}

        a { text-decoration: none; color:none; }
        a:hover { text-decoration: underline; }
        .green, .green a { color:darkgreen; }
        .red, .red a { color:red; }
        .orange, .orange a { color:orange; }

        .bold { font-weight: bold; }
        #container, #container2 {
            width: 50%;
            height: 400px; 
            float:left;
            }

        
            .highcharts-figure, .highcharts-data-table table {
                width:100%;
                margin: 1em auto;
            }

        @media print{
            * {font-size: 0.7em; }
            table, td, tr {
            border: 1px solid #DDD;
            }
        table {border-collapse: collapse;}
            .no_print{ display:none;  }

            .linha_resultados:nth-last-child(2n) {background:#E0E0F8;}

            #container, #container2 { display:none; }

        
        }

</style>

</head>
<body>
<?php 

include('config.php'); 


/*
    //Paginaçço
    $registros_por_pagina = isset($_GET['num_registros']) == '' ? '50' : $_GET['num_registros'];
    $pagina = isset($_GET['pagina']) == '' ? $pc = "1" : $pc = $_GET['pagina'];

    $inicio = $pc - 1;
    $inicio = $inicio * $registros_por_pagina;
*/
    $data_atual = date('Y-m-d');
    
    $data_inicial = isset($_GET['data_inicial']) == '' ? $data_atual : $_GET['data_inicial'];

    $busca = isset($_GET['busca']) ? (string)$_GET['busca'] : '';


?>

<?php include('../../topo.php'); ?>
	
    <nav class="breadcrumb has-bullet-separator is-centered" aria-label="breadcrumbs">
        <ul>
            <li class="is-active no_print"><a href="#" aria-current="page"> 
                <?php echo $Manifest->{'name'}." - V ". $Manifest->{'version'}; ?> </a>
            </li>
            <li>
                <img src="img/icon_print.png" class="icon no_print" title="Imprimir" onClick="window.print()"/>
            </li>
            <!--<li>
                <a href="<?php echo "gerar_csv.php?data_inicial=$data_inicial&data_final=$data_final&busca=$busca&local=$local"; ?>" target="_blank"><img src="img/icon_excel.png" class="icon no_print" title="Gerar em Excel"/></a>
            </li>-->
        </ul>
    </nav>    


    
<?php

    $lista_filtro = array(
        "todos" => "Todos",
        "on" => "Apenas Online",
        "off" => "Apenas Offline",
    );

    $filtro = isset($_GET['filtro']) == '' ? 'todos' : $_GET['filtro'];

    // INFO DE CLIENTES ONLINE COM MKAUTH
    $query_cliente_on = mysqli_query($link, "SELECT username, acctstarttime, acctstoptime FROM radacct WHERE acctstoptime IS NULL");

    while ($row2 = mysqli_fetch_array($query_cliente_on)) {
        $username_on[trim(strtolower($row2['username']))] = $row2['username'];
  
        $inicio_conn[trim(strtolower($row2['username']))] = date('d/m/Y - H:i:s', strtotime($row2['acctstarttime']));
    }


    // Principais e adicionais usam a mesma elegibilidade do titular.
    require_once __DIR__ . '/report-clients.php';
    $query = relCtoClientesQuery($link, isset($busca) ? $busca : '');

    //echo $query;

    $result = mysqli_query($link, "$query");
    
    /*if(!$result){
        echo mysql_error()."<br>";
    }*/
    //Paginacao
    /*$result_limit = mysqli_query($link, "$query LIMIT $inicio,$registros_por_pagina");

    if (!$result || $result_limit){
        echo "<br>".mysql_error();
    }*/

    if(!file_exists("../../cliente_alt.hhvm")){
        $format = '.php';
    }else{
        $format = '.hhvm';
    }
?>


    <form action="" method="get">
        <table id="form_graf" class="no_print">
            <tr>               
                <td class="buscar"><b>Busca Integrada :</b></td>
                <td><b>Filtro:</b></td>

                <td></td>
            </tr>
            <tr>
                <td><input type="text" name="busca" class="buscar" placeholder="Pesquisar" value="<?php echo $busca; ?>"/></td>


                <td>
                <select name="filtro">
                        <?php
                            foreach ($lista_filtro as $key => $value) {
                                $selected = ($filtro == $key) ? "selected=\"selected\"" : null;
                                echo "<option value=\"$key\" $selected >$value</option>";
                        }
                        ?>
                        </select>
                </td>

                <td><input type="submit" name="submit" id="btn_buscar" value="OK" /></td>
            </tr>
        </table>
    </form>



    <table class='conn_cliente'>

    <?php

    $cc = 0;
    $starttime = microtime(true);


    $cli_nome = $cli_uuid = $cli_caixa_herm = $cli_porta_olt = array();
    $cli_endereco = $cli_bairro = $cli_armario_olt = array();
    $cli_login_real = $cli_tipo = $caixa_herm = array();
    while ($row = mysqli_fetch_array($result)) {
        $cli_login = $row['tipo_registro'] . ':' . $row['registro_id'];
        $cli_login_real[$cli_login] = $row['login'];
        $cli_tipo[$cli_login] = $row['tipo_registro'];
        $caixa_herm[$row['caixa_herm']] = $row['caixa_herm'];

        // Base for foreach
        $cli_nome[$cli_login] = $row['nome'];

        $cli_uuid[$cli_login] = $row['uuid_cliente'];

        $cli_caixa_herm[$cli_login] = $row['caixa_herm'];

        if($cfg_port_olt){
            $cli_porta_olt[$cli_login] = $row['porta_olt'];
        }else{
            $cli_porta_olt[$cli_login] = $row['porta_splitter'];
        }
        $cli_endereco[$cli_login] = $row['endereco'] .' n. '. $row['numero'];
        $cli_bairro[$cli_login] = $row['bairro'];
        $cli_armario_olt[$cli_login] = $row['armario_olt'];

        //$cli_caixa_herm[$row['caixa_herm']] = $row['caixa_herm'];
    }
  
    
    //Ordenar por A-Z
    asort($caixa_herm);

    // Capacidade real cadastrada de cada CTO
    $capacidade_caixa = array();
    $query_capacidade = mysqli_query($link, "SELECT nome, capacidade FROM mp_caixa");

    while ($row_cap = mysqli_fetch_array($query_capacidade)) {
        $nome_cap = trim(strtolower($row_cap['nome']));
        $capacidade_caixa[$nome_cap] = intval($row_cap['capacidade']);
    }

    // Inicializacao de variaveis
    $tot_on = 0;
    $tot_off = 0;
    $tot_olt = array();

    if($cfg_port_olt){
        $text = "SINAL DE ATIVAÇÃO";
    }else{
        $text = "PORTA";
    }
    //$tot_olt = 0;
    //$status = ""; $situacao = 0; $c_a_vencer = 0; $c_vencido = 0; $c_em_dia = 0; $cc = 0;



    foreach($caixa_herm as $caixa){

        if($caixa == '')
            continue;

        // Verifica se a CTO existe no addon de Caixas
        $nome_caixa_normalizado = trim(strtolower($caixa));
        $cto_cadastrada = array_key_exists(
            $nome_caixa_normalizado,
            $capacidade_caixa
        );

        $capacidade = $cto_cadastrada
            ? intval($capacidade_caixa[$nome_caixa_normalizado])
            : 0;

        if(!$cto_cadastrada){
            $aviso_cto = " <span style='color:#d93025;font-weight:bold;'>[ CTO NÃO CADASTRADA ]</span>";
        }elseif($capacidade <= 0){
            $aviso_cto = " <span style='color:#e37400;font-weight:bold;'>[ CAPACIDADE NÃO DEFINIDA ]</span>";
        }else{
            $aviso_cto = " <span style='color:#188038;font-weight:bold;'>[ $capacidade PORTAS ]</span>";
        }

        echo "
        <tr class='linha_titulo'>
        <td colspan='8'> - $caixa - $aviso_cto</td>
        </tr>
        <tr class='linha_sub_titulo'>
        <td>CTO</td>
        <td>STATUS</td>
        <td>LOGIN</td>
        <td>ENDEREÇO</td>
        <td>BAIRRO</td>
        <td>$text</td>
        <td>PON / OLT</td>
        <td>CONEXÃO</td>
        </tr>
        ";

        /*
         * Quando usamos PORTA SPLITTER, percorremos fisicamente
         * todas as portas cadastradas na capacidade da CTO.
         */
        if(!$cfg_port_olt && $capacidade > 0){

            for($porta = 1; $porta <= $capacidade; $porta++){

                $porta_ocupada = false;

                foreach($cli_nome as $registro => $nome){
                    $login = $cli_login_real[$registro];
                    $login_sql = mysqli_real_escape_string($link, $login);
                    if (!isset($inicio_conn[trim(strtolower($login))])) $inicio_conn[trim(strtolower($login))] = 'Sem registro';
                    if (!isset($final_conn[trim(strtolower($login))])) $final_conn[trim(strtolower($login))] = 'Sem registro';
                    $login_exibicao = htmlspecialchars($login, ENT_QUOTES, 'UTF-8');
                    if ($cli_tipo[$registro] === 'adicional') {
                        $login_exibicao .= " <span class='rel-cto-adicional'>Adicional</span>";
                    }

                    if($cli_caixa_herm[$registro] != $caixa)
                        continue;

                    $porta_cliente = intval(trim($cli_porta_olt[$registro]));

                    if($porta_cliente != $porta)
                        continue;

                    $porta_ocupada = true;
                    $lg_lower = trim(strtolower($login));
                    $porta_formatada = str_pad($porta, 2, '0', STR_PAD_LEFT);

                    if(isset($username_on[$lg_lower]) &&
                       strcasecmp($username_on[$lg_lower], $login) == 0){

                        if($filtro != 'off'){

                            $tot_on++;
                            $tot_olt[$cli_armario_olt[$registro]] = isset($tot_olt[$cli_armario_olt[$registro]]) ? $tot_olt[$cli_armario_olt[$registro]] + 1 : 1;

                            echo "
                            <tr class='linha_resultados'>
                            <td>$cli_caixa_herm[$registro]</td>
                            <td>ONLINE</td>
                            <td><a href='../../cliente_alt$format?uuid=$cli_uuid[$registro]' target='_blank'>$login_exibicao</a></td>
                            <td>$cli_endereco[$registro]</td>
                            <td>$cli_bairro[$registro]</td>
                            <td>$porta_formatada</td>
                            <td>$cli_armario_olt[$registro]</td>
                            <td>Inicio: $inicio_conn[$lg_lower]</td>
                            </tr>";
                        }

                    }else{

                        if($filtro == 'off' || $filtro == 'todos'){

                            $tot_off++;
                            $tot_olt[$cli_armario_olt[$registro]] = isset($tot_olt[$cli_armario_olt[$registro]]) ? $tot_olt[$cli_armario_olt[$registro]] + 1 : 1;

                            $query_cliente_off = mysqli_query(
                                $link,
                                "SELECT username, acctstarttime, acctstoptime
                                 FROM radacct
                                 WHERE username = '$login_sql' ORDER BY acctstarttime DESC LIMIT 1"
                            );

                            while ($row2 = mysqli_fetch_array($query_cliente_off)) {
                                $username_off = trim(strtolower($row2['username']));
                                $inicio_conn[$username_off] = date(
                                    'd/m/Y - H:i:s',
                                    strtotime($row2['acctstarttime'])
                                );
                                $final_conn[$username_off] = date(
                                    'd/m/Y - H:i:s',
                                    strtotime($row2['acctstoptime'])
                                );
                            }

                            echo "
                            <tr class='linha_resultados red bold'>
                            <td>$cli_caixa_herm[$registro]</td>
                            <td>OFFLINE</td>
                            <td><a href='../../cliente_alt$format?uuid=$cli_uuid[$registro]' target='_blank'>$login_exibicao</a></td>
                            <td>$cli_endereco[$registro]</td>
                            <td>$cli_bairro[$registro]</td>
                            <td>$porta_formatada</td>
                            <td>$cli_armario_olt[$registro]</td>
                            <td>
                            Inicio: $inicio_conn[$lg_lower]
                            Final: $final_conn[$lg_lower]
                            </td>
                            </tr>";
                        }
                    }
                }

                /*
                 * Porta sem nenhum cliente.
                 * Mostra somente no filtro TODOS.
                 */
                if(!$porta_ocupada && $filtro == 'todos'){

                    $porta_formatada = str_pad($porta, 2, '0', STR_PAD_LEFT);

                    echo "
                    <tr class='linha_resultados'
                        style='color:#188038;font-weight:bold;'>
                    <td>$caixa</td>
                    <td>LIVRE</td>
                    <td>LIVRE</td>
                    <td></td>
                    <td></td>
                    <td>$porta_formatada</td>
                    <td></td>
                    <td></td>
                    </tr>";
                }
            }

        }else{

            /*
             * Mantem comportamento original caso:
             * - esteja usando SINAL DE ATIVACAO
             * - ou a CTO nao tenha capacidade cadastrada em mp_caixa.
             */
            foreach($cli_nome as $registro => $nome){
                    $login = $cli_login_real[$registro];
                    $login_sql = mysqli_real_escape_string($link, $login);
                    if (!isset($inicio_conn[trim(strtolower($login))])) $inicio_conn[trim(strtolower($login))] = 'Sem registro';
                    if (!isset($final_conn[trim(strtolower($login))])) $final_conn[trim(strtolower($login))] = 'Sem registro';
                    $login_exibicao = htmlspecialchars($login, ENT_QUOTES, 'UTF-8');
                    if ($cli_tipo[$registro] === 'adicional') {
                        $login_exibicao .= " <span class='rel-cto-adicional'>Adicional</span>";
                    }

                $lg_lower = trim(strtolower($login));

                if($cli_caixa_herm[$registro] == $caixa){

                    if(isset($username_on[$lg_lower]) &&
                       strcasecmp($username_on[$lg_lower], $login) == 0){

                        if($filtro != 'off'){

                            $tot_on++;
                            $tot_olt[$cli_armario_olt[$registro]] = isset($tot_olt[$cli_armario_olt[$registro]]) ? $tot_olt[$cli_armario_olt[$registro]] + 1 : 1;

                            echo "
                            <tr class='linha_resultados'>
                            <td>$cli_caixa_herm[$registro]</td>
                            <td>ONLINE</td>
                            <td><a href='../../cliente_alt$format?uuid=$cli_uuid[$registro]' target='_blank'>$login_exibicao</a></td>
                            <td>$cli_endereco[$registro]</td>
                            <td>$cli_bairro[$registro]</td>
                            <td>$cli_porta_olt[$registro]</td>
                            <td>$cli_armario_olt[$registro]</td>
                            <td>Inicio: $inicio_conn[$lg_lower]</td>
                            </tr>";
                        }

                    }else{

                        if($filtro == 'off' || $filtro == 'todos'){

                            $tot_off++;
                            $tot_olt[$cli_armario_olt[$registro]] = isset($tot_olt[$cli_armario_olt[$registro]]) ? $tot_olt[$cli_armario_olt[$registro]] + 1 : 1;

                            $query_cliente_off = mysqli_query(
                                $link,
                                "SELECT username, acctstarttime, acctstoptime
                                 FROM radacct
                                 WHERE username = '$login_sql' ORDER BY acctstarttime DESC LIMIT 1"
                            );

                            while ($row2 = mysqli_fetch_array($query_cliente_off)) {
                                $username_off = trim(strtolower($row2['username']));
                                $inicio_conn[$username_off] = date(
                                    'd/m/Y - H:i:s',
                                    strtotime($row2['acctstarttime'])
                                );
                                $final_conn[$username_off] = date(
                                    'd/m/Y - H:i:s',
                                    strtotime($row2['acctstoptime'])
                                );
                            }

                            echo "
                            <tr class='linha_resultados red bold'>
                            <td>$cli_caixa_herm[$registro]</td>
                            <td>OFFLINE</td>
                            <td><a href='../../cliente_alt$format?uuid=$cli_uuid[$registro]' target='_blank'>$login_exibicao</a></td>
                            <td>$cli_endereco[$registro]</td>
                            <td>$cli_bairro[$registro]</td>
                            <td>$cli_porta_olt[$registro]</td>
                            <td>$cli_armario_olt[$registro]</td>
                            <td>
                            Inicio: $inicio_conn[$lg_lower]
                            Final: $final_conn[$lg_lower]
                            </td>
                            </tr>";
                        }
                    }
                }
            }
        }
    }

    $endtime = microtime(true);

    $tempo_sql = (($endtime - $starttime) * 1000);
    $tempo_sql = number_format($tempo_sql, 2);

    echo "<span class='no_print'><b>Tempo de Consulta no Banco de Dados:</b> $tempo_sql milissegundos<br>
    <b>Total de Registros:</b> " . ($tot_on + $tot_off) . "<span>";       


    //echo $c_vencido;

    /*$tot_resultados = mysql_num_rows($result);

    // Paginaçço
    $limite = mysql_num_rows($result_limit);
    $tot_paginas = $tot_resultados / $registros_por_pagina;
        
    // agora vamos criar os botçes "Anterior e prçximo"
    $anterior = $pc -1;
    $proximo = $pc +1;

    
    $url = "?data_inicial=$data_inicial&data_final=$data_final&busca=$busca&num_registros=$registros_por_pagina";

    echo "<span class='no_print'>";
        if ($pc>1) {
        echo " <a href='$url&pagina=$anterior'><- Anterior</a> ";
        }
        echo "|";
        if ($pc<$tot_paginas) {
        echo " <a href='$url&pagina=$proximo'>Próxima -></a>";
        }
    echo "</span>";*/

    mysqli_close($link);


?>

<script src="js/highcharts.js"></script>
    <script src="js/exporting.js"></script>

<figure class="highcharts-figure">
    <div id="container"></div>
    <p class="highcharts-description">
    <script>
        
        Highcharts.chart('container', {
        chart: {
            type: 'pie'
        },
        title: {
            text: 'Controle de OLT'
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        accessibility: {
            point: {
                /*valueSuffix: '%'*/
            }
        },
        tooltip: {
        headerFormat: '',
        pointFormat: '<b> {point.name}: </b>{point.y}'
        },
        credits: {
            enabled: false
        },
        series: [{
            name: 'Totais',
            colorByPoint: true,
            data: [
                <?php 
                    foreach($tot_olt as $name_olt => $tot){
                        echo "
                        { 
                            name: '$name_olt',
                            y: $tot
                        },
                        ";
                    }

                ?>
            ]
        }]
    });
   
    </script>

    </script>
    </p>
    </figure>


    <figure class="highcharts-figure">
    <div id="container2"></div>
    <p class="highcharts-description">
    <script>
        
        Highcharts.chart('container2', {
        chart: {
            type: 'pie'
        },
        title: {
            text: 'Status dos Clientes'
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        accessibility: {
            point: {
                /*valueSuffix: '%'*/
            }
        },
        tooltip: {
        headerFormat: '',
        pointFormat: '<b> {point.name}: </b>{point.y}'
        },
        credits: {
            enabled: false
        },
        series: [{
            name: 'Totais',
            colorByPoint: true,
            data: [
                <?php 
                    echo "
                    { 
                        name: 'CLIENTES ONLINE',
                        y: $tot_on,
                        color: Highcharts.getOptions().colors[2]
                    },
                    { 
                        name: 'CLIENTES OFFLINE',
                        y: $tot_off,
                        color: Highcharts.getOptions().colors[8]
                    }
                    ";
                ?>
            ]
        }]
    });
   
    </script>
    
        </p>
    </figure>


</table>


<?php include('../../baixo.php'); ?>

        <script src="../../menu.js.php"></script>

    </body>
</html>