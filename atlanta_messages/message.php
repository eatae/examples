<?php


class message
{


    const DB_STATUS_NEW = 'new';
    const DB_STATUS_EXIST = 'exist';
    const DB_STATUS_DELETE = 'del';

    const NEW_MESSAGES = 'new';
    const SENT_MESSAGES = 'sent';
    const RECEIVED_MESSAGES = 'received';
    const DELETED_MESSAGES = 'deleted';

    const TABLE_NAME = 'atlanta_messages';
    const NECESSARY_FIELDS = ['sender', 'receiver', 'status', 'datetime', 'title', 'message'];


    public $id;
    public $sender;
    public $receiver;
    public $datetime;
    public $status;
    public $title;
    public $message;


    /**
     * @var PDO
     */
    protected static $dbh;
    public static $instance;
    public static $configuration = [];

    public static function register(array $configuration = null)
    {
        self::$dbh = sql::setSqlDriver('sql');

        self::$configuration = ($configuration === null) ? self::$configuration : $configuration;

        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }



    /* CRUD
    --------*/

    /**
     * Create
     *
     * @throws Exception
     */
    protected function create()
    {
        $columns = [];
        $names =   [];
        $values =  [];

        $this->checkFields();

        foreach ($this as $key => $val) {
            if ($key == 'id') continue;
            $columns[] = $key;
            $names[] = ':' . $key;
            $values[':' . $key] = $val;
        }
        $sql = 'INSERT INTO '. self::TABLE_NAME .
                    ' ('. implode(', ', $columns) .') ' .
                    'VALUES('. implode(', ', $names) .')';
        $sth = self::$dbh->prepare($sql);
        if ( !$sth->execute($values) ) {
            throw new Exception('FILE: ' .__FILE__.'<br>'.'LINE: '. __LINE__ .'<br>'. 'MESSAGE: Fail Create'.'<br>');
        }
        $this->id = self::$dbh->lastInsertId();
    }


    /**
     * Update
     *
     * @throws Exception
     */
    public function update()
    {
        $names = [];
        $values = [];
        if (empty($this->id)) {
            throw new Exception('FILE: ' .__FILE__.'<br>'.'LINE: '. __LINE__ .'<br>'. 'MESSAGE: Id is empty'.'<br>');
        }
        foreach ($this as $name => $val) {
            if (empty($val)) continue;
            $names[] = $name .'=:'.$name;
            $values[':'.$name] = $val;
        }
        $sql = 'UPDATE '. self::TABLE_NAME .
                    ' SET ' . implode(', ', $names) .
                    ' WHERE id='. $this->id;
        $sth = self::$dbh->prepare($sql);
        if ( !$sth->execute($values) ) {
            throw new Exception('FILE: ' .__FILE__.'<br>'.'LINE: '. __LINE__ .'<br>'. 'MESSAGE: Fail Update'.'<br>');
        }
    }


    /**
     * Delete
     *
     * @throws Exception
     */
    public function delete()
    {
        $sql = 'DELETE FROM '.self::TABLE_NAME .' WHERE id = :id';
        $sth = self::$dbh->prepare($sql);
        if ( !$sth->execute([':id'=>$this->id]) ) {
            throw new Exception('FILE: ' .__FILE__.'<br>'.'LINE: '. __LINE__ .'<br>'. 'MESSAGE: Fail Delete'.'<br>');
        }
    }


    /**
     * Save
     */
    public function save()
    {
        if (empty($this->id)) {
            $this->create();
        } else {
            $this->update();
        }
    }


    /**
     * Find by ID
     *
     * @param $id
     * @return message
     * @throws Exception
     */
    public static function findById($id)
    {
        $sql = 'SELECT * FROM '.self::TABLE_NAME. ' WHERE id =:id';
        $sth = self::$dbh->prepare($sql);
        if ( !$sth->execute([':id'=>$id]) ) {
            throw new Exception('FILE: ' .__FILE__.'<br>'.'LINE: '. __LINE__ .'<br>'. 'MESSAGE: Fail Find id'.'<br>');
        }

        return $sth->fetchObject(self::class);
    }


