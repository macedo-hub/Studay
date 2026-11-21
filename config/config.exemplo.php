<?php
/*
 * CONFIGURAÇÃO DO STUDAY (arquivo de exemplo)
 * -------------------------------------------
 * Este arquivo é só um MODELO e pode ficar no GitHub.
 *
 * COMO USAR:
 *  1) Copie este arquivo e salve a cópia com o nome  config.php  (na mesma pasta).
 *  2) Preencha os dados do banco nas linhas abaixo.
 *  3) NUNCA envie o config.php para o GitHub: ele guarda a senha do banco.
 *     (o .gitignore do projeto já bloqueia esse arquivo)
 *
 * COMO PREENCHER NO INFINITYFREE:
 *  1) No painel, entre em "MySQL Databases" e crie um banco.
 *  2) Copie os dados que aparecem lá e cole nas linhas abaixo.
 *  3) O host NÃO é localhost. Ele é parecido com: sqlXXX.infinityfree.com
 *
 * COMO PREENCHER NO XAMPP (teste no computador):
 *  host = localhost | usuario = root | senha = (vazia) | banco = studay
 */

// Se o arquivo de teste já definiu o banco, não sobrescreve.
if (!defined('BANCO_DSN')) {
    define('BANCO_HOST',    'localhost');            // host do MySQL
    define('BANCO_NOME',    'studay');               // nome do banco
    define('BANCO_USUARIO', 'root');                 // usuário do MySQL
    define('BANCO_SENHA',   '');                     // senha do MySQL

    // Monta o endereço de conexão que o site usa (includes/conexao.php)
    define('BANCO_DSN', 'mysql:host=' . BANCO_HOST . ';dbname=' . BANCO_NOME . ';charset=utf8mb4');
}

// E-mails que viram ADMINISTRADORES quando fazem login (podem cadastrar os sites da turma).
// Use letras minúsculas.
const EMAILS_ADMIN = [
    'admin@exemplo.com',
];

// Fuso horário da Paraíba (sem horário de verão).
date_default_timezone_set('America/Fortaleza');
