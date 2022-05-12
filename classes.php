<?php

class UserExistsException extends Exception {}
class UserNotFoundException extends Exception {}
class AttributeException extends Exception {}

class BezpecnostnaPrevierka
{
    public string $uroven = '';
    public readonly array $vsetky_urovne;
    public int $kto_udelil = 0;
    public string $datum = '';
    public bool $platnost = true;
    public static mysqli $mysqli;

    /**
     * @param int $id
     * @throws UserNotFoundException
     */
    public function __construct(public int $id=0) {
        if ($id > 0) $this->select();
        $type = self::$mysqli->query("SHOW COLUMNS FROM bezp_previerka WHERE Field = 'uroven'")->fetch_assoc()['Type'];
        preg_match("/^enum\('(.*)'\)$/", $type, $matches);
        $this->vsetky_urovne = explode("','", $matches[1]);
    }

    private function check_database(): bool {
        return !self::$mysqli->connect_errno;
    }

    /**
     * @return void
     * @throws UserNotFoundException
     */
    public function select(): void {
        if ($this->check_database()){
            $stmt = self::$mysqli->prepare("SELECT uroven, kto_udelil, datum, platnost FROM bezp_previerka WHERE id=?");
            $stmt->bind_param('i', $this->id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $result->free();
                $this->uroven = $row['uroven'];
                $this->kto_udelil = $row['kto_udelil'];
                $this->datum = $row['datum'];
                $this->platnost = $row['platnost'];
            } else throw new UserNotFoundException('Previerka with the specified id does not exist!');
        }
        else throw new Exception("Unknown error");
    }

    /**
     * @return void
     * @throws AttributeException
     */
    public function insert(): void {
        if ($this->kto_udelil == 0) throw new AttributeException("kto_udelil must be set");
        if (!in_array($this->uroven, $this->vsetky_urovne)) throw new AttributeException("Invalid uroven");
        if ($this->check_database()) {
            $stmt = self::$mysqli->prepare("INSERT INTO bezp_previerka(uroven, kto_udelil) VALUES(?, ?)");
            $stmt->bind_param('si', $this->uroven, $this->kto_udelil);
            $stmt->execute();
            if (!$stmt->errno) $this->id = $stmt->insert_id;
        }
        else throw new Exception("Unknown error");
    }

    /**
     * @return void
     * @throws AttributeException
     */
    public function update_platnost(): void {
        if ($this->id == 0) throw new AttributeException("id must be set");
        if ($this->check_database()) {
            $stmt = self::$mysqli->prepare("UPDATE bezp_previerka SET platnost=platnost^1 WHERE id=?");
            $stmt->bind_param('i', $this->id);
            $stmt->execute();
        }
        else throw new Exception("Unknown error");
    }

    /**
     * @return void
     * @throws AttributeException
     */
    public function update_uroven(): void {
        if ($this->id == 0) throw new AttributeException("id must be set");
        if ($this->kto_udelil == 0) throw new AttributeException("kto_udelil must be set");
        if (!in_array($this->uroven, $this->vsetky_urovne)) throw new AttributeException("Invalid uroven");
        if ($this->check_database()) {
            $stmt = self::$mysqli->prepare("UPDATE bezp_previerka SET uroven=?, kto_udelil=? WHERE id=?");
            $stmt->bind_param('sii', $this->uroven, $this->kto_udelil, $this->id);
            $stmt->execute();
        }
        else throw new Exception("Unknown error");
    }
}

class OsobneUdaje
{
    public ?int $id_previerka = null;
    public string $email = '';
    public string $titul = '';
    public string $meno = '';
    public string $priezvisko = '';
    public string $adresa = '';
    public static mysqli $mysqli;

    /**
     * @param int $id
     * @throws AttributeException
     * @throws UserNotFoundException
     */
    public function __construct(public int $id=0) {
        if ($id > 0) $this->select();
    }

    private function check_database(): bool {
        return !self::$mysqli->connect_errno;
    }

