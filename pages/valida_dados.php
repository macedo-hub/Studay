<?php

function valida_dados($dados)
{
    // Garante que o valor recebido seja um texto
    if (!is_string($dados)) {
        return "";
    }
    
    // Remove espaços desnecessários
    $dados = trim($dados); 
    // Remove barras invertidas
    $dados = stripslashes($dados); 
     // Protege contra HTML e JavaScript
    $dados = htmlspecialchars($dados);
    return $dados;
}