<?php
// Sai da conta e volta para o início.
require_once __DIR__ . '/../includes/sessao.php';

sair_da_conta();
header('Location: ../index.html');
exit;