    /**
     * @return void
     * @throws AttributeException
     */
    private function check_attributes(): void {
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) throw new AttributeException("Email must be valid");
        if (strlen($this->email) > 50) throw new AttributeException("Email was too long.");
        if (strlen($this->meno) < 3 || strlen($this->priezvisko) < 3) throw new AttributeException("Names must be valid");
        if (strlen($this->meno . ' '. $this->priezvisko) > 100) throw new AttributeException("Name was too long");
        if (strlen($this->adresa) < 6 || strlen($this->adresa) > 100) throw new AttributeException("Address must be valid");
    }

    /**
     * @param string $str
     * @return string
     */
    private function sanitize(string $str): string {
        return trim(strip_tags($str));
    }

    private function sanitize_attributes(): void {
        $this->email = filter_var($this->email, FILTER_SANITIZE_EMAIL);
        $this->titul = $this->sanitize($this->titul);
        $this->meno = $this->sanitize($this->meno);
        $this->priezvisko = $this->sanitize($this->priezvisko);
        $this->adresa = $this->sanitize($this->adresa);
    }

    /**
     * @return void
     * @throws AttributeException
     * @throws UserExistsException
     */
    public function insert(): void {
        $this->sanitize_attributes();
        if ($this->email_exists()) throw new UserExistsException('This email is already in use!');
        $this->check_attributes();
        if ($this->check_database()) {
            $stmt = self::$mysqli->prepare('INSERT INTO osobne_udaje(email, meno, priezvisko, adresa, titul) VALUES(?, ?, ?, ?, ?)');
            $stmt->bind_param('sssss', $this->email, $this->meno, $this->priezvisko, $this->adresa, $this->titul);
            $stmt->execute();
            if (!$stmt->errno) $this->id = $stmt->insert_id;
        }
        else throw new Exception("Unknown error");
    }

    /**
     * @return void
     * @throws AttributeException
     * @throws UserNotFoundException
     */
    public function select(): void {
        if ($this->id == null) throw new AttributeException("id must be initialized");
        if ($this->check_database()) {
            $stmt = self::$mysqli->prepare('SELECT * FROM osobne_udaje WHERE osobne_udaje.id=?');
            $stmt->bind_param('i', $this->id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $result->free();
                $this->id_previerka = $row['id_previerka'];
                $this->email = $row['email'];
                $this->titul = $row['titul'];
                $this->meno = $row['meno'];
                $this->priezvisko = $row['priezvisko'];
                $this->adresa = $row['adresa'];
            } else throw new UserNotFoundException('User with the specified id does not exist!');
        }
        else throw new Exception("Unknown error");
    }

    /**
     * @param string $email
     * @return void
     * @throws UserNotFoundException
     */
    public function select_by_email(string $email): void {
        if ($this->check_database()) {
            $stmt = self::$mysqli->prepare('SELECT * FROM osobne_udaje WHERE osobne_udaje.email=?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $result->free();
                $this->id = $row['id'];
                $this->email = $row['email'];
                $this->titul = $row['titul'];
                $this->meno = $row['meno'];
                $this->priezvisko = $row['priezvisko'];
                $this->adresa = $row['adresa'];
            } else throw new UserNotFoundException('User with the specified email does not exist!');
        }
        else throw new Exception("Unknown error");
    }

    /**
     * @return bool
     */
    private function email_exists(): bool {
        if ($this->check_database()) {
            $stmt = self::$mysqli->prepare('SELECT id FROM osobne_udaje WHERE email=?');
            $stmt->bind_param('s', $this->email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $result->free();
                return true;
            }
            $result->free();
        }
        return false;
    }

    /**
     * @return void
     * @throws AttributeException
     * @throws UserExistsException
     * @throws UserNotFoundException
     */
    public function update(): void {
        if ($this->id == null) throw new AttributeException("id must be initialized");
        $this->sanitize_attributes();
        $this->check_attributes();
        if ($this->check_database()) {
            $old_udaje = new OsobneUdaje($this->id);
            if ($this->email_exists() && $old_udaje->email != $this->email) throw new UserExistsException("The specified email already exists!");
            $stmt = self::$mysqli->prepare("UPDATE osobne_udaje SET email=?,meno=?,priezvisko=?,adresa=?,titul=?,id_previerka=? WHERE osobne_udaje.id=?");
            $stmt->bind_param('sssssii', $this->email, $this->meno, $this->priezvisko, $this->adresa, $this->titul, $this->id_previerka, $this->id);
            $stmt->execute();
            /** @noinspection PhpNonStrictObjectEqualityInspection */
            if ($stmt->affected_rows != 1 && $this != $old_udaje) throw new UserNotFoundException('User with the specified id does not exist!');
        }
        else throw new Exception("Unknown error");
    }

    /**
     * @return void
     * @throws AttributeException
     * @throws UserNotFoundException
     */
    public function delete(): void {
        if ($this->id == null) throw new AttributeException("id must be initialized");
        if ($this->check_database()){
            $stmt = self::$mysqli->prepare("DELETE FROM osobne_udaje WHERE id=?");
            $stmt->bind_param('i', $this->id);
            $stmt->execute();
            if ($stmt->affected_rows > 1) throw new Exception("nieco je velmi zle!!");
            else if ($stmt->affected_rows == 0) throw new UserNotFoundException('User with the specified id does not exist!');
        }
        else throw new Exception("Unknown error");
    }
}

class Admin
{
    public int $id_udaje = 0;
    public ?OsobneUdaje $udaje = null;
    public static mysqli $mysqli;

