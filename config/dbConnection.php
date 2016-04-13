<?php
class dbConnection
{
    protected $db;
    public function connect()
    {
        $dataB = mysqli_connect("localhost", "root", "ense400", "StudentInfo") or die("could not connect to the database: Error " . mysqli_error($db));
        $this->setDb($dataB);
    }
    private function setDb($dataB)
    {
        $this->db = $dataB;
    }
    public function getDb()
    {
        return $this->db;
    }
    public function test()
    {
        $t = 9;
        return $t;
        
    }
}
?>