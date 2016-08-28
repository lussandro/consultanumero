#!/usr/bin/php -q
<?php
                # Consulta Operadora usando a API da Fluxo-TI  #
                # SNEP 3.0                                     #
                # Lussandro da Cunha Ilha                      #
                # Palhoça 27/08/2016                           #


        require('phpagi.php');
	$agi = new AGI();
	$numero = $argv[1];
        $token = 'INSIRA SEU TOKE AQUI';
        $usuario_mysql = 'snep';
        $senha_mysql = 'sneppass';
        $banco_mysql = 'portabilidade';
        $conta = 99 ;

        # Validação do numero:
        if( !( isset($numero) && is_numeric($numero) && $numero>1100000000 && $numero<99999999999 ) ){
	$rn1 = '99999';
	$agi->verbose("*** NUMERO INVALIDO *** ");
	$agi->set_variable("RN1", $rn1);

                die();
        }


        $id = mysql_connect('localhost',$usuario_mysql, $senha_mysql);
        $con=mysql_select_db($banco_mysql ,$id);
        $sql = "SELECT rn1, counter FROM portabilidade  WHERE numero = '$numero' LIMIT 1";
        $result = mysql_query($sql);
        $row = mysql_fetch_array($result);
        if(is_array($row)){
            if($row['counter'] < $conta){
               $rn1 = $row['rn1'];
               $sql = "UPDATE portabilidade SET counter = counter + 1 WHERE numero = '$numero'";
               $result = mysql_query($sql);
	       $cost = '(BANCO DE DADOS LOCAL)';
        } else
            {

		$ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "http://consulta-operadora.fluxoti.com/v1/consult/" . $numero);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Auth-Token: $token"));
                $response = curl_exec($ch);
                $response = json_decode($response, true);
                $rn1 = $response['data']['rn1'] ;
                $sql = "UPDATE portabilidade SET numero = '$numero', rn1 = '$rn1', counter = 1 WHERE numero = '$numero'";
                $result = mysql_query($sql);
                $cost = 'SERVIDOR - ATUALIZADO O BANCO LOCAL';
		curl_close($ch);

          }

}
else
{

	        $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "http://consulta-operadora.fluxoti.com/v1/consult/" . $numero);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Auth-Token: $token"));
                $response = curl_exec($ch);
                $response = json_decode($response, true);
                $rn1 = $response['data']['rn1'] ;
                $sql = "INSERT INTO portabilidade (numero, rn1, counter) VALUES ('$numero', '$rn1', 1)";
                $result = mysql_query($sql);
                $cost = 'SERVIDOR - ATUALIZADO O BANCO LOCAL';
                curl_close($ch);

}

$agi->verbose("Consulta via: " . $cost);
$agi->set_variable("RN1", $rn1);


mysql_close();
exit();
?>