    /**
     * @param int $id
     * @throws AttributeException
     * @throws UserNotFoundException
     */
    public function __construct(public int $id=0) {
        if ($id > 0) $this->select();
    }

    private function check_database(): bool {
        return !self::$mysqli->connect_errno;
    }

    /**
     * @return void
     * @throws AttributeException
     */
    private function check_attributes(): void {
        if ($this->udaje == null) throw new AttributeException("udaje must be initialized");
    }

    /**
     * @return void
     * @throws AttributeException
     * @throws UserNotFoundException
     */
    public function select(): void {
        if ($this->id == null) throw new AttributeException("id must be initialized");
        if ($this->check_database()) {
            $stmt = self::$mysqli->prepare("SELECT id_udaje FROM admin WHERE admin.id=?");
            $stmt->bind_param('i', $this->id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $result->free();
                $this->id_udaje = $row['id_udaje'];
                $this->udaje = new OsobneUdaje($this->id_udaje);
            }
            else throw new UserNotFoundException('User with the specified id does not exist!');
        }
        else throw new Exception("Unknown error");
    }

    /**
     * @param string $heslo
     * @return void
     * @throws AttributeException
     * @throws UserExistsException
     */
    public function insert(string $heslo): void {
        $this->check_attributes();
        if ($this->check_database()) {
            $this->udaje->insert();
            $udaje_id = $this->udaje->id;
            $stmt = self::$mysqli->prepare("INSERT INTO admin(id_udaje, heslo) VALUES(?, ?)");
            $password_hash = password_hash($heslo, PASSWORD_DEFAULT);
            $stmt->bind_param('is', $udaje_id, $password_hash);
            $stmt->execute();
            if (!$stmt->errno) $this->id = $stmt->insert_id;
        }
        else throw new Exception("Unknown error");
    }

    /**
     * @param string $heslo
     * @return void
     * @throws AttributeException
     */
    public function update_heslo(string $heslo): void {
        if ($this->id == null) throw new AttributeException("id must be initialized");
        $stmt = self::$mysqli->prepare("UPDATE admin SET heslo=? WHERE id=?");
        $password_hash = password_hash($heslo, PASSWORD_DEFAULT);
        $stmt->bind_param('si', $password_hash, $this->id);
        $stmt->execute();
    }

    /**
     * @param string $email
     * @param string $heslo
     * @return bool
     * @throws AttributeException
     */
    public function login(string $email, string $heslo): bool {
        try {
            $udaje = new OsobneUdaje();
            $udaje->select_by_email($email);
            $stmt = self::$mysqli->prepare("SELECT heslo, id FROM admin WHERE admin.id_udaje=?");
            $stmt->bind_param('i', $udaje->id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            if ($result->num_rows != 1) return false;
            else {
                $row = $result->fetch_assoc();
                $result->free();
                if (password_verify($heslo, $row['heslo'])) {
                    $this->id = $row['id'];
                    $this->select();
                    return true;
                }
            }
        }
        catch (UserNotFoundException) {
            return false;
        }
        return false;
    }

    /**
     * @return int
     * @throws AttributeException
     * @throws UserNotFoundException
     */
    public function delete(): int {
        if ($this->id == null) throw new AttributeException("id must be initialized");
        if ($this->check_database()) {
            $stmt = self::$mysqli->prepare("DELETE FROM admin WHERE admin.id=?");
            $stmt->bind_param('i', $this->id);
            $stmt->execute();
            if ($stmt->affected_rows > 1) throw new Exception("nieco je velmi zle!!");
            else if ($stmt->affected_rows == 0) throw new UserNotFoundException('User with the specified id does not exist!');
            $this->udaje->delete();
        }
        throw new Exception("Unknown error");
    }
}

class Poslanec
{
    public int $id_udaje = 0;
    public int $id_klub = 1;
    public array $specializacia = array();
    private array $vsetky_specializacie;
    public ?OsobneUdaje $udaje = null;
    public static mysqli $mysqli;

    /**
     * @param int $id
     * @throws AttributeException
     * @throws UserNotFoundException
     */
    public function __construct(public int $id=0) {
        if ($id > 0) $this->select();
        $type = self::$mysqli->query("SHOW COLUMNS FROM poslanec WHERE Field = 'specializacia'")->fetch_assoc()['Type'];
        preg_match("/^set\('(.*)'\)$/", $type, $matches);
        $this->vsetky_specializacie =  explode("','", $matches[1]);
    }

    private function check_database(): bool {
        return !self::$mysqli->connect_errno;
    }

    /**
     * @return void
     * @throws AttributeException
     */
    private function check_attributes(): void {
        if ($this->udaje == null) throw new AttributeException("udaje must be initialized");
        foreach ($this->specializacia as $sp) {
            if (!in_array($sp, $this->vsetky_specializacie)) throw new AttributeException("Invalid specializacia");
        }
    }

