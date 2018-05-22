### Объектно-ориентированная обертка для работы с Oci8 php-extention.

```
$_conn = new Oci8Connection(
    getenv($this->connectionLogin),
    getenv($this->connectionPass),
    getenv($this->connectionString)
);

$_stm = $_conn->prepare($sql);

$clob = null;
$_stm->bindParam(self::CLOB_COLUMN_NAME, $clob, \PDO::PARAM_LOB);
$_stm->execute();
$_stm->freeStatement();

$response = json_decode($clob->load(), true);
$clob->free();
```
