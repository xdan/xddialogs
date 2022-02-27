<?php

/**
 * @class     db
 * @author        leroy <skoder@ya.ru>
 * @site        http://xdan.ru
 * @version    1.7
 */
class db
{
    public $sql = '';
    public $inq = '';
    public $sqlcount = 0;
    public $pfx = 'pfx_';
    private $connid = 0;

    /**
     * При инициализации пдключаетсяк MySQL и подключается к нужной БД
     *
     * @param $server название хоста
     * @param $user логин
     * @param $password пароль
     * @param $dbname название бд
     * @param $pfx префикс таблиц, по умолчанию pfx_, т.е. в любом запросе к названию таблиц
     * будет добавлятся pfx_
     * @param $charset кодировка
     */
    function __construct($server, $user, $password, $dbname, $pfx = 'pfx_', $charset = "utf8")
    {

        $this->connid = mysqli_connect($server, $user, $password, $dbname);

//		if( $this->connid = mysqli_connect($server, $user, $password) ){
//			$this->pfx = $pfx;
//			if( mysqli_select_db($dbname, $this->connid) ){
//				$this->query("SET NAMES '".$charset."'") && $this->query("SET CHARSET '".$charset."'") && $this->query("SET CHARACTER SET '".$charset."'") && $this->query("SET SESSION collation_connection = '{$charset}_general_ci'");
//			}
//		}else{
//			$this->error();
//		}
    }

    /**
     * Выполняет SQL запрос, заменяя #_ на заданный в настройках префикс
     */
    function query($sql)
    {
        $this->sql = str_replace('#_', $this->pfx, $sql);
        $this->sqlcount++;

        if (($this->inq = mysqli_query($this->connid, $this->sql)) || $this->error()) {
            return $this->inq;
        }

        ($this->inq = mysqli_query($this->connid, $this->sql)) || $this->error();
        return $this->inq;
    }

    /**
     * Возвращает последний выполненный запрос
     */
    function last()
    {
        return $this->sql;
    }

    /**
     * Возвращает одну запись из БД соответствующую запросу
     *
     * @param $sql SQL запрос
     * @param $field если не задано то возвращается вся запись, иначе возвращается значение поля $field
     * @example $db->getRow('select * from #_book where id=12'); // вернет array('id'=>12,'name'=>'Tolkien' ...)
     * @example $db->getRow('select name from #_book where id=12',name); // вернет 'Tolkien'
     */
    function getRow($sql, $field = '')
    {
        $item = mysqli_fetch_array($this->query($sql));
        return ($field == '') ? $item : $item[$field];
    }

    /**
     * Перебирает все записи из запроса и передает их в callback функцию
     *
     * @param $sql SQL запрос, с префиксом #_
     * @param $callback функция, вызываемая к каждой записи запроса,
     * в параметрах 1) массив, содержащий данные полученной записи 2)указатель на db
     * @return Возвращает db
     */
    public function each($sql, $callback)
    {
        $this->query($sql);
        if (is_callable($callback))
            while ($item = mysqli_fetch_array($this->inq))
                call_user_func($callback, $item, $this);
        return $this;
    }

    /**
     * Изымает лишь запись по ее идентификатору, по умолчанию id
     *
     * @param $sTable название таблицы без префикса
     * @param $id значение идентификатора
     * @param $fieldname поле по которому производится сравнение, по умолчанию id
     * @param $field значение поля, которое необходимо вернуть. Если не указано то возвращается вся запись
     * @return ассоциативный массив либо конкретное значение пи заданном $field
     * @example $db->getRowById('book',12); // вернет запись о книге с id=12
     * @example $db->getRowById('book','Tolkien','name'); // вернет запись о книге с названием которое содержит Tolkien
     * @example $db->getRowById('book',12,'id','name'); // вернет название книги с id=12
     */
    public function getRowById($sTable, $id, $fieldname = 'id', $field = '')
    {
        return $this->getRow("SELECT * FROM `#_" . $sTable . "` WHERE `$fieldname` ='" . $this->escape($id) . "'", $field);
    }

    /**
     * Проверяет существует ли запись в таблице с таким идентификатором, если существует то возвращает идентификатор
     * иначе возвращает false
     *
     * @param $sTable название таблицы без префикса
     * @param $id значение идентификатора
     * @param $fieldname поле по которому производится сравнение, по умолчанию id
     * @param $allf дополнительные параметры запроса, обычное sql сравнение
     * @param $field поле которое необходимо вернуть в случае удачи, по умолчанию равно $fieldname
     * @return При удаче возвращает значение поля $field, иначе false
     * @example if( $db->exists('book',12) ) echo 'Книга существует';
     * @example if( $db->exists('book','Tolkien','name')!==false ) echo 'Книга содерщащая Tolkien существует';
     * @example if( $db->exists('book','Tolkien','name','active="yes" and public="12.09.2008"') )
     *    echo 'Книга содерщащая Tolkien опубликованная 12.09.2008 существует';
     * @example if( ($name=$db->exists('book','%Tolkien%','name','','izdatel'))!==false )
     *    echo 'Книга содерщащая Tolkien существует ее издал '.$name;
     */
    public function exists($sTable, $id, $fieldname = 'id', $allf = '', $field = '')
    {
        if (!$field)
            $field = $fieldname;
        $item = $this->getRow('select ' . $field . ' from ' . $this->pfx . $sTable . ' where `' . $fieldname . '`=\'' . $this->escape($id) . '\' ' . $allf);
        return isset($item[$field]) ? $item[$field] : false;
    }