    /**
     * @return void
     * @throws AttributeException
     * @throws UserNotFoundException
     */
    public function select(): void {
        if ($this->id == null) throw new AttributeException("id must be initialized");
        if ($this->check_database()) {
            $stmt = self::$mysqli->prepare("SELECT id_udaje, id_klub, specializacia FROM poslanec WHERE poslanec.id=?");
            $stmt->bind_param('i', $this->id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $result->free();
                $this->id_udaje = $row['id_udaje'];
                $this->id_klub = $row['id_klub'];
                $this->specializacia = explode(',', $row['specializacia']);
                $this->udaje = new OsobneUdaje($this->id_udaje);
            }
            else throw new UserNotFoundException('User with the specified id does not exist!');
        }
        else throw new Exception("Unknown error");
    }

    /**
     * @param string $heslo
     * @return void
     * @throws AttributeException
     * @throws UserExistsException
     */
    public function insert(string $heslo): void {
        $this->check_attributes();
        if ($this->check_database()) {
            $this->udaje->insert();
            $udaje_id = $this->udaje->id;
            $stmt = self::$mysqli->prepare("INSERT INTO poslanec(id_udaje, id_klub, specializacia, heslo) VALUES(?, ?, ?, ?)");
            $password_hash = password_hash($heslo, PASSWORD_DEFAULT);
            $spec_str = implode(',', $this->specializacia);
            $stmt->bind_param('iiss', $udaje_id, $this->id_klub, $spec_str, $password_hash);
            $stmt->execute();
            if (!$stmt->errno) $this->id = $stmt->insert_id;
        }
        else throw new Exception("Unknown error");
    }

    /**
     * @return void
     * @throws AttributeException
     * @throws UserExistsException
     * @throws UserNotFoundException
     */
    public function update(): void {
        if ($this->id == null) throw new AttributeException("id must be initialized");
        $this->check_attributes();
        if ($this->check_database()) {
            $this->udaje->update();
            $old_poslanec = new Poslanec($this->id);
            if ($old_poslanec->specializacia != $this->specializacia) $this->update_specializacia();
            if ($old_poslanec->id_klub != $this->id_klub) $this->update_klub();
        }
        else throw new Exception("Unknown error");
    }

    private function update_specializacia(): void {
        $stmt = self::$mysqli->prepare("UPDATE poslanec SET specializacia=? WHERE id=?");
        $spec_str = implode(',', $this->specializacia);
        $stmt->bind_param('si', $spec_str, $this->id);
        $stmt->execute();
    }

    private function update_klub(): void {
        $stmt = self::$mysqli->prepare("UPDATE poslanec SET id_klub=? WHERE id=?");
        $stmt->bind_param('si', $this->id_klub, $this->id);
        $stmt->execute();
    }

    /**
     * @param string $heslo
     * @return void
     * @throws AttributeException
     */
    public function update_heslo(string $heslo): void {
        if ($this->id == null) throw new AttributeException("id must be initialized");
        $stmt = self::$mysqli->prepare("UPDATE poslanec SET heslo=? WHERE id=?");
        $password_hash = password_hash($heslo, PASSWORD_DEFAULT);
        $stmt->bind_param('si', $password_hash, $this->id);
        $stmt->execute();
    }

    /**
     * @param string $email
     * @param string $heslo
     * @return bool
     * @throws AttributeException
     */
    public function login(string $email, string $heslo): bool {
        try {
            $udaje = new OsobneUdaje();
            $udaje->select_by_email($email);
            $stmt = self::$mysqli->prepare("SELECT heslo, id FROM poslanec WHERE poslanec.id_udaje=?");
            $stmt->bind_param('i', $udaje->id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            if ($result->num_rows != 1) return false;
            else {
                $row = $result->fetch_assoc();
                $result->free();
                if (password_verify($heslo, $row['heslo'])) {
                    $this->id = $row['id'];
                    $this->select();
                    return true;
                }
            }
        }
        catch (UserNotFoundException) {
            return false;
        }
        return false;
    }

    /**
     * @return void
     * @throws AttributeException
     * @throws UserNotFoundException
     */
    public function delete(): void {
        if ($this->id == null) throw new AttributeException("id must be initialized");
        if ($this->check_database()) {
            $stmt = self::$mysqli->prepare("DELETE FROM poslanec WHERE poslanec.id=?");
            $stmt->bind_param('i', $this->id);
            $stmt->execute();
            if ($stmt->affected_rows > 1) throw new Exception("nieco je velmi zle!!");
            else if ($stmt->affected_rows == 0) throw new UserNotFoundException('User with the specified id does not exist!');
            $this->udaje->delete();
        }
        else throw new Exception("Unknown error");
    }
}