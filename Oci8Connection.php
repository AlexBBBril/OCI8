<?php

declare(strict_types=1);

namespace Oci8;


use Oci8\Exception\Oci8Exception;

class Oci8Connection
{
    /**
     * @var resource
     */
    private $connection;

    /**
     * @var int
     */
    protected $executeMode = OCI_COMMIT_ON_SUCCESS;

    /**
     * Создает соединение с базой данных Oracle с помощью расширения oci8.
     *
     * @param string $username
     * @param string $password
     * @param string $db          Строка соединения с базой данных
     * @param string $charset
     * @param int    $sessionMode Режим выполнения выражений для oci_execute()
     * @param bool   $persistent  Вариант соединения с базой данных Oracle
     *
     * @throws Oci8Exception
     */
    public function __construct(
        $username,
        $password,
        $db,
        $charset = '',
        $sessionMode = OCI_NO_AUTO_COMMIT,
        $persistent = false
    ) {
        $this->connection = $persistent
            ? @oci_pconnect($username, $password, $db, $charset, $sessionMode)
            : @oci_connect($username, $password, $db, $charset, $sessionMode);

        if (!$this->connection) {
            throw Oci8Exception::fromErrorInfo(oci_error());
        }
    }

    /**
     * Выполняет инструкцию SQL, возвращая результирующий набор как объект Statement.
     *
     * @param string $sql
     *
     * @return Oci8Statement
     *
     * @throws Oci8Exception
     */
    public function query(string $sql): Oci8Statement
    {
        $stmt = $this->prepare($sql);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Подготавливает инструкцию для выполнения и возвращает объект Statement,
     * который хранит дескриптор подготовленного выражения.
     *
     * @param string $prepareString
     *
     * @return Oci8Statement
     */
    public function prepare(string $prepareString): Oci8Statement
    {
        return new Oci8Statement($this->connection, $prepareString, $this);
    }

    /**
     * @param string $name
     *
     * @return bool|int
     *
     * @throws Oci8Exception
     */
    public function lastInsertId(string $name)
    {
        $sql    = 'SELECT '.$name.'.CURRVAL FROM DUAL';
        $stmt   = $this->query($sql);
        $result = $stmt->fetchColumn();

        if ($result === false) {
            throw new Oci8Exception('lastInsertId failed: Запрос был выполнен без результата.');
        }

        return (int)$result;
    }

    /**
     * Получает код ошибки, связанный с последней операцией над дескриптором оператора.
     *
     * @return string|null Kод ошибки если есть.
     */
    public function errorCode(): ?string
    {
        $error = oci_error($this->connection);

        return $error !== false ? $error['code'] : null;
    }

    /**
     * Получает расширенную информацию об ошибке, связанную с последней операцией над дескриптором оператора.
     *
     * @return array Массив информации об ошибках
     */
    public function errorInfo(): array
    {
        return oci_error($this->connection);
    }

    /**
     * Вернет текущий режим выполнения запросов
     *
     * @return int
     */
    public function getExecuteMode(): int
    {
        return $this->executeMode;
    }

    /**
     * Освобождает ресурсы, занимаемые курсором или SQL-выражением
     */
    public function clearStatement(): void
    {
        oci_free_statement($this->connection);
    }
}