    /**
     * Find all
     *
     * @param $user_sess
     * @return array
     * @throws Exception
     */
    public static function findAll($user_sess)
    {
        $sql = "SELECT '".self::NEW_MESSAGES."' AS kind, id, sender, receiver, datetime, status, title, message
                    FROM atlanta_messages
                    WHERE receiver = :sess AND status = 'new'
                UNION
                
                SELECT '".self::RECEIVED_MESSAGES."' AS kind, id, sender, receiver, datetime, status, title, message
                    FROM atlanta_messages
                    WHERE receiver = :sess AND status IN ('exist', 'new')
                UNION
                
                SELECT '".self::SENT_MESSAGES."' AS kind, id, sender, receiver, datetime, status, title, message
                    FROM atlanta_messages
                    WHERE sender = :sess
                UNION
                
                  SELECT '".self::DELETED_MESSAGES."' AS kind, id, sender, receiver, datetime, status, title, message
                    FROM atlanta_messages
                    WHERE receiver = :sess AND status = 'del'
                    ORDER BY datetime DESC";

        $sth = $sth = self::$dbh->prepare($sql);
        if ( !$sth->execute([':sess'=>$user_sess]) ) {
            throw new Exception('FILE: ' .__FILE__.'<br>'.'LINE: '. __LINE__ .'<br>'. 'MESSAGE: Fail Find all'.'<br>');
        }

        return $sth->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_CLASS, self::class);
    }


    /**
     * Check fields
     *
     * @throws Exception
     */
    public function checkFields()
    {
        foreach (self::NECESSARY_FIELDS as $name) {
            if (empty($this->$name)) {
                throw new Exception('FILE: ' .__FILE__.'<br>'.'LINE: '. __LINE__ .'<br>'. 'MESSAGE: Fail Check necessary fields: '.$name.'<br>');
            }
        }
    }

    /**
     * Set attributes
     *
     * @param array $data
     */
    public function setAttributes(array $data)
    {
        foreach ($data as $name => $val) {
            if ( property_exists($this, $name) && !empty($val)) {
                $this->$name = $val;
            }
        }
    }


    /**
     * Create message
     *
     * @param $sender
     * @param $receiver
     * @param $title
     * @param $message
     */
    public function createMessage($sender, $receiver, $title, $message)
    {
        $status = self::DB_STATUS_NEW;
        $datetime = $this->getDatetime();
        $this->setAttributes( compact('sender', 'receiver', 'datetime', 'status', 'title') );

        $message = $this->stringCaption() . $message;
        $this->setAttributes( compact('message') );
        $this->save();
    }



//    public function createResponseMessage($text){}
//    public function getCntNewMessage() {}


    public function setReadStatus()
    {
        $this->status = self::DB_STATUS_EXIST;
        $this->save();
    }


    public function setDeleteStatus()
    {
        $this->status = self::DB_STATUS_DELETE;
        $this->save();
    }




    public function getReceiverData()
    {
        if (empty($this->receiver)) {
            throw new Exception('FILE: ' .__FILE__.'<br>'.'LINE: '. __LINE__ .'<br>'. 'MESSAGE: Fail not receiver'.'<br>');
        }
        $receiver = UsersService::getUserInfoBySession($this->receiver);

        return _un($receiver['depend']);
    }

    public function getReceiverName()
    {
        return $this->getReceiverData()['cabinet']['name'];
    }



    public function getSenderData()
    {
        if (empty($this->sender)) {
            throw new Exception('FILE: ' .__FILE__.'<br>'.'LINE: '. __LINE__ .'<br>'. 'MESSAGE: Fail not sender'.'<br>');
        }
        $sender = UsersService::getUserInfoBySession($this->sender);

        return _un($sender['depend']);
    }


    public function getSenderName()
    {
        return $this->getSenderData()['cabinet']['name'];
    }



    protected function getDatetime()
    {
        return date("Y-m-d H:i:s");
    }



    protected function stringCaption()
    {
        if (empty($this->sender)) {
            throw new Exception('FILE: ' .__FILE__.'<br>'.'LINE: '. __LINE__ .'<br>'. 'MESSAGE: Fail caption, not sender'.'<br>');
        }
        if (empty($this->datetime)) {
            throw new Exception('FILE: ' .__FILE__.'<br>'.'LINE: '. __LINE__ .'<br>'. 'MESSAGE: Fail caption, not datetime'.'<br>');
        }
        $caption = '-----------------------------' . '<br>';
        $caption .= $this->getSenderName() . '<br>';
        $caption .= $this->datetime . '<br>' . '<br>';

        return $caption;
    }




    public function getFirstLineMessage()
    {
        if (empty($this->message) || empty($this->id)) {
            throw new Exception('FILE: ' .__FILE__.'<br>'.'LINE: '. __LINE__ .'<br>'. 'MESSAGE: Fail first line message, not message'.'<br>');
        }
        /* cut string caption */
        $clearMsg = substr($this->message, iconv_strlen($this->stringCaption()), 100);
        /* cut cut to the last space */
        $firstLine = substr($clearMsg, 0, strripos($clearMsg, ' '));

        return $firstLine;
    }


}