    /**
     * @deprecated 1.7 Используйте getRows
     */
    function loadResults($sql, $field = '')
    {
        return $this->getRows($sql, $field);
    }

    /**
     * Выдает массив всех записей из запроса
     *
     * @param $sql SQL запрос, с префиксом #_
     * @param $field если указано это поле, то результирующий массив будет состоять только из значений этого поля
     * @return Array
     */
    function getRows($sql, $field = '')
    {
        $inq = $this->query($sql);
        $items = array();
        while ($item = mysqli_fetch_array($inq)) $items[] = ($field == '') ? $item : $item[$field];
        return $items;
    }

    /**
     * Экранирует значение
     */
    function escape($sql)
    {

        return $sql;
//        return mysqli_real_escape_string($sql,$this->connid);
    }

    /**
     * Вставка данных в таблицу
     *
     * @param $sTable название таблицы без префикса
     * @param $values либо строка вида id=12,name="Tolkien",
     * либо ассоциативный массив вида array('id'=>12,'name'=>'Tolkien')
     * в случае ассоциативного массива экранировать данные не требуется
     * @example $db->insert('book','id=12,name="'.$db->escape('Tolkien').'"');
     * @example $db->insert('book',array('id'=>12,'name'='Tolkien'));
     */
    function insert($sTable, $values)
    {
        $ret = $this->_arrayKeysToSet($values);
        return $this->query('insert into #_' . $sTable . ' set ' . $ret);
        return false;
    }

    /**
     * Возвращает значение перичного ключа последней вставленной записи
     */
    function insertid()
    {
        return mysqli_insert_id($this->connid);
    }

    /**
     * Обновление данных в таблице
     *
     * @param $sTable название таблицы без префикса
     * @param $values либо строка вида id=12,name="Tolkien",
     * либо ассоциативный массив вида array('id'=>12,'name'=>'Tolkien')
     * в случае ассоциативного массива экранировать данные не требуется
     * @param $sWhere условия соответсвия
     * @example $db->update('book','id=12,name="'.$db->escape('Tolkien').'"','id=5');
     * @example $db->update('book',array('id'=>12,'name'='Tolkien'),'where name like %Tolkien%');
     */
    public function update($sTable, $values, $sWhere = 1)
    {
        $ret = $this->_arrayKeysToSet($values);
        return $this->query('update ' . $this->pfx . $sTable . ' set ' . $ret . ' where ' . $sWhere);
    }

    /**
     * Удаление данных соответствующих словию
     */
    public function delete($sTable, $sWhere)
    {
        return $this->query('delete from ' . $this->pfx . $sTable . ' where ' . $sWhere);
    }


    private function _arrayKeysToSet($values)
    {
        $ret = '';
        if (is_array($values)) {
            foreach ($values as $key => $value) {
                if (!empty($ret)) $ret .= ',';
                $ret .= "`$key`='" . $this->escape($value) . "'";
            }
        } else $ret = $values;
        return $ret;
    }

    private function error(): int
    {
        $langcharset = 'utf-8';
        echo "<HTML>\n";
        echo "<HEAD>\n";
        echo "<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=" . $langcharset . "\">\n";
        echo "<TITLE>MySQL Debugging</TITLE>\n";
        echo "</HEAD>\n";
        echo "<div style=\"border:1px dotted #000000; font-size:11px; font-family:tahoma,verdana,arial; background-color:#f3f3f3; color:#A73C3C; margin:5px; padding:5px;\">";
        echo "<b><font style=\"color:#666666;\">MySQL Debugging</font></b><br /><br />";
        echo "<li><b>SQL.q :</b> <font style=\"color:#666666;\">" . $this->sql . "</font></li>";
        echo "<li><b>MySQL.e :</b> <font style=\"color:#666666;\">" . mysqli_error() . "</font></li>";
        echo "<li><b>MySQL.e.№ :</b> <font style=\"color:#666666;\">" . mysqli_errno() . "</font></li>";
        echo "<li><b>PHP.v :</b> <font style=\"color:#666666;\">" . phpversion() . "\n</font></li>";
        echo "<li><b>Data :</b> <font style=\"color:#666666;\">" . date("d.m.Y H:i") . "\n</font></li>";
        echo "<li><b>Script :</b> <font style=\"color:#666666;\">" . getenv("REQUEST_URI") . "</font></li>";
        echo "<li><b>Refer :</b> <font style=\"color:#666666;\">" . getenv("HTTP_REFERER") . "</li></div>";
        echo "</BODY>\n";
        echo "</HTML>";
        return 1;
    }
}
