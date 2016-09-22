<?php

require_once './DBService.php';

abstract class AbstractDBService implements DBService {

    /** @var mysqli */
    private $db;
    /** Nome da tabela no banco de dados */
    private $tableName;
    /** Objeto de referencia utilizado para obter o estado atual
    *   o modelo utilizado pelo serviço */
    protected $reference;

    function __construct(mysqli $db, $tableName, $reference) {
        $this->db = $db;
        $this->tableName = $tableName;
        $this->reference = $reference;
    }

    /**
     * Obtém a lista de todos os
     * @param array $functions Opcional - array associativo com funções do SQL:
     * Valores aceitos são:
     * order => coluna ordem | ex: id DESC
     * limit => offset, limit | ex: limit => 0,5 - limita 5 resultados a partir no primeiro registro
     * @return array
     */
    public function getAll(array $functions = array()) {
        $sql = "SELECT * FROM {$this->tableName}";
        if (!empty($functions)):

            if (array_key_exists('where', $functions)):
              $where = " WHERE {$functions['where']}";
              $sql .= $where;
            endif;

            foreach ($functions as $key => $value) :
                switch ($key):
                    case 'order':
                        $order = " ORDER BY {$value}";
                        $sql .= $order;
                        break;
                    case 'limit':
                        $limit = " LIMIT {$value}";
                        $sql .= $limit;
                        break;
                endswitch;
            endforeach;

        endif;
        $query = $this->db->query($sql);
        return $query->fetch_all(MYSQLI_ASSOC);
    }

    /**
    * Método utilitário que obtém as variáveis do modelo de referência,
    * tanto seus nomes quanto valores, e retorna um vetor com os mesmos.
    * @return array Vetor contendo um vetor com os nomes dos atributos, e outro com os respectivos valores
    */
    private function splitReference(bool $removeID = false) {
        $ret = array();
        $teste = get_object_vars($this->reference);
        foreach (get_object_vars($this->reference) as $key => $value) {
            if ($removeID && $key == 'id'):
                continue;
            endif;
            $ret['key'][] = $key;
            $ret['value'][] = $value;
        }
        return $ret;
    }

    /**
    * Insere o objeto de referência no banco de dados
    */
    public function create() {
        $stmt = $this->db->stmt_init();
        $keysAndValues = $this->splitReference(true);
        $keys = '(' . implode(', ', $keysAndValues['key']) . ')';
        $values = $keysAndValues['value'];
        $dummy = '';
        foreach ($keysAndValues['key'] as $k) {
            $dummy .= empty($dummy) ? "?" : ", ?";
        }
        $dummy = "({$dummy})";
        $types = "";
        foreach ($keysAndValues['value'] as $value) {
            if (is_int($value)):
                $types .= 'i';
            elseif (is_double($value)):
                $types .= 'd';
            elseif (is_string($value)):
                $types .= 's';
            else:
                $types .= 'b';
            endif;
        }
        $stmt->prepare("INSERT INTO {$this->tableName} {$keys} VALUES {$dummy}");
        $tmp = array();
        foreach($values as $key => $value) $tmp[$key] = &$values[$key];
        array_unshift($tmp, $types);
        call_user_func_array(array($stmt, 'bind_param'), array_merge(array($types), $values));
        if (!$stmt->execute()):
            return false;
        endif;
        return $stmt->insert_id;
    }

    /**
    * Carrega do banco de dados os dados e insere-os no objeto de referência.
    */
    public function read($idToRead) {
        $stmt = $this->db->stmt_init();
        $stmt->prepare("SELECT * FROM {$this->tableName} WHERE id=?");
        $stmt->bind_param('i', $idToRead);
        $keysAndValues = $this->splitReference(false);
        $keys = $keysAndValues['key'];
        $values = $keysAndValues['value'];
        for ($i=0; $i < count($keys) ; $i++) {
            $tmp[$keys[$i]] = &$values[$i];
        }
        $stmt->execute();
        // foreach($keys as $key) $tmp[] = $key;
        call_user_func_array(array($stmt, 'bind_result'), $tmp);
        if ($stmt->fetch()):
            extract($tmp);
            foreach ($keys as $key) {
                $this->reference->$key = $$key;
            }
            return true;
        endif;
        return false;
    }

    /**
    * Atualiza o registro baseado no id fornecido. Se não informado, utiliza o id
    * do objeto de referência.
    */
    public function update($id = null) {
        if (is_null($id)):
          $id = $this->reference->id;
        endif;
        $stmt = $this->db->stmt_init();
        $keysAndValues = $this->splitReference(true);
        $keys = '';
        for ($i = 0; $i < count($keysAndValues['key']); $i++) {
            if (!empty($keys)):
                $keys .= ', ';
            endif;
            $keys .=  $keysAndValues['key'][$i].'=?';
        }
        $values = $keysAndValues['value'];
        $types = "";
        foreach ($keysAndValues['value'] as $value) {
            if (is_int($value)):
                $types .= 'i';
            elseif (is_double($value)):
                $types .= 'd';
            elseif (is_string($value)):
                $types .= 's';
            else:
                $types .= 'b';
            endif;
        }
        $types .= 'i';
        $stmt->prepare("UPDATE {$this->tableName} SET {$keys} WHERE id=?");
        $tmp = array();
        foreach($values as $key => $value) $tmp[$key] = &$values[$key];
        $tmp[] = &$id;
        array_unshift($tmp, $types);
        call_user_func_array(array($stmt, 'bind_param'), $tmp);
        if (!$stmt->execute()):
            return false;
        endif;
        return true;
    }

    /**
    * Remove o registro baseado no id fornecido. Se não informado, utiliza o id
    * do objeto de referência.
    */
    public function delete($id = null) {
        if (is_null($id)):
          $id = $this->reference->id;
        endif;
        $stmt = $this->db->stmt_init();
        $stmt->prepare("DELETE FROM {$this->tableName} WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    /**
    * Limpa o objeto de referencia
    */
    public abstract function clear();

}
