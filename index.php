<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once './UserService.php';
require_once './User.php';

$mysqli = new mysqli('localhost', 'root', '1010', 'php_com_mysql');
if (mysqli_connect_errno()):
	echo 'Falha ao conectar no MySQL: (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error;
	exit;
endif;

$us = new UserService($mysqli);

$id = 2;

$us->read($id);
$us->getUser()->name = 'Teste de Atualização';
if ($us->update()):
	echo "Registro atualizado com êxito<br />";
else:
	echo "Houve problemas ao atualizar registro</br>";
endif;

foreach ($us->getAll(['where' => "id >= 1"]) as $value) :
	echo "Usuário retornado pelo banco: <br />";
	echo "ID&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;: {$value['id']}<br />";
	echo "Nome&thinsp;: {$value['name']}<br />";
	echo "Email&thinsp;: {$value['email']}<hr />";
endforeach;
// if ($us->read($id)) :
// 	echo "Usuário retornado pelo banco: <br />";
// 	echo "ID&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;: {$us->getUser()->id}<br />";
// 	echo "Nome&thinsp;: {$us->getUser()->name}<br />";
// 	echo "Email&thinsp;: {$us->getUser()->email}<hr />";
// else:
// 	echo "Não achou registros no banco com o id {$id}";
// endif;
